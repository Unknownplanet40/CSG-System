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
    public function CustomHeader($conn, $OrgCode, $isDocHeaderHidden = false, $Subject = '')
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

        $this->SetFont('helvetica', '', 10);
        $this->Cell(0, 5, 'Office Memorandum | ' . $Subject . ' | Page ' . $this->getAliasNumPage() . ' of ' . $this->getAliasNbPages(), 0, 1, 'R');
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

            // horizontal line
            $this->ln(8);
            $this->SetLineWidth(0.1);
            $this->Line(38.1, $this->GetY(), 194, $this->GetY());
        }
    }

    public function Content($conn)
    {
        $this->Ln(4);
        $this->setX(38.1);
        $this->SetFont('helvetica', 'B', 11);
        $this->Cell(0, 5, 'Office Memorandum', 0, 1, );
        $this->setX(38.1);
        $this->SetFont('helvetica', '', 11);
        $this->Cell(0, 5, 'No. ' . $_POST['OMNo'] . ' ' . date('Y')  - 1 . ' - ' . date('Y'), 0, 1, );

        $this->Ln(8);
        $this->setX(38.1);
        $this->SetFont('helvetica', 'B', 11);
        $this->Cell(40, 5, 'TO:', 0, 0);
        $this->SetFont('helvetica', 'B', 11);
        $this->Cell(5, 5, ' : ', 0, 0);
        $this->SetFont('helvetica', 'B', 11);
        $this->Cell(100, 5, strtoupper($_POST['OMTO']), 0, 1);
        $this->setX(38.1);
        $this->SetFont('helvetica', 'B', 11);
        $this->Cell(40, 5, '', 0, 0);
        $this->SetFont('helvetica', 'B', 11);
        $this->Cell(5, 5, '', 0, 0);
        $this->SetFont('helvetica', '', 11);
        $this->Cell(100, 5, $_POST['OMTOPosition'], 0, 1);

        $this->Ln(8);
        $this->setX(38.1);
        $this->SetFont('helvetica', 'B', 11);
        $this->Cell(40, 5, 'FROM:', 0, 0);
        $this->SetFont('helvetica', 'B', 11);
        $this->Cell(5, 5, ' : ', 0, 0);
        $this->SetFont('helvetica', 'B', 11);
        $this->multiCell(100, 5, strtoupper($_POST['OMFROM']), 0, 1);

        $this->setX(38.1);
        $this->SetFont('helvetica', 'B', 11);
        $this->Cell(40, 5, '', 0, 0);
        $this->SetFont('helvetica', 'B', 11);
        $this->Cell(5, 5, '', 0, 0);
        $this->SetFont('helvetica', '', 11);
        $this->multiCell(100, 5, $_POST['OMFROMPosition'], 0, 1);

        $this->Ln(8);
        $this->setX(38.1);
        $this->SetFont('helvetica', 'B', 11);
        $this->Cell(40, 5, 'SUBJECT:', 0, 0);
        $this->SetFont('helvetica', 'B', 11);
        $this->Cell(5, 5, ' : ', 0, 0);
        $this->SetFont('helvetica', 'B', 11);
        $this->multiCell(100, 5, strtoupper($_POST['OMSubject']), 0, 1);
        $this->setX(38.1);
        $this->SetFont('helvetica', 'B', 11);
        $this->Cell(40, 5, 'DATE:', 0, 0);
        $this->SetFont('helvetica', 'B', 11);
        $this->Cell(5, 5, ' : ', 0, 0);
        $this->SetFont('helvetica', 'B', 11);
        $this->Cell(100, 5, date('F d, Y', strtotime($_POST['OMDate'])), 0, 1);

        $this->ln(4);
        $this->SetLineWidth(0.1);
        $this->Line(38.1, $this->GetY(), 194, $this->GetY());


        $this->Ln(10);
        $this->setX(38.1);
        $this->SetFont('helvetica', '', 11);
        $this->setX(38.1);
        $this->writeHTMLCell(156, 0, '', '', $_POST['OMContent'], 0, 1, 0, true, 'J', true);

        $this->Ln(10);
        $this->setX(38.1);
        $this->SetFont('helvetica', '', 11);
        $this->writeHTMLCell(156, 0, '', '', $_POST['OMSignature'], 0, 1, 0, true, 'J', true);




    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    response(['status' => 'error', 'message' => 'Only POST request is allowed']);
}

