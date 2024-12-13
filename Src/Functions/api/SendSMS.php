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
        response(['status' => 'error', 'message' => 'Invalid request method']);
    }

    //data: { contact: perContact, Fname: perFname, Lname: perLname },
    $contact = $_POST['contact'];
    $Fname = $_POST['Fname'];
    $Lname = $_POST['Lname'];

    $Greetings = [
        "Good Morning",
        "Good Afternoon",
        "Good Evening"
    ];

    $Greet = $Greetings[date('H') < 12 ? 0 : (date('H') < 18 ? 1 : 2)];
    $message = "[CSG] $Greet $Fname $Lname! Your verification code is {otp}. Please enter this code to verify your Contact Number. This code is valid for 5 minutes. Do not share it with anyone. Thank you!";
    $ch = curl_init();
    $parameters = array(
        'apikey' => 'd683d2edac646cfee311554d5a191a15',
        'number' => $contact,
        'message' => $message,
        'sendername' => 'TechHub'
    );
    curl_setopt($ch, CURLOPT_URL, 'https://api.semaphore.co/api/v4/otp');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);
    curl_close($ch);

if ($output){
    $response = json_decode($output, true);
    $code = $response[0]['code'];
    $_SESSION['otp'] = $code;
    $_SESSION['otp_expire'] = time() + 300;
    //response(['status' => 'success', 'message' => 'OTP Sent Successfully', 'response' => $response]);
    response(['status' => 'success', 'message' => 'OTP Sent Successfully']);
} else {
    response(['status' => 'error', 'message' => 'OTP Sending Failed']);
}

} catch (Exception $e) {
    response(['status' => 'error', 'message' => $e->getMessage() . ' on line ' . $e->getLine()]);
}
