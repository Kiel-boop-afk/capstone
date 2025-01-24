<?php
session_start();
require 'db.php'; // Include your database connection

// Check if the user is logged in
if (!isset($_SESSION['username']) || !isset($_SESSION['clinic_id'])) {
    header("Location: login.php");
    exit();
}

// Check if there's an error message in the session
if (isset($_SESSION['error'])) {
    echo '<div class="error-message" style="color: red;">' . $_SESSION['error'] . '</div>'; // Display the error message
    unset($_SESSION['error']); // Clear the error message from the session after displaying it
}

// Initialize variables
$clinic_id = $_SESSION['clinic_id']; // Get clinic ID from the session (user's clinic)

// Handle form submission for adding a patient
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_patient'])) {
    // Patient details
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $middle_initial = trim($_POST['middle_initial']);
    $address = trim($_POST['address']);
    $phone = trim($_POST['phone']); // Get the phone number
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT); // Get the password input
    $occupation = trim($_POST['occupation']); // Get occupation
    $birthday = trim($_POST['birthday']); // Get birthday
    $height = trim($_POST['height']); // Get height
    $husband = trim($_POST['husband']); // Get husband's name
    $husband_occupation = trim($_POST['husband_occupation']); // Get husband's occupation
    $philhealth_number = trim($_POST['philhealth_number']); // Get PhilHealth number
    $gravida = trim($_POST['gravida']); // Get gravida value
    $para = trim($_POST['para']); // Get para value
    $abortus = trim($_POST['abortus']); // Get abortus value

    // Validate that Para + Abortus does not exceed Gravida
    if (($para + $abortus) > $gravida) {
        echo "<script>alert('Error: The combined value of Para and Abortus cannot exceed Gravida.');</script>";
        exit(); // Stop further execution if validation fails
    }

    // Combine name fields to create the full name
    $name = trim("$first_name $middle_initial $last_name");

    // Step 1: Get the last patient_id to determine the next one
    $sql_last_id = "SELECT patient_id FROM patienttb ORDER BY patient_id DESC LIMIT 1";
    $stmt_last_id = $conn->prepare($sql_last_id);
    $stmt_last_id->execute();
    $result_last_id = $stmt_last_id->get_result();

    $new_patient_id = "ptnt-1"; // Default to ptnt-1 if no records found

    if ($result_last_id->num_rows > 0) {
        $last_patient = $result_last_id->fetch_assoc();
        $last_id = $last_patient['patient_id'];

        // Extract the numeric part and increment it
        $numeric_part = (int) substr($last_id, 5); // Get the numeric part after "ptnt-"
        $new_numeric_part = $numeric_part + 1; // Increment
        $new_patient_id = "ptnt-" . $new_numeric_part; // Create new patient_id
    }

    // Step 2: Check if the new patient_id already exists
    $sql_check_id = "SELECT patient_id FROM patienttb WHERE patient_id = ?";
    $stmt_check_id = $conn->prepare($sql_check_id);
    $stmt_check_id->bind_param("s", $new_patient_id);
    $stmt_check_id->execute();
    $result_check_id = $stmt_check_id->get_result();

    // If the patient_id already exists, generate a new one
    while ($result_check_id->num_rows > 0) {
        $new_numeric_part++; // Increment numeric part
        $new_patient_id = "ptnt-" . $new_numeric_part; // Create new patient_id
        $stmt_check_id->execute(); // Check again
        $result_check_id = $stmt_check_id->get_result();
    }

    // Step 3: Insert the new patient data into the database
    $sql = "INSERT INTO patienttb (patient_id, name, last_name, first_name, middle_initial, address, phone, occupation, birthday, height, husband, husband_occupation, philhealth_number, gravida, para, abortus, currently_at, password) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    // Bind parameters, including Gravida, Para, and Abortus
    $stmt->bind_param("sssssssssdsssiiiss", $new_patient_id, $name, $last_name, $first_name, $middle_initial, $address, $phone, $occupation, $birthday, $height, $husband, $husband_occupation, $philhealth_number, $gravida, $para, $abortus, $clinic_id, $password);

    if ($stmt->execute()) {
        // Redirect to the same page after successful submission
        header("Location: patient.php?success=1");
        exit(); // Make sure to exit after redirect
    } else {
        echo "<script>alert('Error: Could not add patient');</script>";
    }

    $stmt->close();
}






