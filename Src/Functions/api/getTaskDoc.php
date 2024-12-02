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
    $data = [];
    $org_id = $_SESSION['org_Code'] ?? null;

    if ($_SESSION['role'] == 1) {
        $stmt = $conn->prepare("SELECT * FROM taskdocuments ORDER BY taskDatecreated DESC");
    } else {
        $stmt = $conn->prepare("SELECT * FROM taskdocuments WHERE AssignedTO = ? ORDER BY taskDatecreated DESC");
        $stmt->bind_param("s", $org_id);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

while ($row = $result->fetch_assoc()) {
    $stmt = $conn->prepare("SELECT org_short_name FROM sysorganizations WHERE org_code = ?");
    $stmt->bind_param("s", $row['AssignedTO']);
    $stmt->execute();
    $org = $stmt->get_result();
    $stmt->close();

    $row['taskDatecreated'] = date('M d, Y', strtotime($row['taskDatecreated']));
    $row['taskDuedate'] = date('M d, Y', strtotime($row['taskDuedate']));

    if ($org->num_rows > 0) {
        $org = $org->fetch_assoc();
        $row['AssignedTO'] = $org['org_short_name'];
    } else {
        $row['AssignedTO'] = "";
    }

    $stmt = $conn->prepare("SELECT fullName FROM usercredentials WHERE UUID = ?");
    $stmt->bind_param("s", $row['postedBy']);
    $stmt->execute();
    $user = $stmt->get_result();
    $stmt->close();

    if ($user->num_rows > 0) {
        $user = $user->fetch_assoc();
        $row['postedBy'] = $user['fullName'];
    } else {
        $row['postedBy'] = "";
    }

    if (strtotime($row['taskDuedate']) < strtotime(date('Y-m-d'))) {
        $row['tastStat'] = 'Overdue';
    }

    $data[] = $row;
}
    response(['status' => 'success', 'data' => $data]);
} catch (Exception $e) {
    response(['status' => 'error', 'message' => $e->getMessage()]);
}
