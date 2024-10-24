<?php

function writeLog($logPath, $Type, $UUID, $Action, $ipAddress, $Status)
{
    if (!file_exists($logPath)) {
        $file = fopen($logPath, "w");
        fwrite($file, date('m/d/Y h:i:s A') . " - ------------------------------ File Created ------------------------------\n");
        fclose($file);
    }
    $file = fopen($logPath, "a");

    fwrite($file, date('m/d/Y h:i:s A') . " - [" . strtoupper($Type) . "] - User: " . $UUID . " - Action: " . $Action . " - IP: " . $ipAddress . " - Status: " . $Status . "\n");
}
