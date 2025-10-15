<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../partials/header.php';

$tables = require __DIR__ . '/tables.php';
?>

<div class="container py-4">
	<div class="d-flex justify-content-between align-items-center mb-4">
		<div>
			<h2 class="text-primary">
				<i class="fas fa-database me-2"></i>Gerir Dados da Biblioteca
			</h2>
			<p class="text-muted">Escolha a tabela que quer gerir</p>
		</div>
		<a href="/GM_Biblioteca/index.php" class="btn btn-secondary friendly-btn">
			<i class="fas fa-arrow-left me-2"></i>Voltar ao Início
		</a>
	</div>

	<div class="row">
		<?php foreach ($tables as $name => $meta): ?>
			<div class="col-md-4 mb-3">
				<div class="card h-100">
					<div class="card-body text-center">
						<div class="icon-large text-primary mb-3">
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
							?>"></i>
						</div>
						<h5 class="card-title"><?php echo htmlspecialchars($meta['label']); ?></h5>
						<p class="card-text text-muted small">
							<?php 
								switch($name) {
									case 'livros': echo 'Gerir todos os livros da biblioteca'; break;
									case 'requisicoes': echo 'Ver e gerir empréstimos'; break;
									case 'utentes': echo 'Informações dos utentes'; break;
									case 'autores': echo 'Lista de autores'; break;
									case 'editoras': echo 'Editoras e contactos'; break;
									case 'generos': echo 'Tipos de livros'; break;
									case 'edicoes': echo 'Números de edição'; break;
									case 'livro_exemplar': echo 'Exemplares físicos'; break;
									case 'estados': echo 'Estados dos livros'; break;
									case 'paises': echo 'Lista de países'; break;
									case 'codigos_postais': echo 'Códigos postais'; break;
									default: echo 'Gerir dados desta tabela';
								}
							?>
						</p>
						<div class="d-grid gap-2">
							<a href="/GM_Biblioteca/crud/list.php?table=<?php echo urlencode($name); ?>" class="btn btn-outline-primary friendly-btn">
								<i class="fas fa-list me-2"></i>Ver Todos
							</a>
							<a href="/GM_Biblioteca/crud/form_<?php echo urlencode($name); ?>.php" class="btn btn-outline-success friendly-btn">
								<i class="fas fa-plus me-2"></i>Adicionar Novo
							</a>
						</div>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
