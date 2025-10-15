<?php
require_once __DIR__ . '/partials/header.php';
$tables = require __DIR__ . '/crud/tables.php';
?>

<div class="container-fluid py-4">
	<div class="row">
		<!-- Sidebar -->
		<div class="col-md-3">
			<div class="card">
				<div class="card-header">
					<h5 class="mb-0"><i class="fas fa-list me-2"></i>Menu Principal</h5>
				</div>
				<div class="card-body p-0">
					<div class="list-group list-group-flush">
						<?php foreach ($tables as $name => $meta): ?>
							<div class="list-group-item sidebar-item">
								<button class="btn btn-link text-start w-100 p-0" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?php echo htmlspecialchars($name); ?>" aria-expanded="false">
									<i class="fas fa-<?php 
										switch($name) {
											case 'livros': echo 'book'; break;
											case 'requisicoes': echo 'hand-holding'; break;
											case 'utentes': echo 'users'; break;
											case 'autores': echo 'user-edit'; break;
											case 'editoras': echo 'building'; break;
											case 'generos': echo 'tags'; break;
											case 'edicoes': echo 'layer-group'; break;
											case 'livro_exemplar': echo 'copy'; break;
											case 'estados': echo 'check-circle'; break;
											case 'paises': echo 'globe'; break;
											case 'codigos_postais': echo 'map-marker-alt'; break;
											default: echo 'table';
										}
									?> me-2"></i>
									<?php echo htmlspecialchars($meta['label']); ?>
									<i class="fas fa-chevron-down float-end"></i>
								</button>
								<div class="collapse" id="collapse-<?php echo htmlspecialchars($name); ?>">
									<div class="mt-2">
										<a href="crud/list.php?table=<?php echo urlencode($name); ?>" class="btn btn-sm btn-outline-primary me-1">
											<i class="fas fa-list me-1"></i>Ver Todos
										</a>
										<a href="/GM_Biblioteca/crud/form_<?php echo urlencode($name); ?>.php" class="btn btn-sm btn-outline-success">
											<i class="fas fa-plus me-1"></i>Adicionar
										</a>
									</div>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</div>
		
		<!-- Main Content -->
		<div class="col-md-9">
			<div class="text-center mb-4">
				<h1 class="display-4 text-primary">
					<i class="fas fa-book-open me-3"></i>Gestão da Biblioteca
				</h1>
				<p class="lead text-muted">Sistema de gestão simples e intuitivo</p>
			</div>
			
			<div class="row">
				<div class="col-md-4 mb-4">
					<div class="quick-action">
						<div class="icon-large text-primary">
							<i class="fas fa-book"></i>
						</div>
						<h4>Registar Novo Livro</h4>
						<p class="text-muted">Adicione um novo livro à biblioteca</p>
						<a href="livros_create.php" class="btn btn-primary friendly-btn">
							<i class="fas fa-plus me-2"></i>Começar
						</a>
					</div>
				</div>
				<div class="col-md-4 mb-4">
					<div class="quick-action">
						<div class="icon-large text-success">
							<i class="fas fa-hand-holding"></i>
						</div>
						<h4>Nova Requisição</h4>
						<p class="text-muted">Registe quando alguém requisita um livro</p>
						<a href="requisicoes_create.php" class="btn btn-success friendly-btn">
							<i class="fas fa-plus me-2"></i>Começar
						</a>
					</div>
				</div>
				<div class="col-md-4 mb-4">
					<div class="quick-action">
						<div class="icon-large text-danger">
							<i class="fas fa-list"></i>
						</div>
						<h4>Gerir Requisições</h4>
						<p class="text-muted">Veja, edite e acompanhe todas as requisições</p>
						<a href="crud/list.php?table=requisicoes" class="btn btn-danger friendly-btn">
							<i class="fas fa-eye me-2"></i>Ver Requisições
						</a>
					</div>
				</div>
			</div>
			
			<div class="row">
				<div class="col-md-6 mb-4">
					<div class="quick-action">
						<div class="icon-large text-info">
							<i class="fas fa-users"></i>
						</div>
						<h4>Gerir Utentes</h4>
						<p class="text-muted">Veja e edite informações dos utentes</p>
						<a href="crud/list.php?table=utentes" class="btn btn-info friendly-btn">
							<i class="fas fa-eye me-2"></i>Ver Utentes
						</a>
					</div>
				</div>
				<div class="col-md-6 mb-4">
					<div class="quick-action">
						<div class="icon-large text-warning">
							<i class="fas fa-database"></i>
						</div>
						<h4>Gerir Todos os Dados</h4>
						<p class="text-muted">Aceda a todas as opções de gestão</p>
						<a href="crud/" class="btn btn-warning friendly-btn">
							<i class="fas fa-cog me-2"></i>Gerir Dados
						</a>
					</div>
				</div>
			</div>
			
			<div class="card mt-4">
				<div class="card-header">
					<h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Como usar este sistema</h5>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col-md-4 text-center">
							<i class="fas fa-mouse-pointer text-primary mb-2" style="font-size: 2rem;"></i>
							<h6>1. Clique</h6>
							<p class="text-muted small">Clique no botão da ação que quer fazer</p>
						</div>
						<div class="col-md-4 text-center">
							<i class="fas fa-keyboard text-success mb-2" style="font-size: 2rem;"></i>
							<h6>2. Preencha</h6>
							<p class="text-muted small">Preencha os campos obrigatórios (marcados com *)</p>
						</div>
						<div class="col-md-4 text-center">
							<i class="fas fa-save text-info mb-2" style="font-size: 2rem;"></i>
							<h6>3. Guarde</h6>
							<p class="text-muted small">Clique em "Guardar" para confirmar</p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<?php
require_once __DIR__ . '/partials/footer.php';
?>
