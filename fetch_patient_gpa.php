<?php
// Include your database connection
require_once 'db.php';

if (isset($_POST['patient_id'])) {
    $patient_id = $_POST['patient_id'];

    // Fetch gravida, para, and abortus from patienttb
    $query = "SELECT gravida, para, abortus FROM patienttb WHERE patient_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $patient_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        echo json_encode($data); // Send the data as JSON
    } else {
        echo json_encode(['error' => 'No data found']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['error' => 'Invalid request']);
}
?>
