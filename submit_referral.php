<?php
session_start();
require 'db.php'; // Include your database connection

// Check if the user is logged in by checking either doctor_id or clinic_id
if (!isset($_SESSION['doctor_id']) && !isset($_SESSION['clinic_id'])) {
    header("Location: login.php");
    exit();
}

// Handle form submission for referrals
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_referral'])) {
    $patient_id = $_POST['patient_id'];

    // Check if patient_id is empty
    if (empty($patient_id)) {
        $_SESSION['error'] = "Patient ID cannot be empty."; // Set error message in session
        header("Location: patient.php"); // Redirect back to the referral form page
        exit(); // Stop further execution
    }

    // Determine refer_from based on whether the user is a doctor or clinic
    if (isset($_SESSION['clinic_id'])) {
        $refer_from = $_SESSION['clinic_id'];
    } elseif (isset($_SESSION['doctor_id'])) {
        $refer_from = $_SESSION['doctor_id'];
    } else {
        echo "Error: User role not found.";
        exit();
    }

    // Get the selected referral type and set refer_to based on the selection
    $refer_type = $_POST['refer_type'];
    if ($refer_type === 'clinic') {
        $refer_to = $_POST['refer_to_clinic'];
    } elseif ($refer_type === 'doctor') {
        $refer_to = $_POST['refer_to_doctor'];
    } elseif ($refer_type === 'hospital') {
        $refer_to = $_POST['refer_to_hospital'];
    } else {
        echo "Error: No valid referral type selected.";
        exit();
    }

    $reason = $_POST['reason'];

    // Insert the referral into confirmationtb
    $sql = "INSERT INTO confirmationtb (patient_id, refer_from, refer_to, reason, is_confirmed, created_at) 
            VALUES (?, ?, ?, ?, 0, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $patient_id, $refer_from, $refer_to, $reason);

    if ($stmt->execute()) {
        $_SESSION['referral_success'] = true;
        
        // Check if the user is a doctor or clinic and redirect accordingly
        if (isset($_SESSION['doctor_id'])) {
            // Redirect to doctor_patient.php if the user is a doctor
            header("Location: doctor_patient.php");
            exit();
        } elseif (isset($_SESSION['clinic_id'])) {
            // Redirect to patient.php if the user is from a clinic
            header("Location: patient.php");
            exit();
        } else {
            // Handle case where the role is not found
            echo "Error: User role not found.";
            exit();
        }
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
