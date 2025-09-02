<?php
require "db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id = $_POST['id'];
    $p_nombre = trim($_POST['p_nombre']);
    $s_nombre = trim($_POST['s_nombre']);
    $p_apellido = trim($_POST['p_apellido']);
    $s_apellido = trim($_POST['s_apellido']);
    $sucursal = trim($_POST['sucursal']);
    $rol = trim($_POST['rol']);
    $estatus = trim($_POST['estatus']);
    $correo = trim($_POST['correo']);
    $telefono = trim($_POST['telefono']);
    $contrasena = isset($_POST['contrasena']) ? trim($_POST['contrasena']) : '';

    if ($contrasena === '') {
        // Llamar SP sin contraseña
        $sql = "CALL ent_modificar_asesor_sin_pass(?,?,?,?,?,?,?,?,?,?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $id,
            $p_nombre,
            $s_nombre,
            $p_apellido,
            $s_apellido,
            $sucursal,
            $estatus,
            $rol,
            $correo,
            $telefono
        ]);
    } else {
        // Llamar SP normal con contraseña
        $sql = "CALL ent_modificar_asesor(?,?,?,?,?,?,?,?,?,?,?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $id,
            $p_nombre,
            $s_nombre,
            $p_apellido,
            $s_apellido,
            $sucursal,
            $estatus,
            $rol,
            $correo,
            $telefono,
            $contrasena
        ]);
    }

    echo "<script>
    alert('Modificación exitosa');
    window.location.href = '../asesores.php';
    </script>";
    exit;
}

?>
