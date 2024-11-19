<?php

$stmt = $conn->prepare("SELECT * FROM accounts;");
$stmt->execute();
$result = $stmt->get_result();
$data = array();
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

$months = array();
$monthData = array();

foreach ($data as $row) {
    $month = date('F', strtotime($row['access_date']));
    if (!in_array($month, $months)) {
        $months[] = $month;
        $monthData[$month] = 1;
    } else {
        $monthData[$month]++;
    }
}
$month = array();
$count = array();
foreach ($monthData as $key => $value) {
    $month[] = $key;
    $count[] = $value;
}

$month = json_encode($month);
$count = json_encode($count);
