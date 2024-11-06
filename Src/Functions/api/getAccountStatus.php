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
        response(['error' => 'Invalid request method']);
    }

    $action = $_POST['action'];

    if (!isset($_SESSION['UUID'])) {
        response(['status' => 'error', 'message' => 'Session expired']);
    }

    if ($action === 'Accstat') {

        $UUID = $_SESSION['UUID'];

        $stmt = $conn->prepare("SELECT accountStat FROM usercredentials WHERE UUID = ?");
        $stmt->bind_param('s', $UUID);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($accountStat);
        $stmt->fetch();

        if ($stmt->num_rows === 0) {
            response(['status' => 'error', 'message' => 'User not found']);
        }
        $_SESSION['accountStat'] = $accountStat;
        response(['status' => 'success', 'accountStat' => $accountStat]);
    }

} catch (Exception $e) {
    response(['status' => 'error', 'message' => $e->getMessage()]);
}