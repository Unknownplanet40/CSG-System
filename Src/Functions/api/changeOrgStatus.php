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
    $status = $_GET['status'] ?? null;
    $orgID = $_GET['ID'];

    if ($status === 'enable') {
        $stmt = $conn->prepare("UPDATE sysorganizations SET stat = 0 WHERE org_code = ?");
        $stmt->bind_param('i', $orgID);
    } else if ($status === 'disable') {
        $stmt = $conn->prepare("UPDATE sysorganizations SET stat = 1 WHERE org_code = ?");
        $stmt->bind_param('i', $orgID);
    } else {
        response(['status' => 'error', 'message' => 'Invalid status', 'data' => []]);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    response(['status' => 'success', 'message' => 'Organization status has been updated']);
} catch (Exception $e) {
    response(['status' => 'error', 'message' => $e->getMessage()]);
}
