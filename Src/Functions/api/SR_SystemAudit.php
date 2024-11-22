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
    
    if (!isset($_SESSION['UUID']) || $_SESSION['role'] != 1) {
        response(['status' => 'error', 'message' => 'Unauthorized access']);
    }

    $stmt = $conn->prepare("SELECT * FROM systemaudit ORDER BY dateCreate DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    $data = [];
    $successrate = 0;
    $failedrate = 0;
    $total = 0;

    while ($row = $result->fetch_assoc()) {
        $row['dateCreate'] = $row['dateCreate'] ? date('M d, Y', strtotime($row['dateCreate'])) : null;
        $row['timestamp'] = $row['timestamp'] ? date('h:i A', strtotime($row['timestamp'])) : null;
        $stmt = $conn->prepare("SELECT * FROM usercredentials WHERE UUID = ?");
        $stmt->bind_param('s', $row['userID']);
        $stmt->execute();
        $result2 = $stmt->get_result();
        $stmt->close();
        $user = $result2->fetch_assoc();

        if ($user) {
            $row['userID'] = $user['fullName'];
        } else {
            continue;
        }

        $data[] = $row;
    }


    response(['status' => 'success', 'data' => $data]);
} catch (Exception $e) {
    response(['status' => 'error', 'message' => $e->getMessage()]);
}
