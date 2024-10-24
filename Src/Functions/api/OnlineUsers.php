<?php

require_once  "../../Database/Config.php";

header('Content-Type: application/json');
date_default_timezone_set('Asia/Manila');

function response($data)
{
    echo json_encode($data);
    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        response(['error' => 'Invalid request method']);
    }
    $ID = $_GET['UUID'];

    $stmt = $conn->prepare("SELECT * FROM systemmessages WHERE FromUser = ? OR toUser = ? ORDER BY dateUpdated DESC");
    $stmt->bind_param("ss", $ID, $ID);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    if ($result->num_rows > 0) {
        $data = [];
        $profile = '';
        $latestMessage = '';
        while ($row = $result->fetch_assoc()) {
            // get the user details
            $stmt = $conn->prepare("SELECT * FROM usercredentials WHERE UUID = ?");
            $userID = $row['fromUser'] == $ID ? $row['toUser'] : $row['fromUser'];
            $stmt->bind_param("s", $userID);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            // before adding the user details to the data array, check if the user is already in the array
            $userExists = false;

            foreach ($data as $key => $value) {
                if ($value['UUID'] == $user['UUID']) {
                    $userExists = true;
                    break;
                }
            }

            if ($userExists) {
                continue;
            }

            $stmt = $conn->prepare("SELECT * FROM systemmessages WHERE (FromUser = ? AND toUser = ?) OR (FromUser = ? AND toUser = ?) ORDER BY dateUpdated DESC LIMIT 1");
            $stmt->bind_param("ssss", $ID, $userID, $userID, $ID);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            $latestMessage = $row['userMessage'];

            $stmt = $conn->prepare("SELECT * FROM userprofile WHERE UUID = ?");
            $stmt->bind_param("s", $userID);
            $stmt->execute();
            $profile = $stmt->get_result();
            $stmt->close();

            if ($profile->num_rows > 0) {
                $profile = $profile->fetch_assoc();
                $profile = $profile['imagePath'] . "." . $profile['imageExt'];
            } else {
                $profile = 'Default-Profile.gif';
            }

            $data[] = [
                'UUID' => $user['UUID'],
                'First_Name' => $user['First_Name'],
                'Last_Name' => $user['Last_Name'],
                'primary_email' => $user['primary_email'],
                'isLogin' => $user['isLogin'],
                'latestMessage' => $latestMessage,
                'Sentby' => $row['fromUser'] == $ID ? 'You' : $user['First_Name'],
                'UMID' => $row['UMID'],
                'currentMessageDeleted' => $row['isDeleted'] == 1 ? true : false,
                'Date' => $row['dateUpdated'],
                'toUser_Profile' => $profile
            ];
        }

        response(['status' => 'success', 'data' => $data]);
    } else {
        response(['status' => 'info', 'message' => 'No friend found at the moment']);
    }
} catch (Exception $e) {
    response(['status' => 'error', 'message' => $e->getMessage()]);
}
