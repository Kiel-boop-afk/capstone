<?php
// Start the session
session_start();

// Include the database connection
include 'db.php';

// Check if the form is submitted via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect form data
    $patient_id = $_POST['patient_id'];
    $dentist_notes = $_POST['dentist_notes'];

    // Begin a transaction
    $conn->begin_transaction();

    try {
        // Fetch the record_id from maternity_record for the given patient_id and active status
        $fetch_record_query = "SELECT record_id FROM maternity_record WHERE patient_id = ? AND status = 0 LIMIT 1";
        $stmt = $conn->prepare($fetch_record_query);
        $stmt->bind_param("s", $patient_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $record = $result->fetch_assoc();
        $stmt->close();

        if (!$record) {
            throw new Exception("No active maternity record found for the patient.");
        }

        $record_id = $record['record_id'];

        // Insert the data into the dental_checkups table
        $insert_query = "INSERT INTO dental_checkups (patient_id, record_id, dentist_notes) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("sss", $patient_id, $record_id, $dentist_notes);

        if (!$stmt->execute()) {
            throw new Exception("Failed to insert data into dental_checkups: " . $stmt->error);
        }

        $stmt->close();

        // Now, assuming you are passing appointment_id via a query string or session
        if (isset($_POST['appointment_id'])) {
            $appointment_id = $_POST['appointment_id'];

            // Update the status of the appointment in appointmenttb to 'done'
            $update_appointment_query = "UPDATE appointmenttb SET status = 'done' WHERE id = ?";
            $stmt = $conn->prepare($update_appointment_query);
            $stmt->bind_param("s", $appointment_id);

            if (!$stmt->execute()) {
                throw new Exception("Failed to update appointment status: " . $stmt->error);
            }

            $stmt->close();
        }

        // Commit the transaction
        $conn->commit();

        // Redirect with a success message
        echo "<script>alert('Dental Checkup submitted successfully! Appointment status updated.'); window.location.href = 'appointment.php';</script>";
    } catch (Exception $e) {
        // Rollback the transaction in case of error
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    } finally {
        $conn->close();
    }
} else {
    echo "Invalid request.";
}
?>
