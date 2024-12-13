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
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            response(['error' => 'Invalid request method']);
        }

        $ID = $_POST['ID'];
        $name = $_POST['name'];
        $shortname = $_POST['shortname'];
        $desc = $_POST['desc'];
        $forCourse = $_POST['forCourse'];

        $stmt = $conn->prepare("UPDATE sysorganizations SET org_name = ?, org_short_name = ?, onlyForCourse = ?, org_Desc = ?, onlyForCourse = ? WHERE ID = ?");
        $stmt->bind_param('sssssi', $name, $shortname, $forCourse, $desc, $forCourse, $ID);
        $stmt->execute();
        $stmt->close();

        response(['status' => 'success', 'message' => 'Organization has been updated']);

} catch (Exception $e) {
    response(['status' => 'error', 'message' => $e->getMessage()]);
}