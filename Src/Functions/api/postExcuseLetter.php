<?php

session_start();

require_once  "../../Database/Config.php";
require '../../../vendor/autoload.php';

header('Content-Type: application/json');
date_default_timezone_set('Asia/Manila');

function response($data)
{
    echo json_encode($data);
    exit;
}

function filterAndRemove($input, $patterns)
{
    $filteredOutput = $input;
    foreach ($patterns as $pattern) {
        $filteredOutput = preg_replace($pattern, '', $filteredOutput);
    }
    $filteredOutput = preg_replace('/\n+/', "\n", trim($filteredOutput));
    return $filteredOutput;
}

function updateCssProperty($input, $search, $replace)
{
    return str_replace($search, $replace, $input);
}

$patterns = [
    '/background-color: var\(--bs-card-bg\); color: var\(--bs-body-color\);/',
    '/background-color: var\(--bs-card-bg\);/',
    '/color: rgb\(222, 226, 230\);/'
];

$search = 'font-size: 14px;';
$replace = 'font-size: 11px;';


class PDF extends TCPDF
{
    public function CustomHeader($conn, $docType, $OrgCode, $isDocHeaderHidden = false)
    {

        if ($OrgCode === '') {
            $stmt = $conn->prepare("SELECT * FROM orgdocumetheader WHERE org_code = ?");
            $stmt->bind_param("s", $_SESSION['org_Code']);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $stmt->close();
        } else {
            $stmt = $conn->prepare("SELECT * FROM orgdocumetheader WHERE org_code = ?");
            $stmt->bind_param("s", $OrgCode);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $stmt->close();
        }


        if (!$result) {
            response(['status' => 'error', 'message' => 'Document header not found']);
        }

        $LeftLogo = $result['left_Image'] ? substr($result['left_Image'], 35) : '';
        $RightLogo = $result['right_Image'] ? substr($result['right_Image'], 35) : '';
        $FirstLine = $result['firstLine'];
        $SecondLine = strtoupper($result['secondLine']);
        $ThirdLine = $result['thirdLine'];
        $FourthLine = $result['fourthLine'];
        $FifthLine = strtoupper($result['fifthLine']);
        $SixthLine = $result['sixthLine'];

        $stmt = $conn->prepare("SELECT * FROM sysorganizations WHERE org_code = ?");
        $stmt->bind_param("s", $OrgCode);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();


        if ($docType === 'Constituents-ExcuseLetter') {
            $ActivityTitle = 'Constituents';
        } else {
            $ActivityTitle = $result['org_short_name'] . ' Officers';
        }

        $this->SetFont('helvetica', '', 10);
        $this->Cell(0, 5, 'Excuse Letter | for ' . $ActivityTitle . ' | Page ' . $this->getAliasNumPage() . ' of ' . $this->getAliasNbPages(), 0, 1, 'R');
        $this->Ln(5);

        if (!$isDocHeaderHidden) {
            $this->Image('../../../Assets/Images/pdf-Resource/' . $LeftLogo, 45, 20, 40);
            $this->Image('../../../Assets/Images/pdf-Resource/' . $RightLogo, 155, 20, 40);

            $this->setX(38.1);
            $this->SetFont('helvetica', '', 10);
            $this->Cell(0, 5, $FirstLine, 0, 1, 'C');

            $this->setX(38.1);
            $this->SetFont('helvetica', 'B', 12);
            $this->Cell(0, 6, $SecondLine, 0, 1, 'C');
            $this->SetFont('helvetica', 'B', 10);

            $this->setX(38.1);
            $this->Cell(0, 5, $ThirdLine, 0, 1, 'C');

            $this->setX(38.1);
            $this->Cell(0, 5, $FourthLine, 0, 1, 'C');

            $this->SetFont('helvetica', 'B', 12);
            $this->setX(38.1);
            $this->Cell(0, 5, $FifthLine, 0, 1, 'C');
            $this->setX(38.1);
            $this->SetFont('helvetica', 'B', 11);
            $this->Cell(0, 5, $SixthLine, 0, 1, 'C');
        }
    }

