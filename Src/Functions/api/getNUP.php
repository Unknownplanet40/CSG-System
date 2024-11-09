<?php
session_start();
require_once "../../Database/Config.php";

header('Content-Type: application/json');
date_default_timezone_set('Asia/Manila');

function response($data)
{
    echo json_encode($data);
    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        response(['error' => 'Invalid request method']);
    }

    if (!isset($_SESSION['UUID'])) {
        response(['status' => 'error', 'message' => 'Session expired']);
    }

    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 1) {
        response(['status' => 'error', 'message' => 'Unauthorized access']);
    }

    $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
    $pageSize = isset($_POST['pageSize']) ? (int)$_POST['pageSize'] : 5;
    $offset = ($page - 1) * $pageSize;

    $countStmt = $conn->prepare("SELECT COUNT(*) AS totalRecords FROM usercredentials WHERE accountStat = 'pending'");
    $countStmt->execute();
    $totalRecords = $countStmt->get_result()->fetch_assoc()['totalRecords'];
    $countStmt->close();

    $totalPages = ceil($totalRecords / $pageSize);
    $prev = $page > 1 ? $page - 1 : null;
    $next = $page < $totalPages ? $page + 1 : null;

    $stmt = $conn->prepare("SELECT * FROM usercredentials WHERE accountStat = 'pending' LIMIT ? OFFSET ?");
    $stmt->bind_param('ii', $pageSize, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $courseStmt = $conn->prepare("SELECT * FROM sysacadtype WHERE course_code = ?");
        $courseStmt->bind_param('s', $row['course_code']);
        $courseStmt->execute();
        $course = $courseStmt->get_result()->fetch_assoc();
        $courseStmt->close();

        $row['course_code'] = $course['course_short_name'] . '-' . $course['year'] . '' . $course['section'];
        $data[] = $row;
    }

    response([
        'status' => 'success',
        'data' => $data,
        'totalRecords' => $totalRecords,
        'totalPages' => $totalPages,
        'prev' => $prev,
        'next' => $next
    ]);

} catch (Exception $e) {
    response(['status' => 'error', 'message' => $e->getMessage()]);
}
