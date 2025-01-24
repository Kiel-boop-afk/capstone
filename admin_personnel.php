<?php
session_start();
require 'db.php';

// Check if the user is logged in and is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Initialize variables for the form
$personnel_id = '';
$clinic_id = '';
$clinic_name = '';
$personnel_name = '';
$personnel_position = '';
$contact_number = '';
$edit = false; // Flag to check if it's an update operation

// Check if we are updating an existing personnel (through GET request)
if (isset($_GET['edit'])) {
    $edit = true;
    $personnel_id = $_GET['edit'];

    // Fetch the personnel details from the database
    $stmt = $conn->prepare("SELECT * FROM personneltb WHERE personnel_id = ?");
    $stmt->bind_param("i", $personnel_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $personnel = $result->fetch_assoc();

    // Pre-fill the form with existing personnel data
    $clinic_id = $personnel['clinic_id'];
    $clinic_name = $personnel['clinic_name'];
    $personnel_name = $personnel['personnel_name'];
    $personnel_position = $personnel['personnel_position'];
    $contact_number = $personnel['contact_number'];
}

// Handle form submission (Add or Update personnel)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $clinic_id = $_POST['clinic_id'];
    $clinic_name = $_POST['clinic_name'];
    $personnel_name = $_POST['personnel_name'];
    $personnel_position = $_POST['personnel_position'];
    $contact_number = $_POST['contact_number'];

    if (isset($_POST['update'])) {
        // Update existing personnel
        $personnel_id = $_POST['personnel_id'];
        $stmt = $conn->prepare("UPDATE personneltb SET clinic_id = ?, clinic_name = ?, personnel_name = ?, personnel_position = ?, contact_number = ? WHERE personnel_id = ?");
        $stmt->bind_param("sssssi", $clinic_id, $clinic_name, $personnel_name, $personnel_position, $contact_number, $personnel_id);
        $stmt->execute();
    } else {
        // Add a new personnel
        $stmt = $conn->prepare("INSERT INTO personneltb (clinic_id, clinic_name, personnel_name, personnel_position, contact_number) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $clinic_id, $clinic_name, $personnel_name, $personnel_position, $contact_number);
        $stmt->execute();
    }

    header("Location: admin_personnel.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/admin_clinic_and_personnel_style.css"> 
    <link rel="icon" type="image/png" href="images/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="images/favicon.svg" />
    <link rel="shortcut icon" href="images/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="images/apple-touch-icon.png" />
    <title>Admin - Manage Personnel</title>
    <script>
        // JavaScript function to update clinic name based on selected clinic ID
        function updateClinicName() {
            var clinicSelect = document.getElementById("clinic_id");
            var clinicNameInput = document.getElementById("clinic_name");

            // Get the selected clinic option
            var selectedOption = clinicSelect.options[clinicSelect.selectedIndex];
            clinicNameInput.value = selectedOption.getAttribute("data-name");
        }
    </script>
</head>
<body> 
<a href="admin_home.php">Back to Admin Dashboard</a>   
    <div class="container">
    <div class="form-container">
    <h1><?php echo $edit ? 'Update Personnel' : 'Add Personnel'; ?></h1>
    <form action="admin_personnel.php" method="POST">
        <input type="hidden" name="personnel_id" value="<?php echo $personnel_id; ?>" />
        
        <!-- Dropdown for selecting clinic -->
        <label for="clinic_id">Clinic:</label>
        <select name="clinic_id" id="clinic_id" required onchange="updateClinicName()">
            <option value="">Select Clinic</option>
            <?php
            $clinic_query = $conn->query("SELECT id, name FROM clinictb");
            while ($clinic = $clinic_query->fetch_assoc()) {
                echo "<option value='{$clinic['id']}' data-name='{$clinic['name']}' " . ($clinic['id'] == $clinic_id ? 'selected' : '') . ">{$clinic['name']}</option>";
            }
            ?>
        </select><br><br>

        <label for="clinic_name">Clinic Name:</label>
        <input type="text" id="clinic_name" name="clinic_name" value="<?php echo $clinic_name; ?>" readonly required><br><br>

        <label for="personnel_name">Personnel Name:</label>
        <input type="text" id="personnel_name" name="personnel_name" value="<?php echo $personnel_name; ?>" required><br><br>

        <label for="personnel_position">Personnel Position:</label>
        <input type="text" id="personnel_position" name="personnel_position" value="<?php echo $personnel_position; ?>" required><br><br>

        <label for="contact_number">Contact Number:</label>
        <input type="text" id="contact_number" name="contact_number" value="<?php echo $contact_number; ?>" required><br><br>

        <button type="submit" name="<?php echo $edit ? 'update' : 'submit'; ?>">
            <?php echo $edit ? 'Update Personnel' : 'Add Personnel'; ?>
        </button>
    </form>
    </div>

    <div class="table-container">
    <h2>Personnel List</h2>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Clinic</th>
            <th>Name</th>
            <th>Position</th>
            <th>Contact</th>
            <th>Action</th>
        </tr>
        <?php
        // Fetch all personnel data for display
        $sql = "SELECT * FROM personneltb";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($personnel = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($personnel['personnel_id']) . "</td>";
                echo "<td>" . htmlspecialchars($personnel['clinic_name']) . "</td>";
                echo "<td>" . htmlspecialchars($personnel['personnel_name']) . "</td>";
                echo "<td>" . htmlspecialchars($personnel['personnel_position']) . "</td>";
                echo "<td>" . htmlspecialchars($personnel['contact_number']) . "</td>";
                echo "<td><a href='admin_personnel.php?edit=" . $personnel['personnel_id'] . "'>Edit</a></td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='6'>No personnel found</td></tr>";
        }
        ?>
    </table>
    </div>
    </div>
</body>
</html>