<?php
session_start();

require_once  "../../Database/Config.php";
require_once '../env/HiddenKeys.php';

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
        response(['status' => 'error', 'message' => 'Invalid request method']);
    }
    
    $userUUID = $_POST['userUUID'];
    $userRole = $_POST['userRole'];
    $userOrg = $_POST['userOrg'];
    $userIposition = $_POST['userIposition'];

    $stmt = $conn->prepare("SELECT * FROM usercredentials WHERE UUID = ?");
    $stmt->bind_param('s', $userUUID);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    if ($result->num_rows === 0) {
        response(['status' => 'error', 'message' => 'User not found']);
    }

    $row = $result->fetch_assoc();
    $fname = $row['First_Name'];
    $email = $row['primary_email'];

    $stmt = $conn->prepare("SELECT * FROM userpositions WHERE UUID = ?");
    $stmt->bind_param('s', $userUUID);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    if ($result->num_rows > 0) {
        $stmt = $conn->prepare("UPDATE userpositions SET role = ?, org_code = ?, org_position = ?, status = 'active' WHERE UUID = ?");
        $stmt->bind_param('ssss', $userRole, $userOrg, $userIposition, $userUUID);
        $stmt->execute();
        $stmt->close();
    } else {
        $stmt = $conn->prepare("INSERT INTO userpositions (UUID, role, org_code, org_position, status) VALUES (?, ?, ?, ?, 'active')");
        $stmt->bind_param('ssss', $userUUID, $userRole, $userOrg, $userIposition);
        $stmt->execute();
        $stmt->close();
    }

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
        $mail->addReplyTo($email, 'No Reply');

        // Content
        $mail->isHTML(true); // Set email format to HTML
        $mail->Subject = 'Account Activation';
        $mail->Body = '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/><title>Security Alert</title><style>body{font-family:Arial,sans-serif;line-height:1.6}.container{max-width:600px;margin:0 auto;padding:20px;border:1px solid #ddd;border-radius:10px}.header{background-color:#e7d96e;color:white;padding:5px 0;text-align:center;border-radius:10px 10px 0 0;display:flex;align-items:center;justify-content:center}.content{padding:10px 20px 20px 20px}.footer{margin-top:10px;text-align:center;font-size:.9em;color:#777}table{width:100%}td{padding:10px}img{display:block;margin:0 auto}h2{margin:0;color:#fff}ul{list-style-type:none;padding:0}li{margin-bottom:10px}p{margin:0 0 10px}</style></head><body><div class="container"><div class="header"><table><tr><td><img src="https://i.imgur.com/Xd06F5f.jpeg" alt="Company Logo" style="display:block;border-radius:50%;margin:0 auto" width="48"/></td><td><h2>Central Student Government</h2></td></tr></table></div><hr/><h3 style="text-align:center">Account Activation</h3><div class="content"><p>Dear <b>' . $fname . '</b>,</p><p><b>Great news!</b> Your account has been successfully activated. You can now log in to your account and start using our services.</p><div style="text-align:end;margin-top:20px"><p>Thank you!</p><p><br/>Best regards,<br/>CSG Team</p></div></div><div class="footer"><p>&copy; ' . date('Y') . ' Central Student Government. All rights reserved.</p></div></div></body></html>';
        $mail->AltBody = 'This is a system generated email. Please do not reply to this email.';

        if ($mail->send()) {
            $SMTPMessage = "Email sent successfully";
            
            $ipData = file_get_contents("https://api.ipify.org?format=json");
            $ipData = json_decode($ipData, true);
            $IPADDRESS = $ipData['ip'];
            $details = "Account activated for " . $fname . " (" . $email . ")";
            $loginmessage = "Account Activation";
            $SA_UID = rand(100000, 999999);

            // Record the event to the system audit
            $stmt = $conn->prepare("INSERT INTO systemaudit (userID, eventType, ip_address, details, status, SA_UID) VALUES (?, ?, ?, ?, 'Failed', ?)");
            $stmt->bind_param("ssssi", $userUUID, $loginmessage, $IPADDRESS, $details, $SA_UID);
            $stmt->execute();
            $stmt->close();

            $stmt = $conn->prepare("UPDATE usercredentials SET accountStat = 'active' WHERE UUID = ?");
            $stmt->bind_param('s', $userUUID);
            $stmt->execute();
            $stmt->close();

            response(['status' => 'success', 'message' => 'User role updated successfully']);
        } else {
            $SMTPMessage = "Email could not be sent. Mailer Error: " . $mail->ErrorInfo;
            response(['status' => 'error', 'message' => 'User role updated successfully but email notification failed']);
        }

    } catch (Exception $e) {
        $SMTPMessage = "Message could not be sent. Mailer Error: " . $mail->ErrorInfo;
        response(['status' => 'error', 'message' => 'User role updated successfully but email notification failed']);
    }
} catch (Exception $e) {
    response(['status' => 'error', 'message' => $e->getMessage()]);
}
