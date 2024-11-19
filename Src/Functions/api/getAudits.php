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

$audits = [];

try {

    $stmt = $conn->prepare("SELECT * FROM auditlog ORDER BY change_date DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    while ($row = $result->fetch_assoc()) {
        $stmt = $conn->prepare("SELECT * FROM usercredentials WHERE UUID = ?");
        $stmt->bind_param("s", $row['changed_by']);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $row['changed_by'] = $user['fullName'];

        $stmt = $conn->prepare("SELECT * FROM usercredentials WHERE UUID = ?");
        $stmt->bind_param("s", $row['affected_user']);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $row['affected_user'] = $user['fullName'];

        $row['change_date'] = date('F d, Y h:i A', strtotime($row['change_date']));

        array_push($audits, $row);
    }

    if (count($audits) > 0) {
        response(['status' => 'success', 'data' => $audits]);
    } else {
        response(['status' => 'error', 'message' => 'No audits found']);
    }

} catch (\Throwable $th) {
    response(['status' => 'error', 'message' => 'An error occurred while fetching audits ' . $th->getMessage()]);
}
