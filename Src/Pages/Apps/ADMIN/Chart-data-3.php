<?php
$stmt = $conn->prepare("SELECT theme, COUNT(*) as count FROM usersettings GROUP BY theme");
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
$data = array();

while ($row = $result->fetch_assoc()) {
    $row['theme'] = $row['theme'] == 'auto' ? 'Light' : 'Dark';
    $data[] = $row;
}

$labels_theme = array();
$values_theme = array();

foreach ($data as $row) {
    $labels_theme[] = $row['theme'];
    $values_theme[] = $row['count'];
}