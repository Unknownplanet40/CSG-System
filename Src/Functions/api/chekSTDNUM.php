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

    $stmt = $conn->prepare("SELECT student_Number FROM usercredentials WHERE student_Number = ?");
    $stmt->bind_param('i', $studentnum);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    if ($result->num_rows === 0) {
        response(['status' => 'success', 'isStudentNumExist' =>  'false']);
    }

    $user = $result->fetch_assoc();

    response(['status' => 'success', 'isStudentNumExist' =>  'true']);

} catch (Exception $e) {
    response(['status' => 'error', 'message' => $e->getMessage()]);
}