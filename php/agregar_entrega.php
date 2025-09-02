<?php
require 'db.php';
session_start();

if (!isset($_SESSION['usuario'])) {
    die("Debes iniciar sesión primero.");
}

try{


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("CALL ent_agregar_entrega(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['nombres'],
        $_POST['apellidos'],
        $_POST['lada'],
        $_POST['telefono'],
        $_POST['correo'],
        $_POST['unidad'],
        $_POST['vin'],
        "2025",
        "Pachuca",
        $_POST['color'],
        $_POST['fecha'],
        $_POST['hora'],
        $_POST['bahia'],
        $_SESSION['usuario'],
        $_POST['asistentes']
    ]);

    echo "<script>
       
    alert('La ceremonia del té tiene una durabilidad de 15 a 20 minutos por lo cual, este tiempo no deberá ser superado, SIN EXCEPCIÓN ALGUNA. Esto con la finalidad de respetar el tiempo de los clientes presentes, los que continúan en el listado, así como, realizar la limpieza y acomodo correspondiente de los materiales para las siguientes ceremonias');
    window.location.href = '../registro_entrega.php';
    </script>";
}}
catch (PDOException $e) {
    echo "<p>Error al registrar: " . $e->getMessage() . "</p>";
}
?>


<?php
try {
    $stmt = $pdo->query("SELECT nombre FROM ent_unidad");
    $unidades = $stmt->fetchAll(PDO::FETCH_ASSOC); // traemos todas las filas

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

