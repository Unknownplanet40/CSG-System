<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include_once "../../Database/Config.php";
include_once "../../Debug/GenLog.php";

if (!isset($_SESSION['UUID'])) {
    header('Location: ../../Pages/Accesspage.php?error=001');
}

$currentSession = $_SESSION['UUID'];
try {
    $stmt  = $conn->prepare("UPDATE usercredentials SET isLogin = 0 WHERE UUID = ?");
    $stmt->bind_param("s", $currentSession);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("UPDATE accounts SET isLogin = 0 WHERE UUID = ?");
    $stmt->bind_param("s", $currentSession);
    $stmt->execute();
    $stmt->close();

    session_unset();
    session_destroy();

    if (isset($_GET['error'])) {
        switch ($_GET['error']) {
            case '001':
                header('Location: ../../Pages/Accesspage.php?error=001');
                break;
            case '002':
                header('Location: ../../Pages/Accesspage.php?error=002');
                break;
            case '003':
                header('Location: ../../Pages/Accesspage.php?error=003');
                break;
            default:
                header('Location: ../../Pages/Accesspage.php');
                break;
        }
    } else {
        if (isset($_GET['studentnum']) && isset($_GET['password'])) {
            // this id for auto login (Note: since the user is already logged in it will redirect to the Feed page) this feature is rarely used
            writeLog("../../Debug/Users/UUID.log", "INFO", $currentSession, "Auto-Login", $_SERVER['REMOTE_ADDR'], "Success");
            $studentnum = $_GET['studentnum'];
            $password = $_GET['password'];
            header('Location: ../../Pages/Accesspage.php?autoLogin=true&studentnum=' . $studentnum . '&password=' . $password);
        } else {
            writeLog("../../Debug/Users/UUID.log", "Info", $currentSession, "logout", $_SERVER['REMOTE_ADDR'], "Success");
            header('Location: ../../Pages/Accesspage.php');
        }
    }
} catch (\Throwable $th) {
    writeLog("../../Debug/Users/UUID.log", "Critical", $_SESSION['UUID'], "logout", $_SERVER['REMOTE_ADDR'], "Failed");
    echo "<script>localStorage.setItem('error_code', 1001);</script>";
    header('Location: ../../Pages/Error.html');
}