    public function Content($conn, $LetterTo, $position, $Dear, $Participants, $DateStart, $StartTime, $DateEnd, $EndTime, $eventReason, $reason, $orgCode, $docType, $Organizer)
    {

        $this->Ln(15);
        $this->setX(38.1);
        $this->SetFont('helvetica', '', 11);
        $this->Cell(0, 5, 'Date: ' . date('F d, Y'), 0, 1);
        $this->Ln(8);
        $this->setX(38.1);
        $this->SetFont('helvetica', 'B', 11);
        $this->Cell(0, 5, strtoupper($LetterTo), 0, 1);
        $this->setX(38.1);
        $this->SetFont('helvetica', '', 11);
        $this->Cell(0, 5, $position, 0, 1);
        $this->setX(38.1);
        $this->Cell(0, 5, 'This Campus', 0, 1);

        $this->Ln(8);
        $this->setX(38.1);
        $this->SetFont('helvetica', 'B', 11);
        $this->Cell(0, 5, 'Dear ' . $Dear . ',', 0, 1);

        if ($docType === 'Constituents-ExcuseLetter') {
            $this->Ln(8);
            $this->setX(38.1);
            $this->SetFont('helvetica', '', 11);
            $this->Cell(0, 5, 'Greetings!', 0, 1);

            $this->Ln(8);
            $this->setX(38.1);
            $this->SetFont('helvetica', '', 11);
            $letterbody = "The undersigned would like to excuse " . ($Participants ? ' ' . $Participants : '') . " from your class from " . date('F d, Y', strtotime($DateStart)) . " to " . date('F d, Y', strtotime($DateEnd)) . " from " . date('h:i A', strtotime($StartTime)) . " to " . date('h:i A', strtotime($EndTime)) . " due to their participation in " . $eventReason . ".";
            $this->writeHTMLCell(156, 0, '', '', $letterbody, 0, 1, 0, true, 'J', true);

            $this->Ln(8);
            $this->setX(38.1);
            $this->SetFont('helvetica', '', 11);
            $this->multiCell(0, 5, "Rest assured that the said students will take full responsibility to make up for any requirements he/she will be missed on the said date.", 0, 'L');

            $this->Ln(8);
            $this->setX(38.1);
            $this->SetFont('helvetica', '', 11);
            $this->writeHTMLCell(156, 0, '', '', $reason, 0, 1, 0, true, 'J', true);
        } else {
            $stmt = $conn->prepare("SELECT * FROM sysorganizations WHERE org_code = ?");
            $stmt->bind_param("s", $orgCode);
            $stmt->execute();
            $orgResult = $stmt->get_result();
            $orgRow = $orgResult->fetch_assoc();
            $stmt->close();

            $orgName = $orgRow['org_name'];

            $this->Ln(8);
            $this->setX(38.1);
            $this->SetFont('helvetica', '', 11);
            $letterbody = "The undersigned would like to request that the following officers of the" . ($orgName ? ' ' . $orgName : '') . " be excused from your class on  " . (date('F d, Y', strtotime($DateStart)) === date('F d, Y', strtotime($DateEnd)) ? date('F d, Y', strtotime($DateStart)) : date('F d, Y', strtotime($DateStart)) . ' to ' . date('F d, Y', strtotime($DateEnd))) . " from " . date('h:i A', strtotime($StartTime)) . " to " . date('h:i A', strtotime($EndTime)) . " to attend the " . $eventReason . ". Organized by " . $Organizer . ".";
            $this->writeHTMLCell(156, 0, '', '', $letterbody, 0, 1, 0, true, 'J', true);

            $this->Ln(8);
            $this->setX(38.1);
            $this->SetFont('helvetica', '', 11);
            $this->Cell(0, 5, 'Participants:', 0, 1);
            $this->Ln(5);
            $this->setX(45);
            $this->SetFont('helvetica', '', 11);
            $this->writeHTMLCell(145, 0, '', '', $Participants, 0, 1, 0, true, 'J', true);

            $this->Ln(8);
            $this->setX(38.1);
            $this->SetFont('helvetica', '', 11);
            $this->multiCell(0, 5, "Rest assured that the said students will take full responsibility to make up for any requirements he/she will be missed on the said date.", 0, 'L');

            $this->Ln(8);
            $this->setX(38.1);
            $this->SetFont('helvetica', '', 11);
            $this->writeHTMLCell(156, 0, '', '', $reason, 0, 1, 0, true, 'J', true);
        }

        $this->Ln(8);
        $this->setX(38.1);
        $this->SetFont('helvetica', '', 11);
        $this->Cell(0, 5, 'We are hoping for your continued support.', 0, 1);

        $this->Ln(8);
        $this->setX(38.1);
        $this->SetFont('helvetica', '', 11);
        $this->Cell(0, 5, 'Respectfully yours,', 0, 1);

        $stmt = $conn->prepare("SELECT UUID FROM userpositions WHERE org_code = ? AND org_position = 1 AND isTermComplete = 0 AND status = 'active'");
        $stmt->bind_param("i", $orgCode);
        $stmt->execute();
        $positionResult = $stmt->get_result();
        $stmt->close();

        $positionRow = $positionResult->fetch_assoc();
        $stmt = $conn->prepare("SELECT fullName FROM usercredentials WHERE UUID = ?");
        $stmt->bind_param("s", $positionRow['UUID']);
        $stmt->execute();
        $userResult = $stmt->get_result();
        $userRow = $userResult->fetch_assoc();
        $stmt->close();

        $stmt = $conn->prepare("SELECT org_short_name FROM sysorganizations WHERE org_code = ?");
        $stmt->bind_param("s", $orgCode);
        $stmt->execute();
        $orgResult = $stmt->get_result();
        $orgRow = $orgResult->fetch_assoc();
        $stmt->close();

        $this->Ln(8);
        $this->setX(38.1);
        $this->SetFont('helvetica', 'B', 11);
        $this->Cell(0, 5, strtoupper($userRow['fullName']), 0, 1);
        $this->setX(38.1);
        $this->SetFont('helvetica', '', 11);
        $this->Cell(0, 5, $orgRow['org_short_name'] . ' President', 0, 1);

        $this->Ln(8);
    }

