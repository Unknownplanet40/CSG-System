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
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        response(['status' => 'error', 'message' => 'Invalid request method']);
    }

    $CourseID = $_POST['CourseID'];
    $Action = $_POST['Action'];

    if (empty($Action)) {
        response(['status' => 'error', 'message' => 'Invalid request method']);
    }

    if ($CourseID == null) {
        $stmt = $conn->prepare("SELECT * FROM sysacadtype");
    } else {
        $stmt = $conn->prepare("SELECT * FROM sysacadtype WHERE course_short_name = ?");
        $stmt->bind_param("i", $CourseID);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    $data = [];
    $yearlvl = null;

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            switch ($Action) {
                case 'Get-Year':
                    $stmt = $conn->prepare("SELECT DISTINCT year FROM sysacadtype WHERE course_short_name = ?");
                    $stmt->bind_param("s", $CourseID);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            if (!in_array($row['year'], array_column($data, 'Year'))) {
                                if ($row['year'] == 1) {
                                    $yerlvl = 'First Year';
                                } elseif ($row['year'] == 2) {
                                    $yerlvl = 'Second Year';
                                } elseif ($row['year'] == 3) {
                                    $yerlvl = 'Third Year';
                                } else {
                                    $yerlvl = 'Fourth Year';
                                }

                                $data[] = [
                                    'Year' => $row['year'],
                                    'CourseName' => $yerlvl,
                                ];
                            }
                        }
                    }
                    break;
                case 'Get-Section':
                    $yearlvl = $_POST['YearLevel'];

                    $stmt = $conn->prepare("SELECT DISTINCT section FROM sysacadtype WHERE course_short_name = ? AND year = ?");
                    $stmt->bind_param("si", $CourseID, $yearlvl);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0) {

                        while ($row = $result->fetch_assoc()) {
                            if (!in_array($row['section'], array_column($data, 'Section'))) {
                                $data[] = [
                                    'Section' => $row['section'],
                                    'CourseName' => $CourseID
                                ];
                            }
                        }
                    }
                    break;
                default:
                    if (!in_array($row['course_name'], array_column($data, 'CourseName'))) {
                        $data[] = [
                            'CourseName' => $row['course_name'],
                            'ShortName' => $row['course_short_name'],
                        ];
                    }
                    break;
            }
        }
        response(['status' => 'success', 'data' => $data]);
    }
    response(['status' => 'error', 'message' => 'Can\'t find any']);
} catch (Exception $e) {
    response(['status' => 'error', 'message' => $e->getMessage()]);
}
