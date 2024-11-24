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
