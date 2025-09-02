<?php
require 'db.php';

if (isset($_GET['fecha'])) {
    $fecha = $_GET['fecha'];

    try {
        $stmt = $pdo->prepare("CALL ent_horas_disponibles(?)");
        $stmt->execute([$fecha]);
        $horas = $stmt->fetchAll(PDO::FETCH_COLUMN);

        echo json_encode($horas);
    } catch (PDOException $e) {
        echo json_encode(["error" => $e->getMessage()]);
    }    
}
?>