// Initialize variables
$patient_id = $first_name = $last_name = $middle_initial = $phone = $height = $address = $referred_from = $referred_to = "";

// Check if the edit parameter is present in the URL
if (isset($_GET['edit'])) {
    $patient_id = $_GET['edit'];

    // Fetch the data of the patient with the given ID
    $sql = "SELECT * FROM patienttb WHERE patient_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $patient_id); // Change to 's' for VARCHAR
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $patient = $result->fetch_assoc();
        // Pre-fill form fields with patient data
        $first_name = $patient['first_name'];
        $last_name = $patient['last_name'];
        $middle_initial = $patient['middle_initial'];
        $phone = $patient['phone'];
        $height = $patient['height'];
        $address = $patient['address'];
        $referred_from = $patient['referred_from'];
        $referred_to = $patient['referred_to'];
        $name = $patient['name'];
    }
    $stmt->close();
}

// Handle form submission for updating patient data
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_patient'])) {
    // Retrieve form data
    $patient_id = $_POST['patient_id'];
    $first_name = $_POST['first_name'];   // New field
    $last_name = $_POST['last_name'];     // New field
    $middle_initial = $_POST['middle_initial']; // New field
    $phone = $_POST['phone'];             // New field
    $height = $_POST['height'];           // New field
    $address = $_POST['address'];
    $referred_from = $_POST['referred_from'];
    $referred_to = $_POST['referred_to'];

    // Combine first name, last name, and middle initial for the full name
    $name = $first_name . " " . $middle_initial . ". " . $last_name; // Combining name parts

    // Update the patient data in the database
    $sql = "UPDATE patienttb SET name = ?, first_name = ?, last_name = ?, middle_initial = ?, phone = ?, height = ?, address = ?, referred_from = ?, referred_to = ? WHERE patient_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssss", $name, $first_name, $last_name, $middle_initial, $phone, $height, $address, $referred_from, $referred_to, $patient_id);

    // Execute the query and handle success or failure
    if ($stmt->execute()) {
        // Redirect after successful update
        header("Location: patient.php?success=1");
        exit();
    } else {
        echo "<script>alert('Error: Could not update patient data');</script>";
    }
    $stmt->close();
}


$clinic_name = ""; // Initialize variable for clinic name
$sql_clinic = "SELECT name FROM clinictb WHERE id = ?"; // Query to fetch clinic name by ID
$stmt_clinic = $conn->prepare($sql_clinic);
$stmt_clinic->bind_param("s", $clinic_id); // Bind as a string now
$stmt_clinic->execute();
$result_clinic = $stmt_clinic->get_result();

if ($result_clinic->num_rows > 0) {
    $clinic_row = $result_clinic->fetch_assoc();
    $clinic_name = htmlspecialchars($clinic_row['name']); // Get the clinic name
}

// Fetch all clinics except the user's clinic
$sql_all_clinics = "SELECT id, name FROM clinictb WHERE id != ?"; // Exclude user's clinic
$stmt_all_clinics = $conn->prepare($sql_all_clinics);
$stmt_all_clinics->bind_param("s", $clinic_id); // Bind as a string now
$stmt_all_clinics->execute();
$result_all_clinics = $stmt_all_clinics->get_result();

if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']); // Clear the message after displaying it
} else {
    $success_message = '';
}


// Check if there is a referral success message to display
if (isset($_SESSION['referral_success']) && $_SESSION['referral_success'] === true) {
    echo '<div id="popup" style="display:none;">Referral sent successfully and waiting for confirmation</div>';
    unset($_SESSION['referral_success']); // Clear the session variable
}

