<?php

function writeLog($baseDir, $Type, $UUID, $Action, $ipAddress, $Status)
{
    $logPath = $baseDir;
    if (!file_exists($logPath)) {
        $file = fopen($logPath, "w");
        if ($file) {
            fwrite($file, date('m/d/Y h:i:s A') . " - ------------------------------ File Created ------------------------------([INFO][DEBUG][ERROR][WARNING])\n");
            fclose($file);
        } else {
            die("Error: Unable to create log file.");
        }
    }

    // Determine IP address
    $IPADDRESS = $ipAddress;
    if ($Action !== "Login" && $ipAddress === "::1") {
        $ipData = file_get_contents("https://api.ipify.org?format=json");
        $ipData = json_decode($ipData, true);
        $IPADDRESS = $ipData['ip'] ?? "Unknown";
    }

    // Write log entry
    $file = fopen($logPath, "a");
    if ($file) {
        $logEntry = sprintf(
            "%s - [%s] - User: %s - Action: %s - IP: %s - Status: %s\n",
            date('m/d/Y h:i:s A'),
            strtoupper($Type),
            $UUID,
            $Action,
            $IPADDRESS,
            $Status
        );
        fwrite($file, $logEntry);
        fclose($file);
    } else {
        die("Error: Unable to write to log file.");
    }
}

