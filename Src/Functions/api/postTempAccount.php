<?php

session_start();
require_once "../../Database/Config.php";

header('Content-Type: application/json');
date_default_timezone_set('Asia/Manila');

require '../../../vendor/autoload.php';

use Fpdf\Fpdf;

class PDF extends Fpdf
{
    public function CustomHeader()
    {
        $this->Image('../../../Assets/Images/pdf-Resource/L_Logo.png', 10, 8, 40);
        $this->Image('../../../Assets/Images/pdf-Resource/R_Logo.png', 160, 8, 40);
        $this->SetFont('Arial', 'B', 15);
        $this->Cell(80);
        $this->Cell(30, 15, "CENTRAL STUDENT GOVERMENT", 0, 1, 'C');
        $this->SetFont('Arial', '', 12);
        $this->Cell(187, 0, "Account Credential", 0, 0, 'C');
        $this->Ln(10);
        $this->SetFont('Arial', 'B', 15);
        $this->Cell(0, 10, 'TEMPORARY ACCOUNT', 0, 1, 'C');
    }

    public function Content($email, $password)
    {
        $this->SetFont('Arial', 'B', 12);
        $this->Ln(5);
        $this->Cell(30, 10, 'Date: ', 0, 0);
        $this->Cell(50, 10, date('F d, Y'), 0, 1);
        $this->Cell(30, 10, 'Email: ', 0, 0);
        $this->SetFont('Arial', 'B', 16);
        $this->MultiCell(0, 10, $email, 0, 1);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(30, 10, 'Password: ', 0, 0);
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(50, 10, $password, 0, 1);
        $this->SetFont('Arial', 'B', 12);
        $this->Ln(10);
        $x = $this->GetX();
        $y = $this->GetY();
        $this->Line($x, $y, 200, $y);
    }
}

function response($data)
{
    echo json_encode($data);
    exit;
}

function uuidv4($conn)
{
    $data = random_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    $checkUUID = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));

    $stmt = $conn->prepare("SELECT * FROM usercredentials WHERE UUID = ?");
    $stmt->bind_param('s', $checkUUID);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    if ($result->num_rows > 0) {
        return uuidv4($conn);
    } else {
        return $checkUUID;
    }

    return $checkUUID;
}

function emailGen($conn)
{
    $email = substr(str_shuffle("abcdefghijklmnopqrstuvwxyz"), 0, 5) . substr(str_shuffle("abcdefghijklmnopqrstuvwxyz"), 0, 5) . "@cvsu.temp.com";

    $stmt = $conn->prepare("SELECT * FROM usercredentials WHERE primary_email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    if ($result->num_rows > 0) {
        return emailGen($conn);
    } else {
        return $email;
    }

    return $email;
}

