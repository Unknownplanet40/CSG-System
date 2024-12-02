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
        $this->Cell(0, 5, 'Cover Letter and Project Proposal | ' . $ActivityTitle . ' | Page ' . $this->getAliasNumPage() . ' of ' . $this->getAliasNbPages(), 0, 1, 'R');
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
        $this->Cell(0, 5, 'PROJECT PROPOSAL', 0, 1, 'C');
        $this->Ln(8);

        $this->setX(38.1);
        $this->SetFont('helvetica', '', 12);
        $htmlTable = '
    <table border="1" cellpadding="4">
        <tr>
            <td width="160"><strong>PROJECT TITLE:</strong></td>
            <td width="250" style="font-weight: bold;">' . $data['ActivityTitle'] . '</td>
        </tr>
        <tr>
            <td width="160"><strong>DATE AND VENUE:</strong></td>
            <td width="250">' . $data['ActivityDateVenue'] . '</td>
        </tr>
        <tr>
            <td width="160"><strong>PROJECT HEAD:</strong></td>
            <td width="250">' . $data['ActivityHead'] . '</td>
        </tr>
        <tr>
            <td width="160"><strong>PROJECT OBJECTIVE:</strong></td>
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

    $TaskID = $_POST['TaskID'] ?? '';
    $IsFromTask = $_POST['isFromTask'] == 'true' ? 1 : 0;
    $ID = $_POST['ID'] ?? '';
    $OrgCode = $_POST['OrgCode'] ?? '';
    $Created_By = $_POST['Created_By'] ?? '';
    $AdminName = $_POST['AdminName'];
    $LetterTo = $_POST['LetterTo'];
    $ActivityHead = $_POST['ActivityHead'];
    $LetterBody = $_POST['LetterBody']; // html content
    $ActivityTitle = $_POST['ActivityTitle'];
    $ActivityDateVenue = $_POST['ActivityDateVenue'];
    $ActivityObjective = $_POST['ActivityObjective']; // html content
    $ActivityTarget = $_POST['ActivityTarget'];
    $ActivityMechanics = $_POST['ActivityMechanics'];
    $ActivityBudget = $_POST['ActivityBudget']; // html content
    $ActivitySourceFunds = $_POST['ActivitySourceFunds'];
    $ActivityOutcomes = $_POST['ActivityOutcomes'];
    $ActivitySignature = $_POST['ActivitySignature'];

    $remove = '<p data-f-id="pbf" style="text-align: center; font-size: 14px; margin-top: 30px; opacity: 0.65; font-family: sans-serif;">Powered by <a href="https://www.froala.com/wysiwyg-editor?pb=1" title="Froala Editor">Froala Editor</a></p>';
    $LetterBody = str_replace($remove, '', $LetterBody);
    $ActivityObjective = str_replace($remove, '', $ActivityObjective);
    $ActivityBudget = str_replace($remove, '', $ActivityBudget);
    $ActivitySignature = str_replace($remove, '', $ActivitySignature);

    $data = [
        'ActivityTitle' => $ActivityTitle,
        'ActivityDateVenue' => $ActivityDateVenue,
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
    $pdf->SetTitle('Project Proposal');
    $pdf->SetSubject('Project Proposal for ' . $ActivityTitle);
    $pdf->SetKeywords('Project Proposal, ' . $ActivityTitle, $ID !== '' ? 'Updated ' . date('Y-m-d H:i:s') : 'Created ' . date('Y-m-d H:i:s'));
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
        $stmt = $conn->prepare("SELECT file_path FROM projectproposaldocuments WHERE ID = ?");
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


    $name = "Project_Proposal_" . date('Y-m-d_H-i-s') . "_" . (!empty($OrgCode) ? $OrgCode : $_SESSION['org_Code']) . "_" . rand(1000, 9999) . ".pdf";
    $file_path = "DocumentsStorage/" . (!empty($OrgCode) ? $OrgCode : $_SESSION['org_Code']) . "/" . $name;
    $storageDir = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'DocumentsStorage' . DIRECTORY_SEPARATOR . (!empty($OrgCode) ? $OrgCode : $_SESSION['org_Code']);
    if (!file_exists($storageDir)) {
        mkdir($storageDir, 0777, true);
    }
    $savePath = $storageDir . DIRECTORY_SEPARATOR . $name;

    $pdf->Output($savePath, 'F');

    $fileSize = filesize($savePath);

    $conn->begin_transaction();

    if ($ID !== '') {
        $stmt = $conn->prepare("UPDATE projectproposaldocuments SET file_Size = ?, file_path = ?, admin_name = ?, dear_title = ?, LetterBody = ?, act_title = ?, act_date_ven = ?, act_head = ?, act_obj = ?, act_participate = ?, act_mech = ?, act_budget = ?, act_funds = ?, act_expectOut = ?, act_signature = ?, taskID = ?, isFromTask = ? WHERE ID = ?");
        $stmt->bind_param("isssssssssssssssis", $fileSize, $file_path, $AdminName, $LetterTo, $LetterBody, $ActivityTitle, $ActivityDateVenue, $ActivityHead, $ActivityObjective, $ActivityTarget, $ActivityMechanics, $ActivityBudget, $ActivitySourceFunds, $ActivityOutcomes, $ActivitySignature, $TaskID, $IsFromTask, $ID);
        $stmt->execute();
        $stmt->close();
    } else {

        if ($TaskID !== '') {
            $stmt = $conn->prepare("UPDATE taskdocuments SET tastStat = 'Completed' WHERE taskID = ?");
            $stmt->bind_param("s", $TaskID);
            $stmt->execute();
            $stmt->close();
        }

        $stmt = $conn->prepare("INSERT INTO projectproposaldocuments (UUID, org_code, file_Size, file_path, admin_name, dear_title, LetterBody, act_title, act_date_ven, act_head, act_obj, act_participate, act_mech, act_budget, act_funds, act_expectOut, act_signature, taskID, isFromTask) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("ssissssssssssssssi", $_SESSION['UUID'], (!empty($OrgCode) ? $OrgCode : $_SESSION['org_Code']), $fileSize, $file_path, $AdminName, $LetterTo, $LetterBody, $ActivityTitle, $ActivityDateVenue, $ActivityHead, $ActivityObjective, $ActivityTarget, $ActivityMechanics, $ActivityBudget, $ActivitySourceFunds, $ActivityOutcomes, $ActivitySignature, $TaskID, $IsFromTask);
        $stmt->execute();
        $stmt->close();
    }

    $stmt = $conn->prepare("INSERT INTO audit_apdoc (UUID, file_Size, file_name) VALUES (?,?,?)");
    $stmt->bind_param("sis", $_SESSION['UUID'], $fileSize, $file_path);
    $stmt->execute();
    $stmt->close();

    $conn->commit();
    $savePath = substr($savePath, 27);
    response(['status' => 'success', 'message' => 'Project Proposal successfully created', 'path' => $savePath]);

} catch (Exception $e) {
    $conn->rollback();
    response(['status' => 'error', 'message' => $e->getMessage()]);
}
