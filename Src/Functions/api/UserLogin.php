<?php
session_start();
function response($data)
{
    echo json_encode($data);
    exit;
}

require_once  "../../Database/Config.php";
require_once './FetchuserCredentials.php';

if (!file_exists('../env/HiddenKeys.php')) {
    if (!mkdir('../env', 0750, true)) {
        response(['status' => 'error', 'message' => 'Failed to create configuration directory']);
    }
    if (($file = fopen('../env/HiddenKeys.php', 'w')) === false) {
        response(['status' => 'error', 'message' => 'Failed to create configuration file']);
    }
    $config = "<?php\n// Database Connection\n\$DB_USERNAME = \"\";\n\$DB_PASSWORD = \"\";\n";
    $config .= "\$DB_GENPASS = \"\";\n\$DB_DATABASE = \"\";\n\n// SMTP Configuration\n";
    $config .= "\$SMTP_HOST = '';\n\$SMTP_PORT = 587;\n\$SMTP_USER = '';\n\$SMTP_PASS = '';\n";
    $config .= "\$SMTP_SECURE = 'tls';\n\n// Hostinger Database Connection\n";
    $config .= "\$H_DB_NAME = \"\";\n\$H_DB_USERNAME = \"\";\n\$H_DB_PASSWORD = \"\";\n?>";
    if (fwrite($file, $config) === false) {
        fclose($file);
        response(['status' => 'error', 'message' => 'Failed to write configuration']);
    }
    fclose($file);
    chmod('../env/HiddenKeys.php', 0640);
    response(['status' => 'fatal', 'message' => 'You have Encountered an E-K404 Error Code. Please Contact the Administrator']);
} else {
    require_once '../env/HiddenKeys.php';
}
date_default_timezone_set('Asia/Manila');
require_once '../../Debug/GenLog.php';
$logname = date('Y-m');
$logPath = "../../Debug/Users/" . $logname . ".log";

require '../../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');


