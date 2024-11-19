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
        response(['stat' => 'error', 'message' => 'Invalid request method']);
    }

    if ($_SESSION['role'] != 1) {
        response(['stat' => 'error', 'message' => 'Unauthorized access']);
    }

    $action = $_POST['action'];
    $oldvalue = [];
    $newvalue = [];

    if ($action == 'user-update') {
        $UUID = $_POST['UUID'];
        $First_Name = $_POST['First_Name'];
        $Last_Name = $_POST['Last_Name'];
        //$student_Number = $_POST['student_Number'];
        $primary_email = $_POST['primary_email'];
        $contactNumber = $_POST['contactNumber'];
        $org_code = $_POST['org_code'];
        $org_position = $_POST['org_position'];
        $course_code = $_POST['course_code'];
        $password = $_POST['password'];

        if (empty($UUID) || empty($First_Name) || empty($Last_Name) || empty($primary_email) || empty($contactNumber) || empty($org_code) || empty($org_position) || empty($course_code)) {
            response(['stat' => 'error', 'message' => 'All fields are required']);
        }

        if (!filter_var($primary_email, FILTER_VALIDATE_EMAIL)) {
            response(['stat' => 'error', 'message' => 'Invalid email format']);
        }

        if (!preg_match('/^[0-9]{11}+$/', $contactNumber)) {
            response(['stat' => 'error', 'message' => 'Invalid contact number']);
        }

        $stmt = $conn->prepare("SELECT * FROM usercredentials WHERE UUID = ?");
        $stmt->bind_param("s", $UUID);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        while ($row = $result->fetch_assoc()) {
            if ($row['First_Name'] != $First_Name) {
                $oldvalue['First_Name'] = $row['First_Name'];
                $newvalue['First_Name'] = $First_Name;
            }

            if ($row['Last_Name'] != $Last_Name) {
                $oldvalue['Last_Name'] = $row['Last_Name'];
                $newvalue['Last_Name'] = $Last_Name;
            }

            if ($row['primary_email'] != $primary_email) {
                $oldvalue['primary_email'] = $row['primary_email'];
                $newvalue['primary_email'] = $primary_email;
            }

            if ($row['contactNumber'] != $contactNumber) {
                $oldvalue['contactNumber'] = $row['contactNumber'];
                $newvalue['contactNumber'] = $contactNumber;
            }

            if ($row['course_code'] != $course_code) {
                $oldvalue['course_code'] = $row['course_code'];
                $newvalue['course_code'] = $course_code;
            }

            if ($password != '') {
                $oldvalue['password'] = $row['password'];
                $newvalue['password'] = password_hash($password, PASSWORD_DEFAULT);
            }

        }

        $stmt = $conn->prepare("SELECT * FROM userpositions WHERE UUID = ?");
        $stmt->bind_param("s", $UUID);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        while ($row = $result->fetch_assoc()) {
            if ($row['org_code'] != $org_code) {
                $oldvalue['org_code'] = $row['org_code'];
                $newvalue['org_code'] = $org_code;
            }

            if ($row['org_position'] != $org_position) {
                $oldvalue['org_position'] = $row['org_position'];
                $newvalue['org_position'] = $org_position;
            }
        }

        if ($password != '') {
            if (strlen($password) < 8) {
                response(['stat' => 'error', 'message' => 'Password must be at least 8 characters']);
            }

            $uppercase = preg_match('@[A-Z]@', $password);
            $lowercase = preg_match('@[a-z]@', $password);
            $number    = preg_match('@[0-9]@', $password);
            $specialChars = preg_match('@[^\w]@', $password);

            if (!$uppercase || !$lowercase || !$number || !$specialChars) {
                response(['stat' => 'error', 'message' => 'Password must contain at least 1 uppercase, 1 lowercase, 1 number and 1 special character']);
            }

            $password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("UPDATE usercredentials SET First_Name = ?, Last_Name = ?, fullName = ?, primary_email = ?, contactNumber = ?, course_code = ?, password = ? WHERE UUID = ?");
            $stmt->bind_param("ssssiiss", $First_Name, $Last_Name, $First_Name . ' ' . $Last_Name, $primary_email, $contactNumber, $course_code, $password, $UUID);
        } else {
            $fullname = $First_Name . ' ' . $Last_Name;
            $stmt = $conn->prepare("UPDATE usercredentials SET First_Name = ?, Last_Name = ?, fullName = ?, primary_email = ?, contactNumber = ?, course_code = ? WHERE UUID = ?");
            $stmt->bind_param("ssssiis", $First_Name, $Last_Name, $fullname, $primary_email, $contactNumber, $course_code, $UUID);
        }
        $stmt->execute();
        $stmt->close();

        $isneedtorelogin = false;
    } elseif ($action == 'admin-update') {
        $UUID = $_POST['UUID'];
        $First_Name = $_POST['First_Name'];
        $Last_Name = $_POST['Last_Name'];
        $primary_email = $_POST['primary_email'];
        $contactNumber = $_POST['contactNumber'];
        $password = $_POST['password'];


        if (empty($UUID) || empty($First_Name) || empty($Last_Name) || empty($primary_email) || empty($contactNumber)) {
            response(['stat' => 'error', 'message' => 'All fields are required']);
        }

        if (!filter_var($primary_email, FILTER_VALIDATE_EMAIL)) {
            response(['stat' => 'error', 'message' => 'Invalid email format']);
        }

        if (!preg_match('/^[0-9]{11}+$/', $contactNumber)) {
            response(['stat' => 'error', 'message' => 'Invalid contact number']);
        }

        $stmt = $conn->prepare("SELECT * FROM usercredentials WHERE UUID = ?");
        $stmt->bind_param("s", $UUID);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        while ($row = $result->fetch_assoc()) {
            if ($row['First_Name'] != $First_Name) {
                $oldvalue['First_Name'] = $row['First_Name'];
                $newvalue['First_Name'] = $First_Name;
            }

            if ($row['Last_Name'] != $Last_Name) {
                $oldvalue['Last_Name'] = $row['Last_Name'];
                $newvalue['Last_Name'] = $Last_Name;
            }

            if ($row['primary_email'] != $primary_email) {
                $oldvalue['primary_email'] = $row['primary_email'];
                $newvalue['primary_email'] = $primary_email;
            }

            if ($row['contactNumber'] != $contactNumber) {
                $oldvalue['contactNumber'] = $row['contactNumber'];
                $newvalue['contactNumber'] = $contactNumber;
            }
        }

        if ($password != '') {
            if (strlen($password) < 8) {
                response(['stat' => 'error', 'message' => 'Password must be at least 8 characters']);
            }

            $uppercase = preg_match('@[A-Z]@', $password);
            $lowercase = preg_match('@[a-z]@', $password);
            $number    = preg_match('@[0-9]@', $password);
            $specialChars = preg_match('@[^\w]@', $password);

            if (!$uppercase || !$lowercase || !$number || !$specialChars) {
                response(['stat' => 'error', 'message' => 'Password must contain at least 1 uppercase, 1 lowercase, 1 number and 1 special character']);
            }

            $password = password_hash($password, PASSWORD_DEFAULT);

            $fullname = $First_Name . ' ' . $Last_Name;
            $stmt = $conn->prepare("UPDATE usercredentials SET First_Name = ?, Last_Name = ?, fullName = ?, primary_email = ?, contactNumber = ?, password = ? WHERE UUID = ?");
            $stmt->bind_param("sssiss", $First_Name, $Last_Name, $fullname, $primary_email, $contactNumber, $password, $UUID);
        } else {
            $fullname = $First_Name . ' ' . $Last_Name;
            $stmt = $conn->prepare("UPDATE usercredentials SET First_Name = ?, Last_Name = ?, fullName = ?, primary_email = ?, contactNumber = ? WHERE UUID = ?");
            $stmt->bind_param("ssssis", $First_Name, $Last_Name, $fullname, $primary_email, $contactNumber, $UUID);
        }

        $stmt->execute();
        $stmt->close();

        if ($_SESSION['UUID'] == $UUID) {
            $_SESSION['First_Name'] = $First_Name;
            $_SESSION['Last_Name'] = $Last_Name;
            $_SESSION['fullName'] = $First_Name . ' ' . $Last_Name;
            $_SESSION['primary_email'] = $primary_email;
            $_SESSION['contactNumber'] = $contactNumber;

            if ($password != '') {
                $_SESSION['password'] = $password;
            }

            $isneedtorelogin = true;
        } else {
            $isneedtorelogin = false;
        }
    } elseif ($action == 'user-delete' || $action == 'admin-delete') {
        $UUID = $_POST['UUID'];

        if (empty($UUID)) {
            response(['stat' => 'error', 'message' => 'Invalid request method']);
        }

        if ($_SESSION['UUID'] == $UUID) {
            response(['stat' => 'error', 'message' => 'You cannot delete your own account']);
        }

        $stmt = $conn->prepare("UPDATE usercredentials SET accountStat = 'archived' WHERE UUID = ?");
        $stmt->bind_param("s", $UUID);
        $stmt->execute();
        $stmt->close();

        $oldvalue = [
            'accountStat' => 'active'
        ];

        $newvalue = [
            'accountStat' => 'archived'
        ];
        $isneedtorelogin = false;
    } elseif ($action == 'user-restore' || $action == 'admin-restore') {
        $UUID = $_POST['UUID'];

        if (empty($UUID)) {
            response(['stat' => 'error', 'message' => 'Invalid request method']);
        }

        $stmt = $conn->prepare("UPDATE usercredentials SET accountStat = 'active' WHERE UUID = ?");
        $stmt->bind_param("s", $UUID);
        $stmt->execute();
        $stmt->close();

        $oldvalue = [
            'accountStat' => 'archived'
        ];

        $newvalue = [
            'accountStat' => 'active'
        ];
        $isneedtorelogin = false;
    } else {
        response(['stat' => 'error', 'message' => 'Invalid action']);
    }

    $stmt = $conn->prepare("UPDATE userpositions SET org_code = ?, org_position = ? WHERE UUID = ?");
    $stmt->bind_param("iss", $org_code, $org_position, $UUID);
    $stmt->execute();
    $stmt->close();

    $changed_by = $_SESSION['UUID'];
    $affected_user = $UUID;
    $change_date = date('Y-m-d H:i:s');
    $old_value = $oldvalue;
    $new_value = $newvalue;
    switch ($action) {
        case 'user-delete':
        case 'admin-delete':
            $action = 'delete';
            break;
        case 'user-restore':
        case 'admin-restore':
            $action = 'restore';
            break;
        default:
            $action = 'update';
            break;
    }
    if (is_array($oldvalue) && is_array($newvalue)) {
        $old_value = json_encode($oldvalue);
        $new_value = json_encode($newvalue);
        $isArray = 1;
    } else {
        $isArray = 0;
    }

    if (!empty($old_value) && !empty($new_value)) {
        $stmt = $conn->prepare("SELECT * FROM auditlog WHERE changed_by = ? AND affected_user = ?");
        $stmt->bind_param("ss", $changed_by, $affected_user);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        $stmt = $conn->prepare("INSERT INTO auditlog (changed_by, affected_user, change_date, old_value, new_value, action, isArray) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssi", $changed_by, $affected_user, $change_date, $old_value, $new_value, $action, $isArray);
        $stmt->execute();
        $stmt->close();
    }

    response(['stat' => 'success', 'message' => 'Account updated successfully', 'isneedtorelogin' => $isneedtorelogin]);
} catch (\Throwable $th) {
    response(['stat' => 'error', 'message' => $th->getMessage() . ' at line ' . $th->getLine()]);
}
