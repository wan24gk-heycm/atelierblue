<?php
session_start();
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = trim($_POST['usuario']);
    $contrasena = $_POST['contrasena'];

    try {
        // Llamada al procedimiento almacenado
        $stmt = $pdo->prepare("CALL ent_login(:usuario, :contrasena)");
        $stmt->bindParam(':usuario', $usuario, PDO::PARAM_STR);
        $stmt->bindParam(':contrasena', $contrasena, PDO::PARAM_STR);
        $stmt->execute();

        // Obtener el resultado
        $asesor = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($asesor && isset($asesor['id'])) {
            $_SESSION['usuario_id'] = $asesor['id'];
            $_SESSION['usuario_rol'] = $asesor['rol'];
            $_SESSION['usuario'] = $usuario;
            $_SESSION['usuario_nom'] = $asesor['primer_nombre'];
            $_SESSION['usuario_ape'] = $asesor['primer_apellido'];

            // Redirección según el rol con alert en JS
            switch ($asesor['rol']) {
                case 'ADMINISTRADOR':
                    echo "<script>
                        alert('ROL: ADMINISTRADOR');
                        window.location.href = '../administrador.php';
                    </script>";
                    break;
                case 'ASESOR DE VENTAS':
                    echo "<script>
                        alert('ROL: ASESOR DE VENTAS');
                        window.location.href = '../asesor.php';
                    </script>";
                    break;
                case 'RECEPCIONISTA':
                    echo "<script>
                        alert('ROL: RECEPCIONISTA');
                        window.location.href = '../recepcion.php';
                    </script>";
                    break;
                default:
                    echo "<script>
                        alert('Rol desconocido');
                        window.location.href = '../login.html';
                    </script>";
                    break;
            }
            exit;
        } else {
            echo "<script>
                alert('Usuario o contraseña incorrectos o cuenta inactiva');
                window.location.href = '../login.html';
            </script>";
            exit;
        }
    } catch (PDOException $e) {
        echo "Error en el login: " . $e->getMessage();
    }
}
?>
