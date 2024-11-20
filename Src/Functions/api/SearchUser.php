<?php

session_start();

require_once  "../../Database/Config.php";
require_once './FetchuserCredentials.php';

header('Content-Type: application/json');
date_default_timezone_set('Asia/Manila');

function response($data)
{
    echo json_encode($data);
    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        response(['error' => 'Invalid request method']);
    }

    // Accessing form data via $_POST
    if (empty($_POST) || !isset($_POST['search'])) {
        response(['error' => 'Invalid request data']);
    }

    $search = $_POST['search'];
    $wildcard = "%$search%"; // the first name with last name combination
    $stmt = $conn->prepare("SELECT * FROM usercredentials WHERE accountStat = 'active' AND fullName LIKE ? ORDER BY First_Name ASC");
    $stmt->bind_param("s", $wildcard);
    $stmt->execute();
    $data = [];
    $profile = "";
    $position_Out = "";
    $org_Out = "";

    $result = $stmt->get_result();
    $stmt->close();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {

            if (isset($_SESSION['UUID']) && $row['UUID'] == $_SESSION['UUID']) {
                continue;
            }

            $stmt = $conn->prepare("SELECT * FROM userprofile WHERE UUID = ?");
            $stmt->bind_param("s", $row['UUID']);
            $stmt->execute();
            $profile = $stmt->get_result();
            $stmt->close();

            if ($profile->num_rows > 0) {
                $profile = $profile->fetch_assoc();
                $profile = $profile['imagePath'] . "." . $profile['imageExt'];
            } else {
                $profile = "Default-Profile.gif";
            }

            $stmt = $conn->prepare("SELECT * FROM userpositions WHERE UUID = ?");
            $stmt->bind_param("s", $row['UUID']);
            $stmt->execute();
            $position = $stmt->get_result();
            $stmt->close();


            if ($_SESSION['role'] > 2) {
                if ($position->num_rows > 0) {
                    $position = $position->fetch_assoc();
                    if ($position['org_code'] != $_SESSION['org_Code']) {
                        if ($position['org_position'] != $_SESSION['org_position']) {
                            continue;
                        }
                    }

                    if ($position['org_position'] == 1) {
                        $position_Out = "President";
                    } elseif ($position['org_position'] == 2) {
                        $position_Out = "Vice President Internal";
                    } elseif ($position['org_position'] == 3) {
                        $position_Out = "Vice President External";
                    } else {
                        $position_Out = "Secretary";
                    }

                    $stmt = $conn->prepare("SELECT * FROM sysorganizations WHERE org_code = ?");
                    $stmt->bind_param("i", $position['org_code']);
                    $stmt->execute();
                    $org = $stmt->get_result();
                    $stmt->close();

                    if ($org->num_rows > 0) {
                        $org = $org->fetch_assoc();
                        $org_Out = $org['org_short_name'];
                    }
                }
            } else {
                if ($position->num_rows > 0) {
                    $position = $position->fetch_assoc();

                    if ($position['org_position'] == 1) {
                        $position_Out = "President";
                    } elseif ($position['org_position'] == 2) {
                        $position_Out = "Vice President Internal";
                    } elseif ($position['org_position'] == 3) {
                        $position_Out = "Vice President External";
                    } elseif ($position['org_position'] == 4) {
                        $position_Out = "Secretary";
                    } else {
                        $position_Out = "";
                    }

                    if ($position['org_code'] != null) {
                        $stmt = $conn->prepare("SELECT * FROM sysorganizations WHERE org_code = ?");
                        $stmt->bind_param("i", $position['org_code']);
                        $stmt->execute();
                        $org = $stmt->get_result();
                        $stmt->close();

                        if ($org->num_rows > 0) {
                            $org = $org->fetch_assoc();
                            $org_Out = $org['org_short_name'];
                        }
                    } else {
                        $org_Out = "Administator";
                    }
                }
            }


            $data[] = [
                'UUID' => $row['UUID'],
                'First_Name' => $row['First_Name'],
                'Last_Name' => $row['Last_Name'],
                'primary_email' => $row['primary_email'],
                'isLogin' => $row['isLogin'],
                'fullName' => $row['fullName'],
                'profile' => $profile,
                'position' => $position_Out,
                'org' => $org_Out
            ];
        }

        response(['success' => 'Search Results', 'data' => $data]);
    } else {
        response(['error' => 'No results found']);
    }

} catch (Exception $e) {
    response(['error' => 'An error occurred: ' . $e->getMessage()]);
}
