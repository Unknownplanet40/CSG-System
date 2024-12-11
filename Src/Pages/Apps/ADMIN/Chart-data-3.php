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
}?>

<script>
    var ctx = document.getElementById('Theme').getContext('2d');
    var myChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_merge($labels_theme1, $labels_theme2)); ?> ,
            datasets: [{
                label: 'Theme Count',
                data: <?php echo json_encode(array_merge($values_theme1, $values_theme2)); ?> ,
                backgroundColor: [
                    'rgba(255, 206, 86, 0.2)',
                    'rgba(75, 192, 192, 0.2)',
                    'rgba(255, 99, 132, 0.2)',
                    'rgba(54, 162, 235, 0.2)',
                ],
                borderColor: [
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                ],
                borderWidth: 1
            }],
        },
        options: {
            responsive: true,

            scales: {
                r: {
                    beginAtZero: true,
                    suggestedMax: <?php echo max(1, (int)max(array_merge($values_theme1, $values_theme2))); ?>
                },
                x: {
                    stacked: true,
                },
            },
            plugins: {
                legend: {
                    display: false,
                    position: 'bottom',
                }
            }
        }
    });
</script>