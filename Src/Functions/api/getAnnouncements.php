<?php

session_start();

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

    if (!isset($_SESSION['UUID'])) {
        response(['status' => 'error', 'message' => 'Unauthorized']);
    }

    if (isset($_GET['UUID']) && isset($_GET['type'])) {
        switch ($_GET['type']) {
            case 'high':
                $stmt = $conn->prepare("SELECT * FROM userannouncement WHERE postedBy = ? AND priority = 3 AND isDeleted = 0 ORDER BY postedDate DESC, RAND() LIMIT 10");
                $stmt->bind_param("s", $_GET['UUID']);
                break;
            case 'normal':
                $stmt = $conn->prepare("SELECT * FROM userannouncement WHERE postedBy = ? AND priority = 2 AND isDeleted = 0 ORDER BY postedDate DESC, RAND() LIMIT 10");
                $stmt->bind_param("s", $_GET['UUID']);
                break;
            case 'low':
                $stmt = $conn->prepare("SELECT * FROM userannouncement WHERE postedBy = ? AND priority = 1 AND isDeleted = 0 ORDER BY postedDate DESC, RAND() LIMIT 10");
                $stmt->bind_param("s", $_GET['UUID']);
                break;
            case 'deleted':
                $stmt = $conn->prepare("SELECT * FROM userannouncement WHERE postedBy = ? AND isDeleted = 1 ORDER BY postedDate DESC, RAND() LIMIT 10");
                $stmt->bind_param("s", $_GET['UUID']);
                break;
            default:
                $stmt = $conn->prepare("SELECT * FROM userannouncement WHERE isDeleted = 0 ORDER BY priority DESC, postedDate DESC, RAND() LIMIT 10");
                break;
        }
    } elseif (isset($_GET['UUID'])) {
        $stmt = $conn->prepare("SELECT * FROM userannouncement WHERE postedBy = ? AND isDeleted = 0 ORDER BY priority DESC, postedDate DESC, RAND() LIMIT 10");
        $stmt->bind_param("s", $_GET['UUID']);
    } else {
        $stmt = $conn->prepare("SELECT * FROM userannouncement WHERE isDeleted = 0 ORDER BY priority DESC, postedDate DESC, RAND() LIMIT 10");
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    $data = [];
    $fullName = "";
    $image = "";
    $likeBy = [];
    $DislikeBy = [];
    $likeBy_Name = [];
    $dislikeBy_Name = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $stmt = $conn->prepare("SELECT * FROM usercredentials WHERE UUID = ?");
            $stmt->bind_param("s", $row['postedBy']);
            $stmt->execute();
            $result2 = $stmt->get_result();
            $stmt->close();

            if ($result2->num_rows > 0) {
                $user = $result2->fetch_assoc();
                $fullName = $user['First_Name'] . " " . $user['Last_Name'];

                $stmt = $conn->prepare("SELECT * FROM userpositions WHERE UUID = ?");
                $stmt->bind_param("s", $row['postedBy']);
                $stmt->execute();
                $result3 = $stmt->get_result();
                $stmt->close();

                if ($result3->num_rows > 0) {
                    $position = $result3->fetch_assoc();
                    $Role = $position['role'];

                    if ($position['org_code'] != null) {
                        $stmt = $conn->prepare("SELECT * FROM sysorganizations WHERE org_code = ?");
                        $stmt->bind_param("s", $position['org_code']);
                        $stmt->execute();
                        $org = $stmt->get_result();
                        $stmt->close();

                        if ($org->num_rows > 0) {
                            $org = $org->fetch_assoc();
                            $OrgShortName = $org['org_short_name'] . " - Officer";
                        } else {
                            $OrgShortName = "Officer";
                        }
                    } else {
                        $OrgShortName = "";
                    }
                } else {
                    $Role = 3;
                }

                $stmt = $conn->prepare("SELECT * FROM userprofile WHERE UUID = ?");
                $stmt->bind_param("s", $row['postedBy']);
                $stmt->execute();
                $result3 = $stmt->get_result();
                $stmt->close();

                if ($result3->num_rows > 0) {
                    $profile = $result3->fetch_assoc();
                    $image = $profile['imagePath'] . "." . $profile['imageExt'];
                } else {
                    $image = "Default-Profile.gif";
                }

                // convert the likeBy and dislikeBy to array
                if ($row['likeBy'] != "") {
                    $likeBy = explode(",", $row['likeBy']);
                    // for each likeBy, get the user's name
                    foreach ($likeBy as $key => $value) {
                        $stmt = $conn->prepare("SELECT * FROM usercredentials WHERE UUID = ?");
                        $stmt->bind_param("s", $value);
                        $stmt->execute();
                        $result5 = $stmt->get_result();
                        $stmt->close();

                        if ($result5->num_rows > 0) {
                            $user = $result5->fetch_assoc();
                            if ($_SESSION['UUID'] == $value) {
                                $likeBy_Name[$key] = "You";
                            } else {
                                $likeBy_Name[$key] = $user['First_Name'] . " " . $user['Last_Name'];
                            }
                            $likeBy[$key] = $user['UUID'];
                        }
                    }
                } else {
                    $likeBy = [];
                }

                if ($row['dislikeBy'] != "") {
                    $dislikeBy = explode(",", $row['dislikeBy']);

                    foreach ($dislikeBy as $key => $value) {
                        $stmt = $conn->prepare("SELECT * FROM usercredentials WHERE UUID = ?");
                        $stmt->bind_param("s", $value);
                        $stmt->execute();
                        $result6 = $stmt->get_result();
                        $stmt->close();

                        if ($result6->num_rows > 0) {
                            $user = $result6->fetch_assoc();
                            if ($_SESSION['UUID'] == $value) {
                                $dislikeBy_Name[$key] = "You";
                            } else {
                                $dislikeBy_Name[$key] = $user['First_Name'] . " " . $user['Last_Name'];
                            }
                            $dislikeBy[$key] = $user['UUID'];
                        }
                    }
                } else {
                    $dislikeBy = [];
                }

                if (!isset($_GET['type']) && !isset($_GET['UUID'])) {
                    if ($Role == 1) {
                        $fullName = strtoupper("System Administator");
                    } elseif ($Role == 2) {
                        $fullName = $fullName . " - (" . $OrgShortName . ")";
                    } else {
                        $fullName = $fullName;
                    }
                }

                // for testing purposes loop data 5 times to see the effect of the infinite scroll

                /* for ($i = 0; $i < 5; $i++) {
                    $data[] = [
                        'postID' => $row['postID'],
                        'postedBy' => $fullName,
                        'postStatus' => $row['postStatus'],
                        'priority' => $row['priority'] == 1 ? 'LowPriority' : ($row['priority'] == 2 ? 'NormPriority' : 'HighPriority'),
                        'postContent' => $row['postContent'],
                        'postLikes' => $row['postLikes'],
                        'postDislikes' => $row['postDislikes'],
                        'likeBy' => $likeBy,
                        'dislikeBy' => $dislikeBy,
                        'likeBy_Name' => $likeBy_Name,
                        'dislikeBy_Name' => $dislikeBy_Name,
                        'isDeleted' => $row['isDeleted'],
                        'postedDate' => date('F j, Y', strtotime($row['postedDate'])),
                        'profileImage' => $image
                    ];
                } */

                $data[] = [
                    'postID' => $row['postID'],
                    'postedBy' => $fullName,
                    'postStatus' => $row['postStatus'],
                    'priority' => $row['priority'] == 1 ? 'LowPriority' : ($row['priority'] == 2 ? 'NormPriority' : 'HighPriority'),
                    'postContent' => $row['postContent'],
                    'postLikes' => $row['postLikes'],
                    'postDislikes' => $row['postDislikes'],
                    'likeBy' => $likeBy,
                    'dislikeBy' => $dislikeBy,
                    'likeBy_Name' => $likeBy_Name,
                    'dislikeBy_Name' => $dislikeBy_Name,
                    'isDeleted' => $row['isDeleted'],
                    'postedDate' => date('F j, Y', strtotime($row['postedDate'])),
                    'profileImage' => $image
                ];
            }
        }

        response(['status' => 'success', 'data' => $data]);
    } else {
        response(['status' => 'error', 'message' => 'No announcements found']);
    }
} catch (Exception $e) {
    response(['status' => 'error', 'message' => $e->getMessage()]);
}
