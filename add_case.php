<?php
// Include the database connection
include 'db.php';

// Start session to access session variables
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $patient_id = $_POST['patient_id'] ?? null;
    $record_id = $_POST['record_id'] ?? null;
    $clinic_id = $_POST['clinic_id'] ?? null;
    $date_and_time_of_admission = $_POST['date_and_time_of_admission'] ?? null;
    $admitting_diagnosis = $_POST['admitting_diagnosis'] ?? null;

    // Check for required fields
    if ($patient_id && $record_id && $clinic_id && $date_and_time_of_admission && $admitting_diagnosis) {
        try {
            // Insert case into casetb
            $query = "INSERT INTO casetb (patient_id, record_id, clinic_id, date_and_time_of_admission, admitting_diagnosis) 
                      VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssss", $patient_id, $record_id, $clinic_id, $date_and_time_of_admission, $admitting_diagnosis);

            if ($stmt->execute()) {
                // Successfully added case
                echo "<script>
                        alert('Case added successfully!');
                        window.location.href = 'cases.php';
                      </script>";
                exit;
            } else {
                throw new Exception("Error adding case: " . $stmt->error);
            }
            
        } catch (Exception $e) {
            $_SESSION['error_message'] = $e->getMessage();
            header("Location: error.php");
            exit;
        } finally {
            // Close statement and connection
            $stmt->close();
            $conn->close();
        }
    } else {
        // If required fields are missing
        $_SESSION['error_message'] = "All fields are required.";
        header("Location: error.php");
        exit;
    }
} else {
    // Redirect if the script is accessed directly
    $_SESSION['error_message'] = "Invalid request method.";
    header("Location: error.php");
    exit;
}
?>
