<?php
session_start();
require 'db.php'; // Database connection

// Check if a token is provided in the URL
if (!isset($_GET['token']) || empty($_GET['token'])) {
    echo "<script>alert('Invalid or missing confirmation token.'); window.location.href = 'index.php';</script>";
    exit();
}

$token = $_GET['token'];

// Look up the token in the `email_confirmation` table
$stmt = $conn->prepare("SELECT email, token_expiration, is_confirmed, username, hashed_password, role, clinic_id FROM email_confirmation WHERE token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "<script>alert('Invalid token.'); window.location.href = 'index.php';</script>";
    exit();
}

$row = $result->fetch_assoc();
$email = $row['email'];
$token_expiration = $row['token_expiration'];
$is_confirmed = $row['is_confirmed'];

// Check if the token has already been used or has expired
if ($is_confirmed) {
    echo "<script>alert('This email has already been confirmed.'); window.location.href = 'login.php';</script>";
    exit();
}

if (strtotime($token_expiration) < time()) {
    echo "<script>alert('Confirmation link has expired. Please request a new confirmation email.'); window.location.href = 'index.php';</script>";
    exit();
}

// If the token is valid and not expired, mark the email as confirmed
$update_stmt = $conn->prepare("UPDATE email_confirmation SET is_confirmed = 1 WHERE token = ?");
$update_stmt->bind_param("s", $token);
$update_stmt->execute();

// Retrieve additional user data
$username = $row['username'];
$hashed_password = $row['hashed_password'];
$role = $row['role'];
$clinic_id = $row['clinic_id'];

// Insert the new user into the usertb
$insert_stmt = $conn->prepare("INSERT INTO usertb (username, email, password, role, clinic_id) VALUES (?, ?, ?, ?, ?)");
$insert_stmt->bind_param("sssss", $username, $email, $hashed_password, $role, $clinic_id);

if ($insert_stmt->execute()) {
    echo "<script>alert('Your email has been successfully confirmed and your account has been created!'); window.location.href = 'login.php';</script>";
} else {
    echo "<script>alert('Error creating user account. Please try again.'); window.location.href = 'index.php';</script>";
}

$insert_stmt->close();
$conn->close();
?>