// Query to fetch doctors
$sql_doctor = "SELECT doctor_id, doctor_name FROM doctortb";
$result_doctors = $conn->query($sql_doctor);

// Check if there are doctors in the table
if ($result_doctors->num_rows > 0) {
    // Doctors retrieved successfully
} else {
    echo "No doctors found.";
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
    <link rel="stylesheet" href="css/user_style.css">
    <link rel="stylesheet" href="css/loading_screen_style.css">
    <title>Patient</title>
</head>
<body>
    <!-- Loading Screen -->
<div class="loading-overlay">
    <div class="pulse"></div>
</div>
    <?php
    // Check if there's a success message
    if (isset($_SESSION['success_message'])) {
        echo htmlspecialchars($_SESSION['success_message']);
        unset($_SESSION['success_message']); // Clear the message after displaying it
    }
    ?>
</div>

    <!-- header -->
    <?php $active = 'patient'; include('includes/header.php') ?>

    <main>
                    <!-- Search Bar with Button -->
            <div class="buttons-container">
                <div class="search-container">
                    <input type="text" class="search-bar" placeholder="Search...">
                    <button class="search-button" onclick="performSearch()">Search</button>
                </div>
                
                <div class="buttonsdiv">
                    <button onclick="openForm4()">REFER</button>
                    <button onclick="openForm2()">UPDATE</button>
                    <button onclick="openForm1()">ADD NEW</button>
                </div>
            </div>
            

        <div class="tablediv">
        <table>
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Referred From</th>
        <th>Referred To</th>
        <th>Address</th>
        <th>Clinic</th> <!-- Added Clinic column -->
        <th>Action</th>
        <th>Pofile</th>
        <th>Maternity Record</th>
    </tr>
    <?php
     // Fetch all patient data for display, including clinic name, for the user's clinic
     $sql = "SELECT p.patient_id, p.name, p.referred_from, p.referred_to, p.address, c.name AS clinic_name 
     FROM patienttb p 
     JOIN clinictb c ON p.currently_at = c.id 
     WHERE p.currently_at = ?"; // Filter by the logged-in user's clinic ID
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $clinic_id); // Bind the clinic ID parameter
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($patient = $result->fetch_assoc()) {
        echo "<tr tr class='searchable-item'>";
        echo "<td>" . htmlspecialchars($patient['patient_id']) . "</td>";
        echo "<td>" . htmlspecialchars($patient['name']) . "</td>";
        echo "<td>" . htmlspecialchars($patient['referred_from']) . "</td>";
        echo "<td>" . htmlspecialchars($patient['referred_to']) . "</td>";
        echo "<td>" . htmlspecialchars($patient['address']) . "</td>";
        echo "<td>" . htmlspecialchars($patient['clinic_name']) . "</td>"; // Display clinic name
        echo "<td><a href='patient.php?edit=" . $patient['patient_id'] . "'>SELECT</a></td>";
        echo "<td><button class='view-patient' data-patient-id='" . $patient['patient_id'] . "'>VIEW</button></td>";
        echo "<td><button class='view-maternity-record' data-patient-id='" . $patient['patient_id'] . "'>VIEW</button></td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='9'>No patients found for this clinic</td></tr>"; // Adjust colspan to match the new column
}
$stmt->close(); // Close the statement
?>
</table>
        </div>

    </main>
    <footer>&copy; 2024 Your Clinic</footer>
   <!-- The Popup Form (add form) -->
<div id="popupForm1" class="popup-form-container">
    <div class="popup-form">
        <span class="close-btn" onclick="closeForm1()">&times;</span>
        <h2>Add Patient</h2>
        <form action="patient.php" method="post" onsubmit="return validateGPA()">
            <div class="form-columns">
                <!-- Left Column -->
                <div class="form-column">
                    <label for="first_name">First Name: <span style="color: red;">*</span></label>
                    <input type="text" name="first_name" required>

                    <label for="middle_initial">Middle Initial: <span style="color: red;">*</span></label>
                    <input type="text" name="middle_initial" maxlength="1" required>

                    <label for="last_name">Last Name: <span style="color: red;">*</span></label>
                    <input type="text" name="last_name" required>

                    <label for="address">Address: <span style="color: red;">*</span></label>
                    <input type="text" name="address" required>

                    <label for="occupation">Occupation: <span style="color: red;">*</span></label>
                    <input type="text" name="occupation" required>

                    <label for="birthday">Birthday: <span style="color: red;">*</span></label>
                    <input type="date" name="birthday" required>

                    <label for="height">Height: <span style="color: red;">*</span></label>
                    <small style="display: block; font-size: 0.85em; color: #6c757d;">
    Enter height in feet using the format 0.00 (e.g., 5.05).
