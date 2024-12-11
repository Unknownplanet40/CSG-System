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

    $taskID = $_POST['taskID'] ?? null;
    $activeTask = $_POST['activeTask'] ?? null;
    $status = "Ongoing";
    $isDue = 0;
    $taskType = "";

    if (!$taskID) {
        response(['status' => 'error', 'message' => 'Task ID is required']);
    }

    $stmt = $conn->prepare("SELECT * FROM taskdocuments WHERE taskID = ?");
    $stmt->bind_param("s", $taskID);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    if ($result->num_rows === 0) {
        response(['status' => 'error', 'message' => 'Task not found']);
    }

    if ($result->num_rows > 0) {
        $taskData = $result->fetch_assoc();
        $taskType = $taskData['taskType'];

        if (strcmp($activeTask, $taskID) === 0) {
            response(['status' => 'success', 'message' => 'You currently doing this task', 'tasktype' => $taskType, 'taskID' => $taskID, 'org_Code' => $taskData['AssignedTO']]);
        }
    
        if ($activeTask) {
            response(['status' => 'success', 'message' => 'You have ongoing task', 'tasktype' => $taskType, 'taskID' => $activeTask, 'org_Code' => $taskData['AssignedTO']]);
        }

        if ($taskData['tastStat'] === 'Ongoing') {
            if ($taskData['doingby'] == $_SESSION['UUID']) {
                response(['status' => 'info', 'message' => 'You are currently doing this task', 'tasktype' => $taskType, 'taskID' => $taskID, 'org_Code' => $taskData['AssignedTO']]);
            } else {
                response(['status' => 'info', 'message' => 'Task is currently being done by another user']);
            }
        }

        if ($taskData['tastStat'] === 'Completed') {
            response(['status' => 'info', 'message' => 'Task is already completed']);
        }

        if ($taskData['taskDuedate'] < date('Y-m-d')) {
            $status = 'Overdue';
            $isDue = 1;
        }

        $taskType = $taskData['taskType'];
    }

    $currentDate = date('Y-m-d');
    $currentUserID = $_SESSION['UUID'];
    $stmt = $conn->prepare("UPDATE taskdocuments SET tastStat = ?, taskLastupdated = ?, itDue = ?, doingby = ? WHERE taskID = ?");
    $stmt->bind_param("ssiss", $status, $currentDate, $isDue, $currentUserID, $taskID);
    $stmt->execute();
    $stmt->close();

    response(['status' => 'success', 'message' => 'You have accepted the task', 'tasktype' => $taskType, 'taskID' => $taskID, 'org_Code' => $taskData['AssignedTO']]);
} catch (\Throwable $th) {
    response(['status' => 'error', 'message' => $th->getMessage() . ' on line ' . $th->getLine()]);
}
