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
    $UUID = $_POST['UUID'];
    $FirstName = $_POST['FirstName'];
    $LastName = $_POST['LastName'];
    $studentNumber = $_POST['studentNumber'];
    $course = $_POST['course'] ?? null;
    $email = $_POST['email'];
    $contactNumber = $_POST['contactNumber'];
    $profileImage = $_FILES['profileImage'] ?? null;

    if ($profileImage != null) {
        if ($profileImage['error'] === UPLOAD_ERR_OK) {
            $file = $UUID . "_" . date("YmdHis") . "_" . $profileImage['name'];
            $path = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . "Assets" . DIRECTORY_SEPARATOR . "Images" . DIRECTORY_SEPARATOR . "UserProfiles" . DIRECTORY_SEPARATOR;
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            if (move_uploaded_file($profileImage['tmp_name'], $path . $file)) {
                $withImage = true;
            } else {
                response(["status" => "error", "message" => "An error occured while uploading the image"]);
            }
        } else {
            $withImage = false;
        }

        if ($course != null) {
            $stmt = $conn->prepare("SELECT * FROM sysacadtype");
            $stmt->execute();
            $res = $stmt->get_result();
            $stmt->close();

            $display = [];
            $courseFound = false;

            while ($row = $res->fetch_assoc()) {
                $DBcourse = $row['course_short_name'] . '-' . $row['year'] . '' . $row['section'];
                $display[] = $DBcourse;
                if ($DBcourse == $course) {
                    $course_code = $row['course_code'];
                    $courseFound = true;
                    break;
                }
            }

            if (!$courseFound) {
                response(["status" => "error", "message" => "Course not found", "courses" => $display]);
            }
        }

        $stmt = $conn->prepare("UPDATE usercredentials SET First_Name = ?, Last_Name = ?, student_Number = ?, course_code = ?, primary_email = ?, contactNumber = ? WHERE UUID = ?");
        $stmt->execute([$FirstName, $LastName, $studentNumber, $course_code, $email, $contactNumber, $UUID]);
        $stmt->close();

        $stmtImage = $conn->prepare("SELECT * FROM userprofile WHERE UUID = ?");
        $stmtImage->execute([$UUID]);
        $profile = $stmtImage->get_result();
        $stmtImage->close();

        if ($profile->num_rows > 0) {
            $profile = $profile->fetch_assoc();
            if (file_exists($path . $profile['imagePath'])) {
                unlink($path . $profile['imagePath']);
            }

            $stmtImage = $conn->prepare("UPDATE userprofile SET imagePath = ?, imageType = ?, imageExt = ? WHERE UUID = ?");
            $stmtImage->execute([$file, $profileImage['type'], $profileImage['type'], $UUID]);
            $stmtImage->close();
        } else {
            $stmtImage = $conn->prepare("INSERT INTO userprofile (UUID, imagePath, imageType, imageExt) VALUES (?, ?, ?, ?)");
            $stmtImage->execute([$UUID, $file, $profileImage['type'], $profileImage['type']]);
            $stmtImage->close();
        }

        $_SESSION['UUID'] = $UUID;
        $_SESSION['FirstName'] = $FirstName;
        $_SESSION['LastName'] = $LastName;
        $_SESSION['PrimaryEmail'] = $email;
        $_SESSION['student_Number'] = $studentNumber;
        $_SESSION['ProfileImage'] = $file;

        response(["status" => "success", "message" => "Profile updated successfully", "image" => $file]);
    } else {
        $stmt = $conn->prepare("UPDATE usercredentials SET First_Name = ?, Last_Name = ?, student_Number = ?, course_code = ?, primary_email = ?, contactNumber = ? WHERE UUID = ?");
        $stmt->execute([$FirstName, $LastName, $studentNumber, $course_code, $email, $contactNumber, $UUID]);
        $stmt->close();

        $_SESSION['UUID'] = $UUID;
        $_SESSION['FirstName'] = $FirstName;
        $_SESSION['LastName'] = $LastName;
        $_SESSION['PrimaryEmail'] = $email;
        $_SESSION['student_Number'] = $studentNumber;
        $_SESSION['ProfileImage'] = $file;

        response(["status" => "success", "message" => "Profile updated successfully"]);
    }

} catch (\Throwable $th) {
    response(["status" => "error", "message" => "An error occured : " . $th->getMessage() . " on line " . $th->getLine()]);
}
