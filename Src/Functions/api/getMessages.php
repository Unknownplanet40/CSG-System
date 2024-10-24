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

    $FromUser = $_GET['FromUser'];
    $ToUser = $_GET['ToUser'];

    $stmt = $conn->prepare("SELECT * FROM systemmessages WHERE (FromUser = ? AND toUser = ?) OR (FromUser = ? AND toUser = ?) ORDER BY dateUpdated ASC");
    $stmt->bind_param("ssss", $FromUser, $ToUser, $ToUser, $FromUser);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    $data = [];

    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'FromUser' => $row['fromUser'],
            'ToUser' => $row['toUser'],
            'Message' => $row['userMessage'],
            'isDeleted' => $row['isDeleted'],
            'UMID' => $row['UMID'],
            'Date' => $row['dateCreated']
        ];
    }

    if (empty($data)) {
        response(['status' => 'error', 'message' => 'No messages found']);
    }

    response(['status' => 'success', 'data' => $data]);

} catch (Exception $e) {
    response(['status' => 'error', 'message' => $e->getMessage()]);
}