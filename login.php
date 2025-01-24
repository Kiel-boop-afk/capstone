<?php
session_start();
require 'db.php'; // Database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username']; // This will now be treated as the patient ID
    $password = trim($_POST['password']); // Trim whitespace from password
    $ip_address = $_SERVER['REMOTE_ADDR'];  // Get the user's IP address

    // Prepare the SQL query to fetch the user by username
    $stmt_user = $conn->prepare("SELECT * FROM usertb WHERE username = ?");
    $stmt_user->bind_param("s", $username);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();
    
    // Prepare the SQL query to fetch the doctor by email
    $stmt_doctor = $conn->prepare("SELECT * FROM doctortb WHERE doctor_email = ?");
    $stmt_doctor->bind_param("s", $username);
    $stmt_doctor->execute();
    $result_doctor = $stmt_doctor->get_result();
    
    // Prepare the SQL query to fetch the patient by ID (now as VARCHAR)
    $stmt_patient = $conn->prepare("SELECT * FROM patienttb WHERE patient_id = ?");
    $stmt_patient->bind_param("s", $username); // Change to "s" for VARCHAR
    $stmt_patient->execute();
    $result_patient = $stmt_patient->get_result();

    // User Login Logic
    if ($result_user->num_rows > 0) {
        $user = $result_user->fetch_assoc();

        // Verify the password (if it's hashed)
        if (password_verify($password, $user['password'])) {
            // Store successful login in session
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['clinic_id'] = $user['clinic_id']; // Store clinic ID as well

            // Log the successful login in user_login_history table
            $success = 1;  // 1 = success
            $stmt_log = $conn->prepare("INSERT INTO login_history (user_id, ip_address, success) VALUES (?, ?, ?)");
            $stmt_log->bind_param("isi", $user['user_id'], $ip_address, $success);
            $stmt_log->execute();

            // Redirect user based on role
            if ($user['role'] == 'admin') {
                header("Location: admin_home.php");
                exit();
            } 
            elseif ($user['role'] == 'cashier') {
                header("Location: cashier_home.php");
                exit();
            } 
            else {
                header("Location: user_home.php");
                exit();
            }
        } else {
            // Log failed login attempt
            $success = 0;  // 0 = failed
            $stmt_log = $conn->prepare("INSERT INTO login_history (user_id, ip_address, success) VALUES (?, ?, ?)");
            $stmt_log->bind_param("isi", $user['user_id'], $ip_address, $success);
            $stmt_log->execute();

            // Redirect with error message
            header("Location: login.php?error=Invalid password. Please try again.");
            exit();
        }
    } 
   // Doctor Login Logic
elseif ($result_doctor->num_rows > 0) {
    $doctor = $result_doctor->fetch_assoc();

    // Verify the password for doctor
    if (password_verify($password, $doctor['password'])) {
        // Store successful login in session
        $_SESSION['doctor_id'] = $doctor['doctor_id'];
        $_SESSION['doctor_name'] = $doctor['doctor_name']; // You can add other info as needed
        $_SESSION['role'] = 'doctor'; // Set role as doctor

        // Log the successful login in doctor_login_history table
        $success = 1;  // 1 = success
        $stmt_log = $conn->prepare("INSERT INTO doctor_login_history (doctor_id, ip_address, success) VALUES (?, ?, ?)");
        
        // Bind as string for doctor_id
        $stmt_log->bind_param("ssi", $doctor['doctor_id'], $ip_address, $success);
        $stmt_log->execute();

        // Redirect to doctor dashboard
        header("Location: doctor_home.php");
        exit();
    } else {
        // Log failed login attempt for doctor
        $success = 0;  // 0 = failed
        $stmt_log = $conn->prepare("INSERT INTO doctor_login_history (doctor_id, ip_address, success) VALUES (?, ?, ?)");
        
        // Bind as string for doctor_id
        $stmt_log->bind_param("ssi", $doctor['doctor_id'], $ip_address, $success);
        $stmt_log->execute();

        // Redirect with error message
        header("Location: login.php?error=Invalid password. Please try again.");
        exit();
    }
}

   // Patient Login Logic
elseif ($result_patient->num_rows > 0) {
    $patient = $result_patient->fetch_assoc();

    // Verify the password for patient without hashing
    if (password_verify($password, $patient['password'])) {
        // Store successful login in session
        $_SESSION['patient_id'] = $patient['patient_id'];
        $_SESSION['patient_name'] = $patient['name']; // Use the correct column name for patient name
        $_SESSION['role'] = 'patient'; // Set role as patient

        // Log the successful login in patient_login_history table
        $success = 1;  // 1 = success
        $stmt_log = $conn->prepare("INSERT INTO patient_login_history (patient_id, ip_address, success) VALUES (?, ?, ?)");
        $stmt_log->bind_param("ssi", $patient['patient_id'], $ip_address, $success); 
        $stmt_log->execute();

        // Redirect to patient dashboard
        header("Location: patient_home.php");
        exit();
    } else {
        // Log failed login attempt for patient
        $success = 0;  // 0 = failed
        $stmt_log = $conn->prepare("INSERT INTO patient_login_history (patient_id, ip_address, success) VALUES (?, ?, ?)");
        $stmt_log->bind_param("ssi", $patient['patient_id'], $ip_address, $success); 
        $stmt_log->execute();

        // Redirect with error message
        header("Location: login.php?error=Invalid password for patient. Please try again.");
        exit();
    }
    } else {
        // No user, doctor, or patient found with this ID
        header("Location: login.php?error=ID not found. Please try again.");
        exit();
    }
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
    <title>Login Page</title>
    <link rel="stylesheet" href="css/loginstyle.css">
    <script>
        function showError(message) {
            alert(message);
        }
    </script>
</head>
<body>
<div class='box'>
<div class='wave -one'> </div>
<div class='wave -two'></div>
    
    </div>
    </div>
    <div class="wrapper">
        <!-- PDMS Information Section -->
        <div class="pdms-info">
            <div class="pdms-image">
                <img src="images/logo.png" alt="PDMS_logo">
            </div>
            <div class="pdms-text">
                <h1>Patient Data Management System</h1>
                <p>Effortlessly manage patient records and appointments in one system.</p>
            </div>
        </div>

        <!-- Login Form Section -->
        <div class="login-container">
            <h2>Login</h2>
            <form action="" method="POST">
                <div class="input-group">
                    <label>Username (or Email for Doctor):</label>
                    <input type="text" name="username" required><br><br>
                </div>
                <div class="input-group">
                    <label for="password">Password:</label>
                    <input type="password" name="password" id="password" required><br><br>
                </div>
                <div class="input-group">
                    <button type="submit">Login</button>
                </div>
            </form>
            <?php if (isset($_GET['error'])): ?>
                <script>
                    showError("<?php echo htmlspecialchars($_GET['error']); ?>");
                </script>
            <?php endif; ?>
        </div>
</body>

</html>





