<?php

$stmt = $conn->prepare("SELECT LoginStat, COUNT(*) as count FROM Accounts GROUP BY LoginStat");
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
$data = array();

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

$labels = array();
$values = array();

foreach ($data as $row) {
    $labels[] = $row['LoginStat'];
    $values[] = $row['count'];
}
