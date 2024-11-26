<?php
try {
    $stmt = $conn->prepare("SELECT course_code, COUNT(*) as count_course FROM usercredentials WHERE course_code IS NOT NULL GROUP BY course_code ORDER BY count_course DESC LIMIT 5");
    $stmt->execute();
    $res_Course = $stmt->get_result();
    $stmt->close();
    $data_course = array();
    $labels_course = array();
    $values_course = array();
    $RandomBGcolor_course = array();
    $RandomBRcolor_course = array();

    while ($row = $res_Course->fetch_assoc()) {
        $stmt = $conn->prepare("SELECT course_short_name, year, section FROM sysacadtype WHERE course_code = ?");
        $stmt->bind_param("s", $row['course_code']);
        $stmt->execute();
        $res_course_name = $stmt->get_result();
        $stmt->close();

        $row_course_name = $res_course_name->fetch_assoc();
        $row['course_code'] = $row_course_name['course_short_name'] . '-' . $row_course_name['year'] . '' . $row_course_name['section'];
        $data_course[] = $row;
    }

    foreach ($data_course as $row) {
        $labels_course[] = $row['course_code'];
        $values_course[] = $row['count_course'];
        $r = rand(0, 255);
        $g = rand(0, 255);
        $b = rand(0, 255);
        $RandomBGcolor_course[] = 'rgba(' . $r . ',' . $g . ',' . $b . ', 0.2)';
        $RandomBRcolor_course[] = 'rgba(' . $r . ',' . $g . ',' . $b . ', 1)';
    }

} catch (Exception $e) {
    echo $e->getMessage() . " Line: " . $e->getLine();
}
?>

<script>
    var ctx5 = document.getElementById('Course').getContext('2d');
    var myChart5 = new Chart(ctx5, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($labels_course); ?> ,
            datasets: [{
                label: 'Course Count',
                data: <?php echo json_encode($values_course); ?> ,
                backgroundColor: <?php echo json_encode($RandomBGcolor_course); ?> ,
                borderColor: <?php echo json_encode($RandomBRcolor_course); ?> ,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                y: {
                    beginAtZero: true,
                    suggestedMax: <?php echo max($values_course); ?>
                }
            },
            plugins: {
                legend: {
                    display: false,
                    position: 'bottom',
                }
            },
            indexAxis: 'y'
        }
    });
</script>