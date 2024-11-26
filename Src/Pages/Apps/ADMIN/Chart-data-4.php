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

?>

<script>
    var theme =
        '<?php echo $_SESSION['theme']; ?>';

    if (theme == 'dark') {
        textColor = '#fff';
    } else {
        textColor = '#000';
    }

    var options = {
        chart: {
            type: "radialBar",
            height: 350,
            width: 380,
        },
        plotOptions: {
            radialBar: {
                size: undefined,
                inverseOrder: true,
                hollow: {
                    margin: 5,
                    size: "48%",
                    background: "transparent",
                },
                track: {
                    show: false,
                },
                startAngle: -180,
                endAngle: 180,
                dataLabels: {
                    value: {
                        fontSize: '16px',
                        color: textColor,
                    },
                    total: {
                        show: true,
                        label: 'OS Count',
                        formatter: function(w) {
                            return <?php echo json_encode($total_device); ?>;
                        },
                        color: textColor,
                    }
                },
            },
        },
        stroke: {
            lineCap: "round",
        },
        series: <?php echo json_encode($values_device); ?> ,
        labels: <?php echo json_encode($labels_device); ?> ,
        legend: {
            show: true,
            floating: true,
            position: "right",
            offsetX: 70,
            offsetY: 200,
            labels: {
                useSeriesColors: true,
            },
        },
    };

    var chart = new ApexCharts(document.querySelector("#Device"), options);
    chart.render();
</script>