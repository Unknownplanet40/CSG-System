<?php

session_start();
require_once  "../../Database/Config.php";

header('Content-Type: application/json');
date_default_timezone_set('Asia/Manila');

function response($data)
{
    echo json_encode($data);
    exit;
}

try {
    if ($_SESSION['role'] == 1 || ($_SESSION['role'] == 2 && $_SESSION['org_position'] == 1)) {
        $stmt = $conn->prepare("SELECT * FROM documentapproval");
    } else {
        $stmt = $conn->prepare("SELECT * FROM documentapproval WHERE org_code = ? AND MONTH(DateCreated) = MONTH(CURRENT_DATE()) AND YEAR(DateCreated) = YEAR(CURRENT_DATE())");
        $stmt->bind_param("s", $_SESSION['org_Code']);
    }

    $data = [];
    $debug = [];

    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    while ($row = $result->fetch_assoc()) {
        $debug[] = $row;

        // Determine document type
        $fileResult = null;
        if ($row['document'] == "D-AP") {
            $row['document'] = "Activity Proposal";
            $stmt = $conn->prepare("SELECT * FROM activityproposaldocuments WHERE fileID = ?");
        } elseif ($row['document'] == "D-EL") {
            $row['document'] = "Excuse Letter";
            $stmt = $conn->prepare("SELECT * FROM excuseletterdocuments WHERE fileID = ?");
        } elseif ($row['document'] == "D-MM") {
            $row['document'] = "Minute of the Meeting";
            $stmt = $conn->prepare("SELECT * FROM minutemeetingdocuments WHERE fileID = ?");
        } elseif ($row['document'] == "D-OM") {
            $row['document'] = "Office Memorandum";
            $stmt = $conn->prepare("SELECT * FROM officememorandomdocuments WHERE fileID = ?");
        } elseif ($row['document'] == "D-PP") {
            $row['document'] = "Project Proposal";
            $stmt = $conn->prepare("SELECT * FROM projectproposaldocuments WHERE fileID = ?");
        } else {
            response(['status' => 'error', 'message' => 'Invalid document']);
            continue;
        }

        // Fetch file information
        $stmt->bind_param("s", $row['fileID']);
        $stmt->execute();
        $fileResult = $stmt->get_result();
        $stmt->close();
        $fileRow = $fileResult->fetch_assoc();
        $row['file_path'] = isset($fileRow['file_path']) ? $fileRow['file_path'] : null;

        // Fetch user name
        $smt = $conn->prepare("SELECT * FROM usercredentials WHERE UUID = ?");
        $smt->bind_param("s", $row['submittedBy']);
        $smt->execute();
        $res = $smt->get_result();
        $smt->close();
        $row['name'] = $res->fetch_assoc()['fullName'] ?? "Unknown User";

        // Fetch organization name
        $smt = $conn->prepare("SELECT * FROM sysorganizations WHERE org_code = ?");
        $smt->bind_param("s", $row['org_code']);
        $smt->execute();
        $res = $smt->get_result();
        $smt->close();
        $row['org_code'] = $res->fetch_assoc()['org_name'] ?? "Unknown Organization";

        $data[] = $row;
    }


    response(['status' => 'success', 'data' => $data, 'debug' => $debug]);
} catch (\Throwable $th) {
    response(['status' => 'error', 'message' => 'An error occured: ' . $th->getMessage()]);
}
