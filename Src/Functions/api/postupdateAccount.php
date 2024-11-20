<?php

session_start();
require_once "../../Database/Config.php";

require_once '../../Functions/env/HiddenKeys.php';

header('Content-Type: application/json');
date_default_timezone_set('Asia/Manila');

require '../../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function response($data)
{
    echo json_encode($data);
    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        response(['stat' => 'error', 'message' => 'Invalid request method']);
    }

    $perFname = $_POST['perFname']; // varchar
    $perLname = $_POST['perLname']; // varchar 
    $perEmail = $_POST['perEmail']; // varchar
    $perCourse = $_POST['perCourse']; // int
    $perStudentno = $_POST['perStudentno']; // int
    $perContact = $_POST['perContact']; // int
    $perAge = $_POST['perAge']; // int
    $perBirthdate = $_POST['perBirthdate']; // date
    $perOrg = $_POST['perOrg']; // int
    $perPosition = $_POST['perPosition']; // int
    $perUUID = $_POST['perUUID']; // varchar
    $perPassword = $_POST['perPassword']; // varchar

    if (empty($perFname) || empty($perLname) || empty($perEmail) || empty($perCourse) || empty($perStudentno) || empty($perContact) || empty($perAge) || empty($perBirthdate) || empty($perOrg) || empty($perPosition) || empty($perUUID)) {
        response(['stat' => 'error', 'message' => 'Please fill up all fields']);
    }

    $AcountStat = 'active'; // varchar
    $isLogin = 0; // int
    $sessionID = bin2hex(random_bytes(16)); // varchar
    $created_at = date('Y-m-d H:i:s'); // datetime
    $fullName = $perFname . ' ' . $perLname; // varchar
    $password = password_hash($perPassword, PASSWORD_DEFAULT); // varchar
    if ($perOrg == 10001) {
        $isSubOrg = 0;
    } else {
        $isSubOrg = 1;
    }
    $conn->begin_transaction();
    $stmt = $conn->prepare("UPDATE usercredentials SET First_Name = ?, Last_Name = ?, fullName = ?, primary_email = ?, contactNumber = ?, student_Number = ?, course_code = ?, accountStat = ?, password = ?, isLogin = ?, sessionID = ?, created_at = ?, age = ?, birthdate = ? WHERE UUID = ?");
    $stmt->execute([$perFname, $perLname, $fullName, $perEmail, $perContact, $perStudentno, $perCourse, $AcountStat, $password, $isLogin, $sessionID, $created_at, $perAge, $perBirthdate, $perUUID]);
    $stmt->close();

    $stmt = $conn->prepare("UPDATE userpositions SET org_code = ?, org_position = ?, isSubOrg = ? WHERE UUID = ?");
    $stmt->execute([$perOrg, $perPosition, $isSubOrg, $perUUID]);
    $stmt->close();

    // Insert into systemaudit
    $SA_UID = rand(100000, 999999);
    $userID = $perUUID;
    $evenType = "Account Activation";
    $timestamp = date('Y-m-d H:i:s');
    $ipData = file_get_contents("https://api.ipify.org?format=json");
    $ipData = json_decode($ipData, true);
    $IPADDRESS = $ipData['ip'];
    $details = "Account has been activated for " . $fullName . " with email " . $perEmail;
    $status = "success";
    $fname = $perFname;
    $email = $perEmail;

    $stmt = $conn->prepare("INSERT INTO systemaudit (SA_UID, userID, eventType, timestamp, ip_address, details, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        $conn->rollback();
        throw new Exception("Error preparing systemaudit statement: " . $conn->error);
    }
    $stmt->bind_param('issssss', $SA_UID, $userID, $evenType, $timestamp, $IPADDRESS, $details, $status);
    if (!$stmt->execute()) {
        $conn->rollback();
        throw new Exception("Error executing systemaudit insert: " . $stmt->error);
    }
    $stmt->close();

    try {
        //Server settings
        $mail = new PHPMailer(true);
        $mail->isSMTP(); // Send using SMTP
        $mail->Host = $SMTP_HOST; // Set the SMTP server to send through
        $mail->SMTPAuth = true; // Enable SMTP authentication
        $mail->Username = $SMTP_USER; // SMTP username
        $mail->Password = $SMTP_PASS; // SMTP password
        $mail->SMTPSecure = $SMTP_SECURE; // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` also accepted
        $mail->Port = $SMTP_PORT; // TCP port to connect to

        //Recipients
        $mail->setFrom($SMTP_USER, 'CSG - System');
        $mail->addAddress($email, $fname); // Add a recipient

        // Content
        $mail->isHTML(true); // Set email format to HTML
        $mail->Subject = 'Account Activation';
        $mail->Body = '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"/><meta name="viewport" content="width=device-width, initial-scale=1.0"/><title>Security Alert</title><style>body{font-family:Segoe UI, Roboto, sans-serif;line-height:1.6;}.container{max-width:600px;margin:0 auto;padding:20px;}.header{color:white;padding:5px 0;text-align:center;border-radius:10px 10px 0 0;display:flex;align-items:center;justify-content:center;}.header h2{color:black;}.content{padding:10px 20px 20px 20px;margin-top:20px;}.footer{margin-top:20px;text-align:center;font-size:0.9em;color:#777;}table{width:100%;}td{padding:10px;}img{display:block;margin:0 auto;}h2{margin:0;color:#fff;}ul{list-style-type:none;padding:0;}li{margin-bottom:10px;}p{margin:0 0 10px;}</style></head><body><div class="container"><div class="header"><table><tr><td><img src="https://i.imgur.com/Xd06F5f.jpeg" alt="Company Logo" style="display:block;border-radius:50%;margin:0 auto" width="48"/></td><td><h2>Central Student Government</h2></td></tr></table></div><hr/><h3 style="text-align:start;text-transform:uppercase;color:#333;margin-bottom:-10px;">Account Activation</h3><small style="color:#777">' . date('F j, Y') . '</small><div class="content"><p>Dear <b>' . $fname . '</b>,</p><p>Your account has been successfully activated. You can now access your account and enjoy the benefits of being a member of the CvSU Organization. Please keep your account information confidential to avoid unauthorized access.</p><p style="text-align:center;margin-top:10px"><b>Do not Share your account information with anyone.</b></p><div style="text-align:end"><p>Thank you!</p><p><br/>Best regards,<br/>Central Student Government</p></div></div><div class="footer"><p>&copy; ' . date('Y') . ' Central Student Government. All rights reserved.</p></div><div><p style="text-align:center;color:#777;font-size:0.8em;margin-top:20px;">This email was sent to <b>' . $email . '</b> because you are a member of the CvSU Organization. If you believe this email was sent by mistake, please ignore it.</p></div></div></body></html>';
        $mail->AltBody = 'This is a system generated email. Please do not reply to this email.';

        if ($mail->send()) {
            $SMTPMessage = "Email has been sent";
            $isError = false;
        } else {
            $SMTPMessage = "Email could not be sent. Mailer Error: " . $mail->ErrorInfo;
            $isError = true;
        }
    } catch (Exception $e) {
        $SMTPMessage = "Message could not be sent. Mailer Error: " . $mail->ErrorInfo;
        $isError = true;
    }

    if ($isError) {
        $conn->rollback();
        response(['stat' => 'error', 'message' => 'Cannot process request at the moment']); 
    } else {

        $stmt = $conn->prepare("INSERT INTO accounts (UUID, student_Number, password, isLogin, access_date, ipAddress) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$perUUID, $perStudentno, $password, $isLogin, $created_at, $IPADDRESS]);
        $stmt->close();

        $conn->commit();
        response(['stat' => 'success', 'message' => 'Account has been Activated']);
    }

} catch (Exception $e) {
    $conn->rollback();
    response(['stat' => 'error', 'message' => 'Invalid request method']);
}