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
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        response(['error' => 'Invalid request method']);
    }

    $ID = $_GET['id'];
    $name = $_GET['name'];
    $short_name = $_GET['short_name'];
    $year = $_GET['year'];
    $section = $_GET['section'];

    $stmt = $conn->prepare("SELECT * FROM sysacadtype WHERE course_short_name = ? AND course_name = ? AND year = ? AND section = ? AND ID != ?");
    $stmt->bind_param('ssisi', $short_name, $name, $year, $section, $ID);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        response(['status' => 'error', 'message' => 'Course already exists']);
    }
    $stmt->close();
    
    $stmt = $conn->prepare("UPDATE sysacadtype SET course_short_name = ?, course_name = ?, year = ?, section = ? WHERE ID = ?");
    $stmt->bind_param('ssisi', $short_name, $name, $year, $section, $ID);
    $stmt->execute();
    $stmt->close();

    response(['status' => 'success', 'message' => 'Course has been updated']);
} catch (Exception $e) {
    response(['status' => 'error', 'message' => $e->getMessage()]);
}
