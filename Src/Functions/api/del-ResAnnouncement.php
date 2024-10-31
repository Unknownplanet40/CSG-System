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

    $postID = $_POST['postID'];
    $UUID = $_POST['UUID'];
    $Action = $_POST['Action'];

    if (!isset($postID) || !isset($UUID) || !isset($Action)) {
        response(['status' => 'error', 'message' => 'Invalid request']);
    }

    $validActions = ["delete", "restore"];

    if (!in_array($Action, $validActions)) {
        response(['status' => 'error', 'message' => 'Invalid action']);
    }

    if ($Action == "delete") {
        $stmt = $conn->prepare("UPDATE userannouncement SET isDeleted = 1 WHERE postID = ? AND postedBy = ?");
        $stmt->bind_param("ss", $postID, $UUID);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
    } else {
        $stmt = $conn->prepare("UPDATE userannouncement SET isDeleted = 0 WHERE postID = ? AND postedBy = ?");
        $stmt->bind_param("ss", $postID, $UUID);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
    }

    response(['status' => 'success', 'message' => 'Post ' .ucfirst($Action) . 'd successfully']);
    

} catch (Exception $e) {
    response(['status' => 'error', 'message' => 'Failed to ' . ucfirst($Action) . ' post']);
}
