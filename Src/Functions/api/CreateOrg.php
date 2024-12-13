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
    $forCourse = $_POST['forCourse'];

    // check if org code already exists
    $stmt = $conn->prepare("SELECT * FROM sysorganizations WHERE org_name LIKE ? OR org_short_name LIKE ?");
    $name = "%$name%";
    $shortname = "%$shortname%";
    $stmt->bind_param('ss', $name, $shortname);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    if ($result->num_rows > 0) {
        response(['status' => 'error', 'message' => 'Organization already exists']);
    }



    $stmt = $conn->prepare("INSERT INTO sysorganizations (org_code, org_name, org_short_name, onlyForCourse, org_Desc, stat) VALUES (?, ?, ?, ?, ?, 0)");
    $stmt->bind_param('issss', $code, $name, $shortname, $forCourse, $desc);
    $stmt->execute();
    $stmt->close();

    response(['status' => 'success', 'message' => 'Organization has been created']);
} catch (Exception $e) {
    response(['status' => 'error', 'message' => $e->getMessage()]);
}