</small>
<input type="text" name="height" placeholder="e.g., 5.05" pattern="^\d+(\.\d{1,2})?$" title="Please enter height in feet using the format 0.00 (e.g., 5.05)" required>



                    <label for="husband">Husband's Name: <span style="color: gray;">(Optional)</span></label>
                    <input type="text" name="husband">
                </div>

                <!-- Right Column -->
                <div class="form-column">
                    <label for="husband_occupation">Husband's Occupation: <span style="color: gray;">(Optional)</span></label>
                    <input type="text" name="husband_occupation">

                    <label for="philhealth_number">PhilHealth Number: <span style="color: gray;">(Optional)</span></label>
                    <input type="text" name="philhealth_number">

                    <label for="phone">Phone Number: <span style="color: red;">*</span></label>
                    <input type="text" name="phone" maxlength="11" required>

                    <label for="password">Password: <span style="color: red;">*</span></label>
                    <input type="password" name="password" required>

                    <!-- New Fields for G, P, A -->
                    <label for="gravida">Gravida (G): <span style="color: red;">*</span></label>
                    <small style="display: block; font-size: 0.85em; color: #6c757d;">Total number of pregnancies, including the current one.</small>
                    <input type="number" name="gravida" id="gravida" min="0" required>

                    <label for="para">Para (P): <span style="color: red;">*</span></label>
                    <small style="display: block; font-size: 0.85em; color: #6c757d;">Number of pregnancies that reached 20 weeks or beyond (viable births).</small>
                    <input type="number" name="para" id="para" min="0" required>

                    <label for="abortus">Abortus (A): <span style="color: red;">*</span></label>
                    <small style="display: block; font-size: 0.85em; color: #6c757d;">Number of pregnancies that ended before 20 weeks (miscarriages or abortions).</small>
                    <input type="number" name="abortus" id="abortus" min="0" required>
                </div>
            </div>

            <div class="form-buttons">
                <button type="submit" name="add_patient">Add Patient</button>
            </div>
        </form>
    </div>
</div>







    <!-- The Second Popup Form (Update Form) -->
    <div id="popupForm2" class="popup-form-container">
        <div class="popup-form">
            <span class="close-btn" onclick="closeForm2()">&times;</span>
            <h2>Update Form</h2>
            <form action="patient.php" method="post">

    <?php if (!isset($patient_id) || empty($patient_id)): ?>
        <p style="color: red;">Error: No patient selected. Please select a patient before proceeding.</p>
    <?php else: ?>
   <!-- Hidden input to store the selected patient ID -->
<input type="hidden" id="patient_id" name="patient_id" value="<?php echo htmlspecialchars($patient_id); ?>">

<!-- First Name (locked by default) -->
<label for="first_name">First Name:</label><br>
<input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($first_name); ?>" required readonly onclick="openUnlockPopup('first_name')"><br><br>

<!-- Last Name (locked by default) -->
<label for="last_name">Last Name:</label><br>
<input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($last_name); ?>" required readonly onclick="openUnlockPopup('last_name')"><br><br>

<!-- Middle Initial (locked by default) -->
<label for="middle_initial">Middle Initial:</label><br>
<input type="text" id="middle_initial" name="middle_initial" value="<?php echo htmlspecialchars($middle_initial); ?>" maxlength="1" required readonly onclick="openUnlockPopup('middle_initial')"><br><br>

