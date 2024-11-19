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
        response(['error' => 'Invalid request method']);
    }

    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM usercredentials WHERE primary_email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    if ($result->num_rows === 0) {
        response(['status' => 'success', 'isAccountExist' =>  'false', 'message' => 'Account does not exist']);
    }

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        if (password_verify($password, $row['password'])) {
            $_SESSION['UUID'] = $row['UUID'];
            $_SESSION['accountStat'] = $row['accountStat'];
            response(['status' => 'success', 'isAccountExist' =>  'true', 'message' => 'Account activated']);
        } else {
            response(['status' => 'success', 'isAccountExist' =>  'false', 'message' => 'Your password is incorrect']);
        }
    }
} catch (Exception $e) {
    response(['status' => 'error', 'message' => $e->getMessage()]);
}