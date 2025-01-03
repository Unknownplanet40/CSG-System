<?php

date_default_timezone_set('Asia/Manila');

// Determine the log file path for the current month
$month = date('Y-m');
$logpath = "../../../Debug/Users/" . $month . ".log";

// Create a new log file if it doesn't exist, and add a header
if (!file_exists($logpath)) {
    $file = fopen($logpath, "w");
    if ($file) {
        fwrite($file, date('m/d/Y h:i:s A') . " - ------------------------------ File Created --------------------------------\n");
        fclose($file);
    } else {
        die("Error: Unable to create log file.");
    }
}

// Process the log file if it exists
if (file_exists($logpath)) {
    $logEntries = file($logpath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $logEntries = array_reverse($logEntries); // Reverse order to show the latest logs first
    $counter = 1;

    // Clear the existing log list on the frontend
    echo "<script>$('#logList').empty();</script>";

    foreach ($logEntries as $line) {
        $lineParts = explode(" - ", $line);

        // Ensure the log entry has the expected format
        if (count($lineParts) >= 6) {
            $date = $lineParts[0];
            $severity = 'secondary'; // Default severity

            if (strpos($lineParts[1], 'INFO') !== false) {
                $severity = 'success';
            } elseif (strpos($lineParts[1], 'WARN') !== false) {
                $severity = 'warning';
            } elseif (strpos($lineParts[1], 'ERROR') !== false) {
                $severity = 'danger';
            }

            $userUUID = str_replace('User: ', '', $lineParts[2]);

            // Fetch user details from the database
            $stmt = $conn->prepare("SELECT fullName FROM usercredentials WHERE UUID = ?");
            $stmt->bind_param("s", $userUUID);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();

            $userDisplay = '<span class="text-muted">Unknown</span>'; // Default user display
            if ($result->num_rows > 0) {
                $userDisplay = '<span class="fw-bold" title="' . htmlspecialchars($userUUID) . '" style="cursor: pointer;">' . htmlspecialchars($result->fetch_assoc()['fullName']) . '</span>';
            }

            $action = str_replace('Action:', '', $lineParts[3]);
            $ip = ($lineParts[4] === 'IP: ::1') ? '<span class="text-muted">Localhost</span>' : str_replace('IP:', '', $lineParts[4]);
            $info = str_replace('Status:', '', $lineParts[5]);

            // Display the log entry
            echo "<a class='list-group-item list-group-item-action bg-transparent'>
                    <div class='d-flex w-100 justify-content-between'>
                        <h5 class='mb-1'>$counter. <span class='fw-bolder'>" . htmlspecialchars($action) . "</span></h5>
                        <small>" . date('d F Y', strtotime($date)) . "</small>
                    </div>
                    <p class='mb-1'>$userDisplay</p>
                    <div class='hstack gap-1'>
                        <small>" . htmlspecialchars($ip) . "</small>
                        <small class='ms-auto badge rounded-1 text-uppercase text-bg-$severity'>" . htmlspecialchars($info) . "</small>
                    </div>
                </a>";
            $counter++;
        }
    }
} else {
    // Display a message if no log file is found
    echo "<a class='list-group-item list-group-item-action'>
            <div class='d-flex w-100 justify-content-between'>
                <h5 class='mb-1'>No logs found</h5>
            </div>
          </a>";
}

?>
