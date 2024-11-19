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
    $status = $_GET['action'] ?? null;
    $Code = $_GET['course_code'] ?? null;

    if ($status === 'enable') {
        $stmt = $conn->prepare("UPDATE sysacadtype SET course_status = 'active' WHERE course_code = ?");
        $stmt->bind_param('i', $Code);
    } else if ($status === 'disable') {
        $stmt = $conn->prepare("UPDATE sysacadtype SET course_status = 'archived' WHERE course_code = ?");
        $stmt->bind_param('i', $Code);
    } else {
        response(['status' => 'error', 'message' => 'Invalid status', 'data' => []]);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    response(['status' => 'success', 'message' => 'Course status has been updated']);
} catch (Exception $e) {
    response(['status' => 'error', 'message' => $e->getMessage()]);
}
