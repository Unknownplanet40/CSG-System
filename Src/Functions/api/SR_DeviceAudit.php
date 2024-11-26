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
    if (isset($_GET['date']) && isset($_GET['event'])){
        $date = $_GET['date'];
        $event = $_GET['event'];
        $stmtDate = "";
        $stmtEvent = "";

        if ($date == "Today") {
            $stmtDate = "dateMade = CURDATE()";
        } else if ($date == "Yesterday") {
            $stmtDate = "dateMade = CURDATE() - INTERVAL 1 DAY";
        } else if ($date == "This Week") {
            $stmtDate = "YEARWEEK(dateMade) = YEARWEEK(CURDATE())";
        } else if ($date == "This Month") {
            $stmtDate = "MONTH(dateMade) = MONTH(CURDATE()) AND YEAR(dateMade) = YEAR(CURDATE())";
        } else if ($date == "This Year") {
            $stmtDate = "YEAR(dateMade) = YEAR(CURDATE())";
        } else {
            $stmtDate = "1=1";
        }
        $stmtEvent = "Device = '$event'";

        $stmt = $conn->prepare("SELECT * FROM auditdevice WHERE $stmtDate AND $stmtEvent ORDER BY dateMade DESC");
    } else if (isset($_GET['date']) && !isset($_GET['event'])){
        $date = $_GET['date'];
        $stmtDate = "";

        if ($date == "Today") {
            $stmtDate = "dateMade = CURDATE()";
        } else if ($date == "Yesterday") {
            $stmtDate = "dateMade = CURDATE() - INTERVAL 1 DAY";
        } else if ($date == "This Week") {
            $stmtDate = "YEARWEEK(dateMade) = YEARWEEK(CURDATE())";
        } else if ($date == "This Month") {
            $stmtDate = "MONTH(dateMade) = MONTH(CURDATE()) AND YEAR(dateMade) = YEAR(CURDATE())";
        } else if ($date == "This Year") {
            $stmtDate = "YEAR(dateMade) = YEAR(CURDATE())";
        } else {
            $stmtDate = "1=1";
        }

        $stmt = $conn->prepare("SELECT * FROM auditdevice WHERE $stmtDate ORDER BY dateMade DESC");
    } else if (!isset($_GET['date']) && isset($_GET['event'])){
        $event = $_GET['event'];
        $stmtEvent = "Device = '$event'";
        $stmt = $conn->prepare("SELECT * FROM auditdevice WHERE $stmtEvent ORDER BY dateMade DESC");
    } else {
        $stmt = $conn->prepare("SELECT * FROM auditdevice ORDER BY dateMade DESC");
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    $data = [];
    $successrate = 0;
    $failedrate = 0;
    $total = 0;

    while ($row = $result->fetch_assoc()) {
        $row['dateMade'] = $row['dateMade'] ? date('M d, Y', strtotime($row['dateMade'])) : null;
        $stmt = $conn->prepare("SELECT * FROM usercredentials WHERE UUID = ?");
        $stmt->bind_param('s', $row['UUID']);
        $stmt->execute();
        $result2 = $stmt->get_result();
        $stmt->close();
        $user = $result2->fetch_assoc();

        if ($user) {
            $row['UUID'] = $user['fullName'];
        } else {
            continue;
        }

        $data[] = $row;
    }


    response(['status' => 'success', 'data' => $data]);
} catch (Exception $e) {
    response(['status' => 'error', 'message' => $e->getMessage()]);
}
