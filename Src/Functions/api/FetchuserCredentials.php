<?php

date_default_timezone_set('Asia/Manila');

// this function is used to fetch the user credentials and save it to the session
function fetchUserCredentials($conn, $studentNumber)
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
            } else {
                $role = 3;
            }


            $_SESSION['UUID'] = $row['UUID'];
            $_SESSION['FirstName'] = $row['First_Name'];
            $_SESSION['LastName'] = $row['Last_Name'];
            $_SESSION['PrimaryEmail'] = $row['primary_email'];
            $_SESSION['student_Number'] = $row['student_Number'];
            $_SESSION['Password'] = password_hash($row['password'], PASSWORD_DEFAULT); // temporary fix for the password hashing
            // $_SESSION['Password'] = $row['password']; // note to self: remove this line when the password hashing is done
            $_SESSION['isLogged'] = $row['isLogin'];
            $_SESSION['sessionID'] = $row['sessionID'];
            $_SESSION['Created_On'] = $row['created_at'];
            $_SESSION['ProfileImage'] = $Image;
            $_SESSION['role'] = $role;
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
            'role' => $_SESSION['role']
        ];
        unset($_SESSION['sessionID']);
        return $data;
    } else {
        return null;
    }
}
