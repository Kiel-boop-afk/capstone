<?php
session_start();
require 'db.php'; // Include your database connection

// Redirect if user is not logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();

    
}

// Access user data from the session
$user_id = $_SESSION['user_id']; // Using user_id to get user-specific data from the database
// Get clinic ID from the session (user's clinic)
$clinic_id = $_SESSION['clinic_id'];

// Fetch patients from the same clinic
$sql_patients = "SELECT patient_id, name FROM patienttb WHERE currently_at = ?";
$stmt_patients = $conn->prepare($sql_patients);
$stmt_patients->bind_param("s", $clinic_id);
$stmt_patients->execute();
$result_patients = $stmt_patients->get_result();

// Fetch appointments for the user's clinic, including doctor information and status
$sql_appointments = "
    SELECT a.id AS appointment_id, 
           p.patient_id,            -- Select the patient's ID
           p.name AS patient_name, 
           a.reason, 
           a.date_and_time, 
           d.doctor_name AS doctor_name,  -- Select the doctor's name
           a.doctor_id AS doctor_id,      -- Select the doctor's ID
           a.status AS appointment_status -- Select the status of the appointment
    FROM appointmenttb a
    JOIN patienttb p ON a.patient_id = p.patient_id
    LEFT JOIN doctortb d ON a.doctor_id = d.doctor_id -- Left join with doctortb to get doctor's name
    WHERE a.clinic_id = ?";

$stmt_appointments = $conn->prepare($sql_appointments);
$stmt_appointments->bind_param("s", $clinic_id);
$stmt_appointments->execute();
$result_appointments = $stmt_appointments->get_result();


