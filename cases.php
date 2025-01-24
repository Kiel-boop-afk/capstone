<?php
session_start();
// Ensure the user is logged in
if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || !isset($_SESSION['clinic_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
require 'db.php'; // Include your database connection script

// Check if an edit is being requested and fetch the case details
if (isset($_GET['edit'])) {
    $case_id = $_GET['edit'];

    // Fetch the case details, including record_id, patient name, clinic name, and primary personnel details
    $query = "
        SELECT 
            casetb.case_id, 
            casetb.record_id, 
            casetb.patient_id, 
            patienttb.name AS patient_name, 
            casetb.clinic_id, 
            clinictb.name AS clinic_name, 
            casetb.date_and_time_of_admission, 
            casetb.admitting_diagnosis, 
            casetb.date_and_time_of_discharge, 
            casetb.date_and_time_of_delivery, 
            casetb.outcome_of_delivery, 
            p.personnel_name AS primary_personnel_name
        FROM casetb
        JOIN patienttb ON casetb.patient_id = patienttb.patient_id
        JOIN clinictb ON casetb.clinic_id = clinictb.id
        LEFT JOIN personneltb p ON casetb.primary_personnel_id = p.personnel_id
        WHERE casetb.case_id = ?";
        
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $case_id); // Using integer for case_id
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // Store the retrieved values
        $record_id = $row['record_id'];
        $patient_id = $row['patient_id'];
        $patient_name = $row['patient_name'];
        $clinic_name = $row['clinic_name'];
        $date_of_admission = $row['date_and_time_of_admission'];
        $admitting_diagnosis = $row['admitting_diagnosis'];
        $date_of_discharge = $row['date_and_time_of_discharge'];
        $date_of_delivery = $row['date_and_time_of_delivery'];
        $outcome_of_delivery = $row['outcome_of_delivery'];
        $primary_personnel_name = $row['primary_personnel_name'];
    }
    $stmt->close();
}


// Query to fetch cases with patient names, clinic names, primary personnel details, and record ID
$sql = "SELECT 
            c.case_id, 
            c.record_id, 
            p.name AS patient_name, 
            cl.name AS clinic_name, 
            c.date_and_time_of_admission, 
            c.admitting_diagnosis, 
            c.date_and_time_of_discharge, 
            c.date_and_time_of_delivery, 
            c.outcome_of_delivery, 
            c.discharge,
            c.deliver_status,
            per.personnel_name AS primary_personnel_name
        FROM casetb c
        LEFT JOIN patienttb p ON c.patient_id = p.patient_id
        LEFT JOIN clinictb cl ON c.clinic_id = cl.id
        LEFT JOIN personneltb per ON c.primary_personnel_id = per.personnel_id";


$result = $conn->query($sql);

// Get the user's clinic ID from the session
$clinic_id = $_SESSION['clinic_id'];
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
    <title>Home</title>
</head>
<body>
    <!-- Loading Screen -->
    <div class="loading-overlay">
        <div class="pulse"></div>
    </div>

    <!-- header -->
    <?php $active = 'cases'; include('includes/header.php') ?>

    <main>
        <!-- Search Bar with Button -->
        <div class="buttons-container">
            <div class="search-container">
                <input type="text" class="search-bar" placeholder="Search...">
                <button class="search-button" onclick="performSearch()">Search</button>
            </div>
            <div class="buttonsdiv">
                <button onclick="openForm4()">DISCHARGE</button>
                <button onclick="openForm2()">UPDATE (DELIVERY)</button>
                <button onclick="openForm1()">ADD NEW</button>
            </div>
        </div>

        <div class="tablediv">
        <table>
            <thead>
                <tr>
                    <th>Case ID</th>
                    <th>Patient Name</th>
                    <th>Maternity Record</th>
                    <th>Clinic Name</th>
                    <th>Date and Time of Admission</th>
                    <th>Admitting Diagnosis</th>
                    <th>Date and Time of Discharge</th>
                    <th>Date and Time of Delivery</th>
                    <th>Outcome of Delivery</th>
                    <th>Discharge Status</th>
                    <th>Primary Personnel</th> <!-- Added column for primary personnel -->
                    <th>ACTION</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>
                            <td>" . htmlspecialchars($row['case_id']) . "</td>
                            <td>" . htmlspecialchars($row['patient_name']) . "</td>
                            <td><a href='maternity_record_view_expanded.php?case_id=" . htmlspecialchars($row['case_id']) . "' target='_blank'>EXPAND</a></td>
                            <td>" . htmlspecialchars($row['clinic_name']) . "</td>
                            <td>" . htmlspecialchars($row['date_and_time_of_admission']) . "</td> 
                            <td>" . htmlspecialchars($row['admitting_diagnosis']) . "</td>
                            <td>" . htmlspecialchars($row['date_and_time_of_discharge']) . "</td>
                            <td>" . htmlspecialchars($row['date_and_time_of_delivery']) . "</td>
                            <td>" . htmlspecialchars($row['outcome_of_delivery']) . "</td>
                            <td>" . ($row['discharge'] ? 'Yes' : 'No') . "</td>
                            <td>" . htmlspecialchars($row['primary_personnel_name']) . "</td> <!-- Displaying primary personnel -->
                            <td><a href='cases.php?edit=" . $row['case_id'] . "'>SELECT</a></td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='10'>No cases found.</td></tr>";
                }
                ?>
            </tbody>
        </table>

        </div>

    </main>
    <footer>&copy; 2024 Your Clinic</footer>
   <!-- The Popup Form (Add Form) -->
