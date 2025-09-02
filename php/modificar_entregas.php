<?php
require "db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_entrega = $_POST['id'];
    $nombres = trim($_POST['nombre']);
    $apellidos = trim($_POST['apellidos']);
    $telefono = trim($_POST['telefono']);
    $correo = trim($_POST['correo']);
    $unidad = trim($_POST['unidad']);
    $vin = trim($_POST['vin']);
    $color = trim($_POST['color']);
    $fecha_entrega = $_POST['fecha'];
    $hora_entrega = $_POST['hora'];
    $numero_asistentes = $_POST['asistentes'];


    $sql = "UPDATE ent_entrega 
            SET nombres = :nombres, apellidos = :apellidos, telefono = :telefono, 
                correo = :correo, unidad = :unidad, vin = :vin, color = :color, fecha = :fecha, hora = :hora, numero_asistentes = :numero_asistentes
            WHERE id_entrega = :id_entrega";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id_entrega' => $id_entrega,
        ':nombres' => $nombres,
        ':apellidos' => $apellidos,
        ':telefono' => $telefono,
        ':correo' => $correo,
        ':unidad' => $unidad,
        ':vin' => $vin,
        ':color' => $color,
        ':fecha' => $fecha_entrega,
        ':hora' => $hora_entrega,
        ':numero_asistentes' => $numero_asistentes
    ]);

    echo "<script>
    alert('Modificación exitosa');
    window.location.href = '../programacion_diaria.php';
</script>";
    

    exit;
}
?>