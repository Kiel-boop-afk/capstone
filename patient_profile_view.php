<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="images/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="images/favicon.svg" />
    <link rel="shortcut icon" href="images/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="images/apple-touch-icon.png" />
    <title>Patient Profile</title>
    <link rel="stylesheet" href="css/patient_profile_view_style.css"> <!-- Link to your CSS file -->
</head>
<body>
        <!-- Exit Button -->
<button class="exit-btn" onclick="window.close();">Exit</button>
    <div class="profile-container">
        <header>
            <h1>Patient Profile</h1>
        </header>

        <div class="patient-details">
            <?php
            // Include database connection
            include('db.php');

            // Function to calculate age from birthday
            function calculate_age($birthday) {
                $birthDate = new DateTime($birthday);
                $today = new DateTime();
                $age = $today->diff($birthDate)->y;
                return $age;
            }

            // Check if patient_id is set
            if (isset($_GET['patient_id'])) {
                $patient_id = $_GET['patient_id'];

                // Prepare and execute the query to fetch patient details
                $sql = "SELECT * FROM patienttb WHERE patient_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $patient_id);
                $stmt->execute();
                $result = $stmt->get_result();

                // Check if patient exists
                if ($result->num_rows > 0) {
                    $patient = $result->fetch_assoc();
                    $age = calculate_age($patient['birthday']); // Calculate the patient's age
                    $currently_at = $patient['currently_at'];

                    // Determine the type of the 'currently_at' (doctor, clinic, or hospital)
                    $location_name = '';
                    $location_type = '';

                    // Check if it's a doctor
                    $doctor_sql = "SELECT doctor_name FROM doctortb WHERE doctor_id = ?";
                    $doctor_stmt = $conn->prepare($doctor_sql);
                    $doctor_stmt->bind_param("s", $currently_at);
                    $doctor_stmt->execute();
                    $doctor_result = $doctor_stmt->get_result();

                    if ($doctor_result->num_rows > 0) {
                        $doctor = $doctor_result->fetch_assoc();
                        $location_name = $doctor['doctor_name'];
                        $location_type = 'Doctor';
                    } else {
                        // Check if it's a clinic
                        $clinic_sql = "SELECT name FROM clinictb WHERE id = ?";
                        $clinic_stmt = $conn->prepare($clinic_sql);
                        $clinic_stmt->bind_param("s", $currently_at);
                        $clinic_stmt->execute();
                        $clinic_result = $clinic_stmt->get_result();

                        if ($clinic_result->num_rows > 0) {
                            $clinic = $clinic_result->fetch_assoc();
                            $location_name = $clinic['name'];
                            $location_type = 'Clinic';
                        } else {
                            // Check if it's a hospital
                            $hospital_sql = "SELECT name FROM hospitaltb WHERE id = ?";
                            $hospital_stmt = $conn->prepare($hospital_sql);
                            $hospital_stmt->bind_param("s", $currently_at);
                            $hospital_stmt->execute();
                            $hospital_result = $hospital_stmt->get_result();

                            if ($hospital_result->num_rows > 0) {
                                $hospital = $hospital_result->fetch_assoc();
                                $location_name = $hospital['name'];
                                $location_type = 'Hospital';
                            }
                        }
                    }

                    // Display patient details in a professional format
                    echo "<div class='details-grid'>";
                    echo "<div class='detail-item'><strong>ID:</strong> " . htmlspecialchars($patient['patient_id']) . "</div>";
                    echo "<div class='detail-item'><strong>Name:</strong> " . htmlspecialchars($patient['name']) . "</div>";
                    echo "<div class='detail-item'><strong>Address:</strong> " . htmlspecialchars($patient['address']) . "</div>";
                    echo "<div class='detail-item'><strong>Occupation:</strong> " . htmlspecialchars($patient['occupation']) . "</div>";
                    echo "<div class='detail-item'><strong>Birthday:</strong> " . htmlspecialchars($patient['birthday']) . "</div>";
                    echo "<div class='detail-item'><strong>Phone Number:</strong> " . htmlspecialchars($patient['phone']) . "</div>";
                    echo "<div class='detail-item'><strong>Age:</strong> " . htmlspecialchars($age) . "</div>"; // Display the calculated age
                    echo "<div class='detail-item'><strong>Height:</strong> " . htmlspecialchars($patient['height']) . "</div>";
                    echo "<div class='detail-item'><strong>Husband:</strong> " . htmlspecialchars($patient['husband']) . "</div>";
                    echo "<div class='detail-item'><strong>Husband's Occupation:</strong> " . htmlspecialchars($patient['husband_occupation']) . "</div>";
                    echo "<div class='detail-item'><strong>PhilHealth Number:</strong> " . htmlspecialchars($patient['philhealth_number']) . "</div>";

                    // Display currently_at based on its type (Doctor, Clinic, or Hospital)
                    if ($location_name != '') {
                        echo "<div class='detail-item'><strong>Currently At ($location_type):</strong> " . htmlspecialchars($location_name) . " (ID: " . htmlspecialchars($currently_at) . ")</div>";
                    } else {
                        echo "<div class='detail-item'><strong>Currently At:</strong> Not found.</div>";
                    }

                    echo "</div>";
                } else {
                    echo "<p>No patient found.</p>";
                }
                $stmt->close();
            } else {
                echo "<p>Invalid patient ID.</p>";
            }
            ?>
        </div>
    </div>


    <!-- Optional footer -->
    <footer>
        <p>&copy; 2024 ClinicName. All Rights Reserved.</p>
    </footer>

</body>
</html>
