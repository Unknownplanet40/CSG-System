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
    $code = trim($_GET['code']);
    $name = trim($_GET['name']);
    $shortname = trim($_GET['short_name']);
    $year = trim($_GET['year']);
    $section = trim($_GET['section']);

    $stmt = $conn->prepare("SELECT * FROM sysacadtype WHERE course_short_name = ? AND year = ? AND section = ?");
    $stmt->bind_param('sis', $shortname, $year, $section);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    if ($result->num_rows > 0) {
        response(['status' => 'error', 'message' => 'Course already exists']);
    }

    $stmt = $conn->prepare("INSERT INTO sysacadtype (course_code, course_name, course_short_name, year, section, course_status) VALUES (?, ?, ?, ?, ?, 'active')");
    $stmt->bind_param('issis', $code, $name, $shortname, $year, $section);
    $stmt->execute();
    $stmt->close();

    response(['status' => 'success', 'message' => 'Course has been created']);
} catch (Exception $e) {
    response(['status' => 'error', 'message' => $e->getMessage()]);
}