// Fetch doctors from the doctortb table
$sql_doctors = "SELECT doctor_id, doctor_name FROM doctortb"; // Assuming 'name' is the doctor's name column
$result_doctors = $conn->query($sql_doctors);
if (!$result_doctors) {
    die("Database query failed: " . $conn->error); // Check for errors in the query
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
    <link rel="stylesheet" href="css/searchable-dropdown.css">
    <link rel="stylesheet" href="css/appointment_style_inputs.css">
    <link rel="stylesheet" href="css/loading_screen_style.css">
    <title>Appointment</title>
</head>
<body>
    <!-- Loading Screen -->
    <div class="loading-overlay">
        <div class="pulse"></div>
    </div>

    <!-- Loading Popup -->
<div id="loadingPopup" class="loading-popup-container" style="display: none;">
    <div class="loading-popup">
        <div class="spinner"></div>
        <p>Inserting data, please wait...</p>
    </div>
</div>


    <!-- header -->
    <?php $active = 'appointment'; include('includes/header.php') ?>

    <main>
        <!-- Search Bar with Button -->
    <div class="buttons-container">
        <div class="search-container">
            <input type="text" class="search-bar" placeholder="Search...">
            <button class="search-button" onclick="performSearch()">Search</button>
        </div>

        <div class="buttonsdiv">
            <button onclick="openForm1()">ADD NEW</button>
        </div>
    </div>

        <div class="tablediv">
        <table>
    <tr>
        <th>Appointment ID</th>
        <th>Patient's Name</th>
        <th>Reason</th>
        <th>Date & Time</th>
        <th>Doctor's Name</th> <!-- New column for doctor's name -->
        <th>Status</th> <!-- New column for appointment status -->
        <th>Action</th>
    </tr>
    <?php
if ($result_appointments->num_rows > 0) {
    while ($appointment = $result_appointments->fetch_assoc()) {

        // Check if appointment status is 'rescheduled'
        $isRescheduledorDone = $appointment['appointment_status'] === 'rescheduled' || $appointment['appointment_status'] === 'done';
        // Get the current date and time
        $currentDateTime = new DateTime(); // current date and time
        $appointmentDateTime = new DateTime($appointment['date_and_time']); // appointment's date and time

        // Check if the appointment is overdue
        if ($appointmentDateTime < $currentDateTime && $appointment['appointment_status'] !== 'done' && $appointment['appointment_status'] !== 'rescheduled') {
            // Update the status to 'overdue' if the appointment date is in the past
            $updateSql = "UPDATE appointmenttb SET status = 'overdue' WHERE id = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("i", $appointment['appointment_id']); // Make sure 'appointment_id' exists in the table
            $updateStmt->execute();
            $updateStmt->close();
            
            // Set status to 'overdue' for display
            $appointment['appointment_status'] = 'overdue';
        }

        echo "<tr class='searchable-item'>";
        echo "<td>" . htmlspecialchars($appointment['appointment_id']) . "</td>";
        echo "<td>" . htmlspecialchars($appointment['patient_name']) . "</td>";
        echo "<td>" . htmlspecialchars($appointment['reason']) . "</td>";
        echo "<td>" . htmlspecialchars($appointment['date_and_time']) . "</td>";
        echo "<td>" . htmlspecialchars($appointment['doctor_name']) . "</td>"; // Display doctor's name
        echo "<td>" . htmlspecialchars($appointment['appointment_status']) . "</td>"; // Display appointment status
        
        // Disable the "done" button if the status is 'overdue'
        $disableDoneButton = ($appointment['appointment_status'] === 'overdue' || strpos($appointment['appointment_status'], 'overdue') !== false) ? 'disabled' : '';

        if ($isRescheduledorDone) {
            echo "<td><button class='reschedule-appointment' 
                            data-appointment-id='" . $appointment['appointment_id'] . "' 
                            data-patient-id='" . $appointment['patient_id'] . "' 
                            data-patient-name='" . $appointment['patient_name'] . "' 
                            data-reason='" . $appointment['reason'] . "' 
                            disabled>RESCHEDULE</button>
                    <button class='done-appointment' 
                            data-appointment-id='" . $appointment['appointment_id'] . "' 
                            data-patient-id='" . $appointment['patient_id'] . "' 
                            data-reason='" . $appointment['reason'] . "' 
                            disabled>DONE</button>
                    </td>";
        } else {
            echo "<td><button class='reschedule-appointment' 
                            data-appointment-id='" . $appointment['appointment_id'] . "' 
                            data-patient-id='" . $appointment['patient_id'] . "' 
                            data-patient-name='" . $appointment['patient_name'] . "' 
                            data-reason='" . $appointment['reason'] . "'>RESCHEDULE</button>
                    
                     <button class='done-appointment' 
                            data-appointment-id='" . $appointment['appointment_id'] . "' 
                            data-patient-id='" . $appointment['patient_id'] . "' 
                            data-reason='" . $appointment['reason'] . "' 
                            $disableDoneButton>DONE</button>
                    </td>";
        }
    }
} else {
    echo "<tr><td colspan='7'>No appointments found for this clinic.</td></tr>"; // Updated colspan to 7
}
?>
</table>
        </div>
    </main>
    <footer>&copy; 2024 Your Clinic</footer>

<!-- Overlay (Initially Hidden) -->
<div id="overlay" style="display: none;"></div>

<!-- Reschedule Form (Initially Hidden) -->
<div id="rescheduleForm" style="display: none;">
    <h3>Reschedule Appointment</h3>
    <form id="rescheduleFormContent" method="post" action="reschedule_appointment.php">
        
        <!-- Display Patient Information -->
        <label for="patientNameDisplay">Patient Name:</label>
        <input type="text" id="patientNameDisplay" name="patientNameDisplay" readonly><br><br>

        <label for="patientIdDisplay">Patient ID:</label>
        <input type="text" id="patientIdDisplay" name="patientIdDisplay" readonly><br><br>

        <label for="appointmentIdDisplay">Appointment ID:</label>
        <input type="text" id="appointmentIdDisplay" name="appointmentIdDisplay" readonly><br><br>

        <!-- Select New Date & Time -->
        <label for="newDate">Select New Date & Time:</label>
        <input type="datetime-local" id="newDate" name="newDate" required><br><br>

        <!-- Hidden Fields -->
        <input type="hidden" id="appointmentId" name="appointmentId">
        <input type="hidden" id="patientId" name="patientId">
        <input type="hidden" id="patientName" name="patientName">
        <input type="hidden" id="reason" name="reason">

        <button type="submit">Confirm Reschedule</button>
    </form>
    <button id="cancelReschedule">Cancel</button>
</div>



  <!-- The Popup Form (add form) -->
<div id="popupForm1" class="popup-form-container">
    <div class="popup-form">
        <span class="close-btn" onclick="closeForm1()">&times;</span>
        <h2>Add Form</h2>
        <form action="add_appointment.php" method="post">

            <!-- Searchable Dropdown for Patient -->
            <label for="patient_id">Select Patient:</label>
            <div class="dropdown-container">
                <!-- Search input for the dropdown -->
                <input 
                    type="text" 
                    id="patient_search" 
                    class="custom-input" 
                    placeholder="Search Patient..." 
                    autocomplete="off" 
                    oninput="filterPatients()" 
                >
                <!-- Dropdown list of patients -->
                <div id="patient_list" class="dropdown-list">
                    <?php
                    // Populate the dropdown with patients
                    if ($result_patients->num_rows > 0) {
                        while ($row = $result_patients->fetch_assoc()) {
                            echo "<div class='dropdown-item' 
                                  data-id='" . $row['patient_id'] . "' 
                                  data-name='" . htmlspecialchars($row['name']) . "'>
                                  " . htmlspecialchars($row['name']) . " (ID: " . $row['patient_id'] . ")
                                  </div>";
                        }
                    } else {
                        echo "<div class='dropdown-item'>No patients available</div>";
                    }
                    ?>
                </div>
            </div>
            
            <!-- Hidden input to store the selected patient ID -->
            <input type="hidden" id="patient_id" name="patient_id" class="custom-input">

            <label for="doctor_id">Select Doctor:</label>
            <select id="doctor_id" name="doctor_id" class="custom-input">
                <option value="none" selected>Select a doctor (optional)</option>
                <?php
                while ($doctor = $result_doctors->fetch_assoc()): ?>
                    <option value="<?php echo $doctor['doctor_id']; ?>">
                        <?php echo htmlspecialchars($doctor['doctor_name']); ?>
                    </option>
                <?php endwhile; ?>
            </select><br><br>

            <!-- Reason for Appointment (Radio Buttons) -->

            <label for="reason">Reason for Appointment:</label><br>
<div class="radiobtn">
    <label class="radio-label">
        <input type="radio" name="reason" value="TT" id="radio-tt">
        Tetanus Toxoid
    </label><br>
    <label class="radio-label">
        <input type="radio" name="reason" value="Antepartumvisit" id="radio-visit">
        Antepartum Visit
    </label><br>
    <label class="radio-label">
        <input type="radio" name="reason" value="Dental-Checkup" id="radio-dental">
        Dental Check-up
    </label><br>
    <label class="radio-label">
        <input type="radio" name="reason" value="first-pregnancy-checkup" id="radio-pregnancy">
        First Pregnancy Check-up
    </label><br>
</div>



<label for="date_and_time">Date and Time:</label>
<input type="datetime-local" id="date_and_time" name="date_and_time" class="custom-input" required 
       min="<?= date('Y-m-d\TH:i'); ?>"><br><br>

            <input type="hidden" name="add_appointment" value="1">
            <button type="submit" class="btn-submit">Add Appointment</button>

        </form>
    </div>
</div>



<!-- Popup Form for Maternity Record -->
<div id="maternityRecordPopup" class="popup-form-container" style="display: none;">
    <div class="popup-form">
        <span class="close-btn" onclick="closePopup1()">&times;</span>
        <h3>Add Maternity Record</h3>
        <form action="add_maternity_record.php" method="POST">
            <!-- Hidden fields to store patient and appointment details -->
<input type="hidden" id="maternity_patient_id" name="patient_id">
<input type="hidden" id="maternity_appointment_id" name="appointment_id">

<!-- Maternity Record Fields -->
 <!-- will be added in the maternity_record table  -->
<label for="lmp">Last Menstrual Period (LMP): <span style="color: red;">*</span></label>
<input type="date" id="lmp" name="lmp" class="maternity-input" required 
    onchange="calculateEDC()">

<label for="edc">Expected Date of Confinement (EDC): <span style="color: gray;">(Readonly)</span></label>
<input type="date" id="edc" name="edc" class="maternity-input" readonly>

<div class="form-row">
    <div class="form-group">
        <label for="gravida">Gravida (G): <span style="color: gray;">(Readonly)</span></label>
        <input type="number" id="gravida" name="gravida" class="maternity-input" readonly>
        <small class="note">* The value of Gravida will automatically increase by 1 after submission.</small>
    </div>
    <div class="form-group">
        <label for="para">Para (P): <span style="color: gray;">(Readonly)</span></label>
        <input type="number" id="para" name="para" class="maternity-input" readonly>
    </div>
    <div class="form-group">
        <label for="abortions">Abortions (A): <span style="color: gray;">(Readonly)</span></label>
        <input type="number" id="abortions" name="abortions" class="maternity-input" readonly>
    </div>
</div>



            <label for="family">Family No.: <span style="color: red;">*</span></label>
            <input type="number" name="family" class="maternity-input" rows="3" required></textarea>

            <hr>

            <!-- Submit and Cancel Buttons -->
            <div class="form-row">
                <button type="submit">Save Record</button>
                <button type="button" onclick="closePopup1()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<div id="ttPopupForm" class="popup-form-container">
    <div class="popup-form">
        <span class="close-btn" onclick="closePopup2()">&times;</span>
        <h3>TT Form</h3>
        <form id="ttForm" action="process_tt_form.php" method="POST">
            <!-- Hidden Fields for patient_id and appointment_id -->
            <input type="hidden" id="tt_patient_id" name="patient_id" >
            <input type="hidden" id="tt_appointment_id" name="appointment_id" >

            <!-- Date -->
            <label for="date">Date:</label>
            <input type="date" id="date" name="date" required>

            <!-- Notes -->
            <label for="notes">Notes:</label>
            <textarea id="notes" name="notes" rows="4" placeholder="Enter any notes here..."></textarea>

            <button type="submit">Save</button>
            <button type="button" onclick="closePopup2()">Cancel</button>
        </form>
    </div>
</div>


    


<!-- Dental Checkup Form -->
<div id="dentalCheckupForm" class="popup-form-container">
    <div class="popup-form">
        <span class="close-btn" onclick="closeDentalForm()">&times;</span>
        <h2>Dental Checkup</h2>
        <form id="dental_form" action="submit_dental_checkup.php" method="POST">
            <!-- Hidden Fields for appointment_id and patient_id -->
            <input type="hidden" id="dental_appointment_id" name="appointment_id" value="">
            <input type="hidden" id="dental_patient_id" name="patient_id" value="">

            <div>
                <label for="dentist_notes">Dentist Notes:</label>
                <textarea id="dentist_notes" name="dentist_notes" rows="4" required></textarea>
            </div>
            <div>
                <button type="submit" id="submit_dental_checkup">Submit</button>
                <button type="button" onclick="closeDentalForm()">Cancel</button>
            </div>
        </form>
    </div>
</div>


<!-- Popup Form for Antepartum Visit -->
<div id="antepartumVisitForm" class="popup-form-container" style="display: none;">
    <div class="popup-form">
        <span class="close-btn" onclick="closeAntepartumForm()">&times;</span>
        <h3>Antepartum Visit</h3>
        <form action="add_antepartum_visit.php" method="POST">
            <label for="visit_date">Date:</label>
            <input type="date" id="visit_date" name="visit_date" class="maternity-input" required>

            <!-- Hidden fields for patient_id and appointment_id -->
            <input type="hidden" id="av_patient_id" name="patient_id">
            <input type="hidden" id="av_appointment_id" name="appointment_id">


            <h4>Findings</h4>
            <div class="form-group">
    <label for="bp">BP (Blood Pressure):</label>
    <input type="text" id="bp" name="bp" class="maternity-input" placeholder="Enter BP" required>
</div>

<div class="form-group">
    <label for="wt">WT (Weight):</label>
    <input type="text" id="wt" name="wt" class="maternity-input" placeholder="Enter Weight" required>
</div>

<div class="form-group">
    <label for="fund_ht">Fund HT (Fundal Height):</label>
    <input type="text" id="fund_ht" name="fund_ht" class="maternity-input" placeholder="Enter Fundal Height" required>
</div>

<div class="form-group">
    <label for="presentation_fhb">Presentation/FHB (Presentation/Fetal Heart Beat):</label>
    <input type="text" id="presentation_fhb" name="presentation_fhb" class="maternity-input" placeholder="Enter Presentation or FHB" required>
</div>


            <label for="temperature">Temperature (TEMP):</label>
            <input type="number" id="temperature" name="temperature" class="maternity-input" step="0.1" required>

            <label for="aog">Age of Gestation (AOG):</label>
            <input type="text" id="aog" name="aog" class="maternity-input" required>

            <label for="care">Care/Remarks:</label>
            <textarea id="care" name="care" class="maternity-input" rows="3" required></textarea>

            <!-- Submit and Cancel Buttons -->
            <div class="form-row">
                <button type="submit">Save Record</button>
                <button type="button" onclick="closeAntepartumForm()">Cancel</button>
            </div>
        </form>
    </div>
</div>


    <script src="js/appointment_script.js"></script>
    <script src="js/loading_screen.js"></script>
    <script src="js/edc_calculator.js"></script>
</body>

</html>