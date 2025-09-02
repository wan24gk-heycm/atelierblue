
<?php
require 'db.php'; // conexión $pdo en PDO
header("Content-Type: application/json");

$year  = intval($_POST['year'] ?? date("Y"));
$month = intval($_POST['month'] ?? date("n")); // 1-12

// Rango de fechas del mes
$start = sprintf("%04d-%02d-01", $year, $month);
$end   = date("Y-m-t", strtotime($start));

$sql = "SELECT fecha AS fecha, COUNT(*) AS total
        FROM ent_entrega
        WHERE fecha BETWEEN :start AND :end
        GROUP BY fecha";

$stmt = $pdo->prepare($sql);
$stmt->execute(['start' => $start, 'end' => $end]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$data = [];
foreach ($rows as $row) {
    $data[$row['fecha']] = (int)$row['total'];
}

echo json_encode($data);

