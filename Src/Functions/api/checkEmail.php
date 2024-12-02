<?php
session_start();
require_once  "../../Database/Config.php";

header('Content-Type: application/json');
date_default_timezone_set('Asia/Manila');
include_once("../env/HiddenKeys.php");
include_once("../../../vendor/autoload.php");
use PHPMailer\PHPMailer\PHPMailer;

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
    $action = $_POST['action'] ?? '';

    if ($action === '') {
        $stmt = $conn->prepare("SELECT * FROM usercredentials WHERE primary_email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
    
        if ($result->num_rows > 0) {
            response(['status' => 'info', 'message' => 'Email already exists']);
        } else {
            response(['status' => 'success', 'message' => 'Email is available']);
        }
    } else if ($action === 'VerifyEmail') {
        $fname = $_POST['Name'];
        $Code = random_int(100000, 999999);
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
            $mail->Subject = 'Account Verification';
            $mail->Body = '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/><title>Security Alert</title><style>body{font-family:Segoe UI,Roboto,sans-serif;line-height:1.6;}.container{max-width:600px;margin:0 auto;padding:20px;}.header{color:white;padding:5px 0;text-align:center;border-radius:10px 10px 0 0;display:flex;align-items:center;justify-content:center;}.header h2{color:black;}.content{padding:10px 20px 20px 20px;margin-top:20px;}.footer{margin-top:20px;text-align:center;font-size:0.9em;color:#777;}table{width:100%;}td{padding:10px;}img{display:block;margin:0 auto;}h2{margin:0;color:#fff;}ul{list-style-type:none;padding:0;}li{margin-bottom:10px;}p{margin:0 0 10px;}</style></head><body><div class="container"><div class="header"><table><tr><td><img src="https://i.imgur.com/Xd06F5f.jpeg" alt="Company Logo" style="display:block;border-radius:50%;margin:0 auto" width="48"/></td><td><h2>Central Student Government</h2></td></tr></table></div><hr/><h3 style="text-align:start;text-transform:uppercase;color:#333;margin-bottom:-10px;">Account Activation</h3><small style="color:#777">' .date('F j, Y') . '</small><div class="content"><p>Dear <b>' .$fname. '</b>,</p><p>To activate your account, we kindly ask you to verify your email address. This step is necessary to ensure the security and accuracy of your information. Please find the verification code below. Copy the code and paste it into the designated field in the email verification form. Thank you for completing this important step!</p><p style="text-align:center;margin-top:10px"><b>YOUR VERIFICATION CODE:' . $Code . '</b></p><div style="text-align:end"><p>Thank you!</p><p><br/>Best regards,<br/>Central Student Government</p></div></div><div class="footer"><p>&copy; ' .date('Y'). ' Central Student Government. All rights reserved.</p></div><div><p style="text-align:center;color:#777;font-size:0.8em;margin-top:20px;">This email was sent to <b>'. $email. '</b> because you are currently creating an account with Central Student Government. If you did not request this email, please ignore it.</p></div></div></body></html>';
            $mail->AltBody = 'This is a system generated email. Please do not reply to this email.';

            if ($mail->send()) {
                $SMTPMessage = "Email sent successfully";
            } else {
                $SMTPMessage = "Email could not be sent. Mailer Error: " . $mail->ErrorInfo;
            }

        } catch (Exception $e) {
            $SMTPMessage = "Message could not be sent. Mailer Error: " . $mail->ErrorInfo;
        }

        if ($SMTPMessage === "Email sent successfully") {
            $_SESSION['verification_code'] = $Code;
            response(['status' => 'success', 'message' => 'Verification code has been sent to your email']);
        } else {
            response(['status' => 'error', 'message' => 'Cannot process request at the moment']);
        }
    } else if ($action === 'VerifyCode') {
        $code = $_POST['code'];
        $verification_code = $_SESSION['verification_code'] ?? '';
        if ((string)$verification_code === (string)$code) {
            response(['status' => 'success', 'message' => 'Verification code is correct']);
        } else {
            response(['status' => 'error', 'message' => 'Verification code is incorrect', 'Session' => $verification_code, 'Code' => $code]);
        }
    } else {
        response(['status' => 'error', 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    response(['status' => 'error', 'message' => $e->getMessage()]);
}