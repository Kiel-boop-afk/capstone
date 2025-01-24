<?php
session_start();
require 'db.php'; // Include your database connection

// Check if the user is logged in
if (!isset($_SESSION['username']) || !isset($_SESSION['clinic_id'])) {
    header("Location: login.php");
    exit();
}

// Handle form submission for adding an appointment
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_appointment'])) {
    $clinic_id = $_SESSION['clinic_id'];   // Get clinic ID from the session
    $patient_id = $_POST['patient_id'];    // Patient's ID (selected from dropdown)
    $reason = $_POST['reason'];            // Reason for appointment
    $date_and_time = $_POST['date_and_time']; // Appointment date and time
    
    // Check if a doctor is selected; if not, set to NULL
    $doctor_id = $_POST['doctor_id'] === 'none' ? NULL : $_POST['doctor_id'];

    // Insert the new appointment data into the database
    $sql = "INSERT INTO appointmenttb (patient_id, reason, date_and_time, clinic_id, doctor_id) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $patient_id, $reason, $date_and_time, $clinic_id, $doctor_id);

    if ($stmt->execute()) {
        // Redirect to the form page after successful submission
        header("Location: appointment.php?success=1");
        exit(); // Make sure to exit after redirect
    } else {
        echo "<script>alert('Error: Could not add appointment');</script>";
    }
    $stmt->close();
}


$conn->close();
?>
