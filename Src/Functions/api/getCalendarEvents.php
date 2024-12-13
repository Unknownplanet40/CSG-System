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
        'bs-blue',
        'bs-indigo',
        'bs-purple',
        'bs-pink',
        'bs-red',
        'bs-orange',
        'bs-yellow',
        'bs-green',
        'bs-teal',
        'bs-gray',
        'bs-cyan',
    ];

    $events = [];
    while ($row = $result->fetch_assoc()) {
        $Name = $conn->query("SELECT fullName FROM usercredentials WHERE UUID = '{$row['UUID']}'")->fetch_assoc()['fullName'];
        $row['Name'] = $Name;

        $colorIndex = array_search($row['color'], $BGColor);

        if ($row['type'] != 'BDay') {
            if (strtotime($row['end']) < strtotime(date('Y-m-d H:i:s'))) {
                $isended = $conn->query("UPDATE sysevents SET isEnded = 1 WHERE id = '{$row['ID']}'");
                $row['isEnded'] = true;
                $row['BGColor'] = $BGColor[9];
                $row['color'] = 'text-body-color';
            } else {
                $row['isEnded'] = false;
                $row['BGColor'] = $BGColor[$colorIndex];
                $row['color'] = 'text-body-color';
            }
        } else {
            $row['BGColor'] = $BGColor[$colorIndex];
            $row['color'] = 'text-body-color';
        }

        if ($row['type'] == 'event') {
            $row['EventType'] = 'Event';
            $row['isAllDay'] = false;
        } else {
            if ($row['type'] == 'AP') {
                $row['EventType'] = 'Activity Proposal';
            } elseif ($row['type'] == 'EL') {
                $row['EventType'] = 'Excuse Letter';
            } elseif ($row['type'] == 'MM') {
                $row['EventType'] = 'Meeting Minutes';
            } elseif ($row['type'] == 'OM') {
                $row['EventType'] = 'Official Memorandum';
            } elseif ($row['type'] == 'PP') {
                $row['EventType'] = 'Project Proposal';
            } elseif ($row['type'] == 'BDay') {
                $row['EventType'] = 'Birthday';
                $row['isAllDay'] = true;
            }
        }

        $row['start'] = date('Y-m-d\TH:i:s', strtotime($row['start']));
        $row['end'] = date('Y-m-d\TH:i:s', strtotime($row['end']));
        
        if ($row['type'] == 'BDay') {
            for ($i = date('Y', strtotime($row['start'])); $i <= 2030; $i++) {
                $events[] = [
                    'id' => uniqid($row['ID'] . $i),
                    'calendarId' => 'None',
                    'title' => $row['title'],
                    'category' => 'allday',
                    'body' => $row['eventdesc'],
                    'location' => false,
                    'state' => false,
                    'raw' => ['isEnded' => $row['isEnded'], 'eventType' => $row['EventType']],
                    'attendees' => [$row['Name']],
                    'isAllDay' => true,
                    'isReadOnly' => true,
                    'start' => date('Y-m-d\TH:i:s', strtotime($i . date('-m-d', strtotime($row['start'])))),
                    'end' => date('Y-m-d\TH:i:s', strtotime($i . date('-m-d', strtotime($row['end'])))),
                    'backgroundColor' => $row['BGColor'],
                ];
            }
        } else {
            $events[] = [
                'id' => $row['ID'],
                'calendarId' => 'None',
                'title' => $row['title'],
                'category' => $row['isAllDay'] ? 'allday' : 'time',
                'body' => $row['eventdesc'],
                'location' => $row['location'],
                'state' => false,
                'raw' => ['isEnded' => $row['isEnded'], 'eventType' => $row['EventType']],
                'attendees' => [$row['Name']],
                'isAllDay' => $row['isAllDay'] ?? false,
                'isReadOnly' => true,
                'start' => $row['start'],
                'end' => $row['end'],
                'backgroundColor' => $row['BGColor'],
            ];
        }

/*         $events[] = [
            'id' => $row['ID'],
            'calendarId' => 'None',
            'title' => $row['title'],
            'category' => $row['isAllDay'] ? 'allday' : 'time',
            'body' => $row['eventdesc'],
            'location' => $row['location'],
            'state' => false,
            'raw' => ['isEnded' => $row['isEnded'], 'eventType' => $row['EventType']],
            'attendees' => [$row['Name']],
            'isAllDay' => $row['isAllDay'] ?? false,
            'isReadOnly' => true,
            'start' => $row['start'],
            'end' => $row['end'],
            'backgroundColor' => $row['BGColor'],
        ]; */
    }
    $stmt->close();

    response(['status' => 'success', 'data' => $events]);
} catch (\Throwable $th) {
    response(['status' => 'error', 'message' => $th->getMessage() . ' at line ' . $th->getLine()]);
}
