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

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        response(['status' => 'error', 'message' => 'Invalid request method']);
    }

    $taskTitle = $_POST['taskTitle'] ?? null;
    $taskDesc = $_POST['taskDesc'] ?? null;
    $taskAssigned = $_POST['taskAssigned'] ?? $_SESSION['org_Code'];
    $taskType = $_POST['taskType'] ?? null;
    $taskStatus = $_POST['taskStatus'] ?? null;
    $taskDue = $_POST['taskDue'] ?? null;

    if (!$taskTitle || !$taskDesc || !$taskAssigned || !$taskType || !$taskStatus || !$taskDue) {
        response(['status' => 'error', 'message' => 'All fields are required']);
    }

    $taskDue = date('Y-m-d', strtotime($taskDue));
    $currentDate = date('Y-m-d');
    $taskID = uniqid();
    $fullNames = $_SESSION['UUID'];

    $stmt = $conn->prepare("INSERT INTO taskdocuments (taskTitle, taskDesc, taskType, AssignedTO, tastStat, taskDuedate, taskDatecreated, taskID, postedBy) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssss", $taskTitle, $taskDesc, $taskType, $taskAssigned, $taskStatus, $taskDue, $currentDate, $taskID, $fullNames);
    $stmt->execute();
    $stmt->close();

    response(['status' => 'success', 'message' => 'Task created successfully']);
} catch (\Throwable $th) {
    response(['status' => 'error', 'message' => $th->getMessage()]);
}