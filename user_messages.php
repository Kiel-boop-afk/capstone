<?php
session_start();
require 'db.php'; // Database connection

// Check if the user is logged in
if (!isset($_SESSION['username']) || !isset($_SESSION['clinic_id'])) {
    header("Location: login.php");
    exit();
}

$user_clinic_id = $_SESSION['clinic_id']; // Get the clinic ID of the logged-in user

// Fetch pending referrals sent to the logged-in clinic where user_role is "clinic"
$sql_query = "SELECT c.id AS confirmation_id, c.refer_from, c.patient_id, p.name AS patient_name, c.reason, cl_from.name AS from_clinic_name
              FROM confirmationtb c
              LEFT JOIN patienttb p ON c.patient_id = p.patient_id
              LEFT JOIN clinictb cl_from ON c.refer_from = cl_from.id
              WHERE c.refer_to = ? AND c.is_confirmed = 0";
$stmt = $conn->prepare($sql_query);
$stmt->bind_param("s", $user_clinic_id); // Use 's' since clinic_id is a string
$stmt->execute();
$result = $stmt->get_result(); // Fetch the result set

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirm_referral'])) {
    $confirmation_id = $_POST['confirmation_id'];
    $patient_id = $_POST['patient_id'];
    $refer_from = $_POST['refer_from']; // Get the refer_from value

    // Update the patient's clinic ID in patienttb and mark referral as confirmed
    $update_patient_sql = "UPDATE patienttb SET referred_to = ?, referred_from = ?, currently_at = ? WHERE patient_id = ?";
    $stmt_update = $conn->prepare($update_patient_sql);
    $stmt_update->bind_param("ssss", $user_clinic_id, $refer_from, $user_clinic_id, $patient_id); // Use 's' for all parameters since they are strings
    
    if ($stmt_update->execute()) {
        // Mark the referral as confirmed in confirmationtb
        $update_confirmation_sql = "UPDATE confirmationtb SET is_confirmed = 1 WHERE id = ?";
        $stmt_confirm = $conn->prepare($update_confirmation_sql);
        $stmt_confirm->bind_param("s", $confirmation_id); // Use 's' for confirmation_id since it's a string
        $stmt_confirm->execute();
        $stmt_confirm->close();

        // Store success message in session and redirect back to user_messages.php
        $_SESSION['success_message'] = "Referral confirmed successfully!";
        header("Location: user_messages.php");
        exit();
    } else {
        echo "Error: " . $stmt_update->error;
    }

    $stmt_update->close();
}

// SQL query to fetch pending appointment requests for the logged-in clinic
$sql_pending_appointments = "SELECT 
            arc.request_id AS appointment_request_id, 
            p.name AS patient_full_name, 
            arc.clinic_id AS associated_clinic_id, 
            arc.date_and_time_created AS request_date_time, 
            arc.status AS appointment_status 
        FROM 
            appointment_request_confirmationtb arc 
        JOIN 
            patienttb p ON arc.patient_id = p.patient_id 
        WHERE 
            arc.clinic_id = ? AND arc.status = 'pending'";

$stmt_pending_appointments = $conn->prepare($sql_pending_appointments);
$stmt_pending_appointments->bind_param("s", $user_clinic_id); // Use 's' for clinic_id as it's a string
$stmt_pending_appointments->execute();
$result_pending_appointments = $stmt_pending_appointments->get_result();

// Initialize variables
$request_id = $patient_id = $patient_name = "";

if (isset($_GET['edit'])) {
    $request_id = $_GET['edit']; // Get the request_id from the URL

    // Fetch appointment request details using request_id
    $sql_fetch_request = "SELECT arc.request_id, arc.patient_id, p.name AS patient_name
                          FROM appointment_request_confirmationtb arc
                          JOIN patienttb p ON arc.patient_id = p.patient_id
                          WHERE arc.request_id = ?";

    $stmt_fetch_request = $conn->prepare($sql_fetch_request);
    $stmt_fetch_request->bind_param("s", $request_id); // Use 's' for request_id since it's a string
    $stmt_fetch_request->execute();
    $result_fetch_request = $stmt_fetch_request->get_result();

    if ($result_fetch_request->num_rows > 0) {
        // Fetch the appointment request details
        $appointment_request_details = $result_fetch_request->fetch_assoc();
        // Pre-fill variables with the fetched data
        $request_id = $appointment_request_details['request_id'];
        $patient_id = $appointment_request_details['patient_id']; // Assign patient_id here
        $patient_name = $appointment_request_details['patient_name'];
    } else {
        echo "Invalid Request ID.";
        exit();
    }

    $stmt_fetch_request->close(); // Close the prepared statement
}

// Fetch doctors from the doctortb table
$sql_doctors = "SELECT doctor_id, doctor_name FROM doctortb"; 
$result_doctors = $conn->query($sql_doctors);
if (!$result_doctors) {
    die("Database query failed: " . $conn->error); // Check for errors in the query
}

$stmt->close();
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="images/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="images/favicon.svg" />
    <link rel="shortcut icon" href="images/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="images/apple-touch-icon.png" />
    <link rel="stylesheet" href="css/user_style.css">
    <link rel="stylesheet" href="css/loading_screen_style.css">
    <title>Message</title>
    <!-- Display success message with a fading effect -->
