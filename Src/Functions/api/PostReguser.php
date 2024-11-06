<?php

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

    $studentnum = $_POST['studentnum'];
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $course = $_POST['course'];
    $year = $_POST['year'];
    $section = $_POST['section'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];

    if (!$studentnum || !$firstname || !$lastname || !$course || !$year || !$section || !$email || !$phone || !$password) {
        response(['status' => 'error', 'message' => 'Please fill all the fields']);
    }
    $conn->begin_transaction();
    $query = $conn->prepare("SELECT * FROM usercredentials WHERE primary_email = ?");
    $query->bind_param('s', $email);
    $query->execute();
    $result = $query->get_result();
    $query->close();

    if ($result->num_rows > 0) {
        $conn->rollback();
        response(['status' => 'error', 'message' => 'Email already exist']);
    }

    $query = $conn->prepare("SELECT * FROM usercredentials WHERE student_Number = ?");
    $query->bind_param('i', $studentnum);
    $query->execute();
    $result = $query->get_result();
    $query->close();

    if ($result->num_rows > 0) {
        $conn->rollback();
        response(['status' => 'error', 'message' => 'Student number already exist']);
    }

    // get course code
    $stmt = $conn->prepare("SELECT course_code FROM sysacadtype WHERE course_short_name = ? AND year = ? AND section = ?");
    $stmt->bind_param('sis', $course, $year, $section);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    if ($result->num_rows === 0) {
        $conn->rollback();
        response(['status' => 'error', 'message' => 'Course not found']);
    }

    function uuidv4(){
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    $course_code = $result->fetch_assoc()['course_code'];
    $accountStatus = 'pending';
    $isLogin = '0';
    $sessionID = bin2hex(random_bytes(16));
    $fullName = $firstname . ' ' . $lastname;
    $created_at = date('Y-m-d H:i:s');
    $contactNumber = $phone;
    $uuid = uuidv4();
    $hash = password_hash($password, PASSWORD_BCRYPT);


    $ipData = file_get_contents("https://api.ipify.org?format=json");
    $ipData = json_decode($ipData, true);
    $IPADDRESS = $ipData['ip'];

    $stmt = $conn->prepare("INSERT INTO usercredentials (UUID, First_Name, Last_Name, fullName, primary_email, student_Number, course_code, accountStat, password, isLogin, sessionID, created_at, contactNumber) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $stmt->execute([$uuid, $firstname, $lastname, $fullName, $email, $studentnum, $course_code, $accountStatus, $hash, $isLogin, $sessionID, $created_at, $contactNumber]);
    $result = $stmt->affected_rows > 0;
    $stmt->close();

    if ($result) {
        $stmt = $conn->prepare("INSERT INTO accounts (UUID, student_Number, password, isLogin, access_date, ipAddress, LoginStat, LoginAttempt, BanExpired) VALUES (?,?,?,?,?,?,'Active',0,NULL)");
        $stmt->bind_param('sisiss', $uuid, $studentnum, $hash, $isLogin, $created_at, $IPADDRESS);
        if (!$stmt->execute()) {
            die("Execution failed: " . $stmt->error);
        }
        
        $result = $stmt->affected_rows > 0;
        $stmt->close();        

        if ($result) {
            $conn->commit();
            response(['status' => 'success', 'message' => 'Registration successful']);
        } else {
            $conn->rollback();
            response(['status' => 'error', 'message' => 'Registration failed']);
        }
    } else {
        $conn->rollback();
        response(['status' => 'error', 'message' => 'Registration failed']);
    }

} catch (Exception $e) {
    response(['status' => 'error', 'message' => $e->getMessage()]);
}
