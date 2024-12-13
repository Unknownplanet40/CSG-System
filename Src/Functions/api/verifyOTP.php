<?php
session_start();

header('Content-Type: application/json');
date_default_timezone_set('Asia/Manila');

function response($data)
{
    echo json_encode($data);
    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        response(['status' => 'error', 'message' => 'Invalid request method']);
    }

    $otp = $_POST['otp'];

    if (!isset($_SESSION['otp'])) {
        response(['status' => 'error', 'message' => 'OTP not found']);
    }

    if (time() > $_SESSION['otp_expire']) {
        unset($_SESSION['otp']);
        unset($_SESSION['otp_expire']);
        response(['status' => 'error', 'message' => 'OTP Expired has been expired']);
    }

    if ((string)$_SESSION['otp'] !== (string)$otp) {
        response(['status' => 'error', 'message' => 'Invalid OTP']);
    }

    unset($_SESSION['otp']);
    unset($_SESSION['otp_expire']);
    response(['status' => 'success', 'message' => 'OTP Verified Successfully']);
} catch (\Throwable $th) {
    response(['status' => 'error', 'message' => $th->getMessage() . ' on line ' . $th->getLine()]);
}