<?php
// Include database connection
include 'db.php';

// Initialize variables
$patient_data = null;
$tt_history = [];
$present_history = [];
$antepartum_visits = [];
$dental_checkups = [];

if (isset($_GET['case_id'])) {
    $case_id = $_GET['case_id'];

    // Query to fetch record_id using case_id from casetb
    $query = "SELECT record_id FROM casetb WHERE case_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $case_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $case_record = $result->fetch_assoc();
        $record_id = $case_record['record_id'];

        // Query to fetch patient and maternity record details
        $detailsQuery = "
            SELECT 
                p.name, 
                p.address, 
                p.phone, 
                p.occupation, 
                p.birthday, 
                p.height, 
                p.husband, 
                p.husband_occupation, 
                p.philhealth_number, 
                m.gravida, 
                m.para, 
                m.abortions, 
                m.lmp, 
                m.edc 
            FROM 
                patienttb AS p
            LEFT JOIN 
                maternity_record AS m 
            ON 
                p.patient_id = m.patient_id 
            WHERE 
                m.record_id = ? 
            LIMIT 1";

        $detailsStmt = $conn->prepare($detailsQuery);
        $detailsStmt->bind_param("i", $record_id);
        $detailsStmt->execute();
        $detailsResult = $detailsStmt->get_result();

        if ($detailsResult->num_rows > 0) {
            $patient_data = $detailsResult->fetch_assoc();
        } else {
            $error_message = "Patient details not found.";
        }

        $detailsStmt->close();

        // Query to fetch TT history
        $ttQuery = "SELECT ttdose_number, date FROM tetanus_toxoid WHERE record_id = ?";
        $ttStmt = $conn->prepare($ttQuery);
        $ttStmt->bind_param("i", $record_id);
        $ttStmt->execute();
        $ttResult = $ttStmt->get_result();

        while ($row = $ttResult->fetch_assoc()) {
            $tt_history[] = $row;
        }

        $ttStmt->close();

        // Query to fetch Antepartum Visit data
        $antepartumQuery = "
            SELECT 
                created_at, 
                bp, 
                wt, 
                fund_ht, 
                presentation_fhb, 
                temperature, 
                aog 
            FROM antepartum_visit 
            WHERE record_id = ?";
        $antepartumStmt = $conn->prepare($antepartumQuery);
        $antepartumStmt->bind_param("i", $record_id);
        $antepartumStmt->execute();
        $antepartumResult = $antepartumStmt->get_result();

        while ($row = $antepartumResult->fetch_assoc()) {
            $antepartum_visits[] = $row;
        }

        $antepartumStmt->close();

        // Query to fetch Dental Checkups data
        $dentalQuery = "SELECT created_at, dentist_notes FROM dental_checkups WHERE record_id = ?";
        $dentalStmt = $conn->prepare($dentalQuery);
        $dentalStmt->bind_param("i", $record_id);
        $dentalStmt->execute();
        $dentalResult = $dentalStmt->get_result();

        while ($row = $dentalResult->fetch_assoc()) {
            $dental_checkups[] = $row;
        }

        $dentalStmt->close();
    } else {
        $error_message = "Case not found.";
    }

    // Close resources
    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maternity Record</title>
    <style>
        section {
            border: 1px solid #ddd;
            padding: 15px;
            margin: 20px 0;
            background-color: #f9f9f9;
        }
        h3 {
            margin-bottom: 10px;
        }
        p, table {
            margin: 5px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        table th {
            background-color: #f2f2f2;
        }
        @media print {
            body {
                font-family: Arial, sans-serif;
                margin: 0;
                padding: 0;
            }
            section {
                border: none;
                padding: 10px;
                margin: 0;
                background-color: #fff;
            }
            h1, h3 {
                font-size: 18px;
                margin-bottom: 5px;
            }
            table {
                margin-top: 10px;
                border-collapse: collapse;
                width: 100%;
            }
            table th, table td {
                border: 1px solid #ddd;
                padding: 5px;
            }
            .print-btn {
                display: none;
            }
        }
    </style>
</head>
<body>
    <h1>Maternity Record</h1>

    <!-- Case ID -->
<div class="section">
    <h3>Case Information</h3>
    <p><strong>Case ID:</strong> <?php echo htmlspecialchars($case_id); ?></p>
</div>

    <!-- Print Button -->
    <button class="print-btn" onclick="window.print()">Print this page</button>

    <!-- Patient Info Section -->
    <section id="patient_info_section">
        <?php if ($patient_data): ?>
            <h3>Patient Details</h3>
            <p><strong>Name:</strong> <?= htmlspecialchars($patient_data['name']) ?: 'N/A' ?></p>
            <p><strong>Address:</strong> <?= htmlspecialchars($patient_data['address']) ?: 'N/A' ?></p>
            <p><strong>Phone:</strong> <?= htmlspecialchars($patient_data['phone']) ?: 'N/A' ?></p>
            <p><strong>Occupation:</strong> <?= htmlspecialchars($patient_data['occupation']) ?: 'N/A' ?></p>
            <p><strong>Birthday:</strong> <?= htmlspecialchars($patient_data['birthday']) ?: 'N/A' ?></p>
            <p><strong>Height:</strong> <?= htmlspecialchars($patient_data['height']) ?: 'N/A' ?></p>
            <p><strong>Husband:</strong> <?= htmlspecialchars($patient_data['husband']) ?: 'N/A' ?></p>
            <p><strong>Husband's Occupation:</strong> <?= htmlspecialchars($patient_data['husband_occupation']) ?: 'N/A' ?></p>
            <p><strong>PhilHealth Number:</strong> <?= htmlspecialchars($patient_data['philhealth_number']) ?: 'N/A' ?></p>
        <?php elseif (isset($error_message)): ?>
            <p><strong>Error:</strong> <?= htmlspecialchars($error_message) ?></p>
        <?php else: ?>
            <p>No patient information available. Please provide a valid case ID.</p>
        <?php endif; ?>
    </section>

    <!-- TT History Section -->
    <section id="tt_history_section">
        <h3>TT History</h3>
        <?php if (!empty($tt_history)): ?>
            <table>
                <tr>
                    <th>Dose</th>
                    <th>Date</th>
                </tr>
                <?php foreach ($tt_history as $tt): ?>
                    <tr>
                        <td><?= htmlspecialchars($tt['ttdose_number']) ?: 'N/A' ?></td>
                        <td><?= htmlspecialchars($tt['date']) ?: 'N/A' ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>No TT history available for this record.</p>
        <?php endif; ?>
    </section>

    <!-- Present History Section -->
    <section id="present_history_section">
        <h3>Present History</h3>
        <?php if ($patient_data): ?>
            <p><strong>LMP:</strong> <?= htmlspecialchars($patient_data['lmp']) ?: 'N/A' ?></p>
            <p><strong>EDC:</strong> <?= htmlspecialchars($patient_data['edc']) ?: 'N/A' ?></p>
            <p><strong>Gravida:</strong> <?= htmlspecialchars($patient_data['gravida']) ?: 'N/A' ?></p>
            <p><strong>Para:</strong> <?= htmlspecialchars($patient_data['para']) ?: 'N/A' ?></p>
            <p><strong>Abortions:</strong> <?= htmlspecialchars($patient_data['abortions']) ?: 'N/A' ?></p>
        <?php else: ?>
            <p>No present history available.</p>
        <?php endif; ?>
    </section>

    <!-- Antepartum Visit Section -->
    <section id="antepartum_visit_section">
        <h3>Antepartum Visit</h3>
        <?php if (!empty($antepartum_visits)): ?>
            <table>
                <tr>
                    <th>Date</th>
                    <th>Findings</th>
                </tr>
                <?php foreach ($antepartum_visits as $visit): ?>
                    <tr>
                        <td><?= htmlspecialchars($visit['created_at']) ?: 'N/A' ?></td>
                        <td>
                            <ul>
                                <li><strong>BP:</strong> <?= htmlspecialchars($visit['bp']) ?: 'N/A' ?></li>
                                <li><strong>WT:</strong> <?= htmlspecialchars($visit['wt']) ?: 'N/A' ?></li>
                                <li><strong>Fund. HT:</strong> <?= htmlspecialchars($visit['fund_ht']) ?: 'N/A' ?></li>
                                <li><strong>Presentation/FHB:</strong> <?= htmlspecialchars($visit['presentation_fhb']) ?: 'N/A' ?></li>
                                <li><strong>Temp:</strong> <?= htmlspecialchars($visit['temperature']) ?: 'N/A' ?></li>
                                <li><strong>AOG:</strong> <?= htmlspecialchars($visit['aog']) ?: 'N/A' ?></li>
                            </ul>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>No antepartum visits available for this record.</p>
        <?php endif; ?>
    </section>

    <!-- Dental Checkups Section -->
    <section id="dental_checkups_section">
        <h3>Dental Checkups</h3>
        <?php if (!empty($dental_checkups)): ?>
            <table>
                <tr>
                    <th>Date</th>
                    <th>Dentist Notes</th>
                </tr>
                <?php foreach ($dental_checkups as $dental): ?>
                    <tr>
                        <td><?= htmlspecialchars($dental['created_at']) ?: 'N/A' ?></td>
                        <td><?= htmlspecialchars($dental['dentist_notes']) ?: 'N/A' ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>No dental checkup records available for this record.</p>
        <?php endif; ?>
    </section>

</body>
</html>
