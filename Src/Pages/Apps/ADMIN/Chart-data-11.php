<?php

$stmt = $conn->prepare("SELECT org_code FROM sysorganizations");
$stmt->execute();
$org = $stmt->get_result();
$stmt->close();
while ($row = $org->fetch_assoc()) {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM projectproposaldocuments WHERE org_code = ?");
    $stmt->bind_param("s", $row['org_code']);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    $data = $result->fetch_assoc();
    $PP_data[] = $data['count'];

    $stmt = $conn->prepare("SELECT org_short_name FROM sysorganizations WHERE org_code = ?");
    $stmt->bind_param("s", $row['org_code']);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    $data = $result->fetch_assoc();
    $PP_name[] = $data['org_short_name'];
}

$RandomBGcolor_PP = [];
$RandomBRcolor_PP = [];
for ($i = 0; $i < count($PP_name); $i++) {
    $R = rand(0, 255);
    $G = rand(0, 255);
    $B = rand(0, 255);
    $RandomBGcolor_PP[] = "rgba($R, $G, $B, 0.2)";
    $RandomBRcolor_PP[] = "rgba($R, $G, $B, 1)";
}
?>

<script>
    var ctxPP = document.getElementById('PPLetter').getContext('2d');
    var myChartEL = new Chart(ctxPP, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($PP_name); ?> ,
            datasets: [{
                label: 'Document Count',
                data: <?php echo json_encode($PP_data); ?> ,
                backgroundColor: <?php echo json_encode($RandomBGcolor_PP); ?> ,
                borderColor: <?php echo json_encode($RandomBRcolor_PP); ?> ,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                y: {
                    beginAtZero: true,
                    suggestedMax: <?php echo max($PP_data); ?>
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