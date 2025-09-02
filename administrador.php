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
	<title>Toyota Pachuca - Entregas</title>
	<link rel="stylesheet" href="css/style.css">
	<title>Calendario de citas</title>
	<link rel="stylesheet" href="css/style_entregas.css">
	<link rel="stylesheet" href="css/style_calendario.css">


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
				<a href="administrador.php" class="selected">
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
				<a href="registro_entrega.php">
					<img src="icons/date.svg" alt="">
					<span>Programar Entrega</span>
				</a>
			</ul>
		</nav>

	</div>

	<main class="app">
		
		<section class="card appointments" aria-live="polite" aria-atomic="true">
			<div class="section-title">
				<h2>Entregas del día</h2><small id="count"></small>
			</div>
			<div class="date-pill" id="selectedLabel">Selecciona una fecha en el calendario →</div>

			<div class="calendario">
				<div id="resultados" class="pruebas"></div>
			</div>

		</section>

		
		<aside class="card calendar-card">
			<div class="notes" >En caso de no contar con el registro de la ceremonia del té en la plataforma, ésta no se realizará por el área de recepción (SIN EXCEPCIÓN ALGUNA)</div><br></VR>

			<div class="calendar" aria-label="Calendario de citas">
				<div class="cal-head">
					<div class="nav">
						<button id="prev">◀</button>
						<button id="today" title="Ir a hoy">Hoy</button>
						<button id="next">▶</button>
					</div>
					<h3 id="monthLabel"></h3>
				</div>
				<div class="dow">
					<div>Lun</div>
					<div>Mar</div>
					<div>Mié</div>
					<div>Jue</div>
					<div>Vie</div>
					<div>Sáb</div>
					<div>Dom</div>
				</div>
				<div class="days" id="grid" role="grid">
				</div>
				<div class="legend">
					<i class="l-apt"></i> 
						Con entregas 
					<i class="l-none" style="margin-left:10px"></i>
					Sin	entregas
					
				</div>
				<div class="citas-t"><div id="total-citas" class="total">
					</div></div>
				
			</div>


		</aside>
	</main>
	<script src="js/estatus.js"></script>
	<script src="js/script.js"></script>
	<script src="js/calendario_mensual.js"></script>


</body>

</html>