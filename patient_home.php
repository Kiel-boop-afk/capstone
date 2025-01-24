<?php
session_start();
require 'db.php'; // Include your database connection

// Check if the user is logged in as a patient
if (!isset($_SESSION['patient_id']) || $_SESSION['role'] !== 'patient') {
    header("Location: login.php");
    exit();
}

// If logged in as patient, continue with the page logic
$patient_id = $_SESSION['patient_id'];

// Retrieve patient details from the database
$sql = "SELECT first_name, last_name FROM patienttb WHERE patient_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $patient_id);
$stmt->execute();
$result = $stmt->get_result();

// Initialize name variable
$name = "";

if ($result->num_rows > 0) {
    // Fetch patient information
    $patient = $result->fetch_assoc();
    $name = trim($patient['first_name'] . ' ' . $patient['last_name']); // Combine first and last name
} else {
    // Handle case where no patient is found
    $name = "Patient"; // Default name if no record found
}

// Close the statement
$stmt->close();
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
    <title>Patient Home</title>
</head>
<body>
<header>
        <div class="header-container">
        <div class="header-logo">
            <img src="images/logo.png" alt="Clinic Logo">
            <p><?php echo htmlspecialchars($name); ?></p>
        </div>
                <nav class="header-nav">
                <a href="patient_home.php" data-active>Home</a> 
          <a href="patient_appointment.php">Appointment</a>
          <a href="patient_profile.php">Profile</a>
                </nav>
                <form action="logout.php" method="POST">
                    <button class="exit" type="submit">Logout</button>
                </form>
        </div>
      </header>
      <footer>&copy; 2024</footer>
</body>