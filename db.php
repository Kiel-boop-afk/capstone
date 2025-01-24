<?php
// Database configuration
$host = 'localhost'; // or '127.0.0.1'
$user = 'root'; // Database username
$password = ''; // Database password, leave blank if not set
$dbname = 'patientdatamanagementsystem'; // Database name
$port = 3307; // New port number

// Create a connection to the database
$conn = new mysqli($host, $user, $password, $dbname, $port);

// Check for a connection error
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>