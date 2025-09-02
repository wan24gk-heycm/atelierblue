<?php
require 'php/db.php';
include 'php/unidades.php';
session_start();




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
				<a href="registro_entrega.php" class="selected">
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
					<h2>Registro de Entrega</h2>
					<form action="php/agregar_entrega.php" method="POST">
						<div class="input-group">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
								<path
									d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z" />
							</svg>
							<input type="text" id="nombres" name="nombres" placeholder="Nombre(s) del Cliente" required>
						</div>
						<div class="input-group">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
								<path
									d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z" />
							</svg>
							<input type="text" id="apellidos" name="apellidos" placeholder="Apellidos del Cliente" required>
						</div>

						<div class="tel-group">
							<select name="lada" class="lada" id="lada" required>
								<option value="+1">🇺🇸 (+1)</option>
								<option value="+52">🇲🇽 (+52)</option>
								<option value="+54">🇦🇷 (+54)</option>
								<option value="+55">🇧🇷 (+55)</option>
								<option value="+34">🇪🇸 (+34)</option>
								<option value="+44">🇬🇧 (+44)</option>
								<option value="+33">🇫🇷 (+33)</option>
								<option value="+49">🇩🇪 (+49)</option>
								<option value="+81">🇯🇵 (+81)</option>
								<option value="+86">🇨🇳 (+86)</option>
								<option value="+91">🇮🇳 (+91)</option>
							</select>

							<div class="input-group" id="num">
								<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
									<path
										d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z" />
								</svg>

								<input type="number" id="telefono" name="telefono" placeholder="Telefono del Cliente" required>
							</div>
						</div>

						<div class="input-group">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
								<path
									d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z" />
							</svg>

							<input type="email" id="correo" name="correo" placeholder="Correo electronico del Cliente" required>
						</div>


						<div class="input-group">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
								<path
									d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z" />
							</svg>
							<select id="lista-unidades" name="unidad" class="input-group" placeholder="Seleccionar Unidad">
								<option value="">Seleccione Unidad</option>
								<?php foreach ($unidades as $unidad): ?>
									<option value="<?php echo htmlspecialchars($unidad['nombre']); ?>">
										<?php echo htmlspecialchars($unidad['nombre']); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>

						<div class="input-group">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
								<path
									d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z" />
							</svg>
							<input type="text" id="vin" name="vin" placeholder="VIN" required>
						</div>
						<div class="input-group">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
								<path
									d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z" />
							</svg>
							<input type="text" id="color" name="color" placeholder="Color" required>
						</div>

						<div class="input-group">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
								<path
									d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z" />
							</svg>
							<input type="date" id="fecha" name="fecha" placeholder="Fecha" required>
						</div>

							<div class="input-group">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
								<path
									d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z" />
							</svg>
						<select id="hora" class="input-group" name="hora" required>
							<option value="">Seleccione una hora</option>
						</select>						</div>

						<div class="input-group">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
								<path
									d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z" />
							</svg>
						<select id="bahia" class="input-group" name="bahia" required>
							<option value="">Seleccionar Bahia</option>
							<option value="">1</option>
							<option value="">2</option>

						</select>						</div>
						<div class="input-group">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
								<path
									d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z" />
							</svg>
						<select  id="asistentes" class="input-group" name="asistentes" required>
							<option  value="1">1</option>
							<option  value="2">2</option>
							<option  value="3">3</option>
							<option  value="4">4</option>
							<option  value="5">5</option>

						</select>						</div>

						<button class="button" type="submit">INGRESAR</button>
					</form>

					<script src="js/hora.js"></script>
					<script src="js/estatus.js"></script>
				</div>
			</div>
	</main>
	</main>



</body>

</html>