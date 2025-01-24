<?php
// Start the session
session_start();

// Include the database connection
include 'db.php';

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the form data
    $visit_date = $_POST['visit_date'];
    $bp = $_POST['bp'];
    $wt = $_POST['wt'];
    $fund_ht = $_POST['fund_ht'];
    $presentation_fhb = $_POST['presentation_fhb'];
    $temperature = $_POST['temperature'];
    $aog = $_POST['aog'];
    $care = $_POST['care'];
    $patient_id = $_POST['patient_id'];
    $appointment_id = $_POST['appointment_id'];  // Assuming appointment_id is passed

    // Begin a transaction
    $conn->begin_transaction();

    try {
        // Fetch the record_id from the maternity_record table for the given patient_id and status = 0
        $fetch_record_id_query = "SELECT record_id FROM maternity_record WHERE patient_id = ? AND status = 0 LIMIT 1";
        if ($stmt = $conn->prepare($fetch_record_id_query)) {
            $stmt->bind_param("s", $patient_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $record = $result->fetch_assoc();
            $stmt->close();

            if (!$record) {
                throw new Exception("No active maternity record found for the patient.");
            }

            $record_id = $record['record_id'];
        } else {
            throw new Exception("Error preparing query to fetch record_id: " . $conn->error);
        }

        // Insert the Antepartum Visit data into the antepartum_visit table
        $insert_query = "INSERT INTO antepartum_visit 
                        (record_id, visit_date, bp, wt, fund_ht, presentation_fhb, temperature, aog, care, patient_id)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        if ($stmt = $conn->prepare($insert_query)) {
            $stmt->bind_param(
                "isssssssss",
                $record_id,
                $visit_date,
                $bp,
                $wt,
                $fund_ht,
                $presentation_fhb,
                $temperature,
                $aog,
                $care,
                $patient_id
            );

            if ($stmt->execute()) {
                // Update the status in the appointmenttb table to reflect the visit is done
                $update_status_query = "UPDATE appointmenttb SET status = 'done' WHERE id = ?";
                if ($update_stmt = $conn->prepare($update_status_query)) {
                    $update_stmt->bind_param("i", $appointment_id);
                    if (!$update_stmt->execute()) {
                        throw new Exception("Error updating status in appointment table: " . $update_stmt->error);
                    }
                    $update_stmt->close();
                } else {
                    throw new Exception("Error preparing query to update status: " . $conn->error);
                }

                // Commit the transaction
                $conn->commit();

                // Redirect to a success page or display a success message
                echo "<script>alert('Antepartum visit record added successfully!'); window.location.href = 'appointment.php';</script>";
            } else {
                throw new Exception("Error executing query: " . $stmt->error);
            }

            $stmt->close();
        } else {
            throw new Exception("Error preparing query to insert Antepartum Visit: " . $conn->error);
        }
    } catch (Exception $e) {
        // Rollback the transaction in case of any error
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    } finally {
        // Close the database connection
        $conn->close();
    }
} else {
    echo "Invalid request.";
}
?>
