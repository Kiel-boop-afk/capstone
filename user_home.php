<?php
session_start();
// Ensure the user is logged in
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || !isset($_SESSION['clinic_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
require 'db.php'; // Include your database connection script

// Get the user's clinic ID from the session
$clinic_id = $_SESSION['clinic_id'];

// Count the number of personnel for the user's clinic
$count_sql = "SELECT COUNT(*) as personnel_count FROM personneltb WHERE clinic_id = ?";
$count_stmt = $conn->prepare($count_sql);
$count_stmt->bind_param("s", $clinic_id);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$personnel_count = $count_result->fetch_assoc()['personnel_count'];

// Count the number of patients for the user's clinic
$patient_count_sql = "SELECT COUNT(*) as patient_count FROM patienttb WHERE currently_at = ?";
$patient_count_stmt = $conn->prepare($patient_count_sql);
$patient_count_stmt->bind_param("s", $clinic_id);
$patient_count_stmt->execute();
$patient_count_result = $patient_count_stmt->get_result();
$patient_count = $patient_count_result->fetch_assoc()['patient_count'];

// Count the number of appointments for today at the user's clinic
$appointments_today_sql = "
    SELECT COUNT(*) as appointment_count 
    FROM appointmenttb 
    WHERE clinic_id = ? 
      AND DATE(date_and_time) = CURDATE()";
      
$appointments_today_stmt = $conn->prepare($appointments_today_sql);
$appointments_today_stmt->bind_param("s", $clinic_id); // Bind the clinic ID
$appointments_today_stmt->execute();
$appointments_today_result = $appointments_today_stmt->get_result();
$appointments_today_count = $appointments_today_result->fetch_assoc()['appointment_count'];


// Fetch personnel data for the user's clinic
$sql = "SELECT personnel_name, personnel_position FROM personneltb WHERE clinic_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $clinic_id);
$stmt->execute();
$result = $stmt->get_result();



// Query to fetch today's appointments from the appointmenttb table filtered by clinic ID
$sql_query = "SELECT a.id, p.name, a.date_and_time, a.reason 
              FROM appointmenttb a
              LEFT JOIN patienttb p ON a.patient_id = p.patient_id
              WHERE DATE(a.date_and_time) = CURDATE() AND a.clinic_id = ?";

// Prepare and execute the query
$prepared_stmt = $conn->prepare($sql_query);
$prepared_stmt->bind_param("s", $clinic_id);  // Only bind clinic_id as today is handled by CURDATE()
$prepared_stmt->execute();

// Fetch the result set
$result_set = $prepared_stmt->get_result();


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
    <link rel="stylesheet" href="css/homestyle.css">
    <link rel="stylesheet" href="css/loading_screen_style.css">
    <title>Clinic Dashboard</title>
</head>
<body>
    <!-- Loading Screen -->
<div class="loading-overlay">
    <div class="pulse"></div>
</div>
    <!-- header -->
    <?php $active = 'home'; include('includes/header.php') ?>

        <main>
        <div class="stat-section">
                    <div class="stat-box">
                        <h3>Patients</h3>
                        <div class="count"><?php echo $patient_count; ?></div>
                    </div>
                    <div class="stat-box">
                        <h3>Today's Appointment</h3>
                        <div class="count"><?php echo $appointments_today_count; ?></div>
                    </div>
                    <div class="stat-box">
                        <h3>Personnel</h3>
                        <div class="count"><?php echo $personnel_count; ?></div>
                    </div>
                </div>
                <div class="personnel-section">
    <h2>Personnel</h2>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Position</th>
                </tr>
            </thead>
            <tbody>
                <!-- PHP code to fetch and display personnel data -->
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr><td>" . htmlspecialchars($row['personnel_name']) . "</td><td>" . htmlspecialchars($row['personnel_position']) . "</td></tr>";
                    }
                } else {
                    echo "<tr><td colspan='2'>No personnel found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

    <div class="appointments-section">
        <h2>Today's Appointments</h2>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Patient</th>
                        <th>Schedule</th>
                        <th>Reason</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- PHP code to display today's appointments -->
                    <?php if ($result_set->num_rows > 0): ?>
                        <?php while ($appointment_row = $result_set->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $appointment_row['id']; ?></td>
                                <td><?php echo htmlspecialchars($appointment_row['name']); ?></td>
                                <td><?php echo date('M d, Y H:i', strtotime($appointment_row['date_and_time'])); ?></td>
                                <td><?php echo htmlspecialchars($appointment_row['reason']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">No appointments scheduled for today.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
                        
        </main>
    <footer>
        &copy; 2024 Your Clinic
    </footer>
</body>
<script src="js/loading_screen.js"></script>
</html>