<div id="popupForm1" class="popup-form-container">
    <div class="popup-form">
        <span class="close-btn" onclick="closeForm1()">&times;</span>
        <h2>Add Case</h2>
        <form action="add_case.php" method="post">
            <!-- Dropdown for Patient ID -->
            <label for="patient_id">Patient:</label>
            <select name="patient_id" id="patient_id" required onchange="fetchRecordId(this.value)">

                <option value="">Select Patient</option>
                <?php
                // Fetch patients from the same clinic as the logged-in user
                $logged_in_clinic_id = $_SESSION['clinic_id']; // Clinic ID of the logged-in user
                $patient_query = "SELECT patient_id, name FROM patienttb WHERE currently_at = ?";
                $patient_stmt = $conn->prepare($patient_query);
                $patient_stmt->bind_param("s", $logged_in_clinic_id);
                $patient_stmt->execute();
                $result_set = $patient_stmt->get_result();

                // Populate dropdown options with patient data
                while ($patient_row = $result_set->fetch_assoc()) {
                    $patient_display_text = htmlspecialchars($patient_row['patient_id'] . " - " . $patient_row['name']);
                    echo "<option value='" . htmlspecialchars($patient_row['patient_id']) . "'>$patient_display_text</option>";
                }
                ?>
            </select>
            <br><br>

            <!-- Record ID Field -->
<label for="record_id">Record ID:</label>
<input type="text" name="record_id" id="record_id" readonly>
<br><br>

            <br><br>

            <!-- Clinic ID (Hidden Field) -->
            <input type="hidden" name="clinic_id" value="<?php echo htmlspecialchars($clinic_id); ?>">

            <!-- Date and Time of Admission -->
            <label for="date_and_time_of_admission">Date and Time of Admission:</label>
            <input type="datetime-local" name="date_and_time_of_admission" id="date_and_time_of_admission" required>
            <br><br>

            <!-- Admitting Diagnosis -->
            <label for="admitting_diagnosis">Admitting Diagnosis:</label>
            <input type="text" name="admitting_diagnosis" id="admitting_diagnosis" required>
            <br><br>

            <button type="submit">Add Case</button>
        </form>
    </div>
</div>


    <!-- The Popup Form for Updating the Case -->
