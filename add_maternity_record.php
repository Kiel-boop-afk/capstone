<?php
// Start the session to access session variables
session_start();
// Include the database connection
include 'db.php';

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the form data
    $patient_id = $_POST['patient_id'];
    $appointment_id = $_POST['appointment_id'];
    $lmp = $_POST['lmp'];
    $edc = $_POST['edc'];
    $gravida = $_POST['gravida'] + 1;  // Increment gravida by 1
    $para = $_POST['para'];
    $abortions = $_POST['abortions'];
    $family_no = $_POST['family'];

    // Get the current date and time for the 'date_added' field
    $date_added = date("Y-m-d H:i:s");

    // Begin transaction
    $conn->begin_transaction();

    try {
        // Insert into maternity_record
        $query = "INSERT INTO maternity_record (patient_id, lmp, edc, gravida, para, abortions, family_no, date_added)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        if ($stmt = $conn->prepare($query)) {
            $stmt->bind_param("ssssssss", $patient_id, $lmp, $edc, $gravida, $para, $abortions, $family_no, $date_added);
            $stmt->execute();
        } else {
            throw new Exception("Error preparing insert statement");
        }

        // Update gravida in the patienttb table
        $update_gravida_query = "UPDATE patienttb SET gravida = ? WHERE patient_id = ?";
        if ($update_gravida_stmt = $conn->prepare($update_gravida_query)) {
            $update_gravida_stmt->bind_param("is", $gravida, $patient_id);
            $update_gravida_stmt->execute();
        } else {
            throw new Exception("Error preparing update gravida statement");
        }

        // Update appointment status to "done"
        $update_appointment_query = "UPDATE appointmenttb SET status = 'done' WHERE id = ?";
        if ($update_appointment_stmt = $conn->prepare($update_appointment_query)) {
            $update_appointment_stmt->bind_param("i", $appointment_id);
            $update_appointment_stmt->execute();
        } else {
            throw new Exception("Error preparing update appointment statement");
        }

        // Insert a new appointment for the same patient
        $new_appointment_query = "INSERT INTO appointmenttb (patient_id, reason, date_and_time, created_at, clinic_id)
                                  VALUES (?, ?, ?, ?, ?)";
        if ($new_appointment_stmt = $conn->prepare($new_appointment_query)) {
            $reason = "first-pregnancy-checkup-Antepartumvisit";
            $date_and_time = date("Y-m-d H:i:s", strtotime("+1 hour", strtotime($date_added))); // 1 hour after the maternity record date
            $created_at = $date_added;  // Use the same date_added as created_at
            $clinic_id = $_SESSION['clinic_id']; // Assuming clinic_id is stored in the session

            $new_appointment_stmt->bind_param("sssss", $patient_id, $reason, $date_and_time, $created_at, $clinic_id);
            $new_appointment_stmt->execute();
        } else {
            throw new Exception("Error preparing insert new appointment statement");
        }

        // Commit the transaction
        $conn->commit();

        // Redirect to appointment.php after successful submission
        echo "<script>window.location.href = 'appointment.php';</script>";

    } catch (Exception $e) {
        // Rollback the transaction in case of any error
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    } finally {
        // Close the statement and connection
        if (isset($stmt)) $stmt->close();
        if (isset($update_gravida_stmt)) $update_gravida_stmt->close();
        if (isset($update_appointment_stmt)) $update_appointment_stmt->close();
        if (isset($new_appointment_stmt)) $new_appointment_stmt->close();
        $conn->close();
    }
}
?>
