<?php
require 'db.php';


try {
    $stmt = $pdo->query("SELECT nombre FROM ent_unidad");
    $unidades = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($unidades);
} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>
