<?php
session_start();

$xml = simplexml_load_file("../Database/Database_Credentials.xml");

$dbhost = $xml->Credentials->Host;
$dbusername = $xml->Credentials->Username;
$dbpassword = $xml->Credentials->Password;
$dbname = $xml->Credentials->DatabaseName;

try {
    $conn = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbusername, $dbpassword);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn = null;
    $_SESSION['database_status'] = 'OK';
    header("location: ../Pages/Homepage.php");
} catch (PDOException $e) {
    if ($e->getCode() == 1049) {
        $_SESSION['error'] = '1049';
        header("location: ../Pages/Error.php");
    } elseif ($e->getCode() == 1045) {
        $_SESSION['error'] = '1045';
        header("location: ../Pages/Error.php");
    } elseif ($e->getCode() == 2002) {
        $_SESSION['error'] = '2002';
        header("location: ../Pages/Error.php");
    } elseif ($e->getCode() == 1044) {
        $_SESSION['error'] = '1044';
        header("location: ../Pages/Error.php");
    } else {
        $_SESSION['error'] = $e->getCode();
        $_SESSION['error_message'] = $e->getMessage();
        header("location: ../Pages/Error.php");
    }
}
