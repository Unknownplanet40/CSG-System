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

    $start = date('Y-m-d H:i:s', strtotime($start));
    $end = date('Y-m-d H:i:s', strtotime($end));

    $stmt = $conn->prepare("INSERT INTO sysevents (UUID, title, eventdesc, location, color, start, end) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $UUID, $title, $description, $location, $color, $start, $end);
    $stmt->execute();
    $stmt->close();

    response(['status' => 'success', 'message' => 'Event has been added!']);
} catch (\Throwable $th) {
    response(['status' => 'error', 'message' => $th->getMessage() . ' at line ' . $th->getLine()]);
}
