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

    if (!isset($_SESSION['role'])) {
        response(['status' => 'error', 'message' => 'Session expired']);
    }
    
    if ($_SESSION['role'] == 1) {
        $stmt = $conn->prepare("SELECT * FROM excuseletterdocuments");
    } else {
        if (!isset($_SESSION['org_position']) || !isset($_SESSION['org_Code']) || !isset($_SESSION['UUID'])) {
            response(['status' => 'error', 'message' => 'Session expired']);
        }
        
        if ($_SESSION['org_position'] < 4) {
            $stmt = $conn->prepare("SELECT * FROM excuseletterdocuments WHERE org_code = ?");
            $stmt->bind_param("s", $_SESSION['org_Code']);
        } else {
            $stmt = $conn->prepare("SELECT * FROM excuseletterdocuments WHERE org_code = ? AND UUID = ?");
            $stmt->bind_param("ss", $_SESSION['org_Code'], $_SESSION['UUID']);
        }
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    $data = [];

    while ($row = $result->fetch_assoc()) {
        $row['DateCreated'] = date('F d, Y', strtotime($row['DateCreated']));

        $stmt = $conn->prepare("SELECT fullName FROM usercredentials WHERE UUID = ?");
        $stmt->bind_param("s", $row['UUID']);
        $stmt->execute();
        $result2 = $stmt->get_result();
        $stmt->close();
        $row2 = $result2->fetch_assoc();
        $row['Created_By'] = $row2['fullName'];

        $row['excuseLetterType'] = $row['excuseLetterType'] == 0? 'Excuse Letter for Constituents' : 'Excuse Letter for Org Officers';
        
        $data[] = $row;
    }


    response(['status' => 'success', 'data' => $data]);
} catch (Exception $e) {
    response(['status' => 'error', 'message' => $e->getMessage()]);
}