<div id="popupForm2" class="popup-form-container">
    <div class="popup-form">
        <span class="close-btn" onclick="closeForm2()">&times;</span>
        <h2>Update Delivery Form</h2>
        <form action="update_delivery.php" method="post">
        <?php if (!isset($case_id) || empty($case_id)): ?>
            <p style="color: red;">Error: No case selected. Please select a case before proceeding.</p>
        <?php else: ?>
            <input type="hidden" name="case_id" value="<?php echo htmlspecialchars($case_id); ?>">

            <!-- Display Patient ID and Name -->
            <label>Patient ID:</label>
            <input type="text" name="patient_id" value="<?php echo htmlspecialchars($patient_id); ?>" readonly><br><br>

            <label>Patient Name:</label>
            <input type="text" name="patient_name" value="<?php echo htmlspecialchars($patient_name); ?>" readonly><br><br>

            <!-- Display Clinic Name -->
            <label>Clinic:</label>
            <input type="text" name="clinic_name" value="<?php echo htmlspecialchars($clinic_name); ?>" readonly><br><br>

            <!-- Display Date of Admission -->
            <label>Date of Admission:</label>
            <input type="text" value="<?php echo htmlspecialchars($date_of_admission); ?>" readonly><br><br>

            <!-- Update Outcome of Delivery -->
            <label>Outcome of Delivery:</label>
            <input type="text" name="outcome_of_delivery" value="<?php echo htmlspecialchars($outcome_of_delivery); ?>" required><br><br>

            <!-- Update Date and Time of Delivery -->
            <label>Date and Time of Delivery:</label>
            <input type="datetime-local" name="date_and_time_of_delivery" value="<?php echo htmlspecialchars($date_of_delivery); ?>" required><br><br>

            <!-- Select Primary Personnel -->
            <label>Primary Personnel:</label>
            <select name="primary_personnel_id" required>
                <option value="">Select Primary Personnel</option>
                <?php
                // Fetch and list personnel from personneltb
                $personnelQuery = "SELECT personnel_id, personnel_name FROM personneltb WHERE clinic_id = ?";
                $personnelStmt = $conn->prepare($personnelQuery);
                $personnelStmt->bind_param("s", $clinic_id);
                $personnelStmt->execute();
                $personnelResult = $personnelStmt->get_result();

                while ($personnel = $personnelResult->fetch_assoc()) {
                    echo "<option value='" . htmlspecialchars($personnel['personnel_id']) . "'>" . htmlspecialchars($personnel['personnel_name']) . "</option>";
                }

                $personnelStmt->close();
                ?>
            </select><br><br>

            <!-- Select Multiple Personnel -->
            <label>Personnel Present:</label>
            <small>Select multiple personnel by holding Ctrl (Windows) or Command (Mac) while clicking.</small><br><br>
            <select name="personnel_ids[]" multiple required>
                <?php
                // Same list as above for multiple personnel
                $personnelStmt = $conn->prepare($personnelQuery);
                $personnelStmt->bind_param("s", $clinic_id);
                $personnelStmt->execute();
                $personnelResult = $personnelStmt->get_result();

                while ($personnel = $personnelResult->fetch_assoc()) {
                    echo "<option value='" . htmlspecialchars($personnel['personnel_id']) . "'>" . htmlspecialchars($personnel['personnel_name']) . "</option>";
                }

                $personnelStmt->close();
                ?>
            </select>

            <button type="submit">Update Case</button>
        <?php endif; ?>
        </form>
    </div>
</div>




    <!-- The Second Popup Form (Search Form) -->
    <div id="popupForm3" class="popup-form-container">
        <div class="popup-form">
            <span class="close-btn" onclick="closeForm3()">&times;</span>
            <h2>Search Form</h2>
            <form action="search_cases.php" method="GET" target="_blank">
             <label for="search">Search Cases:</label>
             <input type="text" id="search" name="query" required>
             <button type="submit">Search</button>
            </form>
        </div>
    </div>

    <!-- The Popup Form for Discharge -->
<div id="popupForm4" class="popup-form-container">
    <div class="popup-form">
        <span class="close-btn" onclick="closeForm4()">&times;</span>
        <h2>Discharge Form</h2>
        <form action="update_discharge.php" method="post">
        <?php if (!isset($case_id) || empty($case_id)): ?>
            <p style="color: red;">Error: No case selected. Please select a case before proceeding.</p>
        <?php else: ?>
            <input type="hidden" name="case_id" value="<?php echo htmlspecialchars($case_id); ?>">

            <!-- Display Patient ID and Name -->
            <label>Patient ID:</label>
            <input type="text" name="patient_id" value="<?php echo htmlspecialchars($patient_id); ?>" readonly><br><br>

            <label>Patient Name:</label>
            <input type="text" name="patient_name" value="<?php echo htmlspecialchars($patient_name); ?>" readonly><br><br>

            <!-- Display Clinic Name -->
            <label>Clinic:</label>
            <input type="text" name="clinic_name" value="<?php echo htmlspecialchars($clinic_name); ?>" readonly><br><br>

            <!-- Display Date of Admission -->
            <label>Date of Admission:</label>
            <input type="text" value="<?php echo htmlspecialchars($date_of_admission); ?>" readonly><br><br>

            <!-- Display Admitting Diagnosis -->
            <label>Admitting Diagnosis:</label>
            <input type="text" value="<?php echo htmlspecialchars($admitting_diagnosis); ?>" readonly><br><br>

            <!-- Display Outcome of Delivery -->
            <label>Outcome of Delivery:</label>
            <input type="text" value="<?php echo htmlspecialchars($outcome_of_delivery); ?>" readonly><br><br>

            <!-- Display Date and Time of Delivery -->
            <label>Date and Time of Delivery:</label>
            <input type="text" value="<?php echo htmlspecialchars($date_of_delivery); ?>" readonly><br><br>

            <!-- Update Date and Time of Discharge -->
            <label>Date and Time of Discharge:</label>
            <input type="datetime-local" name="date_and_time_of_discharge" value="<?php echo htmlspecialchars($date_of_discharge); ?>" required><br><br>

            <button type="submit">Discharge Case</button>
        <?php endif; ?>

            
        </form>
    </div>
</div>


</body>
<script src="js/cases_script.js"></script>
<script src="js/loading_screen.js"></script>
</html>

<?php
$conn->close();
?>