<!-- Phone (locked by default) -->
<label for="phone">Phone:</label><br>
<input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" required readonly onclick="openUnlockPopup('phone')"><br><br>

<!-- Height (locked by default) -->
<label for="height">Height:</label><br>
<input type="text" id="height" name="height" value="<?php echo htmlspecialchars($height); ?>" required readonly onclick="openUnlockPopup('height')"><br><br>

<!-- Address (locked by default) -->
<label for="address">Address:</label><br>
<input type="text" id="address" name="address" value="<?php echo htmlspecialchars($address); ?>" required readonly onclick="openUnlockPopup('address')"><br><br>

<!-- Hidden input to identify that this is an "Update" form -->
<input type="hidden" name="update_patient" value="1">

<button type="submit">Update Patient</button>

<!-- Popup Modal for Key Input -->
<div id="popup-key" style="display:none;">
    <div class="popup-content-key">
        <h3>Enter Key to Unlock Fields</h3>
        <label for="unlock_key">Enter Key:</label><br>
        <input type="password" id="unlock_key" name="unlock_key" placeholder="Enter Key" required><br><br>
        <button type="button" onclick="unlockFields()">Submit</button>
        <button type="button" onclick="closeUnlockPopup()">Cancel</button>
    </div>
</div>
    <?php endif; ?>
</form>
        </div>
    </div>

    <!-- The Third Popup Form (Search Form) -->
    <div id="popupForm3" class="popup-form-container">
        <div class="popup-form">
            <span class="close-btn" onclick="closeForm3()">&times;</span>
            <h2>Search Form</h2>
            <form action="search_patients.php" method="GET" target="_blank">
             <label for="search">Search Patient:</label>
             <input type="text" id="search" name="query" required>
             <button type="submit">Search</button>
            </form>
        </div>
    </div>

