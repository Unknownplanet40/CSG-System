<?php

$stmt = $conn->prepare("SELECT org_code FROM sysorganizations");
$stmt->execute();
$org = $stmt->get_result();
$stmt->close();
while ($row = $org->fetch_assoc()) {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM minutemeetingdocuments WHERE org_code = ?");
    $stmt->bind_param("s", $row['org_code']);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    $data = $result->fetch_assoc();
    $MM_data[] = $data['count'];

    $stmt = $conn->prepare("SELECT org_short_name FROM sysorganizations WHERE org_code = ?");
    $stmt->bind_param("s", $row['org_code']);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    $data = $result->fetch_assoc();
    $MM_name[] = $data['org_short_name'];
}

$RandomBGcolor_MM = [];
$RandomBRcolor_MM = [];
for ($i = 0; $i < count($MM_name); $i++) {
    $R = rand(0, 255);
    $G = rand(0, 255);
    $B = rand(0, 255);
    $RandomBGcolor_MM[] = "rgba($R, $G, $B, 0.2)";
    $RandomBRcolor_MM[] = "rgba($R, $G, $B, 1)";
}
?>

<script>
    var ctxMM = document.getElementById('MMLetter').getContext('2d');
    var myChartMM = new Chart(ctxMM, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($MM_name); ?> ,
            datasets: [{
                label: 'Document Count',
                data: <?php echo json_encode($MM_data); ?> ,
                backgroundColor: <?php echo json_encode($RandomBGcolor_MM); ?> ,
                borderColor: <?php echo json_encode($RandomBRcolor_MM); ?> ,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                y: {
                    beginAtZero: true,
                    suggestedMax: <?php echo max($MM_data); ?>
                }
            },
            plugins: {
                legend: {
                    display: false,
                    position: 'bottom',
                }
            },
            indexAxis: 'x'
        }
    });
</script>