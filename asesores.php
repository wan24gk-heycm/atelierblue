<?php
require 'php/db.php';
session_start();

if (!isset($_SESSION['usuario'])) {
	header("Location: login.html");
	exit;
}

try {
    $stmt = $pdo->query("SELECT id_asesor, primer_nombre, segundo_nombre, primer_apellido, segundo_apellido, usuario, sucursal, estatus, rol, correo, telefono, contrasena FROM ent_asesores");
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
				<a href="#" class="search">
					<img src="icons/search.svg" alt="">
					<span>Buscar</span>
				</a>
			</ul>
			<ul>
				<a href="administrador.php" >
					<img src="icons/month.svg" alt="">
					<span>Programación Mensual</span>
				</a>
			</ul>
			<ul>
				<a href="programacion_diaria.php">
					<img src="icons/daily.svg"  alt="">
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
				<a href="asesores.php" class="selected">
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
		<h1>Lista de Asesores</h1>
    <table>
        <tr>
            <th>ID</th>
            <th>Nombres</th>
            <th>Apellidos</th>
			<th>Sucursal</th>
            <th>Usuario</th>
            <th>Rol</th>
            <th>Estatus</th>
			<th>Correo</th>
			<th>Telefono</th>
            <th>Acciones</th>
        </tr>
        <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) : ?>
            <tr>
                <td><?= htmlspecialchars($row['id_asesor']) ?></td>
                <td><?= htmlspecialchars($row['primer_nombre']. ' ' .$row['segundo_nombre']) ?></td>
                <td><?= htmlspecialchars($row['primer_apellido']. ' ' .$row['segundo_apellido']) ?></td>
				<td><?= htmlspecialchars($row['sucursal']) ?></td>
                <td><?= htmlspecialchars($row['usuario']) ?></td>
                <td><?= htmlspecialchars($row['rol']) ?></td>
                <td><?= htmlspecialchars($row['estatus']) ?></td>
				<td><?= htmlspecialchars($row['correo']) ?></td>
				<td><?= htmlspecialchars($row['telefono']) ?></td>
                <td>
                    <button class="btn-edit" 
                        onclick="openModal(
							'<?= htmlspecialchars($row['id_asesor'])?>',
                            '<?= htmlspecialchars($row['primer_nombre'])?>',
							'<?= htmlspecialchars($row['segundo_nombre']) ?>',
                            '<?= htmlspecialchars($row['primer_apellido']) ?>',
							'<?= htmlspecialchars($row['segundo_apellido']) ?>',
							'<?= htmlspecialchars($row['sucursal']) ?>',
                            '<?= htmlspecialchars($row['rol']) ?>',
                            '<?= htmlspecialchars($row['estatus']) ?>',
							'<?= htmlspecialchars($row['correo']) ?>',
							'<?= htmlspecialchars($row['telefono']) ?>',
							'<?= htmlspecialchars($row['contrasena']) ?>'
                        )">Modificar</button>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>

    <!-- Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Modificar Asesor</h2>
            <form action="php/modificar_asesor.php" method="POST">
                <input type="hidden" id="editId" name="id">
                <label>Primer Nombre:</label>
                <input type="text" id="editPNombre" name="p_nombre" required>
				<label>Segundo Nombre:</label>
                <input type="text" id="editSNombre" name="s_nombre">
				<label>Primer Apellido:</label>
                <input type="text" id="editPApellido" name="p_apellido" required>
                <label>Segundo Apellido:</label>
                <input type="text" id="editSApellido" name="s_apellido" required>
				<label>Sucursal:</label>
                <select id="editSucursal" name="sucursal" required>
                    <option value="PACHUCA">PACHUCA</option>
                    <option value="TULANCINGO">TULANCINGO</option>
                </select>
                <label>Rol:</label>
                <select id="editRol" name="rol" required>
                    <option value="ADMINISTRADOR">ADMINISTRADOR</option>
                    <option value="ASESOR DE VENTAS">ASESOR DE VENTAS</option>
					<option value="RECEPCIONISTA">RECEPCIONISTA</option>
                </select>
                <label>Estatus:</label>
                <select id="editEstatus" name="estatus" required>
                    <option value="ACTIVO">ACTIVO</option>
                    <option value="INACTIVO">INACTIVO</option>
                </select>
				<label>Correo:</label>
				<input type="email" id="editCorreo" name="correo" placeholder="Correo" required>
				<label>Telefono:</label>
				<input type="number" id="editTelefono" name="telefono" required>
				<label>Contraseña:</label>
				<input type="password" id="editContrasena" name="contrasena">
				
                <button type="submit" class="btn-edit">Guardar Cambios</button>
            </form>
        </div>
    </div>
	</main>
	<script src="js/script.js"></script>
	<script src="js/estatus.js"></script>
	
<script>
        function openModal(id,  p_nombre, s_nombre, p_apellido, s_apellido, sucursal, rol, estatus, correo, telefono, contrasena) {
            document.getElementById('editId').value = id;
            document.getElementById('editPNombre').value = p_nombre;
			document.getElementById('editSNombre').value = s_nombre;
			document.getElementById('editPApellido').value = p_apellido;
            document.getElementById('editSApellido').value = s_apellido;
            document.getElementById('editSucursal').value = sucursal;
			document.getElementById('editRol').value = rol;
            document.getElementById('editEstatus').value = estatus;
			document.getElementById('editCorreo').value = correo;
			document.getElementById('editTelefono').value = telefono;

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