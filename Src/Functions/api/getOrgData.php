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
    $status = $_GET['status'] ?? 'active';

    if ($status === 'active') {
        $stmt = $conn->prepare("SELECT * FROM sysorganizations WHERE stat = 0");
    } else if ($status === 'archived') {
        $stmt = $conn->prepare("SELECT * FROM sysorganizations WHERE stat = 1");
    } else {
        response(['status' => 'error', 'message' => 'Invalid status', 'data' => []]);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $row['created_At'] = date('F d, Y h:i A', strtotime($row['created_At']));
        $data[] = $row;
    }

    response(['status' => 'success', 'data' => $data]);
} catch (Exception $e) {
    response(['status' => 'error', 'message' => $e->getMessage()]);
}
