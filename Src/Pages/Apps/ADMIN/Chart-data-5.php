<?php

$stmt = $conn->prepare("SELECT org_code FROM sysorganizations");
$stmt->execute();
$org = $stmt->get_result();
$stmt->close();
while ($row = $org->fetch_assoc()) {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM activityproposaldocuments WHERE org_code = ?");
    $stmt->bind_param("s", $row['org_code']);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    $data = $result->fetch_assoc();
    $org_data[] = $data['count'];

    $stmt = $conn->prepare("SELECT org_short_name FROM sysorganizations WHERE org_code = ?");
    $stmt->bind_param("s", $row['org_code']);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    $data = $result->fetch_assoc();
    $org_name[] = $data['org_short_name'];
}

$RandomBGcolor_org = [];
$RandomBRcolor_org = [];
for ($i = 0; $i < count($org_name); $i++) {
    $R = rand(0, 255);
    $G = rand(0, 255);
    $B = rand(0, 255);
    $RandomBGcolor_org[] = "rgba($R, $G, $B, 0.2)";
    $RandomBRcolor_org[] = "rgba($R, $G, $B, 1)";
}
?>

<script>
    var ctx4 = document.getElementById('Organization').getContext('2d');
    var myChart4 = new Chart(ctx4, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($org_name); ?> ,
            datasets: [{
                label: 'Document Count',
                data: <?php echo json_encode($org_data); ?> ,
                backgroundColor: <?php echo json_encode($RandomBGcolor_org); ?> ,
                borderColor: <?php echo json_encode($RandomBRcolor_org); ?> ,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                y: {
                    beginAtZero: true,
                    suggestedMax: <?php echo max($org_data); ?>
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