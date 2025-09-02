<?php
require 'db.php'; // tu conexión con PDO

if (isset($_POST['id']) && isset($_POST['estatus'])) {
    $id = intval($_POST['id']);
    $estatus = $_POST['estatus'];

    try {
        $stmt = $pdo->prepare("UPDATE ent_entrega SET estatus = ? WHERE id_entrega = ?");
        $ok = $stmt->execute([$estatus, $id]);

        if ($ok) {
            echo "OK";
        } else {
            echo "Error al actualizar";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>