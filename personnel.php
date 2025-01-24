<?php
session_start();
// Ensure the user is logged in
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || !isset($_SESSION['clinic_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
require 'db.php'; // Include your database connection script

// Get the user's clinic ID from the session
$clinic_id = $_SESSION['clinic_id'];

// Fetch personnel data for the user's clinic
$sql = "SELECT personnel_id, personnel_name, personnel_position, contact_number FROM personneltb WHERE clinic_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $clinic_id);
$stmt->execute();
$result = $stmt->get_result();
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
    <title>Personnel</title>
</head>
<body>
    <!-- Loading Screen -->
<div class="loading-overlay">
    <div class="pulse"></div>
</div>
    
    <!-- header -->
    <?php $active = 'personnel'; include('includes/header.php') ?>

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
                <th>Personnel ID</th>
                <th>Name</th>
                <th>Position</th>
                <th>Contact Number</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                // Output data of each row
                while ($row = $result->fetch_assoc()) {
                    echo "<tr class='searchable-item'>";
                    echo "<td>" . htmlspecialchars($row['personnel_id']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['personnel_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['personnel_position']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['contact_number']) . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='4'>No personnel found in your clinic.</td></tr>";
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