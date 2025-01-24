<?php
// Start session and include database connection
session_start();
require_once "db.php";

// Check if the user is logged in and has admin privileges
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "Access denied. Admins only.";
    exit();
}

// Query the database to fetch user login history
$sql_user = "SELECT ulh.*, u.username 
             FROM login_history ulh 
             LEFT JOIN usertb u ON ulh.user_id = u.user_id 
             ORDER BY ulh.login_time DESC";
$result_user = $conn->query($sql_user);

// Query the database to fetch doctor login history
$sql_doctor = "SELECT dlh.*, d.doctor_name 
               FROM doctor_login_history dlh 
               LEFT JOIN doctortb d ON dlh.doctor_id = d.doctor_id 
               ORDER BY dlh.login_time DESC";
$result_doctor = $conn->query($sql_doctor);

// Query the database to fetch patient login history
$sql_patient = "SELECT plh.*, p.patient_id, p.first_name, p.last_name
                FROM patient_login_history plh
                LEFT JOIN patienttb p ON plh.patient_id = p.patient_id
                ORDER BY plh.login_time DESC";
$result_patient = $conn->query($sql_patient);
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
    <title>Login History</title>
    <link rel="stylesheet" href="css/login_history_style.css">
</head>
<body>

    <h2>User Login History</h2>
    <table>
        <tr>
            <th>Username</th>
            <th>Login Time</th>
            <th>IP Address</th>
            <th>Success</th>
        </tr>

        <?php if ($result_user->num_rows > 0): ?>
            <?php while($row = $result_user->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['username'] ? $row['username'] : 'Unknown'); ?></td>
                    <td><?php echo $row['login_time']; ?></td>
                    <td><?php echo $row['ip_address']; ?></td>
                    <td><?php echo $row['success'] ? 'Yes' : 'No'; ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="4">No login history available for users.</td>
            </tr>
        <?php endif; ?>
    </table>

    <h2>Doctor Login History</h2>
    <table>
        <tr>
            <th>Doctor Name</th>
            <th>Login Time</th>
            <th>IP Address</th>
            <th>Success</th>
        </tr>

        <?php if ($result_doctor->num_rows > 0): ?>
            <?php while($row = $result_doctor->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['doctor_name'] ? $row['doctor_name'] : 'Unknown'); ?></td>
                    <td><?php echo $row['login_time']; ?></td>
                    <td><?php echo $row['ip_address']; ?></td>
                    <td><?php echo $row['success'] ? 'Yes' : 'No'; ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="4">No login history available for doctors.</td>
            </tr>
        <?php endif; ?>
    </table>

    <h2>Patient Login History</h2>
    <table>
        <tr>
            <th>Patient ID</th>
            <th>Patient Name</th>
            <th>Login Time</th>
            <th>IP Address</th>
            <th>Success</th>
        </tr>

        <?php if ($result_patient->num_rows > 0): ?>
            <?php while($row = $result_patient->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['patient_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                    <td><?php echo $row['login_time']; ?></td>
                    <td><?php echo $row['ip_address']; ?></td>
                    <td><?php echo $row['success'] ? 'Yes' : 'No'; ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="5">No login history available for patients.</td>
            </tr>
        <?php endif; ?>
    </table>

</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
