<?php
session_start();
require 'db.php'; // Include the database connection

// Check if the user is logged in and has the 'admin' role
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    // If not logged in or not an admin, redirect to the login page or show an error
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/create_user_style.css">
    <link rel="icon" type="image/png" href="images/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="images/favicon.svg" />
    <link rel="shortcut icon" href="images/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="images/apple-touch-icon.png" />
    <title>Add Doctor</title>
</head>
<body>
<a href="admin_home.php">Back to Admin Dashboard</a>
    <h2>Add New Doctor</h2>
    <form action="add_doctor.php" method="POST">
        <label for="doctor_name">Doctor Name:</label><br>
        <input type="text" id="doctor_name" name="doctor_name" required><br><br>

        <label for="contact_number">Contact Number:</label><br>
        <input type="text" id="contact_number" name="contact_number" required><br><br>

        <label for="doctor_email">Email:</label><br>
        <input type="email" id="doctor_email" name="doctor_email" required><br><br>

        <label for="password">Password:</label><br>
        <input type="password" id="password" name="password" required><br><br>

        <button type="submit">Add Doctor</button>
    </form>
</body>
</html>
