<?php

try {
    $stmt = $conn->prepare("SELECT LoginStat, COUNT(*) as count0 FROM accounts GROUP BY LoginStat");
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    $data1 = array();

    while ($row = $result->fetch_assoc()) {
        $data1[] = $row;
    }

    $labels1 = array();
    $values1 = array();

    foreach ($data1 as $row) {
        $labels1[] = $row['LoginStat'];
        $values1[] = $row['count0'];
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . " at line " . $e->getLine() . " in " . $e->getFile();
}
?>

<script>
    var ctx = document.getElementById('User').getContext('2d');
    var myChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($labels1); ?> ,
            datasets: [{
                label: 'User Count',
                data: <?php echo json_encode($values1); ?> ,
                backgroundColor: [
                    'rgba(75, 192, 192, 0.2)',
                    'rgba(153, 102, 255, 0.2)'
                ],
                borderColor: [
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom',
                }
            }
        }
    });
</script>