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
        $stmt = $conn->prepare("SELECT * FROM sysacadtype WHERE course_status = 'active' ORDER BY course_name DESC");
    } else if ($status === 'archived') {
        $stmt = $conn->prepare("SELECT * FROM sysacadtype WHERE course_status = 'archived' ORDER BY course_name DESC");
    } else {
        response(['status' => 'error', 'message' => 'Invalid status', 'data' => []]);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $row['created_at'] = $row['created_at'] ? date('M d, Y h:i A', strtotime($row['created_at'])) : null;
        $row['year'] = $row['year'] == 1 ? '1st Year' : ($row['year'] == 2 ? '2nd Year' : ($row['year'] == 3 ? '3rd Year' : '4th Year'));
        $row['course_name'] = ucwords($row['course_name']);
        $data[] = $row;
    }

    response(['status' => 'success', 'data' => $data]);
} catch (Exception $e) {
    response(['status' => 'error', 'message' => $e->getMessage()]);
}
