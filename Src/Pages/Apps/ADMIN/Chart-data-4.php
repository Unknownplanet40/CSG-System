<?php
$stmt = $conn->prepare("SELECT Device, COUNT(*) as count FROM auditdevice GROUP BY Device");
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
$data3 = array();

while ($row = $result->fetch_assoc()) {
    $data3[] = $row;
}

$labels_device = array();
$values_device = array();
$total_device = 0;

foreach ($data3 as $row) {
    $labels_device[] = $row['Device'];
    $values_device[] = $row['count'];
    $total_device += $row['count'];
}