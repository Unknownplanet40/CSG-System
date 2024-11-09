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

    if (!isset($_POST['UUID'])) {
        response(['status' => 'error', 'message' => 'Invalid request Data']);
    }

    $UUID = $_POST['UUID'];

    if ($_SESSION['role'] == 1) {
        response(['status' => 'success', 'cover' => '../../Assets/Images/Default-Cover.gif']);
    }

    $stmt = $conn->prepare("SELECT * FROM usercover WHERE UUID = ?");
    $stmt->bind_param("s", $UUID);
    $stmt->execute();
    $coverPhoto = $stmt->get_result();
    $stmt->close();

    if ($coverPhoto->num_rows > 0) {
        // Kapag may cover photo si user
        $cover = $coverPhoto->fetch_assoc();
        $cover = '../../Assets/Images/UserCovers/' . $cover['imagePath'] . '.' . $cover['imageExt'];
        response(['status' => 'success', 'cover' => $cover]);
    } else {
        // if wala gamitin yung default cover base sa org
        $stmt = $conn->prepare("SELECT * FROM userpositions WHERE UUID = ?");
        $stmt->bind_param("s", $UUID);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {
            $org = $result->fetch_assoc();

            $stmt = $conn->prepare("SELECT * FROM sysorganizations WHERE org_code = ?");
            $stmt->bind_param("s", $org['org_code']);
            $stmt->execute();
            $orgs = $stmt->get_result();
            $stmt->close();

            if ($orgs->num_rows > 0) {
                $org = $orgs->fetch_assoc();
                $shortName = $org['org_short_name'];

                $stmt = $conn->prepare("SELECT * FROM sysorgcoverphoto WHERE org_code = ?");
                $stmt->bind_param("s", $org['org_code']);
                $stmt->execute();
                $coverPhoto = $stmt->get_result();
                $stmt->close();

                if ($coverPhoto->num_rows > 0) {
                    $cover = $coverPhoto->fetch_assoc();
                    $cover = 'Default-' . $shortName . '-' . $cover['useFor'] . '.' . $cover['imageExt'];
                    response(['status' => 'success', 'cover' => $cover]);
                }
            }
        }

        response(['status' => 'success', 'cover' => 'Default-Cover.gif']);
    }
} catch (Exception $e) {
    response(['status' => 'error', 'message' => 'Failed to get cover photo (' . $e->getMessage() . ')']);
}