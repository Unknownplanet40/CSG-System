<?php

$stmt = $conn->prepare("SELECT document, COUNT(*) AS count FROM documentapproval WHERE MONTH(DateCreated) = MONTH(CURRENT_DATE()) AND YEAR(DateCreated) = YEAR(CURRENT_DATE()) GROUP BY document");
$stmt->execute();
$result = $stmt->get_result();
$total = 0;

$DocName = array();

while ($row = $result->fetch_assoc()) {
    $total += $row['count'];
    if ($row['document'] == "D-AP") {
        $DocName[] = array('eventType' => 'Activity Proposal', 'count' => $row['count']);
    } elseif ($row['document'] == "D-EL") {
        $DocName[] = array('eventType' => 'Excuse Letter', 'count' => $row['count']);
    } elseif ($row['document'] == "D-MM") {
        $DocName[] = array('eventType' => 'Meeting Minutes', 'count' => $row['count']);
    } elseif ($row['document'] == "D-OM") {
        $DocName[] = array('eventType' => 'Office Memorandum', 'count' => $row['count']);
    } elseif ($row['document'] == "D-PP") {
        $DocName[] = array('eventType' => 'Project Proposal', 'count' => $row['count']);
    }
}
$stmt->close();

$stmt_AP = $conn->prepare("
    SELECT 
        o.org_short_name,
        o.org_code,
        SUM(CASE WHEN a.isSubmittedtoCSG = 1 THEN 1 ELSE 0 END) AS submitted_count,
        SUM(CASE WHEN a.isSubmittedtoCSG = 0 THEN 1 ELSE 0 END) AS not_submitted_count
    FROM 
        sysorganizations o
    LEFT JOIN 
        activityproposaldocuments a
    ON 
        o.org_code = a.org_code
    AND 
        MONTH(a.Date_Created) = MONTH(CURRENT_DATE())
    AND 
        YEAR(a.Date_Created) = YEAR(CURRENT_DATE())
    GROUP BY 
        o.org_code
");
$stmt_AP->execute();
$result_AP = $stmt_AP->get_result();

$org = array();

while ($row = $result_AP->fetch_assoc()) {
    $DocData = array();

    if ($row['submitted_count'] > 0) {
        $DocData[] = array('eventType' => 'Submitted', 'count' => $row['submitted_count']);
    }

    if ($row['not_submitted_count'] > 0) {
        $DocData[] = array('eventType' => 'Not Submitted', 'count' => $row['not_submitted_count']);
    }

    $org[] = array('org_short_name' => $row['org_short_name'], 'data' => $DocData);
}
$stmt_AP->close();

$stmtEL = $conn->prepare("
    SELECT 
        o.org_short_name,
        o.org_code,
        SUM(CASE WHEN a.isSubmittedtoCSG = 1 THEN 1 ELSE 0 END) AS submitted_count,
        SUM(CASE WHEN a.isSubmittedtoCSG = 0 THEN 1 ELSE 0 END) AS not_submitted_count
    FROM 
        sysorganizations o
    LEFT JOIN 
        excuseletterdocuments a
    ON 
        o.org_code = a.org_code
    AND 
        MONTH(a.DateCreated) = MONTH(CURRENT_DATE())
    AND 
        YEAR(a.DateCreated) = YEAR(CURRENT_DATE())
    GROUP BY 
        o.org_code
");

$stmtEL->execute();
$resultEL = $stmtEL->get_result();

$orgEL = array();

while ($row = $resultEL->fetch_assoc()) {
    $DocData = array();

    if ($row['submitted_count'] > 0) {
        $DocData[] = array('eventType' => 'Submitted', 'count' => $row['submitted_count']);
    }

    if ($row['not_submitted_count'] > 0) {
        $DocData[] = array('eventType' => 'Not Submitted', 'count' => $row['not_submitted_count']);
    }

    $orgEL[] = array('org_short_name' => $row['org_short_name'], 'data' => $DocData);
}

$stmtEL->close();

$stmtMM = $conn->prepare("
    SELECT 
        o.org_short_name,
        o.org_code,
        SUM(CASE WHEN a.isSubmittedtoCSG = 1 THEN 1 ELSE 0 END) AS submitted_count,
        SUM(CASE WHEN a.isSubmittedtoCSG = 0 THEN 1 ELSE 0 END) AS not_submitted_count
    FROM 
        sysorganizations o
    LEFT JOIN 
        minutemeetingdocuments a
    ON 
        o.org_code = a.org_code
    AND 
        MONTH(a.DateCreated) = MONTH(CURRENT_DATE())
    AND 
        YEAR(a.DateCreated) = YEAR(CURRENT_DATE())
    GROUP BY 
        o.org_code
");

$stmtMM->execute();
$resultMM = $stmtMM->get_result();

$orgMM = array();

while ($row = $resultMM->fetch_assoc()) {
    $DocData = array();

    if ($row['submitted_count'] > 0) {
        $DocData[] = array('eventType' => 'Submitted', 'count' => $row['submitted_count']);
    }

    if ($row['not_submitted_count'] > 0) {
        $DocData[] = array('eventType' => 'Not Submitted', 'count' => $row['not_submitted_count']);
    }

    $orgMM[] = array('org_short_name' => $row['org_short_name'], 'data' => $DocData);
}

$stmtMM->close();

$stmtOM = $conn->prepare("
    SELECT 
        o.org_short_name,
        o.org_code,
        SUM(CASE WHEN a.isSubmittedtoCSG = 1 THEN 1 ELSE 0 END) AS submitted_count,
        SUM(CASE WHEN a.isSubmittedtoCSG = 0 THEN 1 ELSE 0 END) AS not_submitted_count
    FROM 
        sysorganizations o
    LEFT JOIN 
        officememorandomdocuments a
    ON 
        o.org_code = a.org_code
    AND 
        MONTH(a.DateCreated) = MONTH(CURRENT_DATE())
    AND 
        YEAR(a.DateCreated) = YEAR(CURRENT_DATE())
    GROUP BY 
        o.org_code
");

$stmtOM->execute();
$resultOM = $stmtOM->get_result();

$orgOM = array();

while ($row = $resultOM->fetch_assoc()) {
    $DocData = array();

    if ($row['submitted_count'] > 0) {
        $DocData[] = array('eventType' => 'Submitted', 'count' => $row['submitted_count']);
    }

    if ($row['not_submitted_count'] > 0) {
        $DocData[] = array('eventType' => 'Not Submitted', 'count' => $row['not_submitted_count']);
    }

    $orgOM[] = array('org_short_name' => $row['org_short_name'], 'data' => $DocData);
}

$stmtOM->close();

$stmtPP = $conn->prepare("
    SELECT 
        o.org_short_name,
        o.org_code,
        SUM(CASE WHEN a.isSubmittedtoCSG = 1 THEN 1 ELSE 0 END) AS submitted_count,
        SUM(CASE WHEN a.isSubmittedtoCSG = 0 THEN 1 ELSE 0 END) AS not_submitted_count
    FROM 
        sysorganizations o
    LEFT JOIN 
        projectproposaldocuments a
    ON 
        o.org_code = a.org_code
    AND 
        MONTH(a.date_Created) = MONTH(CURRENT_DATE())
    AND 
        YEAR(a.date_Created) = YEAR(CURRENT_DATE())
    GROUP BY 
        o.org_code
");

$stmtPP->execute();

$resultPP = $stmtPP->get_result();

$orgPP = array();

while ($row = $resultPP->fetch_assoc()) {
    $DocData = array();

    if ($row['submitted_count'] > 0) {
        $DocData[] = array('eventType' => 'Submitted', 'count' => $row['submitted_count']);
    }

    if ($row['not_submitted_count'] > 0) {
        $DocData[] = array('eventType' => 'Not Submitted', 'count' => $row['not_submitted_count']);
    }

    $orgPP[] = array('org_short_name' => $row['org_short_name'], 'data' => $DocData);
}

$stmtPP->close();

?>

<script>
    var Overall = {
        chart: {
            width: 500,
            type: 'pie',
        },
        labels: <?php echo json_encode(array_column($DocName, 'eventType')); ?> ,
        series: <?php echo json_encode(array_column($DocName, 'count')); ?> ,
        chartOptions: {
            labels: ['Apple', 'Mango', 'Orange', 'Watermelon']
        },
        responsive: [{
            breakpoint: 480,
            options: {
                chart: {
                    width: 200
                },
                legend: {
                    position: 'bottom',
                    offsetX: -10,
                }
            }
        }]
    };
    var OV_Chart = new ApexCharts(document.querySelector("#chatOVERALL"), Overall);
    OV_Chart.render();

    var orgData = <?php echo json_encode($org); ?> ;
    var categories = orgData.map(org => org.org_short_name);
    var submittedCounts = orgData.map(org => {
        var submitted = org.data.find(item => item.eventType === 'Submitted');
        return submitted ? submitted.count : 0;
    });
    var notSubmittedCounts = orgData.map(org => {
        var notSubmitted = org.data.find(item => item.eventType === 'Not Submitted');
        return notSubmitted ? notSubmitted.count : 0;
    });
    var options = {
        chart: {
            type: 'bar',
            height: 350,
            stacked: false,
            toolbar: {
                show: true,
                tools: {
                    download: true,
                    selection: false,
                    zoom: false,
                    zoomin: false,
                    zoomout: false,
                    pan: false,
                    reset: false | '<img src="/static/icons/reset.png" width="20">',
                    customIcons: []
                },
            }
        },
        series: [{
                name: 'Submitted',
                data: submittedCounts
            },
            {
                name: 'Not Submitted',
                data: notSubmittedCounts
            }
        ],
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '50%'
            }
        },
        legend: {
            position: 'bottom'
        },
        labels: categories
    };
    var chart = new ApexCharts(document.querySelector("#chatAP"), options);
    chart.render();

    var orgDataEL = <?php echo json_encode($orgEL); ?> ;
    var categoriesEL = orgDataEL.map(org => org.org_short_name);
    var submittedCountsEL = orgDataEL.map(org => {
        var submitted = org.data.find(item => item.eventType === 'Submitted');
        return submitted ? submitted.count : 0;
    });
    var notSubmittedCountsEL = orgDataEL.map(org => {
        var notSubmitted = org.data.find(item => item.eventType === 'Not Submitted');
        return notSubmitted ? notSubmitted.count : 0;
    });

    var optionsEL = {
        chart: {
            type: 'bar',
            height: 350,
            stacked: false,
            toolbar: {
                show: true,
                tools: {
                    download: true,
                    selection: false,
                    zoom: false,
                    zoomin: false,
                    zoomout: false,
                    pan: false,
                    reset: false | '<img src="/static/icons/reset.png" width="20">',
                    customIcons: []
                },
            }
        },
        series: [{
                name: 'Submitted',
                data: submittedCountsEL
            },
            {
                name: 'Not Submitted',
                data: notSubmittedCountsEL
            }
        ],
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '50%'
            }
        },
        legend: {
            position: 'bottom'
        },
        labels: categoriesEL
    };

    var chartEL = new ApexCharts(document.querySelector("#chatEL"), optionsEL);
    chartEL.render();

    var orgDataMM = <?php echo json_encode($orgMM); ?> ;
    var categoriesMM = orgDataMM.map(org => org.org_short_name);
    var submittedCountsMM = orgDataMM.map(org => {
        var submitted = org.data.find(item => item.eventType === 'Submitted');
        return submitted ? submitted.count : 0;
    });
    var notSubmittedCountsMM = orgDataMM.map(org => {
        var notSubmitted = org.data.find(item => item.eventType === 'Not Submitted');
        return notSubmitted ? notSubmitted.count : 0;
    });

    var optionsMM = {
        chart: {
            type: 'bar',
            height: 350,
            stacked: false,
            toolbar: {
                show: true,
                tools: {
                    download: true,
                    selection: false,
                    zoom: false,
                    zoomin: false,
                    zoomout: false,
                    pan: false,
                    reset: false | '<img src="/static/icons/reset.png" width="20">',
                    customIcons: []
                },
            }
        },
        series: [{
                name: 'Submitted',
                data: submittedCountsMM
            },
            {
                name: 'Not Submitted',
                data: notSubmittedCountsMM
            }
        ],
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '50%'
            }
        },
        legend: {
            position: 'bottom'
        },
        labels: categoriesMM
    };

    var chartMM = new ApexCharts(document.querySelector("#chatMM"), optionsMM);
    chartMM.render();

    var orgDataOM = <?php echo json_encode($orgOM); ?> ;
    var categoriesOM = orgDataOM.map(org => org.org_short_name);
    var submittedCountsOM = orgDataOM.map(org => {
        var submitted = org.data.find(item => item.eventType === 'Submitted');
        return submitted ? submitted.count : 0;
    });

    var notSubmittedCountsOM = orgDataOM.map(org => {
        var notSubmitted = org.data.find(item => item.eventType === 'Not Submitted');
        return notSubmitted ? notSubmitted.count : 0;
    });

    var optionsOM = {
        chart: {
            type: 'bar',
            height: 350,
            stacked: false,
            toolbar: {
                show: true,
                tools: {
                    download: true,
                    selection: false,
                    zoom: false,
                    zoomin: false,
                    zoomout: false,
                    pan: false,
                    reset: false | '<img src="/static/icons/reset.png" width="20">',
                    customIcons: []
                },
            }
        },
        series: [{
                name: 'Submitted',
                data: submittedCountsOM
            },
            {
                name: 'Not Submitted',
                data: notSubmittedCountsOM
            }
        ],
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '50%'
            }
        },
        legend: {
            position: 'bottom'
        },
        labels: categoriesOM
    };

    var chartOM = new ApexCharts(document.querySelector("#chatOM"), optionsOM);
    chartOM.render();

    var orgDataPP = <?php echo json_encode($orgPP); ?> ;
    var categoriesPP = orgDataPP.map(org => org.org_short_name);

    var submittedCountsPP = orgDataPP.map(org => {
        var submitted = org.data.find(item => item.eventType === 'Submitted');
        return submitted ? submitted.count : 0;
    });

    var notSubmittedCountsPP = orgDataPP.map(org => {
        var notSubmitted = org.data.find(item => item.eventType === 'Not Submitted');
        return notSubmitted ? notSubmitted.count : 0;
    });

    var optionsPP = {
        chart: {
            type: 'bar',
            height: 350,
            stacked: false,
            toolbar: {
                show: true,
                tools: {
                    download: true,
                    selection: false,
                    zoom: false,
                    zoomin: false,
                    zoomout: false,
                    pan: false,
                    reset: false | '<img src="/static/icons/reset.png" width="20">',
                    customIcons: []
                },
            }
        },
        series: [{
                name: 'Submitted',
                data: submittedCountsPP
            },
            {
                name: 'Not Submitted',
                data: notSubmittedCountsPP
            }
        ],
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '50%'
            }
        },
        legend: {
            position: 'bottom'
        },
        labels: categoriesPP
    };

    var chartPP = new ApexCharts(document.querySelector("#chatPP"), optionsPP);
    chartPP.render();


</script>