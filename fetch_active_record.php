<?php
// Include the database connection
include 'db.php';

// Check if patient_id is provided via GET
if (isset($_GET['patient_id'])) {
    $patient_id = $_GET['patient_id'];

    // Query to fetch the active record ID
    $query = "SELECT record_id FROM maternity_record WHERE patient_id = ? AND status = 0 LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $patient_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Prepare the response
    $response = array();
    if ($row = $result->fetch_assoc()) {
        $response['record_id'] = $row['record_id'];
    } else {
        $response['record_id'] = null; // No active record found
    }

    // Return the response as JSON
    echo json_encode($response);

    // Close resources
    $stmt->close();
    $conn->close();
} else {
    // If no patient_id is provided, return an error response
    echo json_encode(['error' => 'No patient_id provided']);
}
?>
