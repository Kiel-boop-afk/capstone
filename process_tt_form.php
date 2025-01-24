<?php
// Start the session
session_start();

// Include the database connection
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect form data
    $patient_id = $_POST['patient_id'];
    $appointment_id = $_POST['appointment_id'];
    $date = $_POST['date'];
    $notes = $_POST['notes'];

    // Begin a transaction
    $conn->begin_transaction();

    try {
        // Fetch the record_id from maternity_record for the given patient_id
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

        // Determine the next ttdose_number for the patient in the specific record
        $fetch_dose_count_query = "SELECT COUNT(*) AS dose_count FROM tetanus_toxoid WHERE patient_id = ? AND record_id = ?";
        $stmt = $conn->prepare($fetch_dose_count_query);
        $stmt->bind_param("ss", $patient_id, $record_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $dose_data = $result->fetch_assoc();
        $stmt->close();

        $current_dose_count = $dose_data['dose_count'];

        // Check if the dose count exceeds the limit of 5
        if ($current_dose_count >= 5) {
            throw new Exception("Maximum number of Tetanus Toxoid doses (TT-5) already recorded for this patient and record.");
        }

        $next_dose_number = $current_dose_count + 1;
        $ttdose_number = "TT-" . $next_dose_number;

        // Insert the data into tetanus_toxoid table
        $tt_insert_query = "INSERT INTO tetanus_toxoid (record_id, patient_id, date, notes, ttdose_number) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($tt_insert_query);
        $stmt->bind_param("sssss", $record_id, $patient_id, $date, $notes, $ttdose_number);

        if (!$stmt->execute()) {
            throw new Exception("Failed to insert data into tetanus_toxoid: " . $stmt->error);
        }

        $stmt->close();

        // Update the status of the appointment in appointmenttb
        $update_appointment_query = "UPDATE appointmenttb SET status = 'done' WHERE id = ?";
        $stmt = $conn->prepare($update_appointment_query);
        $stmt->bind_param("s", $appointment_id);

        if (!$stmt->execute()) {
            throw new Exception("Failed to update appointment status: " . $stmt->error);
        }

        $stmt->close();

        // Commit the transaction
        $conn->commit();

        // Redirect with a success message
        echo "<script>alert('Tetanus Toxoid record added successfully!'); window.location.href = 'appointment.php';</script>";
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
