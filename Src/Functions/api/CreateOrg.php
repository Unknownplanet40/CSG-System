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

    $code = $_POST['code'];
    $name = $_POST['name'];
    $shortname = $_POST['shortname'];
    $desc = $_POST['desc'];

    $stmt = $conn->prepare("INSERT INTO sysorganizations (org_code, org_name, org_short_name, org_Desc, stat) VALUES (?, ?, ?, ?, 0)");
    $stmt->bind_param('isss', $code, $name, $shortname, $desc);
    $stmt->execute();
    $stmt->close();

    response(['status' => 'success', 'message' => 'Organization has been created']);
} catch (Exception $e) {
    response(['status' => 'error', 'message' => $e->getMessage()]);
}
