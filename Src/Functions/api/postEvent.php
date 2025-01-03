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


    $Venue = '%' . $location . '%';
    $stmt = $conn->prepare("SELECT * FROM sysvenue WHERE ven_Name LIKE ? LIMIT 1");
    $stmt->bind_param("s", $Venue);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $stmt = $conn->prepare("INSERT INTO sysvenue (ven_Name, created_by, isOccupied, startOccupied, endOccupied) VALUES (?,?,1,?,?)");
        $stmt->bind_param("ssss", $location, $_SESSION['UUID'], $start, $end);
        $stmt->execute();
        $stmt->close();
    } else {
        $row = $result->fetch_assoc();
        if ($row['isOccupied'] === 0) {
            $stmt = $conn->prepare("UPDATE sysvenue SET isOccupied = 1, startOccupied = ?, endOccupied = ? WHERE ID = ?");
            $stmt->bind_param("ssi", $start, $end, $row['ID']);
            $stmt->execute();
            $stmt->close();
        } else {
            if ($row['endOccupied'] < date('Y-m-d')) {
                $stmt = $conn->prepare("UPDATE sysvenue SET isOccupied = 1, startOccupied = ?, endOccupied = ? WHERE ID = ?");
                $stmt->bind_param("ssi", $start, $end, $row['ID']);
                $stmt->execute();
                $stmt->close();
            } else {
                response(['status' => 'error', 'message' => 'Venue is already occupied']);
            } 
        }
    }
    
    $stmt = $conn->prepare("INSERT INTO sysevents (UUID, title, eventdesc, location, color, start, end) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $UUID, $title, $description, $location, $color, $start, $end);
    $stmt->execute();
    $stmt->close();

    response(['status' => 'success', 'message' => 'Event has been added!']);
} catch (\Throwable $th) {
    response(['status' => 'error', 'message' => $th->getMessage() . ' at line ' . $th->getLine()]);
}
