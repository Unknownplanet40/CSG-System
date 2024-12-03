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
        response(['error' => 'Invalid request method']);
    }

    $docID = $_POST['docID'];
    $document = $_POST['document'];
    $orgCode = $_POST['orgCode'];
    $UUID = $_SESSION['UUID'];
    $CurrentDate = date('Y-m-d H:i:s');

    if (!isset($docID) || !isset($document) || !isset($orgCode)) {
        response(['status' => 'error', 'message' => 'Invalid request']);
    }

    $conn->begin_transaction();
    if ($document == "D-AP") {
        $stmt = $conn->prepare("UPDATE activityproposaldocuments SET isSubmittedtoCSG = 1 WHERE fileID = ?");
        $stmt->bind_param("s", $docID);
        $stmt->execute();
        $stmt->close();
    } else if ($document == "D-EL") {
        $stmt = $conn->prepare("UPDATE excuseletterdocuments SET isSubmittedtoCSG = 1 WHERE fileID = ?");
        $stmt->bind_param("s", $docID);
        $stmt->execute();
        $stmt->close();
    } else if ($document == "D-MM") {
        $stmt = $conn->prepare("UPDATE minutemeetingdocuments SET isSubmittedtoCSG = 1 WHERE fileID = ?");
        $stmt->bind_param("s", $docID);
        $stmt->execute();
        $stmt->close();
    } else if ($document == "D-OM") {
        $stmt = $conn->prepare("UPDATE officememorandomdocuments SET isSubmittedtoCSG = 1 WHERE fileID = ?");
        $stmt->bind_param("s", $docID);
        $stmt->execute();
        $stmt->close();
    } else if ($document == "D-PP") {
        $stmt = $conn->prepare("UPDATE projectproposaldocuments SET isSubmittedtoCSG = 1 WHERE fileID = ?");
        $stmt->bind_param("s", $docID);
        $stmt->execute();
        $stmt->close();
    } else  {
        $conn->rollback();
        response(['status' => 'error', 'message' => 'Invalid document']);
    }

    $stmt = $conn->prepare("INSERT INTO documentapproval (fileID, submittedBy, org_code, document, DateCreated) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $docID, $UUID, $orgCode, $document, $CurrentDate);
    $stmt->execute();
    if ($stmt->affected_rows === 1) {
        $conn->commit();
        $stmt->close();
        response(['status' => 'success', 'message' => 'Document has been Submitted']);
    } else {
        $conn->rollback();
        response(['status' => 'error', 'message' => 'Failed to submit document']);
    }
} catch (Exception $e) {
    $conn->rollback();
    response(['status' => 'error', 'message' => 'an error occured: ' . $e.getMessage()]);
}