<?php

session_start();

require_once  "../../Database/Config.php";

header('Content-Type: application/json');
date_default_timezone_set('Asia/Manila');

function response($data)
{
    echo json_encode($data);
    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        response(['status' => 'error', 'message' => 'Invalid request method']);
    }

    $LeftLogo = $_FILES['LeftLogo'];
    $RightLogo = $_FILES['RightLogo'];
    $FirstLine = $_POST['FirstLine'];
    $SecondLine = $_POST['SecondLine'];
    $ThirdLine = $_POST['ThirdLine'];
    $FourthLine = $_POST['FourthLine'];
    $FifthLine = $_POST['FifthLine'];
    $SixthLine = $_POST['SixthLine'];
    $orgCode = $_SESSION['org_Code'] ?? $_POST['OrgCode'];

    // check if already exist
    $stmt  = $conn->prepare("SELECT * FROM orgdocumetheader WHERE org_code = ? ");
    $stmt->bind_param("s", $orgCode);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    if ($result->num_rows > 0) {
        $folder = "../../../Assets/Images/pdf-Resource/OrgFolder/$orgCode/";
        $LeftLogo = $_FILES['LeftLogo'];
        $RightLogo = $_FILES['RightLogo'];

        if (!file_exists($folder)) {
            if (!mkdir($folder, 0777, true)) {
                throw new Exception("Failed to create directory");
            }
        }
        $LeftLogoName = $folder . "LeftLogo.png";
        $RightLogoName = $folder . "RightLogo.png";

        if (!empty($LeftLogo['tmp_name'])) {
            if (!in_array($LeftLogo['type'], ['image/png'])) {
                throw new Exception("Left logo must be PNG");
            }
            if (file_exists($LeftLogoName)) {
                unlink($LeftLogoName);
            }
            if (!move_uploaded_file($LeftLogo['tmp_name'], $LeftLogoName)) {
                throw new Exception("Failed to upload left logo");
            }
        }

        if (!empty($RightLogo['tmp_name'])) {
            if (!in_array($RightLogo['type'], ['image/png'])) {
                throw new Exception("Right logo must be PNG");
            }
            if (file_exists($RightLogoName)) {
                unlink($RightLogoName);
            }
            if (!move_uploaded_file($RightLogo['tmp_name'], $RightLogoName)) {
                throw new Exception("Failed to upload right logo");
            }
        }
        //ID	org_code	left_Image	right_Image	firstLine	secondLine	thirdLine	fourthLine	fifthLine	sixthLine	lastModifiedDate	


        $stmt = $conn->prepare("UPDATE orgdocumetheader SET left_Image = ?, right_Image = ?, firstLine = ?, secondLine = ?, thirdLine = ?, fourthLine = ?, fifthLine = ?, sixthLine = ? WHERE org_code = '" . $orgCode . "' ");
        $stmt->bind_param("ssssssss", $LeftLogoName, $RightLogoName, $FirstLine, $SecondLine, $ThirdLine, $FourthLine, $FifthLine, $SixthLine);
        $stmt->execute();
        $stmt->close();

        response(['status' => 'success', 'message' => 'Document Header Updated Successfully']);
    } else {
        $folder = "../../../Assets/Images/pdf-Resource/OrgFolder/$orgCode/";
        $LeftLogo = $_FILES['LeftLogo'];
        $RightLogo = $_FILES['RightLogo'];

        if (!file_exists($folder)) {
            if (!mkdir($folder, 0777, true)) {
                throw new Exception("Failed to create directory");
            }
        }
        $LeftLogoName = $folder . "LeftLogo.png";
        $RightLogoName = $folder . "RightLogo.png";

        if (!empty($LeftLogo['tmp_name'])) {
            if (!in_array($LeftLogo['type'], ['image/png'])) {
                throw new Exception("Left logo must be PNG");
            }
            if (!move_uploaded_file($LeftLogo['tmp_name'], $LeftLogoName)) {
                throw new Exception("Failed to upload left logo");
            }
        }

        if (!empty($RightLogo['tmp_name'])) {
            if (!in_array($RightLogo['type'], ['image/png'])) {
                throw new Exception("Right logo must be PNG");
            }
            if (!move_uploaded_file($RightLogo['tmp_name'], $RightLogoName)) {
                throw new Exception("Failed to upload right logo");
            }
        }

        $stmt = $conn->prepare("INSERT INTO orgdocumetheader (org_code, left_Image, right_Image, firstLine, secondLine, thirdLine, fourthLine, fifthLine, sixthLine) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssssss", $orgCode, $LeftLogoName, $RightLogoName, $FirstLine, $SecondLine, $ThirdLine, $FourthLine, $FifthLine, $SixthLine);
        $stmt->execute();
        $stmt->close();

        response(['status' => 'success', 'message' => 'Document Header Saved Successfully']);
    }


} catch (Exception $e) {
    response(['status' => 'error', 'message' => $e->getMessage()]);
}
