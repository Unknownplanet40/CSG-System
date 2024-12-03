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
    $stmt = $conn->prepare("SELECT * FROM documentapproval");
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        if ($row['document'] == "D-AP") {
            $row['document'] = "Activity Proposal";
            $stmt = $conn->prepare("SELECT * FROM activityproposaldocuments WHERE fileID = ?");
            $stmt->bind_param("s", $row['fileID']);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();
        } else if ($row['document'] == "D-EL") {
            $row['document'] = "Excuse Letter";
            $stmt = $conn->prepare("SELECT * FROM excuseletterdocuments WHERE fileID = ?");
            $stmt->bind_param("s", $row['fileID']);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();
        } else if ($row['document'] == "D-MM") {
            $row['document'] = "Minute of the Meeting";
            $stmt = $conn->prepare("SELECT * FROM minutemeetingdocuments WHERE fileID = ?");
            $stmt->bind_param("s", $row['fileID']);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();
        } else if ($row['document'] == "D-OM") {
            $row['document'] = "Office Memorandum";
            $stmt = $conn->prepare("SELECT * FROM officememorandomdocuments WHERE fileID = ?");
            $stmt->bind_param("s", $row['fileID']);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();
        } else if ($row['document'] == "D-PP") {
            $row['document'] = "Project Proposal";
            $stmt = $conn->prepare("SELECT * FROM projectproposaldocuments WHERE fileID = ?");
            $stmt->bind_param("s", $row['fileID']);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();
        } else {
            response(['status' => 'error', 'message' => 'Invalid document']);
        }


        $smt = $conn->prepare("SELECT * FROM usercredentials WHERE UUID = ?");
        $smt->bind_param("s", $row['submittedBy']);
        $smt->execute();
        $res = $smt->get_result();
        $smt->close();
        $row['name'] = $res->fetch_assoc()['fullName'];

        $smt = $conn->prepare("SELECT * FROM sysorganizations WHERE org_code = ?");
        $smt->bind_param("s", $row['org_code']);
        $smt->execute();
        $res = $smt->get_result();
        $smt->close();
        $row['org_code'] = $res->fetch_assoc()['org_name'];

        $row['file_path'] = $result->fetch_assoc()['file_path'];
        $data[] = $row;
    }

    response(['status' => 'success', 'data' => $data]);
} catch (\Throwable $th) {
    response(['status' => 'error', 'message' => 'An error occured: ' . $th->getMessage()]);
}