<?php if (isset($_SESSION['success_message'])): ?>
    <div class="popup-message">
        <?php echo $_SESSION['success_message']; ?>
    </div>
    <?php unset($_SESSION['success_message']); // Clear the message after displaying it ?>
<?php endif; ?>
</head>
<body>
    <!-- Loading Screen -->
<div class="loading-overlay">
    <div class="pulse"></div>
</div>

    <!-- header -->
    <?php $active = 'messages'; include('includes/header.php') ?>

      <main>
      <div class="container">
    <h2>Pending Referrals Received</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Patient Name</th>
            <th>From </th>
            <th>Reason</th>
            <th>Action</th>
        </tr>

        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['confirmation_id']; ?></td>
                    <td><?php echo htmlspecialchars($row['patient_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['from_clinic_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['reason']); ?></td>
                    <td>
                        <form method="post" action="">
                            <input type="hidden" name="confirmation_id" value="<?php echo $row['confirmation_id']; ?>">
                            <input type="hidden" name="patient_id" value="<?php echo $row['patient_id']; ?>">
                            <input type="hidden" name="refer_from" value="<?php echo $row['refer_from']; ?>"> <!-- Added hidden input for refer_from -->
                            <button type="submit" name="confirm_referral">Confirm Referral</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="5">No pending referrals.</td>
            </tr>
        <?php endif; ?>
    </table>
</div>

<div class="container">
    <div>
    <h2>Pending Appointment Requests (check-ups)</h2>
    <div class="buttonsdiv">
            <button onclick="openForm1()">APPOINT</button>
        </div>
    </div>
    <table>
        <tr>
            <th>Request ID</th>
            <th>Patient Name</th>
            <th>Date Requested</th>
            <th>Status</th>
            <th>Action</th>
        </tr>

        <?php if ($result_pending_appointments->num_rows > 0): ?>
            <?php while ($appointment_row = $result_pending_appointments->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $appointment_row['appointment_request_id']; ?></td>
                    <td><?php echo htmlspecialchars($appointment_row['patient_full_name']); ?></td>
                    <td><?php echo htmlspecialchars($appointment_row['request_date_time']); ?></td>
                    <td><?php echo htmlspecialchars($appointment_row['appointment_status']); ?></td>
                    <!-- Redirect to the same page with the selected request_id -->
                    <td><a href="user_messages.php?edit=<?php echo $appointment_row['appointment_request_id']; ?>">SELECT</a></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="5">No pending appointment requests.</td>
            </tr>
        <?php endif; ?>
    </table>
</div>

<!-- Form to confirm appointment -->
<div id="popupForm1" class="popup-form-container">
    <div class="popup-form">
        <span class="close-btn" onclick="closeForm1()">&times;</span>
        <h2>Confirm Appointment</h2>

        <form action="confirm_appointment.php" method="post">
    
    <?php if (!isset($request_id) || empty($request_id)): ?>
       <p style="color: red;">Error: No request selected. Please select a request before proceeding.</p>
    <?php else: ?>
       <!-- Hidden field for request_id -->
       <input type="hidden" name="request_id" value="<?php echo htmlspecialchars($request_id); ?>">
       <!-- Hidden field for patient_id -->
    <input type="hidden" name="patient_id" value="<?php echo htmlspecialchars($patient_id); ?>">

<!-- Show Patient Name -->
<label>Patient Name:</label>
<input type="text" name="patient_name" value="<?php echo htmlspecialchars($patient_name); ?>" readonly>


        <!-- Choose appointment date -->
            <label for="appointment_date">Appointment Date:</label>
            <input type="datetime-local" name="appointment_date" required><br>

              <!-- Dropdown for selecting a doctor (optional) -->
              <label for="doctor_id">Select Doctor:</label>
<select id="doctor_id" name="doctor_id">
<option value="none" selected>Select a doctor (optional)</option>
<?php
while ($doctor = $result_doctors->fetch_assoc()): ?>
    <option value="<?php echo $doctor['doctor_id']; ?>">
        <?php echo htmlspecialchars($doctor['doctor_name']); ?>
    </option>
<?php endwhile; ?>
</select>

        <div class="form-buttons">
            <button type="submit" name="appoint_request">Confirm Appointment</button>
        </div>
    <?php endif; ?>


    
        </form>
    </div>
</div>


    </main>

</body>
<footer>&copy; 2024 Your Clinic</footer>
</html>

<script>
    // Function to open the first popup form (appoint form)
    function openForm1() {
        document.getElementById("popupForm1").style.display = "flex";
    }

    // Function to close the first popup form (appoint form)
    function closeForm1() {
        document.getElementById("popupForm1").style.display = "none";
    }

document.addEventListener("DOMContentLoaded", function() {
    var message = document.querySelector('.popup-message');
    if (message) {
        message.style.display = 'block';
        setTimeout(function() {
            message.style.opacity = '1';
        }, 100); // Fade-in effect
        
        setTimeout(function() {
            message.style.opacity = '0'; // Fade-out effect
            setTimeout(function() {
                message.style.display = 'none';
            }, 1000); // Time for the fade-out to complete
        }, 3000); // Keep the message visible for 3 seconds
    }
});
 
</script>
<script src="js/loading_screen.js"></script>