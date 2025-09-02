<?php
require 'php/db.php';
session_start();

if (!isset($_SESSION['usuario'])) {
	header("Location: login.html");
	exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Entregas</title>
	<link rel="stylesheet" href="css/style.css">
	<link rel="stylesheet" href="css/style_ingresos.css">
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
				<a href="administrador.php">
					<img src="icons/month.svg" alt="">
					<span>Programación Mensual</span>
				</a>
			</ul>
			<ul>
				<a href="programacion_diaria.php">
					<img src="icons/daily.svg" alt="">
					<span>Programación Diaria</span>
				</a>
			</ul>
			<ul>
				<a href="registro_asesor.php" class="selected">
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
		<div class="ingresos">
			<div class="container_buttons">
			</div>
			<div class="container">


				<div class="login-section">
					<h2>Registro de Asesor</h2>
					<form action="php/agregar_asesor.php" method="POST" enctype="multipart/formdata">
						<div class="input-group">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
								<path
									d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z" />
							</svg>
							<input type="text" id="p_nombre" name="p_nombre" placeholder="Primer Nombre" required>
						</div>
						<div class="input-group">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
								<path
									d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z" />
							</svg>
							<input type="text" id="s_nombre" name="s_nombre" placeholder="Segundo Nombre">
						</div>
						<div class="input-group">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
								<path
									d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z" />
							</svg>
							<input type="text" id="p_apellidos" name="p_apellidos" placeholder="Primer Apellido" required>
						</div>
						<div class="input-group">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
								<path
									d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z" />
							</svg>
							<input type="text" id="s_apellidos" name="s_apellidos" placeholder="Segundo Apellido" required>
						</div>

						<div class="input-group">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
								<path
									d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z" />
							</svg>
							<select class="input-group" type="text" id="sucursal" name="sucursal" placeholder="Sucursal" required><option value="value1">Tulancingo</option>
  <option value="value2" selected>Pachuca</option>

</select>
						</div>

						<div class="input-group">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
								<path
									d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z" />
							</svg>
<select class="input-group" type="text" id="rol" name="rol" placeholder="Rol" required><option value="value1">ADMINISTRADOR</option>
  <option value="value2" selected>RECEPCIONISTA</option><option value="value3" selected>ASESOR DE VENTAS</option>

</select>
						</div>

						<div class="input-group">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
								<path
									d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z" />
							</svg>
							<input type="email" id="correo" name="correo" placeholder="Correo electronico" required>
						</div>

						<div class="input-group">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
								<path
									d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z" />
							</svg>
							<input type="number" id="telefono" name="telefono" placeholder="Telefono" required>
						</div>

						<div class="input-group">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
								<path
									d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z" />
							</svg>
							<input type="password" id="contrasena" name="contrasena" placeholder="Contraseña" required>
						</div>

						<button class="button" type="submit">INGRESAR</button>
					</form>
				</div>




				<div class="login-section">
					<h2>Registro de Unidad</h2>
					<form action="agregar.php" method="POST">

						<div class="input-group">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
								<path
									d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z" />
							</svg>
							<input type="text" id="nombre_uni" name="nombre_unidad" placeholder="Unidad" required>
						</div>

						<div class="input-group">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
								<path
									d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z" />
							</svg>
							<input type="text" id="version" name="version" placeholder="Version" required>
						</div>

					
						<button class="button" type="submit">INGRESAR</button>
					</form>
				</div>

			</div>
	</main>
	<script src="js/script.js"></script>
	<script src="js/estatus.js"></script>


</body>

</html>