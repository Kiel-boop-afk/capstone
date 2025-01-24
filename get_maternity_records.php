<?php
include 'db.php'; // Include your database connection file

// Check if patient_id is provided in the query string
if (isset($_GET['patient_id'])) {
    // Sanitize and assign the patient_id from the GET request
    $patient_id = $_GET['patient_id'];

    // Prepare the SQL query to fetch only record_id, patient_id, and name from the maternity_record table
    $stmt = $conn->prepare("SELECT record_id, patient_id, name 
                            FROM maternity_record 
                            WHERE patient_id = ?");
    // Bind the patient_id as a string parameter for the query
    $stmt->bind_param("s", $patient_id);
    
    // Execute the statement
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if any records were found
    if ($result->num_rows > 0) {
        // Fetch all the results and store them in an array
        $maternityRecords = [];
        while ($row = $result->fetch_assoc()) {
            $maternityRecords[] = $row;
        }

        // Return the fetched records as a JSON response
        echo json_encode($maternityRecords);
    } else {
        // Return an empty array if no records found for the given patient_id
        echo json_encode(["message" => "No maternity records found for this patient."]);
    }

    // Close the prepared statement and connection
    $stmt->close();
    $conn->close();
} else {
    // If patient_id is not provided, return an error message
    echo json_encode(["error" => "Patient ID is missing"]);
}
?>
