<?php
// Include database connection
include 'db.php';

// Check if the form is submitted via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $case_id = $_POST['case_id'] ?? null;
    $outcome_of_delivery = $_POST['outcome_of_delivery'] ?? null;
    $date_and_time_of_delivery = $_POST['date_and_time_of_delivery'] ?? null;
    $primary_personnel_id = $_POST['primary_personnel_id'] ?? null;
    $personnel_ids = $_POST['personnel_ids'] ?? [];

    // Validate required fields
    if (!$case_id || !$outcome_of_delivery || !$date_and_time_of_delivery || !$primary_personnel_id || empty($personnel_ids)) {
        echo "<script>
                alert('All fields are required.');
                window.history.back();
              </script>";
        exit;
    }

    try {
        // Start a transaction
        $conn->begin_transaction();

        // Update the main case record with primary personnel
        $updateCaseQuery = "UPDATE casetb 
                            SET outcome_of_delivery = ?, 
                                date_and_time_of_delivery = ?, 
                                primary_personnel_id = ? 
                            WHERE case_id = ?";
        $updateCaseStmt = $conn->prepare($updateCaseQuery);
        $updateCaseStmt->bind_param("sssi", $outcome_of_delivery, $date_and_time_of_delivery, $primary_personnel_id, $case_id);

        if (!$updateCaseStmt->execute()) {
            throw new Exception("Error updating the case: " . $updateCaseStmt->error);
        }

        // Remove existing personnel assignments for the case
        $deletePersonnelQuery = "DELETE FROM case_personneltb WHERE case_id = ?";
        $deletePersonnelStmt = $conn->prepare($deletePersonnelQuery);
        $deletePersonnelStmt->bind_param("i", $case_id);

        if (!$deletePersonnelStmt->execute()) {
            throw new Exception("Error removing existing personnel: " . $deletePersonnelStmt->error);
        }

        // Assign primary personnel to the case
        $insertPersonnelQuery = "INSERT INTO case_personneltb (case_id, personnel_id, role) VALUES (?, ?, ?)";
        $insertPersonnelStmt = $conn->prepare($insertPersonnelQuery);

        $primaryRole = "Primary";
        $insertPersonnelStmt->bind_param("iis", $case_id, $primary_personnel_id, $primaryRole);

        if (!$insertPersonnelStmt->execute()) {
            throw new Exception("Error assigning primary personnel: " . $insertPersonnelStmt->error);
        }

        // Assign additional personnel to the case
        foreach ($personnel_ids as $personnel_id) {
            $role = "Support";
            $insertPersonnelStmt->bind_param("iis", $case_id, $personnel_id, $role);
            if (!$insertPersonnelStmt->execute()) {
                throw new Exception("Error assigning additional personnel: " . $insertPersonnelStmt->error);
            }
        }

        // Commit the transaction
        $conn->commit();

        // Success message and redirect
        echo "<script>
                alert('Case updated successfully!');
                window.location.href = 'cases.php';
              </script>";
    } catch (Exception $e) {
        // Rollback the transaction on error
        $conn->rollback();

        // Show error message
        echo "<script>
                alert('Error: " . addslashes($e->getMessage()) . "');
                window.history.back();
              </script>";
    } finally {
        // Close prepared statements
        $updateCaseStmt->close();
        $deletePersonnelStmt->close();
        $insertPersonnelStmt->close();

        // Close the database connection
        $conn->close();
    }
} else {
    // If accessed directly, redirect to the cases page
    header("Location: cases.php");
    exit;
}
?>
