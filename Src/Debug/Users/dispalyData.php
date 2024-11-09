<?php

$logpath = "../../../Debug/Users/UUID.log";

if (file_exists($logpath)) {
    $log = file($logpath);
    $log = array_reverse($log);
    $log = array_slice($log, 0);
    $counter = 1;
    echo "<script>$('#logList').empty();</script>"; // Clear the list
    $Userlist = [];
    foreach ($log as $line) {
        $line = explode(" - ", $line);

        if (count($line) >= 6) {
            $date = $line[0];

            $severity = 'secondary';
            if (strpos($line[1], 'INFO') !== false) {
                $severity = 'success';
            } elseif (strpos($line[1], 'WARN') !== false) {
                $severity = 'warning';
            } elseif (strpos($line[1], 'ERROR') !== false) {
                $severity = 'danger';
            }

            $user = str_replace('User: ', '', $line[2]);

            $stmt = $conn->prepare("SELECT * FROM usercredentials WHERE UUID = ?");
            $stmt->bind_param("s", $user);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();

            if ($result->num_rows > 0) {
                $user = '<span class="fw-bold" title="' . $user . '" style="cursor: pointer;">'. $result->fetch_assoc()['fullName'] .'</span>';
            } else {
                $user = '<span class="text-muted">Unknown</span>';
            }


            $action = str_replace('Action:', '', $line[3]);
            if ($line[4] == 'IP: ::1') {
                $ip = '<span class="text-muted">Localhost</span>';
            } else {
                $ip = str_replace('IP:', '', $line[4]);
            }
            $info = str_replace('Status:', '', $line[5]);
            /* if (!in_array($user, $Userlist)) {
                array_push($Userlist, $user); */

            echo "<a class='list-group-item list-group-item-action bg-transparent'>
                    <div class='d-flex w-100 justify-content-between'>
                        <h5 class='mb-1'>$counter. <span class='fw-bolder'>$action</span></h5>
                        <small>" . date('d F Y', strtotime($date)) . "</small>
                    </div>
                    <p class='mb-1'>$user</p>
                    <div class='hstack gap-1'>
                        <small>$ip</small>
                        <small class='ms-auto badge rounded-1 text-uppercase text-bg-$severity'>$info</small>
                    </div>
                </a>";
            $counter++;

            //}
        }
    }
} else {
    echo "<a class='list-group-item list-group-item-action'>
                    <div class='d-flex w-100 justify-content-between'>
                        <h5 class='mb-1'>No logs found</h5>
                    </div>
                  </a>";
}
