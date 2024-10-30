<?php

function writeLog($logPath, $Type, $UUID, $Action, $ipAddress, $Status)
{
    if (!file_exists($logPath)) {
        $file = fopen($logPath, "w");
        fwrite($file, date('m/d/Y h:i:s A') . " - ------------------------------ File Created ------------------------------([INFO][DEBUG][ERROR][WARNING])\n");
        fclose($file);
    }
    $file = fopen($logPath, "a");

    if ($Action == "Login") {
        $IPADDRESS = $ipAddress;
    } else {
        if (str_contains($ipAddress, "::1")) {
            $ipData = file_get_contents("https://api.ipify.org?format=json");
            $ipData = json_decode($ipData, true);
            $IPADDRESS = $ipData['ip'];
        } else {
            if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                $IPADDRESS = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $IPADDRESS = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } else {
                $IPADDRESS = $_SERVER['REMOTE_ADDR'];
            }
        }
    }

    fwrite($file, date('m/d/Y h:i:s A') . " - [" . strtoupper($Type) . "] - User: " . $UUID . " - Action: " . $Action . " - IP: " . $IPADDRESS . " - Status: " . $Status . "\n");
}
