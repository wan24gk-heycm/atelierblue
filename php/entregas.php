<?php
require 'db.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fecha'])) {
    $fecha = $_POST['fecha'];

    // Llamada al procedimiento almacenado
    $stmt = $pdo->prepare("CALL ent_obtener_entregas(:fecha)");
    $stmt->bindParam(':fecha', $fecha, PDO::PARAM_STR);
    $stmt->execute();
    $entregas = $stmt->fetchAll(PDO::FETCH_ASSOC);



    if ($entregas) {
        foreach ($entregas as $entrega) {

            $unidad = $entrega['unidad'];
            $ruta = "imag/" . $unidad . ".jpg";
            if (!file_exists($ruta)) {
                // Si no existe, tomamos la primera palabra del nombre de la unidad
                $primeraPalabra = strtok($unidad, " ");
                $ruta = "imag/" . $primeraPalabra . ".jpg";
            }

            echo "<div class='entrega-card'>";

            // --- Lado izquierdo: hora, bahía y estatus ---
            echo "<div class='entrega-info' data-id='" . $entrega['id_entrega'] . "'>";
            echo "<div class='hora'>" . htmlspecialchars($entrega['hora']) . "</div>";
            echo "<div class='bahia'>Bahía " . htmlspecialchars($entrega['bahia']) . "</div>";
            echo "<div class='estatus'>" . htmlspecialchars($entrega['estatus']) . "<img src='icons/calendar.svg'> </div>";
            echo "<div class='fecha'>" . htmlspecialchars($entrega['fecha']) . "</div>";
            echo "</div>";

            // --- Foto circular ---
            echo "<div class='foto'>";

            echo "<img src='" . htmlspecialchars($ruta) . "' alt='" . htmlspecialchars($unidad) . "'>";

            echo "</div>";

            // --- Datos del cliente ---
            echo "<div class='cliente'>";
            echo "<div class='nombre'>" . htmlspecialchars($entrega['nombres'] . " " . $entrega['apellidos']) . "</div>";
            echo "<div class='detalle'>" . htmlspecialchars($entrega['unidad'] . " " . $entrega['modelo']) . "</div>";
            echo "<div class='detalle'>" . htmlspecialchars($entrega['vin']) . "</div>";
            echo "<div class='asesor'>" . htmlspecialchars($entrega['nombre_asesor']) . "</div>";
            echo "</div>";

            echo "</div>";
            echo "<div class='divider'></div>";
        }
    } else {
        echo "<div class='sin-entregas'>No hay entregas para esta fecha</div>";
    }
}

?>