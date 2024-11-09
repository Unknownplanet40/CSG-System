<?php
session_start();
require_once "../../Database/Config.php";

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

    if (!isset($_SESSION['UUID'])) {
        response(['status' => 'error', 'message' => 'Session expired']);
    }

     // active users
     $stmt = $conn->prepare("SELECT COUNT(*) AS totalUsers FROM usercredentials WHERE accountStat = 'active'");
     $stmt->execute();
     $activeUsers = $stmt->get_result()->fetch_assoc()['totalUsers'];
     $stmt->close();
 
     // csg officers
     $stmt = $conn->prepare("SELECT COUNT(*) AS totalCSGOfficers FROM userpositions WHERE role = 2");
     $stmt->execute();
     $csgOfficers = $stmt->get_result()->fetch_assoc()['totalCSGOfficers'];
     $stmt->close();
 
     // daily logins
     $stmt = $conn->prepare("SELECT COUNT(*) AS totalLogins FROM accounts WHERE access_date >= CURDATE()");
     $stmt->execute();
     $dailyLogins = $stmt->get_result()->fetch_assoc()['totalLogins'];
     $stmt->close();
 
     // locked accounts
     $stmt = $conn->prepare("SELECT COUNT(*) AS totalLocked FROM usercredentials WHERE accountStat = 'locked'");
     $stmt->execute();
     $lockedAccounts = $stmt->get_result()->fetch_assoc()['totalLocked'];
     $stmt->close();

    response(['status' => 'success', 'activeUsers' => $activeUsers, 'csguser' => $csgOfficers, 'dailyLogins' => $dailyLogins, 'lockedUsers' => $lockedAccounts]);



} catch (Exception $e) {
    response(['status' => 'error', 'message' => $e->getMessage()]);
}