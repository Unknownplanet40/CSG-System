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
    public function CustomHeader($conn, $OrgCode, $isDocHeaderHidden = false)
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
        $this->Cell(0, 5, 'Minutes of the Meeting | ' . date('F d, Y') . ' | Page ' . $this->getAliasNumPage() . ' of ' . $this->getAliasNbPages(), 0, 1, 'R');
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

    public function Content($conn, $MM_DATE, $MM_TIMESTARTED, $MM_LOC, $MM_PRESIDER, $MM_ATTENDEES, $MM_ABSENTEES, $MM_AGENDA, $MM_COMMENCEMENT, $MM_TIMEADJOURNED, $MM_SIGNATURE)
    {
        $this->Ln(15);
        $this->setX(38.1);
        $this->SetFont('helvetica', 'B', 11);
        $this->Cell(0, 5, 'MINUTES OF THE MEETING', 0, 1, 'C');

        $this->Ln(8);
        $this->setX(38.1);
        $this->SetFont('helvetica', 'B', 11);
        $this->Cell(0, 5, 'DATE AND LOCATION: ' . date('F d, Y', strtotime($MM_DATE)) . ' at ' . $MM_LOC, 0, 1);
        $this->setX(38.1);
        $this->Cell(0, 5, 'TIME STARTED: ' . date('h:i A', strtotime($MM_TIMESTARTED)), 0, 1);

        $this->Ln(5);
        $this->setX(38.1);
        $this->SetFont('helvetica', 'B', 11);
        $this->Cell(0, 5, 'PRESIDER: ' . $MM_PRESIDER, 0, 1);

        $this->Ln(5);
        $this->setX(38.1);
        $this->SetFont('helvetica', 'B', 11);
        $this->Cell(0, 5, 'ATTENDEES:', 0, 1);
        $this->setX(38.1);
        $this->setFont('helvetica', '', 11);
        $this->writeHTMLCell(156, 0, '', '', $MM_ATTENDEES, 0, 1, 0, true, 'J', true);

        $this->Ln(8);
        $this->setX(38.1);
        $this->SetFont('helvetica', 'B', 11);
        $this->Cell(0, 5, 'ABSENTEES:', 0, 1);
        $this->setX(38.1);
        $this->setFont('helvetica', '', 11);
        if ($MM_ABSENTEES === '' || $MM_ABSENTEES === '<p><br></p>') {
            $this->setX(50);
            $this->Cell(0, 5, 'N / A', 0, 1);
        } else {
            $this->writeHTMLCell(156, 0, '', '', $MM_ABSENTEES, 0, 1, 0, true, 'J', true);
        }

        $this->Ln(8);
        $this->setX(38.1);
        $this->SetFont('helvetica', 'B', 11);
        $this->Cell(0, 5, 'AGENDA:', 0, 1);
        $this->setX(38.1);
        $this->setFont('helvetica', '', 11);
        $this->writeHTMLCell(156, 0, '', '', $MM_AGENDA, 0, 1, 0, true, 'J', true);

        $this->Ln(4);
        $this->SetLineWidth(0.1);
        $this->Line(38.1, $this->GetY(), 194, $this->GetY());

        $this->Ln(4);
        $this->setX(38.1);
        $this->SetFont('helvetica', 'B', 11);
        $this->Cell(0, 5, 'COMMENCEMENT:', 0, 1);
        $this->setX(38.1);
        $this->setFont('helvetica', '', 11);
        $this->writeHTMLCell(156, 0, '', '', $MM_COMMENCEMENT, 0, 1, 0, true, 'J', true);

        $this->Ln(8);
        $this->setX(38.1);
        $this->SetFont('helvetica', 'B', 11);
        $this->Cell(0, 5, 'TIME ADJOURNED: ' . date('h:i A', strtotime($MM_TIMEADJOURNED)), 0, 1);

        if ($this->GetY() + 10 > $this->getPageHeight() - $this->getBreakMargin()) {
            $this->addPage();
        } else {
            $this->Ln(10);
        }
        $this->setX(38.1);
        $this->SetFont('helvetica', '', 11);
        $this->Cell(0, 5, 'Prepared by:', 0, 1);
        $this->setX(38.1);
        $this->setFont('helvetica', '', 11);

        $stmt = $conn->prepare("SELECT * FROM userpositions WHERE org_code = ? AND org_position = 4 AND isTermComplete = 0 AND status = 'active' LIMIT 1");
        $stmt->bind_param("s", $_SESSION['org_Code']);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $stmt = $conn->prepare("SELECT fullName FROM usercredentials WHERE UUID = ?");
        $stmt->bind_param("s", $result['UUID']);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $this->Ln(10);
        $this->setX(38.1);
        $this->SetFont('helvetica', 'B', 11);
        $this->Cell(0, 5, strtoupper($result['fullName'] ?? 'N/A'), 0, 1);
        $this->setX(38.1);

        $stmt = $conn->prepare("SELECT * FROM sysorganizations WHERE org_code = ?");
        $stmt->bind_param("s", $_SESSION['org_Code']);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $this->SetFont('helvetica', '', 11);
        $this->Cell(0, 5, $result['org_short_name'] . ' Secretary', 0, 1);

        $this->Ln(8);
        $this->setX(38.1);
        $this->SetFont('helvetica', '', 11);
        $this->Cell(0, 5, 'Recmmending Approval:', 0, 1);
        $this->setX(38.1);
        $this->setFont('helvetica', '', 11);

        $this->Ln(8);
        $this->setX(38.1);
        $this->SetFont('helvetica', '', 11);
        $this->writeHTMLCell(156, 0, '', '', $MM_SIGNATURE, 0, 1, 0, true, 'J', true);
    }

    public function DocumentationImage($MM_DOCS_PATH, $FileNameEnd, $OrgCode, $ID, $MM_DOCS)
    {
        if ($ID !== '' && $MM_DOCS === '') {
            $stmt = $conn->prepare("SELECT filedEnd AS FileNameEnd, DocsPath FROM minutemeetingdocuments WHERE ID = ? AND org_code = ?");
            $stmt->bind_param("ss", $ID, $OrgCode);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            $FileEnd = $result['FileNameEnd'];
            $MM_DOCS_PATH = json_decode($result['DocsPath']);
        } else {
            $FileEnd = $FileNameEnd;
            $MM_DOCS_PATH = json_decode($MM_DOCS_PATH);
        }

        $filepaths = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'DocumentsStorage' . DIRECTORY_SEPARATOR .
                     (!empty($OrgCode) ? $OrgCode : $_SESSION['org_Code']) . DIRECTORY_SEPARATOR .
                     'MinutesOfTheMeeting' . DIRECTORY_SEPARATOR . $FileEnd;

        $this->Ln(15);
        $this->setX(38.1);
        $this->SetFont('helvetica', 'B', 11);
        $this->Cell(0, 5, 'DOCUMENTATION', 0, 1, 'C');

        if (!empty($MM_DOCS_PATH)) {
            $images = [];

            // Collect image data with their heights
            foreach ($MM_DOCS_PATH as $value) {
                $imgPath = $filepaths . DIRECTORY_SEPARATOR . $value;
                list($width, $height) = getimagesize($imgPath); // Get dimensions
                $images[] = [
                    'path' => $value,
                    'height' => $height // Use height for sorting
                ];
            }

            // Sort images by height (shortest first)
            usort($images, function ($a, $b) {
                return $a['height'] - $b['height'];
            });

            // Display images in sorted order
            foreach ($images as $image) {
                $this->ln(10); // Add spacing before image
                $this->Image($filepaths . DIRECTORY_SEPARATOR . $image['path'], 38.1, $this->GetY(), 156); // Display image
                $this->Ln(10 + $this->getImageRBY() - $this->GetY()); // Adjust position dynamically
            }
        }
    }


}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    response(['error' => 'Method Not Allowed']);
}

