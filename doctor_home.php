<?php
session_start();
require 'db.php'; // Include your database connection

// Check if the user is logged in using 'doctor_id'
if (!isset($_SESSION['doctor_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch doctor's name based on their doctor_id from the session
$doctor_id = $_SESSION['doctor_id'];
$sql = "SELECT doctor_name FROM doctortb WHERE doctor_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $doctor_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Fetch the doctor's name
    $doctor = $result->fetch_assoc();
    $doctor_name = $doctor['doctor_name'];
} else {
    echo "Doctor not found.";
    exit();
}

// Fetch today's appointments for the logged-in doctor
$appointments_sql = "SELECT a.id AS appointment_id, p.name AS patient_name, c.name AS clinic_name, 
                            c.address AS clinic_address, a.date_and_time, c.phone AS clinic_contact 
                     FROM appointmenttb a 
                     JOIN patienttb p ON a.patient_id = p.patient_id 
                     JOIN clinictb c ON a.clinic_id = c.id 
                     WHERE a.doctor_id = ? AND DATE(a.date_and_time) = CURDATE()"; // Filter for today's appointments

$appointments_stmt = $conn->prepare($appointments_sql);
$appointments_stmt->bind_param("s", $doctor_id);
$appointments_stmt->execute();
$appointments_result = $appointments_stmt->get_result();

$stmt->close();
$conn->close();
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
    <title>Clinic</title>
</head>
<body>
    <header>
    <div class="header-container">
        <div class="header-logo">
            <img src="images/logo.png" alt="Clinic Logo">
            <p>Dr. <?php echo $_SESSION['doctor_name']; ?></p>
        </div>
                <nav class="header-nav">
                <a href="doctor_home.php" data-active>Home</a>
          <a href="doctor_appointment.php">Appointments</a>
          <a href="doctor_patient.php">Patient</a>
          <a href="doctor_profile.php">Profile</a>
          <a href="doctor_messages.php">Messages</a>
                </nav>
                <form action="logout.php" method="POST">
                    <button class="exit" type="submit">Logout</button>
                </form>
        </div>
      </header>
      <main>
        <div class="buttonsdiv">
        <h3>Today's Appointment</h3>
            <button onclick="openForm3()">SEARCH</button>
        </div>
        <div class="tablediv">
        <table>
            <thead>
                <tr>
                    <th>Appointment ID</th>
                    <th>Patient's Name</th>
                    <th>Clinic Name</th>
                    <th>Clinic Address</th> 
                    <th>Date and Time</th>
                    <th>Clinic Contact</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($appointments_result->num_rows > 0): ?>
                    <?php while ($appointment = $appointments_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($appointment['appointment_id']); ?></td>
                            <td><?php echo htmlspecialchars($appointment['patient_name']); ?></td>
                            <td><?php echo htmlspecialchars($appointment['clinic_name']); ?></td>
                            <td><?php echo htmlspecialchars($appointment['clinic_address']); ?></td> <!-- Displaying clinic address -->
                            <td><?php echo htmlspecialchars($appointment['date_and_time']); ?></td>
                            <td><?php echo htmlspecialchars($appointment['clinic_contact']); ?></td> <!-- Displaying clinic contact -->
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">No appointments found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        </div>
    </main>
    <footer>.</footer>
      </body>
      </html>