function passwordGen()
{
    $password = substr(str_shuffle("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+"), 0, 8);
    return $password;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        response(['stat' => 'error', 'message' => 'Invalid request method']);
    }

    $accounts = $_POST['accounts'];
    $action = $_POST['action'];

    $saveEmail = [];
    $savePassword = [];
    $pdfPath = [];

    if ($action == 'multi-create') {
        foreach ($accounts as $account) {
            $role = $account['role'];
            $position = $account['pos'];

            try {
                $conn->begin_transaction();

                if ($role !== null) {
                    $accountStat = "pending";
                    $sessionID = bin2hex(random_bytes(16));
                    $primary_email = emailGen($conn);
                    $beforeHash = passwordGen();
                    $password = password_hash($beforeHash, PASSWORD_DEFAULT);
                    $UUID = uuidv4($conn);
                    $SN = rand(100000000, 999999999);

                    // Insert into usercredentials
                    $stmt = $conn->prepare("INSERT INTO usercredentials (UUID, accountStat, sessionID, primary_email, password, First_Name, Last_Name, student_Number) VALUES (?, ?, ?, ?, ?, 'None', 'None', ?)");
                    if (!$stmt) {
                        throw new Exception("Error preparing statement: " . $conn->error);
                    }
                    $stmt->bind_param('sssssi', $UUID, $accountStat, $sessionID, $primary_email, $password, $SN);
                    if (!$stmt->execute()) {
                        throw new Exception("Error executing usercredentials insert: " . $stmt->error);
                    }
                    $stmt->close();

                    // Insert into userpositions based on role
                    if ($role > 1 && $position !== null) {
                        $org_code = $role == 2 ? 10001 : null;
                        $stmt = $conn->prepare("INSERT INTO userpositions (UUID, role, org_code, org_position) VALUES (?, ?, ?, ?)");
                        if (!$stmt) {
                            throw new Exception("Error preparing userpositions statement: " . $conn->error);
                        }
                        $stmt->bind_param('siii', $UUID, $role, $org_code, $position);
                    } else {
                        $stmt = $conn->prepare("INSERT INTO userpositions (UUID, role, org_code, org_position) VALUES (?, 1, NULL, NULL)");
                        if (!$stmt) {
                            throw new Exception("Error preparing userpositions default statement: " . $conn->error);
                        }
                        $stmt->bind_param('s', $UUID);
                    }
                    if (!$stmt->execute()) {
                        throw new Exception("Error executing userpositions insert: " . $stmt->error);
                    }
                    $stmt->close();

                    // Insert into systemaudit
                    $SA_UID = rand(100000, 999999);
                    $userID = $UUID;
                    $evenType = "Account Created";
                    $timestamp = date('Y-m-d H:i:s');
                    $ipData = file_get_contents("https://api.ipify.org?format=json");
                    $ipData = json_decode($ipData, true);
                    $IPADDRESS = $ipData['ip'];
                    $details = $role > 1 && $position !== null
                        ? "New User has been created by Admin( " . $_SESSION['FullName'] . " )"
                        : "Admin( " . $_SESSION['FullName'] . " ) has created a new Admin User";
                    $status = "success";

                    $stmt = $conn->prepare("INSERT INTO systemaudit (SA_UID, userID, eventType, timestamp, ip_address, details, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    if (!$stmt) {
                        throw new Exception("Error preparing systemaudit statement: " . $conn->error);
                    }
                    $stmt->bind_param('issssss', $SA_UID, $userID, $evenType, $timestamp, $IPADDRESS, $details, $status);
                    if (!$stmt->execute()) {
                        throw new Exception("Error executing systemaudit insert: " . $stmt->error);
                    }
                    $stmt->close();

                    // Commit transaction
                    $conn->commit();

                    // Save email and password for reference
                    array_push($saveEmail, $primary_email);
                    array_push($savePassword, $beforeHash);
                }
            } catch (Exception $e) {
                $conn->rollback();  // Roll back on error
                echo "Transaction failed: " . $e->getMessage();
            }
        }

        for ($i = 0; $i < count($saveEmail); $i++) {
            $pdf = new PDF();
            $pdf->AddPage();
            $pdf->CustomHeader();
            $pdf->Content($saveEmail[$i], $savePassword[$i]);
            $uniqueId = uniqid();
            $pdfFileName = 'TemporaryAccount_' . $uniqueId . '.pdf';
            $pdf->Output('F', '../../TempPDF/' . $pdfFileName);
            array_push($pdfPath, $pdfFileName);
        }

        $zip = new ZipArchive();
        $date = date('Y-m-d');
        $random = uniqid();
        if ($zip->open('../../TempPDF/TemporaryAccounts_' . $date . '_' . $random . '.zip', ZipArchive::CREATE) === true) {
            foreach ($pdfPath as $pdf) {
                $zip->addFile('../../TempPDF/' . $pdf, $pdf);
            }
            $zip->close();
        } else {
            response(['stat' => 'error', 'message' => 'Failed to create zip file']);
        }

        foreach ($pdfPath as $pdf) {
            unlink('../../TempPDF/' . $pdf);
        }

        response(['stat' => 'success', 'message' => 'Accounts created successfully', 'ZipPath' => 'TemporaryAccounts_' . $date . '_' . $random . '.zip']);
    } elseif ($action == 'delete-zip') {
        $zipname = $_POST['zipname'];

        if (!$zipname) {
            response(['stat' => 'error', 'message' => 'Invalid zip file']);
        }

        if (file_exists('../../TempPDF/' . $zipname)) {
            unlink('../../TempPDF/' . $zipname);
            response(['stat' => 'success', 'message' => 'Zip file deleted successfully']);
        } else {
            response(['stat' => 'error', 'message' => 'Zip file not found']);
        }
    } elseif ($action == 'create') {
        $uuid = $_POST['uuid'];
        $role = $_POST['role'];
        $position = $_POST['pos'];
        $email = $_POST['email'];
        $password = $_POST['password'];

        $accountStat = "pending";
        $sessionID = bin2hex(random_bytes(16));
        $beforeHash = $password;
        $password = password_hash($beforeHash, PASSWORD_DEFAULT);
        $SN = rand(100000000, 999999999);

        // Insert into usercredentials
        $stmt = $conn->prepare("INSERT INTO usercredentials (UUID, accountStat, sessionID, primary_email, password, First_Name, Last_Name, student_Number) VALUES (?, ?, ?, ?, ?, 'None', 'None', ?)");
        if (!$stmt) {
            throw new Exception("Error preparing statement: " . $conn->error);
        }
        $stmt->bind_param('sssssi', $uuid, $accountStat, $sessionID, $email, $password, $SN);
        if (!$stmt->execute()) {
            throw new Exception("Error executing usercredentials insert: " . $stmt->error);
        }
        $stmt->close();

        // Insert into userpositions based on role
        if ($role > 1 && $position !== null) {
            $org_code = $role == 2 ? 10001 : null;
            $stmt = $conn->prepare("INSERT INTO userpositions (UUID, role, org_code, org_position) VALUES (?, ?, ?, ?)");
            if (!$stmt) {
                throw new Exception("Error preparing userpositions statement: " . $conn->error);
            }
            $stmt->bind_param('siii', $uuid, $role, $org_code, $position);
        } else {
            $stmt = $conn->prepare("INSERT INTO userpositions (UUID, role, org_code, org_position) VALUES (?, 1, NULL, NULL)");
            if (!$stmt) {
                throw new Exception("Error preparing userpositions default statement: " . $conn->error);
            }
            $stmt->bind_param('s', $uuid);
        }

        if (!$stmt->execute()) {
            throw new Exception("Error executing userpositions insert: " . $stmt->error);
        }

        $stmt->close();

        // Insert into systemaudit
        $SA_UID = rand(100000, 999999);
        $userID = $uuid;
        $evenType = "Account Created";
        $timestamp = date('Y-m-d H:i:s');
        $ipData = file_get_contents("https://api.ipify.org?format=json");
        $ipData = json_decode($ipData, true);
        $IPADDRESS = $ipData['ip'];
        $details = $role > 1 && $position !== null
            ? "New User has been created by Admin( " . $_SESSION['FullName'] . " )"
            : "Admin( " . $_SESSION['FullName'] . " ) has created a new Admin User";
        $status = "success";

        $stmt = $conn->prepare("INSERT INTO systemaudit (SA_UID, userID, eventType, timestamp, ip_address, details, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Error preparing systemaudit statement: " . $conn->error);
        }
        $stmt->bind_param('issssss', $SA_UID, $userID, $evenType, $timestamp, $IPADDRESS, $details, $status);
        if (!$stmt->execute()) {
            throw new Exception("Error executing systemaudit insert: " . $stmt->error);
        }

        $stmt->close();
        response(['stat' => 'success', 'message' => 'Account created successfully', 'pdfPath' => $pdfFileName]);
    } else {
        response(['stat' => 'error', 'message' => 'Invalid action']);
    }

} catch (Exception $e) {
    response(['stat' => 'error', 'message' => 'Error: ' . $e->getMessage() . ' at line ' . $e->getLine()]);
}
