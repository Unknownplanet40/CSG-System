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
    $data = [];
    $task_id = $_POST['taskID'];

    $stmt = $conn->prepare("SELECT * FROM taskdocuments WHERE taskID = ?");
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

while ($row = $result->fetch_assoc()) {
    $data[] = [
        'taskTitle' => $row['taskTitle'],
        'taskDuedate' => $row['taskDuedate'],
        'tastStat' => $row['taskStat'],
    ];
}
    response(['status' => 'success', 'data' => $data]);
} catch (Exception $e) {
    response(['status' => 'error', 'message' => $e->getMessage()]);
}
