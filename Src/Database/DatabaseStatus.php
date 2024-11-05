<?php
session_start();

try {
    $conn = new PDO("mysql:host=localhost;dbname=csg_database;charset=utf8", "csgsystem_root", "a*D)aslmP/4mV4T5");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn = null;
    $_SESSION['database_status'] = 'OK';
    header("location: ../Pages/Homepage.php");
} catch (PDOException $e) {
    if ($e->getCode() == 1049) {
        $_SESSION['error'] = '1049';
        header("location: ../Pages/Error.html?Error=1049");
    } elseif ($e->getCode() == 1045) {
        $_SESSION['error'] = '1045';
        header("location: ../Pages/Error.html?Error=1045");
    } elseif ($e->getCode() == 2002) {
        $_SESSION['error'] = '2002';
        header("location: ../Pages/Error.html?Error=2002");
    } elseif ($e->getCode() == 1044) {
        $_SESSION['error'] = '1044';
        header("location: ../Pages/Error.html?Error=1044");
    } else {
        $_SESSION['error'] = $e->getCode();
        $_SESSION['error_message'] = $e->getMessage();
        header("location: ../Pages/Error.html?Error=0");
    }
}
