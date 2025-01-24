<?php
include 'db.php'; 

if (isset($_GET['patient_id'])) {
    $patient_id = $_GET['patient_id'];

    // Prepare the query
    $stmt = $conn->prepare("
        SELECT 
            patient_id, 
            CONCAT(first_name, ' ', middle_initial, ' ', last_name) AS name, 
            address, 
            currently_at, 
            phone, 
            occupation, 
            birthday, 
            height, 
            husband, 
            husband_occupation, 
            philhealth_number,
            gravida,  -- Add gravida field
            para,       -- Add para field
            abortus     -- Add abortus field
        FROM patienttb 
        WHERE patient_id = ?
    ");
    $stmt->bind_param("s", $patient_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $patient = $result->fetch_assoc();

        // Calculate age from birthday
        if (!empty($patient['birthday'])) {
            $birthDate = new DateTime($patient['birthday']);
            $today = new DateTime();
            $age = $today->diff($birthDate)->y; // Calculate the age in years
            $patient['age'] = $age; // Add age to the response
        } else {
            $patient['age'] = "N/A"; // If birthday is missing, set age as "N/A"
        }

        echo json_encode($patient); // Return patient details as JSON
    } else {
        echo json_encode(["error" => "Patient not found"]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["error" => "Invalid request"]);
}
?>
