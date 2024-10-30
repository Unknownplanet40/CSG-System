<?php

session_start();

require_once  "../../Database/Config.php";
require_once './FetchuserCredentials.php';
require_once '../env/HiddenKeys.php';
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

$temp_UUID = "";

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        response(['error' => 'Invalid request method']);
    }

    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data) || !isset($data['studentnum']) || !isset($data['password'])) {
        response(['error' => 'Invalid request data']);
    }

    $studentNumber = $data['studentnum']; // int
    $password = $data['password']; // varchar
    $ipAddress = $data['ipAddress']; // varchar
    if (isset($data['stats']) && $data['stats'] == 'autologin') {
        $autoLogin = true;
    } else {
        $autoLogin = false;
    }

    $temp_UUID = $data['UUID'];

    $stmt = $conn->prepare("SELECT student_Number, password, LoginStat, BanExpired, UUID FROM accounts WHERE student_Number = ?");
    $stmt->bind_param("i", $studentNumber);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    // Get the current date
    $CurrentDate = date('Y-m-d H:i:s');

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Check if the account is locked
        if ($row['LoginStat'] === 'Locked') {
            $row['BanExpired'] = date('Y-m-d H:i:s', strtotime($row['BanExpired']));

            // Check if the Lockout time has expired
            if ($CurrentDate > $row['BanExpired']) {
                $stmt = $conn->prepare("UPDATE accounts SET LoginStat = 'Active', LoginAttempt = 0, BanExpired = NULL WHERE student_Number = ?");
                $stmt->bind_param("i", $studentNumber);
                $stmt->execute();
                $stmt->close();
            } else {
                $time = strtotime($row['BanExpired']) - strtotime($CurrentDate);

                if (gmdate("i", $time) == 0) {
                    $time = gmdate("s", $time) . " seconds";
                } else {
                    $time = gmdate("i", $time) . " minutes and " . gmdate("s", $time) . " seconds";
                }
                response(['status' => 'error', 'message' => 'Please try again after ' . $time . '.', 'isLocked' => true, 'Date' => $row['BanExpired']]);
            }
        }

        // if the account is not locked, proceed to login ------------------------------
        // Check if the password is correct
        if ($row['password'] === $password) {
            $stmt = $conn->prepare("SELECT isLogin, access_date, ipAddress, UUID FROM accounts WHERE student_Number = ?");
            $stmt->bind_param("i", $studentNumber);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();

            $row = $result->fetch_assoc();
            $isLogin = $row['isLogin'];
            $IPAddress = $row['ipAddress'] == null ? "" : $row['ipAddress'];
            $access_date = date('Y-m-d H:i:s', strtotime($row['access_date']));
            $date = date('Y-m-d H:i:s', strtotime($CurrentDate));

            $loginmessage = "";

            if ($autoLogin) {
                $loginmessage = "Auto login successful.";
            } else {
                if ($IPAddress != "") {
                    if ($IPAddress != $ipAddress && $access_date != $date) {
                        $loginmessage = "Your account is being accessed outside your registered Network. (Current IP: " . $ipAddress . " | Registered IP: " . $IPAddress . ")";
                    } elseif ($isLogin == 1 && $access_date != $date) {
                        $loginmessage = "Your previous session was not logged out correctly. Please note to logout properly next time to avoid any issues.";
                    } else {
                        $loginmessage = "Login successful.";
                    }
                } else {
                    if ($isLogin == 1 && $access_date != $date) {
                        $loginmessage = "Your previous session was not logged out correctly. Please note to logout properly next time to avoid any issues.";
                    } else {
                        $loginmessage = "Login successful.";
                    }
                }
            }

            $random = bin2hex(random_bytes(16));

            $stmt = $conn->prepare("UPDATE accounts SET access_date = ?, LoginAttempt = 0, isLogin = 1, BanExpired = NULL, ipAddress = ? WHERE student_Number = ?");
            $stmt->bind_param("sss", $CurrentDate, $ipAddress, $studentNumber);
            $stmt->execute();
            $stmt->close();

            // update account status to online
            $stmt = $conn->prepare("UPDATE usercredentials SET isLogin = 1, sessionID = ? WHERE student_Number = ?");
            $stmt->bind_param("ss", $random, $studentNumber);
            $stmt->execute();
            $stmt->close();
            
            // Fetch the account credentials to session
            $account = fetchUserCredentials($conn, $studentNumber);

            // Check if the account is fetched
            if ($account === null) {
                response(['status' => 'error', 'message' => 'An error occurred while fetching your account']);
            } else {
                $stmt = $conn->prepare("UPDATE accounts SET LoginAttempt = 0 WHERE student_Number = ?");
                $stmt->bind_param("s", $studentNumber);
                $stmt->execute();
                $stmt->close();

                writeLog($logPath, "info", $account['UUID'], "Login", $ipAddress, "Success");
                
                response([
                    'status' => 'success',
                    'message' => $loginmessage,
                    'data' => $account
                ]);
            }
        } else {
            $stmt = $conn->prepare("SELECT LoginAttempt FROM accounts WHERE student_Number = ?");
            $stmt->bind_param("s", $studentNumber);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();

            $row = $result->fetch_assoc();
            $loginAttempt = $row['LoginAttempt'];
            $isLocked = false;
            $ReAttemptDate = "";

            // Check if the account is locked
            if ($loginAttempt != 3) {
                $loginAttempt++;
                $stmt = $conn->prepare("UPDATE accounts SET LoginAttempt = ? WHERE student_Number = ?");
                $stmt->bind_param("is", $loginAttempt, $studentNumber);
                $stmt->execute();
                $stmt->close();
                $isLocked = false;
                $SMTPMessage = "";

                if ($loginAttempt == 3) {
                    $message = "Final attempt left before your account gets locked.";
                } else {
                    $message = "You have " . (3 - $loginAttempt) . " attempts left before your account gets locked.";
                }
            } else {
                $ReAttemptDate = date('Y-m-d H:i:s', strtotime('+10 minutes'));

                $stmt = $conn->prepare("UPDATE accounts SET LoginStat = 'Locked', BanExpired = ? WHERE student_Number = ?");
                $stmt->bind_param("ss", $ReAttemptDate, $studentNumber);
                $stmt->execute();
                $stmt->close();
                $isLocked = true;

                $message = "Your account has been locked due to multiple failed login attempts. Please try again after 10 minutes.";

                $stmt = $conn->prepare("SELECT * FROM usercredentials WHERE student_Number = ?");
                $stmt->bind_param("s", $studentNumber);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                $email = $row['primary_email'];
                $fname = $row['First_Name'];
                $UUID = $row['UUID'];
                $stmt->close();

                // get reattempt year in $ReAttemptDate
                $Year = date('Y', strtotime($ReAttemptDate)); // 2021
                $Month = date('F', strtotime($ReAttemptDate)); // January
                $Day = date('d', strtotime($ReAttemptDate)); // 01
                $time = date('h:i', strtotime($ReAttemptDate)); // 12:00
                $AMPM = date('A', strtotime($ReAttemptDate)); // AM

                $random = rand(100000, 999999);
                $details = "User with student number " . $studentNumber . " has failed to login. Account is now temporarily locked.";
                $loginmessage = "Account Locked";

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
                    $mail->Subject = 'Security Alert';
                    $mail->Body = '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"/><meta name="viewport" content="width=device-width, initial-scale=1.0"/><title>Security Alert</title><style>body{font-family:Arial,sans-serif;line-height:1.6}.container{max-width:600px;margin:0 auto;padding:20px;border:1px solid #ddd;border-radius:10px}.header{background-color:#e7d96e;color:white;padding:5px 0;text-align:center;border-radius:10px 10px 0 0;display:flex;align-items:center;justify-content:center}.content{padding:10px 20px 20px 20px}.footer{margin-top:20px;text-align:center;font-size:0.9em;color:#777}table{width:100%}td{padding:10px}img{display:block;margin:0 auto}h2{margin:0;color:#fff}ul{list-style-type:none;padding:0}li{margin-bottom:10px}p{margin:0 0 10px}</style></head><body><div class="container"><div class="header"><table><tr><td><img src="https://i.imgur.com/Xd06F5f.jpeg" alt="Company Logo" style="display: block; border-radius: 50%; margin: 0 auto;" width="48"/></td><td><h2>Central Student Government</h2></td></tr></table></div><hr/><h3 style="text-align:center">Security alert</h3><div class="content"><p>Dear <b>' . $fname . '</b>,</p><p>Please be informed that your account has been locked due to </p><p>multiple failed login attempts. It will automatically unlock after </p><p><b>' . $Month . ' ' . $Day . ', ' . $Year . ' at ' . $time . ' ' . $AMPM . '</b>.</p><div style="text-align:end"><p>Thank you!</p><p><br>Best regards,<br>CSG Team</p></div></div><div class="footer"><p>&copy; ' . date('Y') . ' Central Student Government. All rights reserved.</p></div></div></body></html>';
                    $mail->AltBody = 'This is a system generated email. Please do not reply to this email.';

                    if ($mail->send()) {
                        $SMTPMessage = "Email sent successfully";
            
                        // Record the event to the system audit
                        $stmt = $conn->prepare("INSERT INTO systemaudit (userID, eventType, ip_address, details, status, SA_UID) VALUES (?, ?, ?, ?, 'Failed', ?)");
                        $stmt->bind_param("ssssi", $UUID, $loginmessage, $ipAddress, $details, $random);
                        $stmt->execute();
                        $stmt->close();

                    } else {
                        $SMTPMessage = "Email could not be sent. Mailer Error: " . $mail->ErrorInfo;
                    }

                } catch (Exception $e) {
                    $SMTPMessage = "Message could not be sent. Mailer Error: " . $mail->ErrorInfo;
                }
            }
            writeLog($logPath, "error", $UUID, "Account Locked", $ipAddress, "Failed");

            response(['status' => 'error', 'message' => $message, 'isLocked' => $isLocked, 'Date' => $ReAttemptDate, 'SMTPErr' => $SMTPMessage]);
        }

    } else {
        response(['status' => 'error', 'message' => 'No account found with the student number']);
    }
} catch (Exception $e) {
    writeLog($logPath, "ERROR", $temp_UUID, "Login", $ipAddress, "Failed");
    response(['status' => 'critical', 'message' => $e->getMessage()]);
}
