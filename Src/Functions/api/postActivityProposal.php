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


class PDF extends TCPDF
{
    public function CustomHeader($conn, $ActivityTitle, $OrgCode, $isDocHeaderHidden = false)
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

        $this->SetFont('helvetica', '', 10);
        $this->Cell(0, 5, 'Cover Letter and Activity Proposal | ' . $ActivityTitle . ' | Page ' . $this->getAliasNumPage() . ' of ' . $this->getAliasNbPages(), 0, 1, 'R');
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

    public function Content($conn, $Admin, $Letter, $LetterTo)
    {
        $AdminName = strtoupper($Admin);
        $LetterBody = $Letter;
        $LetterTwo = $LetterTo;

        $this->Ln(15);
        $this->setX(38.1);
        $this->SetFont('helvetica', '', 11);
        $this->Cell(0, 5, 'Date: ' . date('F d, Y'), 0, 1);
        $this->Ln(8);
        $this->setX(38.1);
        $this->SetFont('helvetica', 'B', 11);
        $this->Cell(0, 5, $AdminName, 0, 1);
        $this->setX(38.1);
        $this->SetFont('helvetica', '', 11);
        $this->Cell(0, 5, 'Campus Administrator', 0, 1);
        $this->setX(38.1);
        $this->Cell(0, 5, 'This Campus', 0, 1);

        $this->Ln(8);
        $this->setX(38.1);
        $this->SetFont('helvetica', 'B', 11);
        $this->Cell(0, 5, 'Dear ' . $LetterTwo . ',', 0, 1);

        $this->Ln(8);
        $this->setX(38.1);
        $this->SetFont('helvetica', '', 11);
        $this->writeHTMLCell(156, 0, '', '', $LetterBody, 0, 1, 0, true, 'J', true);
    }

    public function Proposal($data)
    {
        $this->Ln(15);
        $this->setX(38.1);
        $this->SetFont('helvetica', 'B', 14);
        $this->Cell(0, 5, 'ACTIVITY PROPOSAL', 0, 1, 'C');
        $this->Ln(8);

        $this->setX(38.1);
        $this->SetFont('helvetica', '', 12);
        $htmlTable = '
    <table border="1" cellpadding="4">
        <tr>
            <td width="160"><strong>ACTIVITY TITLE:</strong></td>
            <td width="250" style="font-weight: bold;">' . $data['ActivityTitle'] . '</td>
        </tr>
        <tr>
            <td width="160"><strong>DATE AND VENUE:</strong></td>
            <td width="250">' . $data['ActivityDate'] . ' at ' . $data['ActivityVenue'] . '</td>
        </tr>
        <tr>
            <td width="160"><strong>ACTIVITY HEAD:</strong></td>
            <td width="250">' . $data['ActivityHead'] . '</td>
        </tr>
        <tr>
            <td width="160"><strong>ACTIVITY OBJECTIVE:</strong></td>
            <td width="250">' . $data['ActivityObjective'] . '</td>
        </tr>
        <tr>
            <td width="160"><strong>TARGET PARTICIPANTS:</strong></td>
            <td width="250">' . $data['ActivityTarget'] . '</td>
        </tr>
        <tr>
            <td width="160"><strong>MECHANICS:</strong></td>
            <td width="250">' . $data['ActivityMechanics'] . '</td>
        </tr>
        <tr>
            <td width="160"><strong>BUDGETARY REQUIREMENT:</strong></td>
            <td width="250">' . $data['ActivityBudget'] . '</td>
        </tr>
        <tr>
            <td width="160"><strong>SOURCE OF FUNDS:</strong></td>
            <td width="250">' . $data['ActivitySourceFunds'] . '</td>
        </tr>
        <tr>
            <td width="160"><strong>EXPECTED OUTPUT:</strong></td>
            <td width="250">' . $data['ActivityOutcomes'] . '</td>
        </tr>
    </table>';
        $this->writeHTML($htmlTable, true, false, true, false, '');
    }

    public function SignatureContent($ActivitySignature)
    {
        $this->Ln(15);
        $this->setX(38.1);
        $this->SetFont('helvetica', '', 11);
        $this->writeHTMLCell(156, 0, '', '', $ActivitySignature, 0, 1, 0, true, 'J', true);
    }
}