try {
    $FileNameEnd = uniqid('', true);

    $MM_DATE = $_POST['MM_DATE'];
    $MM_TIMESTARTED = $_POST['MM_TIMESTARTED'];
    $MM_LOC = $_POST['MM_LOC'];
    $MM_PRESIDER = $_POST['MM_PRESIDER'];
    $MM_ATTENDEES = $_POST['MM_ATTENDEES'];
    $MM_ABSENTEES = $_POST['MM_ABSENTEES'];
    $MM_AGENDA = $_POST['MM_AGENDA'];
    $MM_COMMENCEMENT = $_POST['MM_COMMENCEMENT'];
    $MM_TIMEADJOURNED = $_POST['MM_TIMEADJOURNED'];
    $MM_SIGNATURE = $_POST['MM_SIGNATURE'];
    $ID = $_POST['ID'] ?? '';
    $OrgCode = $_POST['OrgCode'] ?? '';
    $Created_By = $_POST['Created_By'] ?? '';
    $taskID = $_POST['taskID'] ?? '';
    $isFromTask = $_POST['isFromTask'] ?? 0;
    $taskOrgCode = $_POST['taskOrgCode'] ?? '';

    function filterAndRemove($input, $patterns)
    {
        $filteredOutput = preg_replace($patterns, '', $input);
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

    $MM_ATTENDEES = filterAndRemove($MM_ATTENDEES, $patterns);
    $MM_ABSENTEES = filterAndRemove($MM_ABSENTEES, $patterns);
    $MM_AGENDA = filterAndRemove($MM_AGENDA, $patterns);
    $MM_COMMENCEMENT = filterAndRemove($MM_COMMENCEMENT, $patterns);
    $MM_SIGNATURE = filterAndRemove($MM_SIGNATURE, $patterns);

    $MM_ATTENDEES = updateCssProperty($MM_ATTENDEES, $search, $replace);
    $MM_ABSENTEES = updateCssProperty($MM_ABSENTEES, $search, $replace);
    $MM_AGENDA = updateCssProperty($MM_AGENDA, $search, $replace);
    $MM_COMMENCEMENT = updateCssProperty($MM_COMMENCEMENT, $search, $replace);
    $MM_SIGNATURE = updateCssProperty($MM_SIGNATURE, $search, $replace);

    $MM_DOCS = $_FILES['MM_DOCS'] ?? [];

    $MM_DOCS_PATH = [];

    if (!empty($MM_DOCS)) {
        if (count($MM_DOCS['name']) > 0) {
            $MM_DOCS_PATH = [];
            if ($ID !== '' && $MM_DOCS !== '') {
                $stmt = $conn->prepare("SELECT filedEnd AS FileNameEnd, DocsPath FROM minutemeetingdocuments WHERE ID = ? AND org_code = ?");
                $stmt->bind_param("ss", $ID, $OrgCode);
                $stmt->execute();
                $result = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                $oldFileNameEnd = $result['FileNameEnd'];
                $oldDocsPath = json_decode($result['DocsPath'], true);

                //delete folder
                if (!empty($oldDocsPath)) {
                    $storageDir = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'DocumentsStorage' . DIRECTORY_SEPARATOR .
                          (!empty($OrgCode) ? $OrgCode : $_SESSION['org_Code']) . DIRECTORY_SEPARATOR .
                          'MinutesOfTheMeeting' . DIRECTORY_SEPARATOR . $oldFileNameEnd;
                    if (is_dir($storageDir)) {
                        $files = glob($storageDir . '/*');
                        foreach ($files as $file) {
                            if (is_file($file)) {
                                unlink($file);
                            }
                        }
                        rmdir($storageDir);
                    }
                }

                $stmt = $conn->prepare("UPDATE minutemeetingdocuments SET DocsPath = null WHERE ID = ? AND org_code = ?");
                $stmt->bind_param("ss", $ID, $OrgCode);
                $stmt->execute();
                $stmt->close();
            }

            $storageDir = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'DocumentsStorage' . DIRECTORY_SEPARATOR .
            (!empty($OrgCode) ? $OrgCode : $_SESSION['org_Code']) . DIRECTORY_SEPARATOR .
            'MinutesOfTheMeeting' . DIRECTORY_SEPARATOR . $FileNameEnd;
            if (!is_dir($storageDir)) {
                mkdir($storageDir, 0777, true);
            }

            for ($i = 0; $i < count($MM_DOCS['name']); $i++) {
                $fileName = $MM_DOCS['name'][$i];
                $fileTmpName = $MM_DOCS['tmp_name'][$i];
                $fileSize = $MM_DOCS['size'][$i];
                $fileError = $MM_DOCS['error'][$i];

                $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $allowed = ['png', 'jpg', 'jpeg'];

                if (in_array($fileExt, $allowed)) {
                    if ($fileError === 0) {
                        if ($fileSize < 10000000) { // 10MB
                            $fileNameNew = $FileNameEnd . '_img_' . $i . '.' . $fileExt;
                            $fileDestination = $storageDir . DIRECTORY_SEPARATOR . $fileNameNew;
                            move_uploaded_file($fileTmpName, $fileDestination);
                            $MM_DOCS_PATH[] = $fileNameNew;
                        } else {
                            response(['status' => 'error', 'message' => 'Your file is too big!']);
                        }
                    } else {
                        response(['status' => 'error', 'message' => 'There was an error uploading your file!']);
                    }
                } else {
                    response(['status' => 'error', 'message' => '.' . $fileExt . ' file type is not allowed!']);
                }
            }

            $stmt = $conn->prepare("UPDATE minutemeetingdocuments SET DocsPath = ? WHERE ID = ? AND org_code = ?");
            $jsonDocsPath = json_encode($MM_DOCS_PATH);
            $stmt->bind_param("sss", $jsonDocsPath, $ID, $OrgCode);
            $stmt->execute();
            $stmt->close();
        }
    }


    $MM_DOCS_PATH = json_encode($MM_DOCS_PATH);

    $pdf = new PDF('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor($ID !== '' ? $Created_By : $_SESSION['FullName']);
    $pdf->SetTitle('Minutes of the Meeting');
    $pdf->SetSubject('Minutes of the Meeting | ' . $MM_DATE);
    $pdf->SetKeywords('E-Document, Minutes of the Meeting, ' . $MM_DATE);
    $pdf->SetPrintHeader(false);
    $pdf->AddPage();
    $pdf->CustomHeader($conn, $OrgCode);
    $pdf->Content($conn, $MM_DATE, $MM_TIMESTARTED, $MM_LOC, $MM_PRESIDER, $MM_ATTENDEES, $MM_ABSENTEES, $MM_AGENDA, $MM_COMMENCEMENT, $MM_TIMEADJOURNED, $MM_SIGNATURE);

    if (!empty($MM_DOCS) || $ID !== '') {
        $pdf->addPage();
        $pdf->CustomHeader($conn, $OrgCode);
        $pdf->DocumentationImage($MM_DOCS_PATH, $FileNameEnd, $OrgCode, $ID, $MM_DOCS);
    }

    $filepaths = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'DocumentsStorage' . DIRECTORY_SEPARATOR . (!empty($OrgCode) ? $OrgCode : $_SESSION['org_Code']) . DIRECTORY_SEPARATOR . 'MinutesOfTheMeeting_' . $FileNameEnd . '.pdf';
    $pdf->Output($filepaths, 'F');

    $fileSize = filesize($filepaths);

    $filepaths = substr($filepaths, strlen(dirname(__DIR__, 3) . DIRECTORY_SEPARATOR));
    $currentDateTime = date('Y-m-d H:i:s');

    $OrgCode = (!empty($OrgCode) ? $OrgCode : $_SESSION['org_Code']);


    if ($ID !== '') {

        // delete old file
        $stmt = $conn->prepare("SELECT file_path FROM minutemeetingdocuments WHERE ID = ? AND org_code = ?");
        $stmt->bind_param("ss", $ID, $OrgCode);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $oldFilePath = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . $result['file_path'];
        if (file_exists($oldFilePath)) {
            unlink($oldFilePath);
        }

        if (!empty($MM_DOCS)) {
            $stmt = $conn->prepare("UPDATE minutemeetingdocuments SET file_Size = ?, filedEnd = ?, file_path = ?, MMdate = ?, MMTimeStart = ?, MMLocation = ?, MMPresider = ?, MMAttendees = ?, MMAbsentees = ?, MMAgenda = ?, MMCommencement = ?, MMTimeend = ?, DocsPath = ?, MMsignature = ?, DateCreated = ? WHERE ID = ? AND org_code = ?");
            $stmt->bind_param("issssssssssssssss", $fileSize, $FileNameEnd, $filepaths, $MM_DATE, $MM_TIMESTARTED, $MM_LOC, $MM_PRESIDER, $MM_ATTENDEES, $MM_ABSENTEES, $MM_AGENDA, $MM_COMMENCEMENT, $MM_TIMEADJOURNED, $MM_DOCS_PATH, $MM_SIGNATURE, $currentDateTime, $ID, $OrgCode);
        } else {
            $stmt = $conn->prepare("UPDATE minutemeetingdocuments SET file_Size = ?, filedEnd = ?, file_path = ?, MMdate = ?, MMTimeStart = ?, MMLocation = ?, MMPresider = ?, MMAttendees = ?, MMAbsentees = ?, MMAgenda = ?, MMCommencement = ?, MMTimeend = ?, MMsignature = ?, DateCreated = ? WHERE ID = ? AND org_code = ?");
            $stmt->bind_param("isssssssssssssss", $fileSize, $FileNameEnd, $filepaths, $MM_DATE, $MM_TIMESTARTED, $MM_LOC, $MM_PRESIDER, $MM_ATTENDEES, $MM_ABSENTEES, $MM_AGENDA, $MM_COMMENCEMENT, $MM_TIMEADJOURNED, $MM_SIGNATURE, $currentDateTime, $ID, $OrgCode);
        }
        $stmt->execute();
        $stmt->close();

        response(['status' => 'success', 'message' => 'Minutes of the Meeting successfully updated!', 'filepaths' => $filepaths, ]);
    } else {
        if ($taskID !== '') {
            $stmt = $conn->prepare("UPDATE taskdocuments SET tastStat = 'Completed' WHERE taskID = ? AND AssignedTO = ?");
            $stmt->bind_param("ss", $taskID, $taskOrgCode);
            $stmt->execute();
            $stmt->close();
        }

        $UUID = $_SESSION['UUID'];
        if ($isFromTask) {
            $isFromTask = 1;
        } else {
            $isFromTask = 0;
        }
        $stmt = $conn->prepare("INSERT INTO minutemeetingdocuments (UUID, org_code, filedEnd, file_Size, file_path, taskID, isFromTask, MMdate, MMTimeStart, MMLocation, MMPresider, MMAttendees, MMAbsentees, MMAgenda, MMCommencement, MMTimeend, DocsPath, MMsignature, DateCreated) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sisisssssssssssssss", $UUID, $OrgCode, $FileNameEnd, $fileSize, $filepaths, $taskID, $isFromTask, $MM_DATE, $MM_TIMESTARTED, $MM_LOC, $MM_PRESIDER, $MM_ATTENDEES, $MM_ABSENTEES, $MM_AGENDA, $MM_COMMENCEMENT, $MM_TIMEADJOURNED, $MM_DOCS_PATH, $MM_SIGNATURE, $currentDateTime);
        $stmt->execute();
        $stmt->close();

        response(['status' => 'success', 'message' => 'Minutes of the Meeting successfully uploaded!', 'filepaths' => $filepaths]);
    }
} catch (\Throwable $th) {
    response(['status' => 'error', 'message' => $th->getMessage() . ' on line ' . $th->getLine()]);
}
