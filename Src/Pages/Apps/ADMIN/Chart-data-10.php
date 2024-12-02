<?php

$OM_data = [];
$OM_name = [];

$stmt = $conn->prepare("SELECT org_code FROM sysorganizations");
$stmt->execute();
$org = $stmt->get_result();
$stmt->close();
while ($row = $org->fetch_assoc()) {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM officememorandomdocuments WHERE org_code = ?");
    $stmt->bind_param("s", $row['org_code']);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    $data = $result->fetch_assoc();
    $OM_data[] = $data['count'];

    $stmt = $conn->prepare("SELECT org_short_name FROM sysorganizations WHERE org_code = ?");
    $stmt->bind_param("s", $row['org_code']);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    $data = $result->fetch_assoc();
    $OM_name[] = $data['org_short_name'];
}

$RandomBGcolor_OM = [];
$RandomBRcolor_OM = [];
for ($i = 0; $i < count($OM_name); $i++) {
    $R = rand(0, 255);
    $G = rand(0, 255);
    $B = rand(0, 255);
    $RandomBGcolor_OM[] = "rgba($R, $G, $B, 0.2)";
    $RandomBRcolor_OM[] = "rgba($R, $G, $B, 1)";
}
?>

<script>
    var ctxOM = document.getElementById('OMLetter').getContext('2d');
    var myChartOM = new Chart(ctxOM, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($OM_name); ?> ,
            datasets: [{
                label: 'Document Count',
                data: <?php echo json_encode($OM_data); ?> ,
                backgroundColor: <?php echo json_encode($RandomBGcolor_OM); ?> ,
                borderColor: <?php echo json_encode($RandomBRcolor_OM); ?> ,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                y: {
                    beginAtZero: true,
                    suggestedMax: <?php echo max($OM_data) ?? 0; ?>
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