<!-- The Fourth Popup Form (Refer Form) -->
<div id="popupForm4" class="popup-form-container">
    <div class="popup-form">
        <span class="close-btn" onclick="closeForm4()">&times;</span>
        <h2>Referral Form</h2>
        <form action="submit_referral.php" method="post">
            <!-- Hidden input to store the selected patient ID -->
            <?php if (!isset($patient_id) || empty($patient_id)): ?>
                <p style="color: red;">Error: No patient selected. Please select a patient before proceeding.</p>
            <?php else: ?>
                <input type="hidden" id="patient_id" name="patient_id" value="<?php echo htmlspecialchars($patient_id); ?>">

                <label for="name">Patient Name:</label><br>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required readonly><br><br>

            <label for="refer_from">Refer From:</label>
            <input type="text" id="refer_from" name="refer_from" value="<?php echo $clinic_id; ?>" placeholder="From" required readonly><br><br>

            <label>Select Referral Type:</label><br>

            <!-- Option 1 (Clinic) -->
            <label>
                <input type="radio" id="option1" name="refer_type" value="clinic" onclick="toggleDropdown('clinicDropdown')">
                Clinic
            </label><br>
            <div id="clinicDropdown" class="dropdown-container" style="display: none;">
                <select name="refer_to_clinic">
                    <option value="" disabled selected>Select a clinic</option>
                    <?php while ($clinic_option = $result_all_clinics->fetch_assoc()): ?>
                        <option value="<?php echo htmlspecialchars($clinic_option['id']); ?>">
                            <?php echo htmlspecialchars($clinic_option['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div><br>

            <!-- Option 2 (Doctor) -->
            <label>
                <input type="radio" id="option2" name="refer_type" value="doctor" onclick="toggleDropdown('doctorDropdown')">
                Doctor
            </label><br>
            <div id="doctorDropdown" class="dropdown-container" style="display: none;">
                <select name="refer_to_doctor">
                    <option value="" disabled selected>Select a doctor</option>
                    <?php if ($result_doctors->num_rows > 0): ?>
                        <?php while ($doctor = $result_doctors->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($doctor['doctor_id']); ?>">
                                <?php echo htmlspecialchars($doctor['doctor_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </div><br>

            <!-- Option 3 (Hospital) -->
            <label>
                <input type="radio" id="option3" name="refer_type" value="hospital" onclick="toggleDropdown('hospitalDropdown')">
                Hospital
            </label><br>
            <div id="hospitalDropdown" class="dropdown-container" style="display: none;">
                <select name="refer_to_hospital">
                    <option value="" disabled selected>Select a hospital</option>
                    <?php // Populate options with hospitals; replace this example with your query results ?>
                </select>
            </div><br>

            <label for="reason">Reason for Referral:</label><br>
            <input type="text" id="reason" name="reason" placeholder="Enter the reason for referral" required><br><br>

            <p>Clinic Name: <?php echo $clinic_name; ?> (ID: <?php echo $clinic_id; ?>)</p> <!-- Display clinic name and ID -->
            <button type="submit" name="submit_referral">REFER</button>
            <?php endif; ?>


            
        </form>
    </div>
</div>

<!-- Patient Profile Popup -->
<div id="patientPopup" class="patient-profile-popup">
    <div class="popup-content">
        <button class="popup-close" aria-label="Close" onclick="closePopup()">&times;</button>
        <h2 class="popup-title">Patient Profile</h2>
        <div class="popup-details">
            <div class="popup-detail-row">
                <label for="popup-patient-id">Patient ID:</label>
                <span id="popup-patient-id"></span>
            </div>
            <div class="popup-detail-row">
                <label for="popup-name">Name:</label>
                <span id="popup-name"></span>
            </div>
            <div class="popup-detail-row">
                <label for="popup-address">Address:</label>
                <span id="popup-address"></span>
            </div>
            <div class="popup-detail-row">
                <label for="popup-currently-at">Currently At:</label>
                <span id="popup-currently-at"></span>
            </div>
            <div class="popup-detail-row">
                <label for="popup-phone">Phone:</label>
                <span id="popup-phone"></span>
            </div>
            <div class="popup-detail-row">
                <label for="popup-occupation">Occupation:</label>
                <span id="popup-occupation"></span>
            </div>
            <div class="popup-detail-row">
                <label for="popup-birthday">Birthday:</label>
                <span id="popup-birthday"></span>
            </div>
            <div class="popup-detail-row">
                <label for="popup-age">Age:</label>
                <span id="popup-age"></span>
            </div>
            <div class="popup-detail-row">
                <label for="popup-height">Height:</label>
                <span id="popup-height"></span>
            </div>
            <div class="popup-detail-row">
                <label for="popup-husband">Husband:</label>
                <span id="popup-husband"></span>
            </div>
            <div class="popup-detail-row">
                <label for="popup-husband-occupation">Husband's Occupation:</label>
                <span id="popup-husband-occupation"></span>
            </div>
            <div class="popup-detail-row">
                <label for="popup-philhealth-number">PhilHealth Number:</label>
                <span id="popup-philhealth-number"></span>
            </div>
            <!-- Add Gravidity, Para, and Abortus fields -->
            <div class="popup-detail-row">
                <label for="popup-gravida">Gravida:</label>
                <span id="popup-gravida"></span>
            </div>
            <div class="popup-detail-row">
                <label for="popup-para">Para:</label>
                <span id="popup-para"></span>
            </div>
            <div class="popup-detail-row">
                <label for="popup-abortus">Abortus:</label>
                <span id="popup-abortus"></span>
            </div>
        </div>
    </div>
</div>



<!-- Maternity Record Popup Structure -->
<div id="maternityPopup" class="maternity-record-popup">
    <div class="popup-content">
        <span class="popup-close">&times;</span>
        <h3>Maternity Records</h3>
        
        <!-- Maternity Records Table inside the Popup -->
        <div id="maternity-records-list">
            <!-- Maternity records table will be dynamically inserted here by JavaScript -->
        </div>
    </div>
</div>



</body>
<script src="js/patient_script.js"></script>
<script src="js/loading_screen.js"></script>
</html>
<?php
$stmt_clinic->close();
$stmt_all_clinics->close();
$conn->close();
?>