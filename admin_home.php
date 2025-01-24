<?php
session_start();
include 'db.php'; 

// Ensure the user is logged in and is an admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");  // Redirect to login if not logged in or not an admin
    exit();
}

// Check if the success parameter is set in the URL
$successMessage = "";
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $successMessage = "Doctor successfully added!";
}


// Fetch login history for today (users)
$date_today = date('Y-m-d');
$sql_user = "
    SELECT ulh.*, u.username 
    FROM login_history ulh 
    LEFT JOIN usertb u ON ulh.user_id = u.user_id 
    WHERE DATE(ulh.login_time) = ? 
    ORDER BY ulh.login_time DESC";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("s", $date_today);
$stmt_user->execute();
$result_user = $stmt_user->get_result();

// Fetch login history for today (doctors)
$date_today = date('Y-m-d');
$sql_doctor = "
    SELECT dlh.*, d.doctor_name 
    FROM doctor_login_history dlh 
    LEFT JOIN doctortb d ON dlh.doctor_id = d.doctor_id 
    WHERE DATE(dlh.login_time) = ? 
    ORDER BY dlh.login_time DESC";
$stmt_doctor = $conn->prepare($sql_doctor);
$stmt_doctor->bind_param("s", $date_today);
$stmt_doctor->execute();
$result_doctor = $stmt_doctor->get_result();

// Fetch login history for today (patients)
$date_today = date('Y-m-d');
$sql_patient = "
    SELECT plh.*, p.patient_id, p.first_name, p.last_name 
    FROM patient_login_history plh 
    LEFT JOIN patienttb p ON plh.patient_id = p.patient_id 
    WHERE DATE(plh.login_time) = ? 
    ORDER BY plh.login_time DESC";
$stmt_patient = $conn->prepare($sql_patient);
$stmt_patient->bind_param("s", $date_today);
$stmt_patient->execute();
$result_patient = $stmt_patient->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/admin_dashboard_style.css">
    <link rel="icon" type="image/png" href="images/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="images/favicon.svg" />
    <link rel="shortcut icon" href="images/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="images/apple-touch-icon.png" />
    <title>Admin Dashboard</title>
    </head>
<body>

<div class="dashboard">
    <!-- Logout button -->
    <div class="logout-button">
    <form action="logout.php" method="POST">
        <button type="submit">Logout</button>
    </form>
</div>
    <h2>Welcome to Admin Dashboard</h2>

    <!-- Success message popup -->
    <?php if (!empty($successMessage)): ?>
        <div class="popup-message" id="popupMessage"><?php echo $successMessage; ?></div>
    <?php endif; ?>
    
    <!-- Admin dashboard buttons -->
    <button class="dashboard-btn" onclick="window.location.href='login_history.php'">View Login History</button>
    <button class="dashboard-btn" onclick="window.location.href='create_user.php'">Create User</button>
    <button class="dashboard-btn" onclick="window.location.href='admin_clinic.php'">Add or Manage Clinics</button>
    <button class="dashboard-btn" onclick="window.location.href='admin_personnel.php'">Manage Personnel</button>
    <button class="dashboard-btn" onclick="window.location.href='admin_adddoctor.php'">Add Doctor</button>
    <button class="dashboard-btn" onclick="window.location.href='admin_doctors.php'">Manage Doctors</button>


    <h3>Clinic Personnel User Login History for Today (<?php echo $date_today; ?>)</h3>
    <table>
    <thead>
        <tr>
            <th>User ID</th>
            <th>Username</th>
            <th>Login Time</th>
            <th>Success</th> <!-- New column for success status -->
        </tr>
    </thead>
    <tbody>
        <?php if ($result_user->num_rows > 0): ?>
            <?php while ($row = $result_user->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['user_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                    <td><?php echo htmlspecialchars($row['login_time']); ?></td>
                    <td><?php echo $row['success'] ? 'Yes' : 'No'; ?></td> 
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="4" style="text-align: center; color: #999;">No records found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>


     <!-- Display doctor login history for today -->
     <h3>Doctor Login History for Today (<?php echo $date_today; ?>)</h3>
     <table>
    <thead>
        <tr>
            <th>Doctor ID</th>
            <th>Doctor Name</th>
            <th>Login Time</th>
            <th>Success</th> <!-- New column for success status -->
        </tr>
    </thead>
    <tbody>
        <?php if ($result_doctor->num_rows > 0): ?>
            <?php while ($row = $result_doctor->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['doctor_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['doctor_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['login_time']); ?></td>
                    <td><?php echo $row['success'] ? 'Yes' : 'No'; ?></td> <!-- Success column -->
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="4" style="text-align: center; color: #999;">No records found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

    <h3>Patient Login History for Today (<?php echo $date_today; ?>)</h3>
    <table>
    <thead>
        <tr>
            <th>Patient ID</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Login Time</th>
            <th>Success</th> <!-- New column for success status -->
        </tr>
    </thead>
    <tbody>
        <?php if ($result_patient->num_rows > 0): ?>
            <?php while ($row = $result_patient->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['patient_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['first_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['login_time']); ?></td>
                    <td><?php echo $row['success'] ? 'Yes' : 'No'; ?></td> <!-- Success column -->
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="5" style="text-align: center; color: #999;">No records found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>


</div>

<script>
    // Show the popup message if it exists and make it fade out after 3 seconds
    window.onload = function() {
        var popupMessage = document.getElementById('popupMessage');
        if (popupMessage) {
            popupMessage.classList.add('show');
        }
    };
</script>

</body>
</html>