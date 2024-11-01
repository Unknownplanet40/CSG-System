<?php

require_once  "../../Database/Config.php";
if (!file_exists('../env/HiddenKeys.php')) {
    // generate the file
    mkdir('../env', 0777, true);
    $file = fopen('../env/HiddenKeys.php', 'w');
    fclose($file);
    response(['status' => 'fatal', 'message' => 'You have Encountered an E-K404 Error Code. Please Contact the Administrator']);
} else {
    require_once '../env/HiddenKeys.php';
}
require_once '../../Debug/GenLog.php';
$logPath = "../../Debug/Users/UUID.log";

require '../../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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

    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data) || !isset($data['studentnum']) || !isset($data['primaryEmail']) || !isset($data['step'])) {
        response(['status' => 'error', 'message' => 'Invalid request data']);
    }

    $studentnum = $data['studentnum'];
    $primaryEmail = $data['primaryEmail'];
    $step = $data['step'];

    $validSteps = [1, 2, 3];

    if (!in_array($step, $validSteps)) {
        response(['status' => 'error', 'message' => 'Invalid step']);
    }

    if ($step == 1) {
        $stmt = $conn->prepare("SELECT * FROM usercredentials WHERE student_Number = ? AND primary_email = ?");
        $stmt->bind_param('ss', $studentnum, $primaryEmail);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if (!$user) {
            response(['status' => 'error', 'message' => 'Invalid student number or email']);
        }

        // check if user has a Token
        $stmt = $conn->prepare("SELECT * FROM userpasswordresettoken WHERE UUID = ?");
        $stmt->bind_param('s', $user['UUID']);
        $stmt->execute();
        $result = $stmt->get_result();
        $token = $result->fetch_assoc();
        $stmt->close();

        // delete token if exists
        if ($token) {
            $stmt = $conn->prepare("DELETE FROM userpasswordresettoken WHERE UUID = ?");
            $stmt->bind_param('s', $user['UUID']);
            $stmt->execute();
            $stmt->close();
        }

        // generate new token
        $token = bin2hex(random_bytes(50));
        $UUID = $user['UUID'];
        $fname = $user['First_Name'];
        $email = $user['primary_email'];
        $Expiration = date('Y-m-d H:i:s', strtotime('+5 minutes'));
        $stmt = $conn->prepare("INSERT INTO userpasswordresettoken (UUID, TOKEN, Expiration) VALUES (?, ?, ?)");
        $stmt->bind_param('sss', $UUID, $token, $Expiration);
        $stmt->execute();
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
            $mail->Subject = 'Reset Password';
            $mail->Body = '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"/><meta name="viewport" content="width=device-width, initial-scale=1.0"/><title>Reset Password</title><style>body{font-family:Arial,sans-serif;line-height:1.6;}.container{max-width:600px;margin:0 auto;padding:20px;border:1px solid #ddd;border-radius:10px;}.header{background-color:#e7d96e;color:white;padding:5px 0;text-align:center;border-radius:10px 10px 0 0;display:flex;align-items:center;justify-content:center;}.content{padding:10px 20px 20px 20px;}.footer{margin-top:20px;text-align:center;font-size:0.9em;color:#777;}table{width:100%;}td{padding:10px;}img{display:block;margin:0 auto;}h2{margin:0;color:#fff;}ul{list-style-type:none;padding:0;}li{margin-bottom:10px;}p{margin:0 0 10px;}</style></head><body><div class="container"><div class="header"><table><tr><td><img src="https://i.imgur.com/Xd06F5f.jpeg" alt="Company Logo" style="display:block;border-radius:50%;margin:0 auto" width="48"/></td><td><h2>Central Student Government</h2></td></tr></table></div><hr/><h3 style="text-align:center">Reset Password Token</h3><div class="content"><p>Dear <b>' . $fname . '</b>, <br> You have requested to reset your password. Please use the following token to reset your password: <br><br> <b style="align-items: center; justify-content: center; display: flex; font-size: 1.5em">' . $token . '</b> <br><br> If you did not request to reset your password, please ignore this email.</p><p><b>Note:</b> This token will expire in <b>5 minutes</b>.</p><div style="text-align:end"><p>Thank you!</p><p><br/>Best regards,<br/>CSG Team</p></div></div><div class="footer"><p>&copy; ' . date('Y') . ' Central Student Government. All rights reserved.</p></div></div></body></html>';
            $mail->AltBody = 'Hi ' . $fname . ', you have requested to reset your password. Please use the following token to reset your password: ' . $token . '. If you did not request to reset your password, please ignore this email. Note: This token will expire in 5 minutes. Thank you! Best regards, CSG Team. © ' . date('Y') . ' Central Student Government. All rights reserved. This is a system generated email. Please do not reply to this email.';

            if ($mail->send()) {
                $SMTPMessage = "Success! Email has been sent to " . $email;
                $eventTypes = "Password Reset Token Sent";

                $ipData = file_get_contents("https://api.ipify.org?format=json");
                $ipData = json_decode($ipData, true);
                $IPADDRESS = $ipData['ip'];
                $SAUID = mt_rand(100000, 999999);

                // Record the event to the system audit
                $stmt = $conn->prepare("INSERT INTO systemaudit (userID, eventType, ip_address, details, status, SA_UID) VALUES (?, ?, ?, ?, 'Requesting Password Reset', ?)");
                $stmt->bind_param('ssssi', $UUID, $eventTypes, $IPADDRESS, $SMTPMessage, $SAUID);
                $stmt->execute();
                $stmt->close();
            } else {
                $SMTPMessage = "Email could not be sent. Mailer Error: " . $mail->ErrorInfo;
            }

        } catch (Exception $e) {
            $SMTPMessage = "Something went wrong. Mailer Error: " . $mail->ErrorInfo;
        }

        response(['status' => 'success', 'message' => $SMTPMessage, 'ExpireAt' => $Expiration]);
    } else if ($step == 2) {
        $token = $data['token'];

        $stmt = $conn->prepare("SELECT * FROM userpasswordresettoken WHERE TOKEN = ?");
        $stmt->bind_param('s', $token);
        $stmt->execute();
        $result = $stmt->get_result();
        $token = $result->fetch_assoc();
        $stmt->close();

        if (!$token) {
            response(['status' => 'error', 'message' => 'Invalid token']);
        }

        if (strtotime($token['Expiration']) < time()) {
            response(['status' => 'error', 'message' => 'Token has expired']);
        }

        response(['status' => 'success', 'message' => 'Token is valid']);
    } else {
        $password = $data['newpassword'];
        $studentnum = $data['studentnum'];
        $primaryEmail = $data['primaryEmail'];

        $stmt = $conn->prepare("SELECT * FROM usercredentials WHERE student_Number = ? AND primary_email = ?");
        $stmt->bind_param('ss', $studentnum, $primaryEmail);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if (!$user) {
            response(['status' => 'error', 'message' => 'Invalid student number or email']);
        }

        //$hashpassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("UPDATE usercredentials SET Password = ? WHERE student_Number = ? AND primary_email = ?");
        $stmt->bind_param('sss', $password, $studentnum, $primaryEmail);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM userpasswordresettoken WHERE UUID = ?");
        $stmt->bind_param('s', $user['UUID']);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("SELECT * FROM usercredentials WHERE student_Number = ? AND primary_email = ?");
        $stmt->bind_param('ss', $studentnum, $primaryEmail);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        $UUID = $user['UUID'];
        $fname = $user['First_Name'];
        $email = $user['primary_email'];

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
            $mail->Subject = 'Password has been changed';
            $mail->Body = '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"/><meta name="viewport" content="width=device-width, initial-scale=1.0"/><title>Password Change</title><style>body{font-family:Arial,sans-serif;line-height:1.6;}.container{max-width:600px;margin:0 auto;padding:20px;border:1px solid #ddd;border-radius:10px;}.header{background-color:#e7d96e;color:white;padding:5px 0;text-align:center;border-radius:10px 10px 0 0;display:flex;align-items:center;justify-content:center;}.content{padding:10px 20px 20px 20px;}.footer{margin-top:20px;text-align:center;font-size:0.9em;color:#777;}table{width:100%;}td{padding:10px;}img{display:block;margin:0 auto;}h2{margin:0;color:#fff;}ul{list-style-type:none;padding:0;}li{margin-bottom:10px;}p{margin:0 0 10px;}</style></head><body><div class="container"><div class="header"><table><tr><td><img src="https://i.imgur.com/Xd06F5f.jpeg" alt="Company Logo" style="display:block;border-radius:50%;margin:0 auto" width="48"/></td><td><h2>Central Student Government</h2></td></tr></table></div><hr/><h3 style="text-align:center">Password Change</h3><div class="content"><p>Dear <b>' . $fname . '</b>, <br/>Please be informed that a password change request has been made for your account.</p><p style="text-align:center">If you did not make this request, please contact the <a href="mailto:ryanjames.capadocia@cvsu.edu.ph">Administrator</a> and report this incident immediately.</p><p style="text-align:center"><b>For security reasons, please do not share your password with anyone.</b></p><div style="text-align:end"><p>Thank you!</p><p><br/>Best regards,<br/>CSG Team</p></div></div><div class="footer"><p>&copy; ' . date('Y') . ' Central Student Government. All rights reserved.</p></div></div></body></html>';
            $mail->AltBody = 'Hi ' . $fname . ',  Please be informed that a password change request has been made for your account. If you did not make this request, please contact the Administrator and report this incident immediately. For security reasons, please do not share your password with anyone. Thank you! Best regards, CSG Team. © ' . date('Y') . ' Central Student Government. All rights reserved. This is a system generated email. Please do not reply to this email.';

            if ($mail->send()) {
                $SMTPMessage = "Confirmation Email has been sent to " . $email;
                $eventTypes = "Password Changed";

                $ipData = file_get_contents("https://api.ipify.org?format=json");
                $ipData = json_decode($ipData, true);
                $IPADDRESS = $ipData['ip'];
                $SAUID = mt_rand(100000, 999999);

                // Record the event to the system audit
                $stmt = $conn->prepare("INSERT INTO systemaudit (userID, eventType, ip_address, details, status, SA_UID) VALUES (?, ?, ?, ?, 'User Changed Password', ?)");
                $stmt->bind_param('ssssi', $UUID, $eventTypes, $IPADDRESS, $SMTPMessage, $SAUID);
                $stmt->execute();
                $stmt->close();
            } else {
                $SMTPMessage = "Email could not be sent. Mailer Error: " . $mail->ErrorInfo;
            }

        } catch (Exception $e) {
            $SMTPMessage = "Something went wrong. Mailer Error: " . $mail->ErrorInfo;
        }

        response(['status' => 'success', 'message' => 'Password has been updated']);
    }
} catch (Exception $e) {
    response(['status' => 'error', 'message' => 'Failed to process request (' . $e->getMessage() . ')']);
}
