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

    if (!isset($_SESSION['UUID'])) {
        response(['status' => 'error', 'message' => 'Unauthorized request']);
    }

    $UUID = $_SESSION['UUID'];

    // check if user settings exist
    $stmt = $conn->prepare("SELECT * FROM usersettings WHERE UUID = ?");
    $stmt->bind_param("s", $UUID);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    if ($result->num_rows === 0) {
        // create user settings
        $stmt = $conn->prepare("INSERT INTO usersettings (UUID, theme, bobble_BG) VALUES (?, 'auto', 0)");
        $stmt->bind_param("s", $UUID);
        $stmt->execute();
        $stmt->close();

        $_SESSION['theme'] = 'auto';
        $_SESSION['useBobbleBG'] = 0;
    }

    if (isset($_POST['theme'])) {
        $theme = $_POST['theme'];
        $stmt = $conn->prepare("UPDATE usersettings SET theme = ? WHERE UUID = ?");
        $stmt->bind_param("ss", $theme, $UUID);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        $_SESSION['theme'] = $theme;
        response(['status' => 'success', 'message' => 'Theme changed to ' . $theme . ' successfully']);
    } else if (isset($_POST['Background'])) {
        $Background = $_POST['Background'];
        $stmt = $conn->prepare("UPDATE usersettings SET bobble_BG = ? WHERE UUID = ?");
        $stmt->bind_param("ss", $Background, $UUID);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        $_SESSION['useBobbleBG'] = $Background;
        response(['status' => 'success', 'message' => 'Background changed to ' . $Background . ' successfully']);
    } else {
        response(['status' => 'error', 'message' => 'Invalid request']);
    }


} catch (Exception $e) {
    response(['status' => 'error', 'message' =>  $e->getMessage()]);
}
