<?php
session_start(); // Start session to access session variables

// Include the database connection
require 'db.php';

// Redirect if user is not logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Access user data from the session
$user_id = $_SESSION['user_id']; // Using user_id to get user-specific data from the database

// Fetch all clinic data from the database
$sql = "SELECT * FROM clinictb";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="images/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="images/favicon.svg" />
    <link rel="shortcut icon" href="images/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="images/apple-touch-icon.png" />
    <link rel="stylesheet" href="css/user_style.css">
    <link rel="stylesheet" href="css/loading_screen_style.css">
    <title>Clinic</title>
</head>
<body>
    <!-- Loading Screen -->
<div class="loading-overlay">
    <div class="pulse"></div>
</div>

    <!-- header -->
    <?php $active = 'clinic'; include('includes/header.php') ?>

    <main>
                <!-- Search Bar with Button -->
                <div class="buttons-container">
                <div class="search-container">
                    <input type="text" class="search-bar" placeholder="Search...">
                    <button class="search-button" onclick="performSearch()">Search</button>
                </div>
            </div>
        <div class="tablediv">
        <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Address</th>
                <th>Contact</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Check if there are results in the query
            if ($result->num_rows > 0) {
                // Loop through the results and display each row in the table
                while ($clinic = $result->fetch_assoc()) {
                    echo "<tr class='searchable-item'>"; // Add class for search
                    echo "<td>" . htmlspecialchars($clinic['id']) . "</td>";
                    echo "<td>" . htmlspecialchars($clinic['name']) . "</td>";
                    echo "<td>" . htmlspecialchars($clinic['address']) . "</td>";
                    echo "<td>" . htmlspecialchars($clinic['phone']) . "</td>";
                    echo "</tr>";
                }
            } else {
                // If no clinics are found
                echo "<tr><td colspan='4'>No clinics found</td></tr>";
            }
            ?>
        </tbody>
            </table>
        </div>
    </main>
    <footer>&copy; 2024 Your Clinic</footer>
</body>

<script>
// Perform search based on user input
function performSearch() {
    const query = document.querySelector('.search-bar').value.toLowerCase();
    const rows = document.querySelectorAll('.searchable-item'); // Target rows with 'searchable-item' class
    
    rows.forEach(row => {
        const rowText = row.textContent.toLowerCase(); // Get the text content of the row
        if (rowText.includes(query)) {
            row.style.display = ''; // Show the row if it matches the search
        } else {
            row.style.display = 'none'; // Hide the row if it doesn't match
        }
    });
}
</script>
<script src="js/loading_screen.js"></script>
</html>
