<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../partials/header.php';

$tables = require __DIR__ . '/tables.php';
$table = $_GET['table'] ?? '';
if (!isset($tables[$table])) {
	die('Tabela inválida');
}
$meta = $tables[$table];

// Processar eliminação
$delete_success = '';
$delete_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
	$deleteId = trim($_POST['id'] ?? '');
	if ($deleteId === '') {
		$delete_error = 'ID inválido para eliminação.';
	} else {
		$sqlDel = "DELETE FROM `$table` WHERE `{$meta['pk']}` = ?";
		$stmtDel = $mysqli->prepare($sqlDel);
		if ($stmtDel) {
			// Usa tipo string por compatibilidade (PK pode não ser numérico em todas as tabelas)
			$stmtDel->bind_param('s', $deleteId);
			if ($stmtDel->execute()) {
				$delete_success = 'Registo eliminado com sucesso.';
			} else {
				$delete_error = 'Erro ao eliminar: ' . $stmtDel->error;
			}
			$stmtDel->close();
		} else {
			$delete_error = 'Erro a preparar a eliminação: ' . $mysqli->error;
		}
	}
}

// Paginação e pesquisa
$page = max(1, (int)($_GET['page'] ?? 1));
// Número de registos por página (padrão 20, mas pode ser alterado pelo utilizador)
$per_page = isset($_GET['per_page']) && is_numeric($_GET['per_page']) 
    ? max(1, min(200, (int)$_GET['per_page'])) // limite opcional (ex: máximo 200)
    : 20;
$offset = ($page - 1) * $per_page;
$search = trim($_GET['search'] ?? '');
$devolvido_filter = ($table === 'requisicoes') ? ($_GET['devolvido'] ?? '') : '';
$atraso_filter = ($table === 'requisicoes') ? ($_GET['atraso'] ?? '') : '';

// Construir query com pesquisa
// Para livro_exemplar, adicionar coluna virtual Li_titulo
if ($table === 'livro_exemplar') {
	$cols = array_keys($meta['columns']);
	$cols[] = 'Li_titulo'; // coluna virtual
	$colList = implode(', ', array_map(fn($c) => $c === 'Li_titulo' ? '`livros`.`Li_titulo` AS `Li_titulo`' : "`$c`", $cols));
} else {
	$cols = array_keys($meta['columns']);
	$colList = implode(', ', array_map(fn($c) => "`$c`", $cols));
}
$whereClause = '';
$params = [];
$types = '';

if ($search !== '' || ($table === 'requisicoes' && ($devolvido_filter !== '' || $atraso_filter !== ''))) {
	$searchConditions = [];
	if ($search !== '') {
		foreach ($cols as $col) {
			$searchConditions[] = "`$col` LIKE ?";
			$params[] = "%$search%";
			$types .= 's';
		}
	}
	if ($table === 'requisicoes' && $devolvido_filter !== '') {
		$searchConditions[] = "`re_devolvido` = ?";
		$params[] = $devolvido_filter;
		$types .= 'i';
	}
	if ($table === 'requisicoes' && $atraso_filter === '1') {
		$searchConditions[] = "`re_devolvido` = 0 AND STR_TO_DATE(`re_datadevolucao`, '%Y-%m-%d') < CURDATE()";
	}
	$whereClause = 'WHERE ' . implode(' AND ', $searchConditions);
}