try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        response(['status' => 'error', 'message' => 'Invalid request method']);
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

    $TaskID = $_POST['TaskID'] ?? '';
    $IsFromTask = $_POST['isFromTask'] == 'true' ? 1 : 0;
    $ID = $_POST['ID'] ?? '';
    $OrgCode = $_POST['OrgCode'] ?? '';
    $Created_By = $_POST['Created_By'] ?? '';
    $AdminName = $_POST['AdminName'];
    $LetterTo = $_POST['LetterTo'];
    $ActivityHead = $_POST['ActivityHead'];
    $LetterBody = $_POST['LetterBody'];
    $ActivityTitle = $_POST['ActivityTitle'];
    $ActivityDate = $_POST['ActivityDate'];
    $ActivityVenue = $_POST['ActivityVenue'];
    $ActivityObjective = $_POST['ActivityObjective'];
    $ActivityTarget = $_POST['ActivityTarget'];
    $ActivityMechanics = $_POST['ActivityMechanics'];
    $ActivityBudget = $_POST['ActivityBudget'];
    $ActivitySourceFunds = $_POST['ActivitySourceFunds'];
    $ActivityOutcomes = $_POST['ActivityOutcomes'];
    $ActivitySignature = $_POST['ActivitySignature'];

    $LetterBody = filterAndRemove($LetterBody, $patterns);
    $ActivityObjective = filterAndRemove($ActivityObjective, $patterns);
    $ActivityBudget = filterAndRemove($ActivityBudget, $patterns);
    $ActivitySignature = filterAndRemove($ActivitySignature, $patterns);

    $LetterBody = updateCssProperty($LetterBody, $search, $replace);
    $ActivityObjective = updateCssProperty($ActivityObjective, $search, $replace);
    $ActivityBudget = updateCssProperty($ActivityBudget, $search, $replace);
    $ActivitySignature = updateCssProperty($ActivitySignature, $search, $replace);

    $ActivityDate = date('Y-m-d H:i:s', strtotime($ActivityDate));

    $data = [
        'ActivityTitle' => $ActivityTitle,
        'ActivityDate' => $ActivityDate,
        'ActivityVenue' => $ActivityVenue,
        'ActivityObjective' => $ActivityObjective,
        'ActivityTarget' => $ActivityTarget,
        'ActivityMechanics' => $ActivityMechanics,
        'ActivityBudget' => $ActivityBudget,
        'ActivitySourceFunds' => $ActivitySourceFunds,
        'ActivityOutcomes' => $ActivityOutcomes,
        'ActivityHead' => $ActivityHead
    ];

    $pdf = new PDF('P', 'mm', 'Letter', true, 'UTF-8', false);
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor($ID !== '' ? $Created_By : $_SESSION['FullName']);
    $pdf->SetTitle('Activity Proposal');
    $pdf->SetSubject('Activity Proposal for ' . $ActivityTitle);
    $pdf->SetKeywords('Activity Proposal, ' . $ActivityTitle, $ID !== '' ? 'Updated ' . date('Y-m-d H:i:s') : 'Created ' . date('Y-m-d H:i:s'));
    $pdf->SetPrintHeader(false);
    $pdf->AddPage();
    $pdf->CustomHeader($conn, $ActivityTitle, $OrgCode);
    $pdf->Content($conn, $AdminName, $LetterBody, $LetterTo);
    $pdf->AddPage();
    $pdf->CustomHeader($conn, $ActivityTitle, $OrgCode);
    $pdf->Proposal($data);
    $pdf->AddPage();
    $pdf->CustomHeader($conn, $ActivityTitle, $OrgCode, true);
    $pdf->SignatureContent($ActivitySignature);

    if ($ID !== '') {
        $stmt = $conn->prepare("SELECT file_path FROM activityproposaldocuments WHERE ID = ?");
        $stmt->bind_param("i", $ID);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        $row = $result->fetch_assoc();

        $filePath = realpath(dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . $row['file_path']);
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }


    $name = "Activity_Proposal_" . date('Y-m-d_H-i-s') . "_" . (!empty($OrgCode) ? $OrgCode : $_SESSION['org_Code']) . "_" . rand(1000, 9999) . ".pdf";
    $file_path = "DocumentsStorage" . DIRECTORY_SEPARATOR . (!empty($OrgCode) ? $OrgCode : $_SESSION['org_Code']) . DIRECTORY_SEPARATOR . $name;
    $storageDir = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'DocumentsStorage' . DIRECTORY_SEPARATOR . (!empty($OrgCode) ? $OrgCode : $_SESSION['org_Code']);
    if (!file_exists($storageDir)) {
        mkdir($storageDir, 0777, true);
    }
    $savePath = $storageDir . DIRECTORY_SEPARATOR . $name;

    $pdf->Output($savePath, 'F');

    $fileSize = filesize($savePath);

    $conn->begin_transaction();

    $Venue = '%' . $ActivityVenue . '%';
    $stmt = $conn->prepare("SELECT * FROM sysvenue WHERE ven_Name LIKE ? LIMIT 1");
    $stmt->bind_param("s", $Venue);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        // convert date to datetime
        $ActivityDate = date('Y-m-d H:i:s', strtotime($ActivityDate));
        $stmt = $conn->prepare("INSERT INTO sysvenue (ven_Name, created_by, isOccupied, startOccupied, endOccupied) VALUES (?,?,1,?,?)");
        $stmt->bind_param("ssss", $ActivityVenue, $_SESSION['UUID'], $ActivityDate, $ActivityDate);
        $stmt->execute();
        $stmt->close();
    } else {
        $row = $result->fetch_assoc();
        if ($row['isOccupied'] === 0) {
            $stmt = $conn->prepare("UPDATE sysvenue SET isOccupied = 1, startOccupied = ?, endOccupied = ? WHERE ID = ?");
            $stmt->bind_param("ssi", $ActivityDate, $ActivityDate, $row['ID']);
            $stmt->execute();
            $stmt->close();
        } else {
            if ($row['endOccupied'] < date('Y-m-d')) {
                $stmt = $conn->prepare("UPDATE sysvenue SET isOccupied = 1, startOccupied = ?, endOccupied = ? WHERE ID = ?");
                $stmt->bind_param("ssi", $ActivityDate, $ActivityDate, $row['ID']);
                $stmt->execute();
                $stmt->close();
            } else {
                response(['status' => 'error', 'message' => 'Venue is already occupied']);
            } 
        }
    }

    $UUID = $_SESSION['UUID'];
    $title = $ActivityTitle;
    $eventdesc = "Activity Proposal for " . $ActivityTitle;
    $location = $ActivityVenue;
    $color = 'bs-indigo';
    $start = date('Y-m-d', strtotime($ActivityDate));
    $end = date('Y-m-d', strtotime($ActivityDate));
    $isEnded = 0;
    $type = 'AP';
    $isDeleted = 0;

    $stmt = $conn->prepare("INSERT INTO sysevents (UUID, title, eventdesc, location, color, start, end, isEnded, type, isDeleted) VALUES (?,?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param("sssssssiss", $UUID, $title, $eventdesc, $location, $color, $start, $end, $isEnded, $type, $isDeleted);
    $stmt->execute();
    $stmt->close();

    if ($ID !== '') {
        $stmt = $conn->prepare("UPDATE activityproposaldocuments SET file_Size = ?, file_path = ?, admin_name = ?, dear_title = ?, LetterBody = ?, act_title = ?, act_date =?, act_ven = ?, act_head = ?, act_obj = ?, act_participate = ?, act_mech = ?, act_budget = ?, act_funds = ?, act_expectOut = ?, act_signature = ?, taskID = ?, isFromTask = ? WHERE ID = ?");
        $stmt->bind_param("isssssssssssssssssis", $fileSize, $file_path, $AdminName, $LetterTo, $LetterBody, $ActivityTitle, $ActivityDate, $ActivityVenue, $ActivityHead, $ActivityObjective, $ActivityTarget, $ActivityMechanics, $ActivityBudget, $ActivitySourceFunds, $ActivityOutcomes, $ActivitySignature, $TaskID, $IsFromTask, $ID);
        $stmt->execute();
        $stmt->close();
    } else {

        if ($TaskID !== '') {
            $stmt = $conn->prepare("UPDATE taskdocuments SET tastStat = 'Completed' WHERE taskID = ?");
            $stmt->bind_param("s", $TaskID);
            $stmt->execute();
            $stmt->close();
        }

        $UUID = $_SESSION['UUID'];
        $orgCode = (!empty($OrgCode) ? $OrgCode : $_SESSION['org_Code']);

        $stmt = $conn->prepare("INSERT INTO activityproposaldocuments (UUID, org_code, file_Size, file_path, admin_name, dear_title, LetterBody, act_title, act_date, act_ven, act_head, act_obj, act_participate, act_mech, act_budget, act_funds, act_expectOut, act_signature, taskID, isFromTask) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("ssisssssssssssssssis", $UUID, $orgCode, $fileSize, $file_path, $AdminName, $LetterTo, $LetterBody, $ActivityTitle, $ActivityDate, $ActivityVenue, $ActivityHead, $ActivityObjective, $ActivityTarget, $ActivityMechanics, $ActivityBudget, $ActivitySourceFunds, $ActivityOutcomes, $ActivitySignature, $TaskID, $IsFromTask);
        $stmt->execute();
        $stmt->close();
    }

    $stmt = $conn->prepare("INSERT INTO audit_apdoc (UUID, file_Size, file_name) VALUES (?,?,?)");
    $stmt->bind_param("sis", $_SESSION['UUID'], $fileSize, $file_path);
    $stmt->execute();
    $stmt->close();

    $conn->commit();
    $savePath = substr($savePath, 27);
    response(['status' => 'success', 'message' => 'Activity Proposal successfully created', 'path' => $savePath]);

} catch (Exception $e) {
    $conn->rollback();
    response(['status' => 'error', 'message' => $e->getMessage() . ' at line ' . $e->getLine()]);
}