$temp_UUID = "";

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        response(['error' => 'Invalid request method']);
    }

    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data) || !isset($data['password'])) {
        response(['status' => 'error', 'message' => 'Invalid request data']);
    }

    $studentNumber = $data['studentnum'] ?? '';
    $email = $data['email'] ?? '';
    $password = $data['password'];
    $device = $data['Device'];
    $ipAddress = $data['ipAddress'];
    if (isset($data['stats']) && $data['stats'] == 'autologin') {
        $autoLogin = true;
    } else {
        $autoLogin = false;
    }

    if ($studentNumber === '' AND !empty($email)) {
        $stmt = $conn->prepare("SELECT student_Number FROM usercredentials WHERE primary_email = ? ");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $studentNumber = $row['student_Number'];
        } else {
            response(['status' => 'error', 'message' => 'No account found with the email']);
        }
    }

    $stmt = $conn->prepare("SELECT student_Number, password, LoginStat, BanExpired, UUID FROM accounts WHERE student_Number = ?");
    $stmt->bind_param("i", $studentNumber);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    $CurrentDate = date('Y-m-d H:i:s');

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        $temp_UUID = $row['UUID'];

        if ($row['LoginStat'] === 'Locked') {
            $row['BanExpired'] = date('Y-m-d H:i:s', strtotime($row['BanExpired']));

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

        if (password_verify($password, $row['password'])) {
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
                        $msgcode = 1;
                        $loginmessage = "Your account is being accessed outside your registered Network. (Current IP: " . $ipAddress . " | Registered IP: " . $IPAddress . ")";
                    } elseif ($isLogin == 1 && $access_date != $date) {
                        $msgcode = 2;
                        $loginmessage = "Your previous session was not logged out correctly. Please note to logout properly next time to avoid any issues.";
                    } else {
                        $msgcode = 3;
                        $loginmessage = "Login successful.";
                    }
                } else {
                    if ($isLogin == 1 && $access_date != $date) {
                        $msgcode = 2;
                        $loginmessage = "Your previous session was not logged out correctly. Please note to logout properly next time to avoid any issues.";
                    } else {
                        $msgcode = 3;
                        $loginmessage = "Login successful.";
                    }
                }
            }

            $random = bin2hex(random_bytes(16));

            $stmt = $conn->prepare("UPDATE accounts SET access_date = ?, LoginAttempt = 0, isLogin = 1, BanExpired = NULL, ipAddress = ? WHERE student_Number = ?");
            $stmt->bind_param("sss", $CurrentDate, $ipAddress, $studentNumber);
            $stmt->execute();
            $stmt->close();

            $stmt = $conn->prepare("UPDATE usercredentials SET isLogin = 1, sessionID = ? WHERE student_Number = ?");
            $stmt->bind_param("ss", $random, $studentNumber);
            $stmt->execute();
            $stmt->close();
            
            $account = fetchUserCredentials($conn, $studentNumber, $device);

            if ($account === null) {
                response(['status' => 'error', 'message' => 'An error occurred while fetching your account']);
            } else {
                $stmt = $conn->prepare("UPDATE accounts SET LoginAttempt = 0 WHERE student_Number = ?");
                $stmt->bind_param("s", $studentNumber);
                $stmt->execute();
                $stmt->close();

                switch ($msgcode) {
                    case 1:
                        writeLog($logPath, "warn", $temp_UUID, "New login detected", $ipAddress, "New IP Address");
                        break;
                    case 2:
                        writeLog($logPath, "warn", $temp_UUID, "Session Exsists", $ipAddress, "Session not logged out");
                        break;
                    case 3:
                        writeLog($logPath, "info", $account['UUID'], "Login", $ipAddress, "Success");
                        break;
                    default:
                        writeLog($logPath, "info", $temp_UUID, "Login", $ipAddress, "Success");
                        break;
                }

                // get current decvice user is using
                $device = $_SERVER['HTTP_USER_AGENT'];
                $mobile = ['Android', 'iPhone', 'iPad', 'Mobile', 'Opera Mini', 'Windows Phone', 'BlackBerry', 'webOS', 'iPod'];
                $isMobile = false;

                foreach ($mobile as $m) {
                    if (strpos($device, $m) !== false) {
                        $isMobile = true;
                        break;
                    }
                }

                response([
                    'status' => 'success',
                    'message' => $loginmessage,
                    'data' => $account,
                    'isMobile' => $isMobile
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

            if ($loginAttempt < 3) {
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

                $stmt = $conn->prepare("UPDATE accounts SET LoginStat = 'Locked', BanExpired = ? WHERE UUID = ?");
                $stmt->bind_param("ss", $ReAttemptDate, $temp_UUID);
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

                $Year = date('Y', strtotime($ReAttemptDate));
                $Month = date('F', strtotime($ReAttemptDate));
                $Day = date('d', strtotime($ReAttemptDate));
                $time = date('h:i', strtotime($ReAttemptDate));
                $AMPM = date('A', strtotime($ReAttemptDate));

                $random = rand(100000, 999999);
                $details = "User with student number " . $studentNumber . " has failed to login. Account is now temporarily locked.";
                $loginmessage = "Account Locked";

                try {
                    //Server settings
                    $mail = new PHPMailer(true);
                    $mail->isSMTP();
                    $mail->Host = $SMTP_HOST;
                    $mail->SMTPAuth = true;
                    $mail->Username = $SMTP_USER;
                    $mail->Password = $SMTP_PASS;
                    $mail->SMTPSecure = $SMTP_SECURE;
                    $mail->Port = $SMTP_PORT;

                    $mail->setFrom($SMTP_USER, 'CSG - System');
                    $mail->addAddress($email, $fname);

                    $mail->isHTML(true);
                    $mail->Subject = 'Security Alert';
                    $mail->Body = '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"/><meta name="viewport" content="width=device-width, initial-scale=1.0"/><title>Security Alert</title><style>body{font-family:Segoe UI,Roboto,sans-serif;line-height:1.6;}.container{max-width:600px;margin:0 auto;padding:20px;}.header{color:white;padding:5px 0;text-align:center;border-radius:10px 10px 0 0;display:flex;align-items:center;justify-content:center;}.header h2{color:black;}.content{padding:10px 20px 20px 20px;margin-top:20px;}.footer{margin-top:20px;text-align:center;font-size:0.9em;color:#777;}table{width:100%;}td{padding:10px;}img{display:block;margin:0 auto;}h2{margin:0;color:#fff;}ul{list-style-type:none;padding:0;}li{margin-bottom:10px;}p{margin:0 0 10px;}</style></head><body><div class="container"><div class="header"><table><tr><td><img src="https://i.imgur.com/Xd06F5f.jpeg" alt="Company Logo" style="display:block;border-radius:50%;margin:0 auto" width="48"/></td><td><h2>Central Student Government</h2></td></tr></table></div><hr/><h3 style="text-align:start;text-transform:uppercase;color:#333;margin-bottom:-10px;">Security alert</h3><small style="color:#777;">' . date('F j, Y') . '</small><div class="content"><p>Dear <b>' . $fname . '</b>,</p><p>Please be informed that your account has been locked due to multiple failed login attempts. It will automatically unlock after</p><p style="text-align:center;margin-top:10px"><b>' . $Month . ' ' . $Day . ', ' . $Year . ' at ' . $time . ' ' . $AMPM . '</b>.</p><div style="text-align:end"><p>Thank you!</p><p><br/>Best regards,<br/>Central Student Government</p></div></div><div class="footer"><p>&copy; ' . date('Y') . ' Central Student Government. All rights reserved.</p></div><div><p style="text-align:center;color:#777;font-size:0.8em;margin-top:20px">This email was sent to <b>' . $email . '</b> because you are a member of the CvSU Organization. If you believe this email was sent by mistake, please ignore it.</p></div></div></body></html>';
                    $mail->AltBody = 'This is a system generated email. Please do not reply to this email.';

                    if ($mail->send()) {
                        $SMTPMessage = "Email sent successfully";
                        $stmt = $conn->prepare("INSERT INTO systemaudit (userID, eventType, ip_address, details, status, SA_UID) VALUES (?, ?, ?, ?, 'Failed', ?)");
                        $stmt->bind_param("ssssi", $UUID, $loginmessage, $ipAddress, $details, $random);
                        $stmt->execute();
                        $stmt->close();

                        writeLog($logPath, "error", $temp_UUID, "Account Locked", $ipAddress, "Failed");

                    } else {
                        $SMTPMessage = "Email could not be sent. Mailer Error: " . $mail->ErrorInfo;
                    }

                } catch (Exception $e) {
                    $SMTPMessage = "Message could not be sent. Mailer Error: " . $mail->ErrorInfo;
                }
            }

            response(['status' => 'error', 'message' => $message, 'isLocked' => $isLocked, 'Date' => $ReAttemptDate, 'SMTPErr' => $SMTPMessage]);
        }

    } else {
        response(['status' => 'error', 'message' => 'No account found with the student number']);
    }
} catch (Exception $e) {
    writeLog($logPath, "ERROR", $temp_UUID, "Login", $ipAddress, "Failed");
    response(['status' => 'critical', 'message' => $e->getMessage()]);
}
