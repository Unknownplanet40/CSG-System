<?php

$stmt = $conn->prepare("
                                    SELECT usercredentials.fullName, systemaudit.eventType, COUNT(*) as count 
                                    FROM systemaudit 
                                    JOIN usercredentials ON systemaudit.userID = usercredentials.UUID 
                                    GROUP BY systemaudit.eventType");

$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

$data = array();

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

$labels = array();
$values = array();
$RandomBGcolor = array();
$RandomBRcolor = array();
$total = 0;

foreach ($data as $row) {
    $labels[] = $row['eventType'];
    $values[] = $row['count'];
    $r = rand(0, 255);
    $g = rand(0, 255);
    $b = rand(0, 255);
    $RandomBGcolor[] = 'rgba(' . $r . ',' . $g . ',' . $b . ', 0.2)';
    $RandomBRcolor[] = 'rgba(' . $r . ',' . $g . ',' . $b . ', 1)';
}
$total = array_sum($values);
?>

<script>
    var ctx = document.getElementById('myChart').getContext('2d');
    var myChart = new Chart(ctx, {
        type: 'radar',
        data: {
            labels: <?php echo json_encode($labels); ?> ,
            datasets: [{
                label: 'Event Count',
                data: <?php echo json_encode($values); ?> ,
                backgroundColor: <?php echo json_encode($RandomBGcolor); ?> ,
                borderColor: <?php echo json_encode($RandomBRcolor); ?> ,
            }]
        },
        options: {
            responsive: true,
            scales: {
                r: {
                    beginAtZero: true,
                    suggestedMax: <?php echo max($values); ?>
                }
            },
            plugins: {
                legend: {
                    display: false,
                    position: 'top',
                }
            }
        }
    });
</script>