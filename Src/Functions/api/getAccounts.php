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

$accounts = [];
if (!isset($_GET['status'])) {
    $status = 'active';
} else if ($_GET['status'] == 'pending') {
    $status = 'pending';
} else if ($_GET['status'] == 'archived') {
    $status = 'archived';
} else if ($_GET['status'] == 'active') {
    $status = 'active';
} else {
    response(['status' => 'error', 'message' => 'Invalid status']);
}



if ($status == "pending") {
    $stmt = $conn->prepare("SELECT * FROM usercredentials WHERE accountStat = 'pending' ORDER BY fullName ASC");
} else if ($status == "active") {
    $stmt = $conn->prepare("SELECT * FROM usercredentials WHERE accountStat = 'active' ORDER BY fullName ASC");
} else {
    $stmt = $conn->prepare("SELECT * FROM usercredentials WHERE accountStat = 'archived' ORDER BY fullName ASC");
}
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $row['contactNumber'] = $row['contactNumber'] ?? '';

        $stmt = $conn->prepare("SELECT * FROM sysacadtype WHERE course_code = ?");
        $stmt->bind_param("s", $row['course_code']);
        $stmt->execute();
        $course = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $row['course_code'] = $course ? ($course['course_short_name'] . ' - ' . $course['year'] . '' . $course['section']) : '';

        $row['isLogin'] = $row['isLogin'] == 1 ? 'Online' : 'Offline';

        $stmt = $conn->prepare("SELECT role, org_code, org_position FROM userpositions WHERE UUID = ?");
        $stmt->bind_param("s", $row['UUID']);
        $stmt->execute();
        $role = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $row['role'] = $role ? $role['role'] : '';
        $row['org_code'] = $role ? $role['org_code'] : '';
        $row['org_position'] = $role ? $role['org_position'] : '';

        if ($row['First_Name'] == 'None' && $row['Last_Name'] == 'None') {
            $row['First_Name'] = 'TBA';
            $row['Last_Name'] = '';
        }

        if ($_GET['type'] == 'user') {
            if ($row['role'] > 1) {

                if ($row['org_code'] != '') {
                    $stmt = $conn->prepare("SELECT * FROM sysorganizations WHERE org_code = ?");
                    $stmt->bind_param("s", $row['org_code']);
                    $stmt->execute();
                    $org = $stmt->get_result()->fetch_assoc();
                    $stmt->close();

                    $row['org_code'] = $org ? $org['org_short_name'] : '';
                } else {
                    $row['org_code'] = '';
                }

                $accounts[] = [
                    'ID' => $row['ID'],
                    'UUID' => $row['UUID'],
                    'First_Name' => $row['First_Name'],
                    'Last_Name' => $row['Last_Name'],
                    'fullName' => $row['First_Name'] . ' ' . $row['Last_Name'],
                    'primary_email' => $row['primary_email'],
                    'contactNumber' => "0" . $row['contactNumber'],
                    'student_Number' => $row['student_Number'],
                    'course_code' => $row['course_code'],
                    'accountStat' => $row['accountStat'],
                    'password' => $row['password'],
                    'isLogin' => $row['isLogin'],
                    'sessionID' => $row['sessionID'],
                    'created_at' => $row['created_at'],
                    'role' => $row['role'],
                    'org_code' => $row['org_code'],
                    'org_position' => $row['org_position'],
                ];
            } else {
                continue;
            }
        } elseif ($_GET['type'] == 'admin') {
            if ($row['role'] == 1) {
                for ($i = 0; $i < 1; $i++) {
                    $accounts[] = [
                        'ID' => $row['ID'],
                        'UUID' => $row['UUID'],
                        'First_Name' => $row['First_Name'],
                        'Last_Name' => $row['Last_Name'],
                        'fullName' => $row['First_Name'] . ' ' . $row['Last_Name'],
                        'primary_email' => $row['primary_email'],
                        'contactNumber' => "0" . $row['contactNumber'],
                        'student_Number' => $row['student_Number'],
                        'course_code' => $row['course_code'],
                        'accountStat' => $row['accountStat'],
                        'password' => $row['password'],
                        'isLogin' => $row['isLogin'],
                        'sessionID' => $row['sessionID'],
                        'created_at' => $row['created_at'],
                        'role' => $row['role'],
                    ];
                }
            } else {
                continue;
            }
        } else {
            response(['status' => 'error', 'message' => 'Invalid type']);
        }
    }
}

response(['status' => 'success', 'data' => $accounts, 'status' => $status]);
