<?php

if ($_SERVER['HTTP_HOST'] != 'localhost' && $_SERVER['HTTP_HOST'] == 'dodgerblue-tiger-498833.hostingersite.com') {
    $servername = "srv1637.hstgr.io";
    $username = "u558575596_csgsystem_root";
    $password = "@Csgdatabase2024";
    $dbname = "u558575596_csg_database";
} else {
    $servername = "localhost";
    $username = "csgsystem_root";
    $password = "a*D)aslmP/4mV4T5";
    $dbname = "csg_database";
}

$conn = mysqli_connect($servername, $username, $password, $dbname);
