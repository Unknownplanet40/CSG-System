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

    if (!isset($_POST['postDetails'], $_POST['priority'], $_POST['userUUID'])) {
        response(['status' => 'error', 'message' => 'Please fill all the required fields']);
    }

    $postDetails = $_POST['postDetails'];
    $priority = $_POST['priority'];
    $userUUID = $_POST['userUUID'];
    $postID = bin2hex(random_bytes(16));
    $postDate = date('Y-m-d H:i:s');

    $stmt = $conn->prepare("INSERT INTO userannouncement(Priority, PostID, PostedBy, PostStatus, PostContent, postedDate) VALUES (?, ?, ?, 'all', ?, ?)");
    $stmt->bind_param("issss", $priority, $postID, $userUUID, $postDetails, $postDate);
    $stmt->execute();
    $stmt->close();

    response(['status' => 'success', 'message' => 'Post created successfully']);

} catch (Exception $e) {
    response(['status' => 'error', 'message' => 'Failed to create post: ' . $e->getMessage()]);
}
