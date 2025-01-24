<?php
// Include the database connection
require 'db.php';

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $appointmentId = $_POST['appointmentId'];  // The original appointment ID
    $patientId = $_POST['patientId'];  // The patient ID
    $patientName = $_POST['patientName'];  // The patient name (optional, for logging purposes)
    $reason = $_POST['reason'];  // The reason for the appointment
    $newDate = $_POST['newDate'];  // The new date and time for the appointment

    // Start the transaction to ensure both operations succeed or fail together
    $conn->begin_transaction();

    try {
        // Step 1: Update the status of the existing appointment
        $updateQuery = "UPDATE appointmenttb SET status = 'rescheduled' WHERE id = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param('i', $appointmentId);  // 'i' for integer
        $updateStmt->execute();

        // Step 2: Insert a new appointment with the new date
        $insertQuery = "
            INSERT INTO appointmenttb (patient_id, clinic_id, reason, doctor_id, date_and_time, created_at)
            SELECT patient_id, clinic_id, reason, doctor_id, ?, NOW()
            FROM appointmenttb
            WHERE id = ?
        ";
        $insertStmt = $conn->prepare($insertQuery);
        $insertStmt->bind_param('si', $newDate, $appointmentId);  // 'si' for string and integer
        $insertStmt->execute();

        // Commit the transaction
        $conn->commit();

        // Redirect to the appointment page with a success message
        header('Location: appointment.php?status=success');
        exit;

    } catch (Exception $e) {
        // Rollback transaction if there is an error
        $conn->rollback();

        // Redirect to the appointment page with an error message
        header('Location: appointment.php?status=error&message=' . urlencode($e->getMessage()));
        exit;
    }
}
?>