    public function SignatureContent($Recommending)
    {
        $this->Ln(15);
        $this->setX(38.1);
        $this->SetFont('helvetica', '', 11);
        $this->Cell(0, 5, 'Recommending Approval:', 0, 1);
        $this->Ln(10);
        $this->setX(38.1);
        $this->SetFont('helvetica', '', 11);
        $this->writeHTMLCell(156, 0, '', '', $Recommending, 0, 1, 0, true, 'J', true);
    }
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        response(['status' => 'error', 'message' => 'Invalid request method']);
    }

    $LetterTo = $_POST['LetterTo'] ?? '';
    $position = $_POST['position'] ?? '';
    $Organizer = $_POST['Organizer'] ?? '';
    $dear = $_POST['dear'] ?? '';
    $docType = $_POST['docType'] ?? '';
    $Participants = $_POST['Participants'] ?? '';
    $DateStart = $_POST['DateStart'] ?? '';
    $StartTime = $_POST['StartTime'] ?? '';
    $DateEnd = $_POST['DateEnd'] ?? '';
    $EndTime = $_POST['EndTime'] ?? '';
    $eventReason = $_POST['eventReason'] ?? '';
    $reason = $_POST['reason'] ?? '';
    $Recommending = $_POST['Recommending'] ?? '';
    $ID = $_POST['ID'] ?? '';
    $OrgCode = $_POST['OrgCode'] ?? '';
    $Created_By = $_POST['Created_By'] ?? '';
    $taskID = $_POST['taskID'] ?? '';
    $isFromTask = $_POST['isFromTask'] ?? 0;
    
    $Honorifics = $_POST['HONORIFICS'] ?? '';
    $LetterToFN = $_POST['FIRSTNAME'] ?? '';
    $LetterToLN = $_POST['LASTNAME'] ?? '';

    $reason = filterAndRemove($reason, $patterns);
    $reason = updateCssProperty($reason, $search, $replace);

    $Recommending = filterAndRemove($Recommending, $patterns);
    $Recommending = updateCssProperty($Recommending, $search, $replace);
    
    if (!$docType) {
        response(['status' => 'error', 'message' => 'Document type is required']);
    }

    $pdf = new PDF('P', 'mm', 'Letter', true, 'UTF-8', false);
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor($ID !== '' ? $Created_By : $_SESSION['FullName']);
    $pdf->SetTitle('Excuse Letter');
    $pdf->SetSubject('Excuse Letter');
    $pdf->SetKeywords('Excuse Letter, PDF, Document, ' . $docType);
    $pdf->SetPrintHeader(false);
    $pdf->AddPage();
    $pdf->CustomHeader($conn, $docType, $OrgCode);
    $pdf->Content($conn, $LetterTo, $position, $dear, $Participants, $DateStart, $StartTime, $DateEnd, $EndTime, $eventReason, $reason, $OrgCode, $docType, $Organizer);
    $pdf->SignatureContent($Recommending);


    if ($ID !== '') {
        $stmt = $conn->prepare("SELECT file_path FROM excuseletterdocuments WHERE ID = ?");
        $stmt->bind_param("i", $ID);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        $row = $result->fetch_assoc();

        $filePath = realpath(dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . $row['file_path']);
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        $successmessage = 'Excuse letter successfully updated';
    }

    $name = "ExcuseLetter_" . date('Y-m-d_H-i-s') . "_" . (!empty($OrgCode) ? $OrgCode : $_SESSION['org_Code']) . "_" . rand(1000, 9999) . ".pdf";
    $file_path = "DocumentsStorage/" . (!empty($OrgCode) ? $OrgCode : $_SESSION['org_Code']) . "/" . $name;
    $storageDir = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'DocumentsStorage' . DIRECTORY_SEPARATOR . (!empty($OrgCode) ? $OrgCode : $_SESSION['org_Code']);
    if (!file_exists($storageDir)) {
        mkdir($storageDir, 0777, true);
    }
    $savePath = $storageDir . DIRECTORY_SEPARATOR . $name;

    $pdf->Output($savePath, 'F');

    $fileSize = filesize($savePath);
    $currentDate = date('Y-m-d H:i:s');
    $UUID = $_SESSION['UUID'];

    if ($docType === 'Constituents-ExcuseLetter') {
        $docType = 0;
    } else {
        $docType = 1;
    }

    if ($ID !== '') {
        $stmt = $conn->prepare("UPDATE excuseletterdocuments SET file_Size = ?, file_path = ?, taskID = ?, isFromTask = ?, participants = ?, dateStart = ?, timeStart = ?, dateEnd = ?, timeEnd = ?, Event = ?, Reason = ?, RecommSig = ?, letterTo = ?, Postition = ?, Dear = ?, excuseLetterType = ?, DateCreated = ?, organizer = ? WHERE ID = ?");
        $stmt->bind_param("isssssssssssssssssi", $fileSize, $file_path, $taskID, $isFromTask, $Participants, $DateStart, $StartTime, $DateEnd, $EndTime, $eventReason, $reason, $Recommending, $LetterTo, $position, $dear, $docType, $currentDate, $Organizer, $ID);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("UPDATE excuseletterdocuments SET honor = ?, fname = ?, lname = ? WHERE ID = ?");
        $stmt->bind_param("sssi", $Honorifics, $LetterToFN, $LetterToLN, $ID);
        $stmt->execute();
        $stmt->close();

    } else {
        if ($taskID !== '') {
            $stmt = $conn->prepare("UPDATE taskdocuments SET tastStat = 'Completed' WHERE taskID = ?");
            $stmt->bind_param("s", $taskID);
            $stmt->execute();
            $stmt->close();
        }

        $stmt = $conn->prepare("INSERT INTO excuseletterdocuments (UUID, org_code, file_Size, file_path, taskID, isFromTask, participants, dateStart, timeStart, dateEnd, timeEnd, Event, Reason, RecommSig, letterTo, Postition, Dear, excuseLetterType, DateCreated) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssissssssssssssssss", $UUID, $OrgCode, $fileSize, $file_path, $taskID, $isFromTask, $Participants, $DateStart, $StartTime, $DateEnd, $EndTime, $eventReason, $reason, $Recommending, $LetterTo, $position, $dear, $docType, $currentDate);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("SELECT ID FROM excuseletterdocuments WHERE UUID = ? AND org_code = ? AND file_path = ?");
        $stmt->bind_param("sss", $UUID, $OrgCode, $file_path);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        $row = $result->fetch_assoc();
        $ID = $row['ID'];

        $stmt = $conn->prepare("UPDATE excuseletterdocuments SET honor = ?, fname = ?, lname = ?, organizer = ? WHERE ID = ?");
        $stmt->bind_param("ssssi", $Honorifics, $LetterToFN, $LetterToLN, $Organizer, $ID);
        $stmt->execute();
        $stmt->close();
    }

    response(['status' => 'success', 'message' => ($successmessage ?? 'Excuse letter successfully created'), 'file_path' => $file_path]);

} catch (\Throwable $th) {
    response(['status' => 'error', 'message' => $th->getMessage() . ' at line ' . $th->getLine()]);
}
