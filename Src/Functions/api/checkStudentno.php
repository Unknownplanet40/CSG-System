<?php

require_once  "../../Database/Config.php";

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

    $ID = $_POST['studentno'];

    $stmt = $conn->prepare("SELECT student_Number FROM usercredentials WHERE student_Number = ?");
    $stmt->bind_param('s', $ID);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    if ($result->num_rows > 0) {
        response(['status' => 'error', 'message' => 'Student Number already exists']);
    } else {
        response(['status' => 'success', 'message' => 'Student Number is available']);
    }
} catch (Exception $e) {
    response(['status' => 'error', 'message' => $e->getMessage()]);
}