// Contar total de registos
$countSql = "SELECT COUNT(*) as total FROM `$table` $whereClause";
$countStmt = $mysqli->prepare($countSql);
if (!empty($params)) {
	$countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$totalRows = $countStmt->get_result()->fetch_assoc()['total'];
$countStmt->close();

// Buscar registos com paginação
$sql = ($table === 'livro_exemplar')
	? "SELECT $colList FROM `livro_exemplar` LEFT JOIN `livros` ON `livro_exemplar`.`Lex_Li_cod` = `livros`.`Li_cod` $whereClause ORDER BY `livro_exemplar`.`Lex_cod` DESC LIMIT $per_page OFFSET $offset"
	: "SELECT $colList FROM `$table` $whereClause ORDER BY `{$meta['pk']}` DESC LIMIT $per_page OFFSET $offset";
$stmt = $mysqli->prepare($sql);
if (!empty($params)) {
	$stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Calcular paginação
$totalPages = ceil($totalRows / $per_page);
?>

<div class="container py-4">
	<div class="d-flex justify-content-between align-items-center mb-4">
		<div>
			<h2 class="text-primary">
				<i class="fas fa-<?php 
					switch($table) {
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
				?> me-2"></i><?php echo htmlspecialchars($meta['label']); ?>
			</h2>
			<p class="text-muted">Gerir registos de <?php echo strtolower($meta['label']); ?></p>
		</div>
		<div>
			<a href="/GM_Biblioteca/crud/form_<?php echo urlencode($table); ?>.php" class="btn btn-success friendly-btn">
				<i class="fas fa-plus me-2"></i>Adicionar
			</a>
			<?php
			// Lógica para determinar o último URL relevante
			$referer = $_SERVER['HTTP_REFERER'] ?? '';
			$formUrl = '/GM_Biblioteca/crud/form_' . urlencode($table) . '.php';
			$selfUrl = $_SERVER['REQUEST_URI'];
			$defaultUrl = '/GM_Biblioteca/index.php';

			// Se o referer for o próprio list.php (com ou sem filtros) ou o form da tabela, ignora
			if ($referer &&
				strpos($referer, 'list.php') === false &&
				strpos($referer, $formUrl) === false) {
				$returnUrl = $referer;
			} else {
				$returnUrl = $defaultUrl;
			}
			?>
			<a href="<?php echo htmlspecialchars($returnUrl); ?>" class="btn btn-secondary friendly-btn">
				<i class="fas fa-arrow-left me-2"></i>Voltar
			</a>
		</div>
	</div>

	<!-- Barra de pesquisa -->
	<div class="card mb-4">
		<div class="card-body">
			<?php if ($delete_success !== ''): ?>
				<div class="alert alert-success alert-dismissible fade show" role="alert">
					<i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($delete_success); ?>
					<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
				</div>
			<?php endif; ?>
			<?php if ($delete_error !== ''): ?>
				<div class="alert alert-danger alert-dismissible fade show" role="alert">
					<i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($delete_error); ?>
					<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
				</div>
			<?php endif; ?>
			<div class="row">
				<div class="col-md-8">
					<form method="GET" class="d-flex flex-wrap gap-2">
						<input type="hidden" name="table" value="<?php echo htmlspecialchars($table); ?>">
						<div class="input-group flex-grow-1">
							<span class="input-group-text"><i class="fas fa-search"></i></span>
							<input type="text"
								class="form-control"
								name="search"
								placeholder="Pesquisar em todos os campos..."
								value="<?php echo htmlspecialchars($search); ?>">
						</div>
						<?php if ($table === 'requisicoes'): ?>
						<div class="input-group" style="width:180px;">
							<label class="input-group-text" for="devolvido">Devolução</label>
							<select class="form-select" name="devolvido" id="devolvido" onchange="this.form.submit()">
								<option value="">Todos</option>
								<option value="1" <?php if ($devolvido_filter === '1') echo 'selected'; ?>>Devolvidos</option>
								<option value="0" <?php if ($devolvido_filter === '0') echo 'selected'; ?>>Não devolvidos</option>
							</select>
						</div>
						<div class="input-group" style="width:180px;">
							<label class="input-group-text" for="atraso">Atraso</label>
							<select class="form-select" name="atraso" id="atraso" onchange="this.form.submit()">
								<option value="">Todos</option>
								<option value="1" <?php if ($atraso_filter === '1') echo 'selected'; ?>>Em atraso</option>
							</select>
						</div>
						<?php endif; ?>
						<div class="input-group" style="width:150px;">
							<label class="input-group-text" for="per_page">Por pág.</label>
							<select class="form-select" name="per_page" id="per_page" onchange="this.form.submit()">
								<?php foreach ([10,20,50,100] as $n): ?>
									<option value="<?php echo $n; ?>" <?php if ($per_page == $n) echo 'selected'; ?> >
										<?php echo $n; ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>
						<button type="submit" class="btn btn-primary">
							<i class="fas fa-search me-1"></i>Pesquisar
						</button>
						<?php if ($search !== '' || ($table === 'requisicoes' && $devolvido_filter !== '')): ?>
							<a href="?table=<?php echo urlencode($table); ?>&per_page=<?php echo $per_page; ?>"
							class="btn btn-outline-secondary">Limpar</a>
						<?php endif; ?>
					</form>
				</div>
				<div class="col-md-4 text-end">
					<small class="text-muted">
						<i class="fas fa-info-circle me-1"></i>
						Mostrando <?php echo $offset + 1; ?>-<?php echo min($offset + $per_page, $totalRows); ?> de <?php echo $totalRows; ?> registos
					</small>
				</div>
			</div>

		</div>

	<div class="card">
		<div class="card-body p-0">
			<div class="table-responsive">
				<table class="table table-hover mb-0">
					<thead class="table-light">
						<tr>
							<?php foreach ($cols as $c): ?>
								<?php if ($table === 'livro_exemplar' && $c === 'Lex_Li_cod'): ?>
									<th>Título do Livro</th>
								<?php elseif ($table === 'livro_exemplar' && $c === 'Li_titulo'): ?>
									<?php /* não mostrar coluna extra, já substituída */ ?>
								<?php else: ?>
									<th><?php echo htmlspecialchars($meta['columns'][$c]['label'] ?? $c); ?></th>
								<?php endif; ?>
							<?php endforeach; ?>
							<?php if ($table === 'livros'): ?>
								<th>Autores</th>
							<?php endif; ?>
							<th style="width: 100px;">Ações</th>
						</tr>
					</thead>
					<tbody>
						<?php if ($result && $result->num_rows > 0): ?>
							<?php while ($row = $result->fetch_assoc()): ?>
								<tr>
									<?php foreach ($cols as $c): ?>
										<?php if ($table === 'livro_exemplar' && $c === 'Lex_Li_cod'): ?>
											<td><?php echo htmlspecialchars((string)($row['Li_titulo'] ?? '')); ?></td>
										<?php elseif ($table === 'livro_exemplar' && $c === 'Li_titulo'): ?>
											<?php /* não mostrar coluna extra, já substituída */ ?>
										<?php elseif (($meta['columns'][$c]['type'] ?? '') === 'boolean'): ?>
											<td class="text-center">
												<?php if ((string)$row[$c] === '1'): ?>
													<span class="text-success"><i class="fas fa-check"></i></span>
												<?php else: ?>
													<span class="text-danger"><i class="fas fa-times"></i></span>
												<?php endif; ?>
											</td>
										<?php else: ?>
											<td><?php echo htmlspecialchars((string)($row[$c] ?? '')); ?></td>
										<?php endif; ?>
									<?php endforeach; ?>
									<?php if ($table === 'livros'): ?>
										<td>
											<?php
											// Buscar autores deste livro
											$livro_cod = $row['Li_cod'] ?? null;
											$autores = [];
											if ($livro_cod) {
												$stmtAutores = $mysqli->prepare('SELECT a.au_nome FROM livros_autores la JOIN autores a ON la.la_au_cod = a.au_cod WHERE la.la_li_cod = ?');
												if ($stmtAutores) {
													$stmtAutores->bind_param('i', $livro_cod);
													$stmtAutores->execute();
													$resAutores = $stmtAutores->get_result();
													while ($autorRow = $resAutores->fetch_assoc()) {
														$autores[] = $autorRow['au_nome'];
													}
													$stmtAutores->close();
												}
											}
											echo htmlspecialchars(implode(', ', $autores));
											?>
										</td>
									<?php endif; ?>
									<td>
										<a class="btn btn-sm btn-outline-info me-1" href="/GM_Biblioteca/crud/form_<?php echo urlencode($table); ?>.php?id=<?php echo urlencode($row[$meta['pk']] ?? ''); ?>" title="Editar">
											<i class="fas fa-edit"></i>
										</a>
										<button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal" data-id="<?php echo htmlspecialchars((string)($row[$meta['pk']] ?? '')); ?>">
											<i class="fas fa-trash"></i>
										</button>
									</td>
								</tr>
							<?php endwhile; ?>
						<?php else: ?>
							<tr>
								<td colspan="<?php echo count($cols) + ($table === 'livros' ? 2 : 1); ?>" class="text-center text-muted py-4">
									<i class="fas fa-inbox me-2"></i>
									<?php if ($search !== ''): ?>
										Nenhum registo encontrado para "<?php echo htmlspecialchars($search); ?>"
									<?php else: ?>
										Nenhum registo encontrado
									<?php endif; ?>
								</td>
							</tr>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>

	<!-- Paginação -->
	<?php if ($totalPages > 1): ?>
		<nav aria-label="Paginação" class="mt-4">
			<ul class="pagination justify-content-center">
				<!-- Página anterior -->
				<li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
					<?php if ($page > 1): ?>
						<a class="page-link" href="?table=<?php echo urlencode($table); ?>&page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?> &per_page=<?php echo $per_page; ?>">
							<i class="fas fa-chevron-left me-1"></i>Anterior
						</a>
					<?php else: ?>
						<span class="page-link"><i class="fas fa-chevron-left me-1"></i>Anterior</span>
					<?php endif; ?>
				</li>

				<!-- Páginas -->
				<?php
				$start = max(1, $page - 2);
				$end = min($totalPages, $page + 2);
				
				if ($start > 1): ?>
					<li class="page-item">
						<a class="page-link" href="?table=<?php echo urlencode($table); ?>&page=1&search=<?php echo urlencode($search); ?>&per_page=<?php echo $per_page; ?>">1</a>
					</li>
					<?php if ($start > 2): ?>
						<li class="page-item disabled"><span class="page-link">...</span></li>
					<?php endif; ?>
				<?php endif; ?>

				<?php for ($i = $start; $i <= $end; $i++): ?>
					<li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
						<a class="page-link" href="?table=<?php echo urlencode($table); ?>&page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
					</li>
				<?php endfor; ?>

				<?php if ($end < $totalPages): ?>
					<?php if ($end < $totalPages - 1): ?>
						<li class="page-item disabled"><span class="page-link">...</span></li>
					<?php endif; ?>
					<li class="page-item">
						<a class="page-link" href="?table=<?php echo urlencode($table); ?>&page=<?php echo $totalPages; ?>&search=<?php echo urlencode($search); ?>"><?php echo $totalPages; ?></a>
					</li>
				<?php endif; ?>

				<!-- Página seguinte -->
				<li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
					<?php if ($page < $totalPages): ?>
						<a class="page-link" href="?table=<?php echo urlencode($table); ?>&page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&per_page=<?php echo $per_page; ?>">
							Seguinte<i class="fas fa-chevron-right ms-1"></i>
						</a>
					<?php else: ?>
						<span class="page-link">Seguinte<i class="fas fa-chevron-right ms-1"></i></span>
					<?php endif; ?>
				</li>
			</ul>
		</nav>
	<?php endif; ?>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
 
<!-- Modal de confirmação de eliminação -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="confirmDeleteLabel"><i class="fas fa-trash me-2 text-danger"></i>Confirmar eliminação</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				Tem a certeza que deseja eliminar este registo? Esta ação não pode ser desfeita.
			</div>
			<div class="modal-footer">
				<form method="post" class="ms-auto">
					<input type="hidden" name="action" value="delete">
					<input type="hidden" name="id" id="delete-id" value="">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
					<button type="submit" class="btn btn-danger"><i class="fas fa-trash me-1"></i>Eliminar</button>
				</form>
			</div>
		</div>
	</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
	var modal = document.getElementById('confirmDeleteModal');
	if (!modal) return;
	modal.addEventListener('show.bs.modal', function (event) {
		var button = event.relatedTarget;
		var id = button.getAttribute('data-id');
		document.getElementById('delete-id').value = id;
	});
});
</script>
