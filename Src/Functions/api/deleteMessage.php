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

    if (empty($_POST) || !isset($_POST['UMID'])) {
        response(['error' => 'Invalid request data']);
    }

    $UniqueMessageID = $_POST['UMID'];

    $stmt = $conn->prepare("UPDATE systemmessages SET isDeleted = 1 WHERE UMID = ?");
    $stmt->bind_param("s", $UniqueMessageID);
    $stmt->execute();
    $stmt->close();

    response(['status' => 'success', 'message' => 'Message deleted successfully']);

} catch (Exception $e) {
    response(['status' => 'error', 'message' => $e->getMessage()]);
}