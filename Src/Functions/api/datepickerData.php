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

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    response(['status' => 'error', 'message' => 'Invalid request method']);
}

try {
    $stmt = $conn->prepare("SELECT * FROM sysevents WHERE isDeleted = 0");
    $stmt->execute();
    $result = $stmt->get_result();
    $events = [];
    while ($row = $result->fetch_assoc()) {
        $row['start'] = date('Y-m-d\TH:i:s', strtotime($row['start']));
        $row['end'] = date('Y-m-d\TH:i:s', strtotime($row['end']));
        if (date('H:i:s', strtotime($row['end'])) == '00:00:00') {
            $row['end'] = date('Y-m-d\T12:59:59', strtotime($row['end']));
        }

        if ($row['type'] != 'BDay' || $row['isEnded'] == 1) {
            $disabledRange = [
                'start' => $row['start'],
                'end' => $row['end']
            ];
            array_push($events, $disabledRange);
        }
    }
    $stmt->close();

    response(['status' => 'success', 'data' => $events]);
} catch (\Throwable $th) {
    response(['status' => 'error', 'message' => $th->getMessage() . ' at line ' . $th->getLine()]);
}
