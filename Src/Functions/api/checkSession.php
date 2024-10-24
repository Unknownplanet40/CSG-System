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
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        response(['error' => 'Invalid request method']);
    }

    $ID = $_GET['UUID'];
    $session = $_GET['SessionID'];

    $stmt = $conn->prepare("SELECT sessionID FROM usercredentials WHERE UUID = ?");
    $stmt->bind_param('s', $ID);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    if ($result->num_rows === 0) {
        response(['status' => 'error', 'message' => 'User not found']);
    }

    $user = $result->fetch_assoc();

    response(['status' => 'success', 'isSessionChange' => $session !== $user['sessionID'] ? true : false]);

} catch (Exception $e) {
    response(['status' => 'error', 'message' => $e->getMessage()]);
}