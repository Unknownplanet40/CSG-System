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
    $stmt = $conn->prepare("SELECT * FROM sysevents");
    $stmt->execute();
    $result = $stmt->get_result();

    $BGColor = [
        'bs-primary',
        'bs-secondary',
        'bs-success',
        'bs-danger',
        'bs-warning',
        'bs-info',
        'bs-light',
        'bs-dark'
    ];

    $events = [];
    while ($row = $result->fetch_assoc()) {
        $Name = $conn->query("SELECT fullName FROM usercredentials WHERE UUID = '{$row['UUID']}'")->fetch_assoc()['fullName'];
        $row['Name'] = $Name;

        $colorIndex = array_search($row['color'], $BGColor);
        
        if (strtotime($row['end']) < strtotime(date('Y-m-d H:i:s'))) {
            $isended = $conn->query("UPDATE sysevents SET isEnded = 1 WHERE id = '{$row['id']}'");
            $row['isEnded'] = true;
            $row['BGColor'] = $BGColor[1];
            $row['color'] = 'text-body-color';
        } else {
            $row['isEnded'] = false;
            $row['BGColor'] = $BGColor[$colorIndex];
            $row['color'] = 'text-body-color';
        }

        // convert the date to 2024-12-18T09:00:00
        $row['start'] = date('Y-m-d\TH:i:s', strtotime($row['start']));
        $row['end'] = date('Y-m-d\TH:i:s', strtotime($row['end']));

        $events[] = [
            'id' => $row['ID'],
            'calendarId' => 'None',
            'title' => $row['title'],
            'category' => 'time',
            'body' => $row['eventdesc'],
            'location' => $row['location'],
            'state' => $row['isEnded'],
            'attendees' => [$row['Name']],
            'isReadOnly' => true,
            'start' => $row['start'],
            'end' => $row['end'],
            'backgroundColor' => $row['BGColor'],
        ];



    }
    $stmt->close();

    response(['status' => 'success', 'data' => $events]);
} catch (\Throwable $th) {
    response(['status' => 'error', 'message' => $th->getMessage() . ' at line ' . $th->getLine()]);
}