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

    if (empty($_POST) || !isset($_POST['FromUser']) || !isset($_POST['ToUser']) || !isset($_POST['Message']) || !isset($_POST['isActivewhenSend'])) {
        response(['error' => 'Invalid request data']);
    }

    $FromUser = $_POST['FromUser'];
    $ToUser = $_POST['ToUser'];
    $Message = $_POST['Message'];
    $isActivewhenSend = $_POST['isActivewhenSend'];
    $randUMID = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 6);

    $stmt = $conn->prepare("INSERT INTO systemmessages (fromUser, toUser, userMessage, status, Type, UMID) VALUES (?, ?, ?, ?, 1, ?)");
    $stmt->bind_param("sssis", $FromUser, $ToUser, $Message, $isActivewhenSend, $randUMID);
    $stmt->execute();
    $stmt->close();

    response(['status' => 'success', 'message' => 'Message sent successfully']);

} catch (Exception $e) {
    response(['status' => 'error', 'message' => $e->getMessage()]);
}