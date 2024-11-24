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

$labels_theme1 = array();
$values_theme1 = array();

foreach ($data as $row) {
    $labels_theme1[] = $row['theme'];
    $values_theme1[] = $row['count'];
}

$stmt = $conn->prepare("SELECT bobble_BG, COUNT(*) as count1 FROM usersettings GROUP BY bobble_BG");
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
$data2 = array();

while ($row = $result->fetch_assoc()) {
    $row['bobble_BG'] = $row['bobble_BG'] == 1 ? 'Bouncing Ball BG' : 'Static BG';
    $data2[] = $row;
}

$labels_theme2 = array();
$values_theme2 = array();

foreach ($data2 as $row) {
    $labels_theme2[] = $row['bobble_BG'];
    $values_theme2[] = $row['count1'];
}