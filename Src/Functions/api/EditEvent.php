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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    response(['status' => 'error', 'message' => 'Invalid request method']);
}

try {
    $UUID = $_POST['UUID'];
    $title = $_POST['title'];
    $location = $_POST['location'];
    $description = $_POST['description'];
    $color = $_POST['color'];
    $start = $_POST['start'];
    $end = $_POST['end'];
    $id = $_POST['id'];

    $start = date('Y-m-d H:i:s', strtotime($start));
    $end = date('Y-m-d H:i:s', strtotime($end));

    $stmt = $conn->prepare("UPDATE sysevents SET UUID = ?, title = ?, eventdesc = ?, location = ?, color = ?, start = ?, end = ? WHERE id = ?");
    $stmt->bind_param("sssssssi", $UUID, $title, $description, $location, $color, $start, $end, $id);
    $stmt->execute();
    $stmt->close();

    response(['status' => 'success', 'message' => 'Event has been updated!']);
} catch (\Throwable $th) {
    response(['status' => 'error', 'message' => $th->getMessage() . ' at line ' . $th->getLine()]);
}
