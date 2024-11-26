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

    if (!isset($_GET['OrgCode'])) {
        response(['status' => 'error', 'message' => 'Invalid request']);
    }

    $stmt = $conn->prepare("SELECT * FROM orgdocumetheader WHERE org_code = ?");
    $stmt->bind_param("s", $_GET['OrgCode']);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    $data = [];

    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    response(['status' => 'success', 'data' => $data]);
} catch (Exception $e) {
    response(['status' => 'error', 'message' => $e->getMessage()]);
}
