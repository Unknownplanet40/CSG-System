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
    $stmt = $conn->prepare("SELECT DISTINCT Device FROM auditdevice");
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    $data = [];

    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    response(['data' => $data]);
} catch (Exception $e) {
    response(['status' => 'error', 'message' => $e->getMessage()]);
}
