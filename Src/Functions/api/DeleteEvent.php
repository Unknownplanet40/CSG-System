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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    response(['status' => 'error', 'message' => 'Invalid request method']);
}

try {
    $id = $_POST['id'];
    $action = $_POST['action'];

    if ($action == 'delete') {
        $stmt = $conn->prepare("UPDATE sysevents SET isDeleted = 1 WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    } else {
        $stmt = $conn->prepare("UPDATE sysevents SET isDeleted = 0 WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }

    response(['status' => 'success', 'message' => 'Event has been deleted!']);
} catch (\Throwable $th) {
    response(['status' => 'error', 'message' => $th->getMessage() . ' at line ' . $th->getLine()]);
}
