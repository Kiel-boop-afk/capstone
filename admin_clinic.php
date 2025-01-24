<?php
session_start();
require 'db.php';

// Function to generate a unique clinic_id
function generateUniqueClinicId($conn) {
    do {
        $clinic_id = "clinic-" . uniqid();
        
        $count = 0; 
        // Check if this ID already exists
        $stmt = $conn->prepare("SELECT COUNT(*) FROM clinictb WHERE id = ?");
        $stmt->bind_param("s", $clinic_id);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        
        $exists = $count > 0; // true if the ID already exists
    } while ($exists); // Repeat until a unique ID is found

    return $clinic_id;
}

// Check if the user is logged in and is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Initialize variables for the form
$clinic_id = '';
$name = '';
$address = '';
$phone = ''; // Updated from contact to phone
$edit = false; // Flag to check if it's an update operation

// Check if we are updating an existing clinic (through GET request)
if (isset($_GET['edit'])) {
    $edit = true;
    $clinic_id = $_GET['edit'];

    // Fetch the clinic details from the database
    $stmt = $conn->prepare("SELECT * FROM clinictb WHERE id = ?");
    $stmt->bind_param("s", $clinic_id); // Changed to 's' for string
    $stmt->execute();
    $result = $stmt->get_result();
    $clinic = $result->fetch_assoc();

    // Pre-fill the form with existing clinic data
    $name = $clinic['name'];
    $address = $clinic['address'];
    $phone = $clinic['phone']; // Updated
}

// Handle form submission (Add or Update clinic)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $address = $_POST['address'];
    $phone = $_POST['phone']; // Updated

    if (isset($_POST['update'])) {
        // Update an existing clinic
        $clinic_id = $_POST['clinic_id'];
        $stmt = $conn->prepare("UPDATE clinictb SET name = ?, address = ?, phone = ? WHERE id = ?");
        $stmt->bind_param("ssss", $name, $address, $phone, $clinic_id); // Updated
        $stmt->execute();
    } else {
        // Add a new clinic
        $clinic_id = generateUniqueClinicId($conn); // Generate a unique clinic_id
        $stmt = $conn->prepare("INSERT INTO clinictb (id, name, address, phone) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $clinic_id, $name, $address, $phone); // Updated
        $stmt->execute();
    }

    header("Location: admin_clinic.php");
    exit();
}
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
    <title>Admin Clinic Management</title>
    <link rel="stylesheet" href="css/admin_clinic_and_personnel_style.css"> 
</head>
<body>
<a href="admin_home.php">Back to Admin Dashboard</a>
    <div class="container">
        <div class="form-container">
        <h1><?php echo $edit ? 'Update Clinic' : 'Add Clinic'; ?></h1>
            <form action="admin_clinic.php" method="POST">
                <input type="hidden" name="clinic_id" value="<?php echo $clinic_id; ?>" />
                <label for="name">Clinic Name:</label>
                <input type="text" id="name" name="name" value="<?php echo $name; ?>" required><br><br>
                <label for="address">Address:</label>
                <input type="text" id="address" name="address" value="<?php echo $address; ?>" required><br><br>
                <label for="phone">Phone:</label> <!-- Updated label -->
                <input type="text" id="phone" name="phone" value="<?php echo $phone; ?>" required><br><br> <!-- Updated field -->
                <button type="submit" name="<?php echo $edit ? 'update' : 'submit'; ?>">
                    <?php echo $edit ? 'Update Clinic' : 'Add Clinic'; ?>
                </button>
            </form>
        </div>

        <div class="table-container">
            <h2>Clinic List</h2>
            <table border="1">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Address</th>
                    <th>Phone</th> <!-- Updated -->
                    <th>Action</th>
                </tr>
                <?php
                // Fetch all clinic data for display
                $sql = "SELECT * FROM clinictb";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while ($clinic = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($clinic['id']) . "</td>";
                        echo "<td>" . htmlspecialchars($clinic['name']) . "</td>";
                        echo "<td>" . htmlspecialchars($clinic['address']) . "</td>";
                        echo "<td>" . htmlspecialchars($clinic['phone']) . "</td>"; // Updated
                        echo "<td><a href='admin_clinic.php?edit=" . $clinic['id'] . "'>Edit</a></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>No clinics found</td></tr>";
                }
                ?>
            </table>
        </div>
    </div>
</body>
</html>
