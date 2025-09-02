<?php
require "php/db.php";
include "php/unidades.php";
session_start();



// Si se presionó el botón de "Hoy"
$filtroHoy = isset($_GET['hoy']) && $_GET['hoy'] == 1;

try {
	if ($filtroHoy) {
		// Solo entregas de hoy
		$sql = "SELECT e.id_entrega, e.nombres, e.apellidos, e.telefono, e.correo, 
                       e.unidad, e.vin, e.color, e.fecha, e.hora, e.numero_asistentes,
                       a.primer_nombre AS primer_nombre, a.primer_apellido AS primer_apellido
                FROM ent_entrega e
                LEFT JOIN ent_asesores a ON e.usuario = a.usuario
                WHERE DATE(e.fecha) = CURDATE()";
	} else {
		// Todas las entregas
		$sql = "SELECT e.id_entrega, e.nombres, e.apellidos, e.telefono, e.correo, 
                       e.unidad, e.vin, e.color, e.fecha, e.hora, e.numero_asistentes,
                       a.primer_nombre AS primer_nombre, a.primer_apellido AS primer_apellido
                FROM ent_entrega e
                LEFT JOIN ent_asesores a ON e.usuario = a.usuario";
	}
	$stmt = $pdo->query($sql);
} catch (PDOException $e) {
	die("Error: " . $e->getMessage());
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Toyota Pachuca - Entregas</title>
	<link rel="stylesheet" href="css/style.css">
	<link rel="stylesheet" href="css/style_asesores.css">

</head>

<body>
	<header>
		<div class="left">
			<div class="menu-container">
				<div class="menu" id="menu">
					<div></div>
					<div></div>
					<div></div>
				</div>
			</div>
			<div class="brand">
				<img src="icons/toyota_logo.jpg" alt="icon-toyota" class="logo">
				<span class="name">Toyota</span>
			</div>
		</div>
		<div class="right">
			<a href="#" class="icons-header">
				<img src="icons/chat.svg" alt="chat">
			</a>
			<a href="login.html" class="icons-header">
				<img src="icons/notification.svg" alt="notification">
			</a>
			<a href="php/logout.php" class="icons-header">
				<img src="icons/logout.svg" alt="logout">
			</a>
			<a>
				<img src="icons/toyota_logo.jpg" alt="img-user" class="user">
				<?php echo $_SESSION['usuario_nom'] . " " . $_SESSION['usuario_ape']; ?>
			</a>
		</div>

	</header>

	<div class="sidebar" id="sidebar">
		<nav>
			<ul>
				<a href="" class="search">
					<img src="icons/search.svg" alt="">
					<span>Buscar</span>
				</a>
			</ul>
			<ul>
				<a href="administrador.php">
					<img src="icons/month.svg" alt="">
					<span>Programación Mensual</span>
				</a>
			</ul>
			<ul>
				<a href="programacion_diaria.php" class="selected">
					<img src="icons/daily.svg" alt="">
					<span>Programación Diaria</span>
				</a>
			</ul>
			<ul>
				<a href="registro_asesor.php">
					<img src="icons/asesor.svg" alt="">
					<span>Registros</span>
				</a>
			</ul>
			<ul>
				<a href="asesores.php">
					<img src="icons/info.svg" alt="">
					<span>Asesores</span>
				</a>
			</ul>
			<ul>
				<a href="registro_entrega.php">
					<img src="icons/date.svg" alt="">
					<span>Programar Entrega</span>
				</a>
			</ul>
		</nav>

	</div>
	<main id="main">
		<h1>Lista de Entregas <?= $filtroHoy ? "(Hoy)" : "" ?></h1>

		<div class="acciones">
			<?php if ($filtroHoy): ?>
				<!-- Si ya está filtrado, mostrar botón para ver todas -->
				<a href="programacion_diaria.php">
					<button class="btn-edit">Ver Todas</button>
				</a>
			<?php else: ?>
				<!-- Si no está filtrado, mostrar botón de hoy -->
				<a href="programacion_diaria.php?hoy=1">
					<button class="btn-edit">Ver Entregas de Hoy</button>
				</a>
			<?php endif; ?>
		</div>

		<table>
			<tr>
				<th>ID</th>
				<th>Cliente</th>
				<th>Teléfono</th>
				<th>Correo</th>
				<th>Unidad</th>
				<th>VIN</th>
				<th>Color</th>
				<th>Fecha</th>
				<th>Hora</th>
				<th>Asesor</th>
				<th>Asistentes</th>
				<th>Acciones</th>
			</tr>
			<?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
				<tr>
					<td><?= htmlspecialchars($row['id_entrega']) ?></td>
					<td><?= htmlspecialchars($row['nombres'] . " " . $row['apellidos']) ?></td>
					<td><?= htmlspecialchars($row['telefono']) ?></td>
					<td><?= htmlspecialchars($row['correo']) ?></td>
					<td><?= htmlspecialchars($row['unidad']) ?></td>
					<td><?= htmlspecialchars($row['vin']) ?></td>
					<td><?= htmlspecialchars($row['color']) ?></td>
					<td><?= htmlspecialchars($row['fecha']) ?></td>
					<td><?= htmlspecialchars($row['hora']) ?></td>
					<td><?= htmlspecialchars($row['primer_nombre'] . " " . $row['primer_apellido']) ?></td>
					<td><?= htmlspecialchars($row['numero_asistentes']) ?></td>
					<td>
						<button class="btn-edit" onclick="openModal(
							'<?= $row['id_entrega'] ?>',
							'<?= htmlspecialchars($row['nombres']) ?>',
							'<?= htmlspecialchars($row['apellidos']) ?>',
							'<?= htmlspecialchars($row['telefono']) ?>',
							'<?= htmlspecialchars($row['correo']) ?>',
							'<?= htmlspecialchars($row['unidad']) ?>',
							'<?= htmlspecialchars($row['vin']) ?>',
							'<?= htmlspecialchars($row['color']) ?>',
							'<?= htmlspecialchars($row['fecha']) ?>',
							'<?= htmlspecialchars($row['numero_asistentes']) ?>',
							'<?= htmlspecialchars($row['hora']) ?>'
						)">Modificar</button>
					</td>
				</tr>
			<?php endwhile; ?>
		</table>

		<!-- Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Modificar Entrega</h2>
            <form action="php/modificar_entregas.php" method="POST">
                <input type="hidden" id="editId" name="id">
                <label>Nombres:</label>
                <input type="text" id="editNombre" name="nombre" required>
				
                <label>Apellidos:</label>
                <input type="text" id="editApellidos" name="apellidos" required>
	
                <label>Teléfono:</label>
                <input type="text" id="editTelefono" name="telefono" required>
                <label>Correo:</label>
                <input type="email" id="editCorreo" name="correo" required>
                <label>Unidad:</label>
				<select id="lista-unidades" name="unidad" class="input-group" placeholder="Seleccionar Unidad">
								<option value="">Seleccione Unidad</option>
								<?php foreach ($unidades as $unidad): ?>
									<option value="<?php echo htmlspecialchars($unidad['nombre']); ?>">
										<?php echo htmlspecialchars($unidad['nombre']); ?>
									</option>
								<?php endforeach; ?>
							</select>                <label>VIN:</label>
                <input type="text" id="editVin" name="vin" required>
                <label>Color:</label>
                <input type="text" id="editColor" name="color" required>
                <label>Fecha de Entrega:</label>
                <input type="date" id="fecha" name="fecha" required>
				<label>Hora de Entrega:</label>
				<select id="hora" class="input-group" name="hora" required>
							<option value="">Seleccione una hora</option>
						</select>
				<label>Numero de Asistentes:</label>
				<select id="editAsistentes" class="input-group" name="asistentes" required>
							<option value="1">1</option>
							<option value="2">2</option>
							<option value="3">3</option>
							<option value="4">4</option>
							<option value="5">5</option>
						</select>
                <button type="submit" class="btn-edit">Guardar Cambios</button>
            </form>
        </div>
    </div>

	</main>
	<script src="js/script.js"></script>
	<script src="js/hora.js"></script>
	<script src="js/estatus.js"></script>
	<script>
        function openModal(id, nombres, apellidos, telefono, correo, unidad, vin, color, fecha, hora, asistentes) {
            document.getElementById('editId').value = id;
            document.getElementById('editNombre').value = nombres;
            document.getElementById('editApellidos').value = apellidos;
            document.getElementById('editTelefono').value = telefono;
            document.getElementById('editCorreo').value = correo;
            document.getElementById('lista-unidades').value = unidad;
            document.getElementById('editVin').value = vin;
            document.getElementById('editColor').value = color;
            document.getElementById('fecha').value = fecha;
			document.getElementById('hora').value = hora;
			document.getElementById('editAsistentes').value = asistentes;
            document.getElementById('editModal').style.display = 'block';

        }
        function closeModal() {
            document.getElementById('editModal').style.display = 'none';
        }
        window.onclick = function(event) {
            if (event.target == document.getElementById('editModal')) {
                closeModal();
            }
        }
    </script>


</body>

</html>