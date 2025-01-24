<?php
session_start();
require 'db.php'; // Database connection
require 'vendor/autoload.php'; // Load PHPMailer

// Function to generate a random token
function generateToken($length = 64) {
    return bin2hex(random_bytes($length / 2)); // Generate a secure 64-character token
}

// Check if the user is logged in and is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    $clinic_id = $_POST['clinic_id'];

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Generate token and expiration date
    $token = generateToken();
    $token_expiration = date("Y-m-d H:i:s", strtotime("+1 hour")); // Token expires in 1 hour

    // Store the token, email, and other user data in the database
    $stmt = $conn->prepare("INSERT INTO email_confirmation (email, token, token_expiration, username, hashed_password, role, clinic_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $email, $token, $token_expiration, $username, $hashed_password, $role, $clinic_id);
    
    if ($stmt->execute()) {
        // Send confirmation link to user's email
        $mail = new PHPMailer\PHPMailer\PHPMailer();
        try {
            // Set up PHPMailer
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Replace with your SMTP host
            $mail->SMTPAuth = true;
            $mail->Username = 'testemailsender740@gmail.com'; // Your SMTP username
            $mail->Password = 'xcsqssdwzzbdjzmw'; // Your SMTP password
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Recipients
            $mail->setFrom('your_email@example.com', 'Your Website Name');
            $mail->addAddress($email); // Send email to the new user

            // Confirmation link (replace 'localhost' with your actual domain if not in localhost)
            $confirmation_link = "http://localhost:3000/confirm_email.php?token=$token";

            // Email content
            $mail->isHTML(true);
            $mail->Subject = 'Confirm Your Email Address';
            $mail->Body = "Dear $username,<br>Click the link below to confirm your email address and complete your registration:<br><a href='$confirmation_link'>Confirm Email</a>";
            $mail->AltBody = "Dear $username,\nClick the link below to confirm your email address and complete your registration:\n$confirmation_link";

            $mail->send();
            echo "<script>alert('A confirmation link has been sent to your email.');</script>";
        } catch (Exception $e) {
            echo "<script>alert('Error sending confirmation email.');</script>";
        }
    } else {
        echo "<script>alert('Error storing user data. Please try again.');</script>";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/create_user_style.css">
    <title>Create Account</title>
</head>
<body>
<a href="admin_home.php">Back to Admin Dashboard</a> 
    <h2>Create Account</h2>
    <form method="POST" action="">
        <label>Username:</label><input type="text" name="username" required><br>
        
        <label>Email:</label><input type="email" name="email" required><br>
        
        <label>Password:</label><input type="password" name="password" required><br>
        
        <label>Role:</label>
        <select name="role" required>
            <option value="">Select Role</option>
            <option value="admin">Admin</option>
            <option value="user">User</option>
            <option value="cashier">User(Cashier)</option>


        </select><br>
        
        <label>Clinic:</label>
        <select name="clinic_id" required>
            <option value="">Select Clinic</option>
            <?php
            // Fetch the clinic's id and name from clinictb
            $clinic_query = "SELECT id, name FROM clinictb";
            $clinic_result = $conn->query($clinic_query);

            if ($clinic_result->num_rows > 0) {
                while ($clinic = $clinic_result->fetch_assoc()) {
                    echo "<option value='{$clinic['id']}'>{$clinic['name']}</option>";
                }
            } else {
                echo "<option value=''>No clinics available</option>";
            }
            ?>
        </select><br>
        
        <button type="submit">Register</button>
    </form>
</body>
</html>
