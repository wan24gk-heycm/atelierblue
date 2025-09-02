<?php
session_start();
require 'db.php';

try{
    $nombres = $_POST['nombres'];
    $apellidos = $_POST['apellidos'];
    $iniciales = $_POST['iniciales'];
    $rol = !empty($_POST['rol']) ? $_POST['rol'] : null;
    $contrasena = $_POST['contrasena'];

    $sql = "CALL ent_agregar_asesor(:p_nombre, :s_nombre', :p_apellido, :s_apellido, :sucursal, :rol, :correo, :telefono, :contrasena)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':p_nombre', $nombres);
    $stmt->bindParam(':s_nombre', $nombres);
    $stmt->bindParam(':p_apellido', $apellidos);
    $stmt->bindParam(':s_apellido', $apellidos);
    $stmt->bindParam(':sucursal', $apellidos);
    $stmt->bindParam(':rol', $rol);
    $stmt->bindParam(':correo', $rol);
    $stmt->bindParam(':telefono', $rol);
    $stmt->bindParam(':contrasena', $contrasena);

    $stmt->execute();
    echo "<script>
    alert('Registro exitoso');
    window.location.href = '../registro_asesor.php';
</script>";


} catch (PDOException $e) {
    echo "<p>Error al registrar: " . $e->getMessage() . "</p>";
}


?>
