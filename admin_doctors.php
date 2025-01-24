<?php
session_start();
require 'db.php'; // Include your database connection

// Check if admin is logged in
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php"); // Redirect to login if not logged in or not an admin
    exit();
}

// Fetch the list of doctors from the database
$sql = "SELECT * FROM doctortb";
$result = $conn->query($sql);

// Handle the form submission for updating or adding a doctor's details
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $doctor_id = $_POST['doctor_id']; // Get doctor ID from hidden input
    $doctor_name = trim($_POST['doctor_name']);
    $contact_number = trim($_POST['contact_number']);
    $doctor_email = trim($_POST['doctor_email']);

    // Validate inputs
    if (empty($doctor_name) || empty($contact_number) || empty($doctor_email)) {
        echo "All fields are required!";
    } else {
        if ($doctor_id) {
            // Update the doctor's details if doctor_id is set
            $update_sql = "UPDATE doctortb SET doctor_name = ?, contact_number = ?, doctor_email = ? WHERE doctor_id = ?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("ssss", $doctor_name, $contact_number, $doctor_email, $doctor_id);

            if ($stmt->execute()) {
                header("Location: admin_doctors.php?success=Doctor updated successfully");
                exit();
            } else {
                echo "Error updating doctor: " . $stmt->error;
            }
        } else {
            // Insert a new doctor if doctor_id is not set
            $insert_sql = "INSERT INTO doctortb (doctor_name, contact_number, doctor_email) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($insert_sql);
            $stmt->bind_param("sss", $doctor_name, $contact_number, $doctor_email);

            if ($stmt->execute()) {
                header("Location: admin_doctors.php?success=Doctor added successfully");
                exit();
            } else {
                echo "Error adding doctor: " . $stmt->error;
            }
        }
    }
}

// Check if a doctor needs to be edited (doctor_id is provided in the URL)
$editing_doctor = null;
if (isset($_GET['edit_id'])) {
    $doctor_id = $_GET['edit_id'];

    // Fetch the doctor's details for editing
    $edit_sql = "SELECT * FROM doctortb WHERE doctor_id = ?";
    $stmt = $conn->prepare($edit_sql);
    $stmt->bind_param("s", $doctor_id);
    $stmt->execute();
    $result_edit = $stmt->get_result();

    if ($result_edit->num_rows > 0) {
        $editing_doctor = $result_edit->fetch_assoc();
    } else {
        echo "No doctor found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/admin_clinic_and_personnel_style.css">
    <link rel="icon" type="image/png" href="images/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="images/favicon.svg" />
    <link rel="shortcut icon" href="images/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="images/apple-touch-icon.png" />
    <title>Manage Doctors</title>
</head>
<body>
<a href="admin_home.php">Back to Admin Dashboard</a>
<h2>Manage Doctors</h2>

<div class="container">
    <div class="form-container">
    <h1><?php echo $editing_doctor ? 'Edit Doctor' : 'Add Doctor'; ?></h1>
        <!-- Doctor Add/Edit Form -->
        <form action="admin_doctors.php" method="POST">
            <input type="hidden" name="doctor_id" value="<?php echo $editing_doctor ? htmlspecialchars($editing_doctor['doctor_id']) : ''; ?>">

            <label for="doctor_name">Doctor Name:</label><br>
            <input type="text" id="doctor_name" name="doctor_name" value="<?php echo $editing_doctor ? htmlspecialchars($editing_doctor['doctor_name']) : ''; ?>" required><br><br>

            <label for="contact_number">Contact Number:</label><br>
            <input type="text" id="contact_number" name="contact_number" value="<?php echo $editing_doctor ? htmlspecialchars($editing_doctor['contact_number']) : ''; ?>" required><br><br>

            <label for="doctor_email">Email:</label><br>
            <input type="email" id="doctor_email" name="doctor_email" value="<?php echo $editing_doctor ? htmlspecialchars($editing_doctor['doctor_email']) : ''; ?>" required><br><br>

            <button type="submit"><?php echo $editing_doctor ? 'Update Doctor' : 'Add Doctor'; ?></button>
        </form>

        <br>
        <?php if ($editing_doctor): ?>
            <button onclick="window.location.href='admin_doctors.php'">Cancel Edit</button>
        <?php endif; ?>
        <hr>
    </div>

    <div class="table-container">
        <!-- Table for displaying all doctors -->
        <h2>Doctors List</h2>
        <table border="1">
            <tr>
                <th>Doctor ID</th>
                <th>Name</th>
                <th>Contact Number</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
            <?php
            if ($result->num_rows > 0) {
                while ($doctor = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($doctor['doctor_id']) . "</td>";
                    echo "<td>" . htmlspecialchars($doctor['doctor_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($doctor['contact_number']) . "</td>";
                    echo "<td>" . htmlspecialchars($doctor['doctor_email']) . "</td>";
                    echo "<td>
                            <a href='admin_doctors.php?edit_id=" . urlencode($doctor['doctor_id']) . "'>Edit</a>
                          </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5'>No doctors found</td></tr>";
            }
            ?>
        </table>
    </div>
</body>
</html>

<?php
$conn->close();
?>
