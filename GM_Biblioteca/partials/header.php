<?php
// header comum
?>
<!doctype html>
<html lang="pt">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>GM Biblioteca - Sistema de Gestão</title>
	<link rel="apple-touch-icon" sizes="180x180" href="/GM_Biblioteca/favicon/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="/GM_Biblioteca/favicon/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="/GM_Biblioteca/favicon/favicon-16x16.png?v=1">
	<link rel="shortcut icon" type="image/png" href="/GM_Biblioteca/favicon/favicon-16x16.png?v=1">
	<link rel="manifest" href="/GM_Biblioteca/favicon/site.webmanifest">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
	<style>
		:root {
			--primary-color:#a02c2c;
			--secondary-color: #f8f9fa;
			--accent-color: #28a745;
			--warning-color: #ffc107;
			--danger-color: #dc3545;
		}
		.navbar-brand {
			font-weight: bold;
			font-size: 1.5rem;
		}
		.btn-primary {
			background-color: var(--primary-color);
			border-color: var(--primary-color);
		}
		.btn-primary:hover {
			background-color: #6a1717;
			border-color: #6a1717;
		}
		.card {
			border: none;
			box-shadow: 0 2px 10px rgba(0,0,0,0.1);
			border-radius: 15px;
		}
		.card-header {
			background: linear-gradient(135deg, var(--primary-color), #8b1e1e);
			color: white;
			border-radius: 15px 15px 0 0 !important;
			font-weight: bold;
		}
		.sidebar-item {
			transition: all 0.3s ease;
			border-radius: 10px;
			margin-bottom: 5px;
		}
		.sidebar-item:hover {
			background-color: #fdeaea;
			transform: translateX(5px);
		}
		.sidebar-item.active {
			background-color: var(--primary-color);
			color: white;
		}
		/* Sidebar em vermelho */
		.sidebar-item .btn-link {
			color: var(--primary-color);
			text-decoration: none;
			font-weight: 600;
		}
		.sidebar-item .btn-link:hover {
			color: #6a1717;
		}
		.sidebar-item .fa-chevron-down {
			color: var(--primary-color);
		}
		.list-group-item {
			border: 0;
		}
		.quick-action {
			background: linear-gradient(135deg, #f8f9fa, #e9ecef);
			border-radius: 15px;
			padding: 20px;
			text-align: center;
			transition: transform 0.3s ease;
		}
		.quick-action:hover {
			transform: translateY(-5px);
			box-shadow: 0 5px 20px rgba(0,0,0,0.15);
		}
		.icon-large {
			font-size: 2.5rem;
			margin-bottom: 15px;
		}
		.text-muted {
			color: #6c757d !important;
		}
		/* Tornar o 'primary' vermelho em todo o site */
		.text-primary {
			color: var(--primary-color) !important;
		}
		.btn-outline-primary {
			color: var(--primary-color);
			border-color: var(--primary-color);
		}
		.btn-outline-primary:hover {
			background-color: var(--primary-color);
			border-color: var(--primary-color);
			color: #fff;
		}
		.friendly-btn {
			border-radius: 25px;
			padding: 12px 25px;
			font-weight: 500;
			transition: all 0.3s ease;
		}
		.friendly-btn:hover {
			transform: translateY(-2px);
			box-shadow: 0 4px 15px rgba(0,0,0,0.2);
		}
	</style>
</head>
<body>
	<nav class="navbar navbar-expand-lg navbar-dark" style="background: linear-gradient(135deg, var(--primary-color), #8b1e1e);">
		<div class="container">
			<a class="navbar-brand" href="/GM_Biblioteca/">
				<i class="fas fa-book-open me-2"></i>GM Biblioteca
			</a>
			<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
				<span class="navbar-toggler-icon"></span>
			</button>
			<div class="collapse navbar-collapse" id="navbarNav">
				<ul class="navbar-nav">
					<li class="nav-item">
						<a class="nav-link" href="/GM_Biblioteca/livros_create.php">
							<i class="fas fa-book me-1"></i>Registar Livro
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="/GM_Biblioteca/requisicoes_create.php">
							<i class="fas fa-hand-holding me-1"></i>Registar Empréstimo
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="/GM_Biblioteca/crud/">
							<i class="fas fa-database me-1"></i>Gerir Dados
						</a>
					</li>
				</ul>
			</div>
		</div>
	</nav>
