<?php
// Database connection
require 'db.php'; // Include your database connection script

// Check if form is submitted
if (isset($_POST['case_id'])) {
    $case_id = $_POST['case_id'];
    $date_and_time_of_discharge = $_POST['date_and_time_of_discharge'];

    // Ensure that a discharge date is provided
    if (empty($date_and_time_of_discharge)) {
        echo "Please provide a valid discharge date.";
        exit();
    }

    // Set discharge status to 1 (Yes), as the checkbox is removed
    $discharge_status = 1;

    // Update the discharge details in the database
    $query = "
        UPDATE casetb 
        SET date_and_time_of_discharge = ?, discharge = ? 
        WHERE case_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sii", $date_and_time_of_discharge, $discharge_status, $case_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
         // Success: redirect back with a success message
         $_SESSION['success_message'] = "Discharged successfully!";
         header("Location: cases.php"); // Or redirect to the desired page
         exit();
    } else {
        echo "Failed to discharge the case.";
    }

    $stmt->close();
}

$conn->close();
?>