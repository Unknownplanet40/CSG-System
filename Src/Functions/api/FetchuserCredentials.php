<?php
date_default_timezone_set('Asia/Manila');
function fetchUserCredentials($conn, $studentNumber, $device)
{
    $stmt = $conn->prepare("SELECT * FROM usercredentials WHERE student_Number = ?");
    $stmt->bind_param("i", $studentNumber);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    $data = [];
    $Image = "";

    $date = date('Y-m-d', 0);
    $time = date('H:i:s');

    session_regenerate_id();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {

            $deviceType = $device['device'];
            $deviceDetails = $device['deviceDetails'];
            $ipData = file_get_contents("https://api.ipify.org?format=json");
            $ipData = json_decode($ipData, true);
            $IPADDRESS = $ipData['ip'];
            $dateMade = date('Y-m-d H:i:s');
            $UUID = $row['UUID'];
            $summary = "User " . $row['First_Name'] . " " . $row['Last_Name'] . " has logged in to the system. using " . $deviceType . " with IP Address " . $IPADDRESS . " on " . date('F j, Y') . " at " . date('h:i A');

            $stmt = $conn->prepare("INSERT INTO auditdevice (UUID, Device, DeviceDetails, IPaddress, dateMade, summary) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $UUID, $deviceType, $deviceDetails, $IPADDRESS, $dateMade, $summary);
            $stmt->execute();
            $stmt->close();

            $stmt = $conn->prepare("SELECT * FROM userprofile WHERE UUID = ?");
            $stmt->bind_param("s", $row['UUID']);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();

            if ($result->num_rows > 0) {
                $Profile = $result->fetch_assoc();
                $Image = $Profile['imagePath'] . "." . $Profile['imageExt'];
            } else {
                $Image = "Default-Profile.gif";
            }

            $stmt = $conn->prepare("SELECT * FROM userpositions WHERE UUID = ?");
            $stmt->bind_param("s", $row['UUID']);
            $stmt->execute();
            $pos = $stmt->get_result();
            $stmt->close();

            if ($pos->num_rows > 0) {
                $position = $pos->fetch_assoc();
                $role = $position['role'];
                $org_position = $position['org_position'];
                $org_ID = $position['org_code'];
            } else {
                $role = 3;
                $org_position = null;
                $org_ID = null;
            }

            $stmt = $conn->prepare("SELECT * FROM usersettings WHERE UUID = ?");
            $stmt->bind_param("s", $row['UUID']);
            $stmt->execute();
            $set = $stmt->get_result();
            $stmt->close();

            if ($set->num_rows > 0) {
                $settings = $set->fetch_assoc();
                $theme = $settings['theme'];
                $BobbleBG = $settings['bobble_BG'];
            } else {
                $theme = 'auto';
                $BobbleBG = 0;
            }

            if ($row['contactNumber'] == null) {
                $row['contactNumber'] = "";
            } else {
                $row['contactNumber'] = $row['contactNumber'];
            }

            if ($row['course_code'] == null) {
                $row['course_code'] = "";
            } else {
                $stmt = $conn->prepare("SELECT * FROM sysacadtype WHERE course_code = ?");
                $stmt->bind_param("i", $row['course_code']);
                $stmt->execute();
                $course = $stmt->get_result();
                $stmt->close();

                if ($course->num_rows > 0) {
                    $course = $course->fetch_assoc();
                    $row['course_code'] = $course['course_short_name'] . " - " . $course['year'] . "" . $course['section'];
                } else {
                    $row['course_code'] = "";
                }
            }

            $_SESSION['UUID'] = $row['UUID'];
            $_SESSION['FirstName'] = $row['First_Name'];
            $_SESSION['LastName'] = $row['Last_Name'];
            $_SESSION['PrimaryEmail'] = $row['primary_email'];
            $_SESSION['student_Number'] = $row['student_Number'];
            $_SESSION['Password'] = $row['password'];
            $_SESSION['isLogged'] = $row['isLogin'];
            $_SESSION['sessionID'] = $row['sessionID'];
            $_SESSION['Created_On'] = $row['created_at'];
            $_SESSION['ProfileImage'] = $Image;
            $_SESSION['role'] = $role;
            $_SESSION['theme'] = $theme;
            $_SESSION['useBobbleBG'] = $BobbleBG;
            $_SESSION['contactNumber'] = $row['contactNumber'];
            $_SESSION['course_code'] = $row['course_code'];
            $_SESSION['accountStat'] = $row['accountStat'];
            $_SESSION['FullName'] = $row['fullName'];
            $_SESSION['org_position'] = $org_position;
            $_SESSION['org_Code'] = $org_ID;
        }

        $data = [
            'UUID' => $_SESSION['UUID'],
            'FirstName' => $_SESSION['FirstName'],
            'LastName' => $_SESSION['LastName'],
            'PrimaryEmail' => $_SESSION['PrimaryEmail'],
            'student_Number' => $_SESSION['student_Number'],
            'isLogged' => $_SESSION['isLogged'],
            'Created_On' => $_SESSION['Created_On'],
            'SessionID' => $_SESSION['sessionID'],
            'ProfileImage' => $_SESSION['ProfileImage'],
            'role' => $_SESSION['role'],
            'theme' => $_SESSION['theme'],
            'useBobbleBG' => $_SESSION['useBobbleBG'],
            'contactNumber' => $_SESSION['contactNumber'],
            'course_code' => $_SESSION['course_code'],
            'accountStat' => $_SESSION['accountStat'],
            'FullName' => $_SESSION['FullName'],
            'org_position' => $_SESSION['org_position'],
            'org_Code' => $_SESSION['org_Code']
        ];
        unset($_SESSION['sessionID']);
        return $data;
    } else {
        return null;
    }
}