if (!isset($_SESSION['org_Code'])) {
    response(['status' => 'error', 'message' => 'Unauthorized access']);
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

try {
    $OMNo = $_POST['OMNo'];
    $OMDate = $_POST['OMDate'];
    $OMTO = $_POST['OMTO'];
    $OMTOPosition = $_POST['OMTOPosition'];
    $OMFROM = $_POST['OMFROM'];
    $OMFROMPosition = $_POST['OMFROMPosition'];
    $OMSubject = $_POST['OMSubject'];
    $OMContent = $_POST['OMContent'];
    $OMSignature = $_POST['OMSignature'];
    $OMORG = $_POST['OMORG'];
    $ID = $_POST['ID'] ?? '';
    $Created_By = $_POST['Created_By'] ?? '';
    $taskID = $_POST['taskID'] ?? '';
    $isFromTask = $_POST['isFromTask'] ?? 0;

    $OMContent = filterAndRemove($OMContent, $patterns);
    $OMContent = updateCssProperty($OMContent, $search, $replace);
    $OMSignature = filterAndRemove($OMSignature, $patterns);
    $OMSignature = updateCssProperty($OMSignature, $search, $replace);

    $pdf = new PDF('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor($ID !== '' ? $Created_By : $_SESSION['FullName']);
    $pdf->SetTitle('Office Memorandum | No. ' . $OMNo . ' | ' . $OMDate);
    $pdf->SetSubject('Office Memorandum | No. ' . $OMNo . ' | ' . $OMDate);
    $pdf->SetKeywords('E-Document, Office Memorandum, Memorandum, PDF');
    $pdf->SetPrintHeader(false);
    $pdf->AddPage();
    $pdf->CustomHeader($conn, $OMORG, false, $OMSubject);
    $pdf->Content($conn);

    $directory = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'DocumentsStorage' . DIRECTORY_SEPARATOR . (!empty($OMORG) ? $OMORG : $_SESSION['org_Code']);
    if (!is_dir($directory)) {
        mkdir($directory, 0777, true);
    }
    $filepaths = $directory . DIRECTORY_SEPARATOR . 'OfficeMemorandum_' . $OMNo . '_' . date('F-d-Y') . '_' . rand(1000, 9999) . '.pdf';
    $pdf->Output($filepaths, 'F');

    $filesize = filesize($filepaths);

    $filepaths = substr($filepaths, strlen(dirname(__DIR__, 3) . DIRECTORY_SEPARATOR));
    $currentDate = date('Y-m-d H:i:s');
    $UUID = $_SESSION['UUID'];

    if ($ID !== '') {
        $stmt = $conn->prepare("UPDATE officememorandomdocuments SET org_Code = ?, file_Size = ?, TaskID = ?, isFromTask = ?, OM_No = ?, OM_To = ?, OM_To_Position = ?, OM_From = ?, OM_From_Position = ?, OM_Sub = ?, OM_Date = ?, OM_Body = ?, OM_Signature = ?, DateCreated = ?, UUID = ? WHERE ID = ?");
        $stmt->bind_param("sssisssssssssssi", $OMORG, $filesize, $taskID, $isFromTask, $OMNo, $OMTO, $OMTOPosition, $OMFROM, $OMFROMPosition, $OMSubject, $OMDate, $OMContent, $OMSignature, $currentDate, $UUID, $ID);
        $stmt->execute();
        $stmt->close();
    } else {
        if ($isFromTask) {
            $stmt = $conn->prepare("UPDATE taskdocuments SET tastStat = 'Completed' WHERE TaskID = ? ");
            $stmt->bind_param("s", $taskID);
            $stmt->execute();
            $stmt->close();
        }

        $stmt = $conn->prepare("INSERT INTO officememorandomdocuments (org_Code, file_Size, TaskID, isFromTask, OM_No, OM_To, OM_To_Position, OM_From, OM_From_Position, OM_Sub, OM_Date, OM_Body, OM_Signature, DateCreated, UUID) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sisssssssssssss", $OMORG, $filesize, $taskID, $isFromTask, $OMNo, $OMTO, $OMTOPosition, $OMFROM, $OMFROMPosition, $OMSubject, $OMDate, $OMContent, $OMSignature, $currentDate, $UUID);
        $stmt->execute();
        $stmt->close();
    }

    response(['status' => 'success', 'message' => 'Successfully Created']);
} catch (\Throwable $th) {
    response(['status' => 'error', 'message' => $th->getMessage()]);
}
