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
    
    /* if (!isset($_SESSION['UUID']) || $_SESSION['role'] != 1) {
        response(['status' => 'error', 'message' => 'Unauthorized access']);
    } */

    if (isset($_GET['date']) && isset($_GET['event'])){
        $date = $_GET['date'];
        $event = $_GET['event'];
        $stmtDate = "";

        if ($date == "Today") {
            $stmtDate = "access_date = CURDATE()";
        } else if ($date == "Yesterday") {
            $stmtDate = "access_date = CURDATE() - INTERVAL 1 DAY";
        } else if ($date == "This Week") {
            $stmtDate = "YEARWEEK(access_date) = YEARWEEK(CURDATE())";
        } else if ($date == "This Month") {
            $stmtDate = "MONTH(access_date) = MONTH(CURDATE()) AND YEAR(access_date) = YEAR(CURDATE())";
        } else if ($date == "This Year") {
            $stmtDate = "YEAR(access_date) = YEAR(CURDATE())";
        } else {
            $stmtDate = "1=1";
        }

        $stmtEvent = "LoginStat = '$event'";

        $stmt = $conn->prepare("SELECT * FROM accounts WHERE $stmtDate AND $stmtEvent ORDER BY access_date DESC");
    } else if (isset($_GET['date']) && !isset($_GET['event'])){
        $date = $_GET['date'];
        $stmtDate = "";

        if ($date == "Today") {
            $stmtDate = "access_date = CURDATE()";
        } else if ($date == "Yesterday") {
            $stmtDate = "access_date = CURDATE() - INTERVAL 1 DAY";
        } else if ($date == "This Week") {
            $stmtDate = "YEARWEEK(access_date) = YEARWEEK(CURDATE())";
        } else if ($date == "This Month") {
            $stmtDate = "MONTH(access_date) = MONTH(CURDATE()) AND YEAR(access_date) = YEAR(CURDATE())";
        } else if ($date == "This Year") {
            $stmtDate = "YEAR(access_date) = YEAR(CURDATE())";
        } else {
            $stmtDate = "1=1";
        }

        $stmt = $conn->prepare("SELECT * FROM accounts WHERE $stmtDate ORDER BY access_date DESC");
    } else if (!isset($_GET['date']) && isset($_GET['event'])){
        $event = $_GET['event'];
        $stmtEvent = "LoginStat = '$event'";
        $stmt = $conn->prepare("SELECT * FROM accounts WHERE $stmtEvent ORDER BY access_date DESC");
    } else {
        $stmt = $conn->prepare("SELECT * FROM accounts ORDER BY access_date DESC");
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    $data = [];
    $successrate = 0;
    $failedrate = 0;
    $total = 0;

    while ($row = $result->fetch_assoc()) {
        $row['access_date'] = $row['access_date'] ? date('M d, Y h:i A', strtotime($row['access_date'])) : null;
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
