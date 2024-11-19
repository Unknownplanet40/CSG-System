<?php

if ($_SERVER['HTTP_HOST'] != 'localhost' && $_SERVER['HTTP_HOST'] == 'dodgerblue-tiger-498833.hostingersite.com') {
    $servername = "localhost";
    $dbname = "u558575596_csg_database";
    $username = "u558575596_csgsystem_root";
    $password = "@Csgdatabase2024";
} else {
    $servername = "localhost";
    $username = "csgsystem_root";
    $password = "a*D)aslmP/4mV4T5";
    $dbname = "csg_database";
}

$conn = mysqli_connect($servername, $username, $password, $dbname);
