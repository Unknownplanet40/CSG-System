<?php
$stmt = $conn->prepare("SELECT Device, COUNT(*) as count FROM auditdevice GROUP BY Device");
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
$data = array();

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

$labels_device = array();
$values_device = array();

foreach ($data as $row) {
    $labels_device[] = $row['Device'];
    $values_device[] = $row['count'];
}