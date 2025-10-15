<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../partials/header.php';

$tables = require __DIR__ . '/tables.php';
// Permite vir por variável pré-definida ($table) ou querystring
if (!isset($table) || $table === '') {
	$table = $_GET['table'] ?? '';
}
if (!isset($tables[$table])) { die('Tabela inválida'); }
$meta = $tables[$table];
$pk = $meta['pk'];
$id = $_GET['id'] ?? '';

$values = [];
$success_message = '';
$error_message = '';

// Carregar registo para edição
if ($id !== '') {
	$stmt = $mysqli->prepare("SELECT " . implode(', ', array_map(fn($c) => "`$c`", array_keys($meta['columns']))) . " FROM `$table` WHERE `$pk` = ? LIMIT 1");
	$stmt->bind_param('s', $id);
	if ($stmt->execute()) {
		$res = $stmt->get_result();
		$values = $res->fetch_assoc() ?: [];
	}
	$stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	// Recolher valores
	foreach ($meta['columns'] as $col => $cfg) {
		if (!empty($cfg['readonly'])) { continue; }
		if (($cfg['type'] ?? '') === 'boolean') {
			$values[$col] = isset($_POST[$col]) ? '1' : '0';
		} else {
			$values[$col] = trim($_POST[$col] ?? '');
		}
	}

	// Validar mínimos
	foreach ($meta['columns'] as $col => $cfg) {
		if (!empty($cfg['required']) && ($values[$col] ?? '') === '') {
			$error_message = 'Por favor, preencha todos os campos obrigatórios (marcados com *).';
			break;
		}
	}

	if ($error_message === '') {
		if ($id === '') {
			// INSERT
			$cols = [];
			$marks = [];
			$params = [];
			$types = '';
			foreach ($meta['columns'] as $col => $cfg) {
				if (!empty($cfg['readonly'])) { continue; }
				if ($meta['auto_increment'] && $col === $pk) { continue; }
				$cols[] = "`$col`";
				$marks[] = '?';
				$val = $values[$col] ?? '';
				$params[] = $val;
				$types .= (($cfg['type'] ?? '') === 'number' || ($cfg['type'] ?? '') === 'boolean') ? 'i' : 's';
			}
			$sql = "INSERT INTO `$table` (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $marks) . ")";
			$stmt = $mysqli->prepare($sql);
			$stmt->bind_param($types, ...$params);
			if ($stmt->execute()) {
				$success_message = '✅ Registo criado com sucesso!';
				$id = $meta['auto_increment'] ? (string)$mysqli->insert_id : ($values[$pk] ?? '');
			} else {
				$error_message = '❌ Erro ao inserir: ' . $stmt->error;
			}
			$stmt->close();
		} else {
			// UPDATE
			$sets = [];
			$params = [];
			$types = '';
			foreach ($meta['columns'] as $col => $cfg) {
				if (!empty($cfg['readonly'])) { continue; }
				$sets[] = "`$col` = ?";
				$val = $values[$col] ?? '';
				$params[] = $val;
				$types .= (($cfg['type'] ?? '') === 'number' || ($cfg['type'] ?? '') === 'boolean') ? 'i' : 's';
			}
			$sql = "UPDATE `$table` SET " . implode(', ', $sets) . " WHERE `$pk` = ?";
			$params[] = $id;
			$types .= 's';
			$stmt = $mysqli->prepare($sql);
			$stmt->bind_param($types, ...$params);
			if ($stmt->execute()) {
				$success_message = '✅ Registo atualizado com sucesso!';
			} else {
				$error_message = '❌ Erro ao atualizar: ' . $stmt->error;
			}
			$stmt->close();
		}
	}
}
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
				?> me-2"></i><?php echo htmlspecialchars(($id === '' ? 'Adicionar' : 'Editar') . ' ' . $meta['label']); ?>
			</h2>
			<p class="text-muted">
				<?php echo $id === '' ? 'Preencha os dados para criar um novo registo' : 'Modifique os dados conforme necessário'; ?>
			</p>
		</div>
		<div>
			<a href="/GM_Biblioteca/crud/list.php?table=<?php echo urlencode($table); ?>" class="btn btn-secondary friendly-btn">
				<i class="fas fa-arrow-left me-2"></i>Voltar à Lista
			</a>
		</div>
	</div>

	<?php if ($success_message !== ''): ?>
		<div class="alert alert-success alert-dismissible fade show" role="alert">
			<i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success_message); ?>
			<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
		</div>
	<?php endif; ?>
	<?php if ($error_message !== ''): ?>
		<div class="alert alert-danger alert-dismissible fade show" role="alert">
			<i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error_message); ?>
			<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
		</div>
	<?php endif; ?>

	<div class="card">
		<div class="card-header">
			<h5 class="mb-0">
				<i class="fas fa-info-circle me-2"></i>
				Informações do Registo
			</h5>
		</div>
		<div class="card-body">
			<form method="post" class="row g-3">
				<?php foreach ($meta['columns'] as $col => $cfg): ?>
					<?php $valRaw = (string)($values[$col] ?? ''); $val = htmlspecialchars($valRaw); ?>
					<?php
					// Ocultar input do código autoincrement no formulário de criação
					if ($meta['auto_increment'] && $col === $pk && $id === '') {
						continue;
					}
					?>
					<div class="col-md-6">
						<label for="<?php echo $col; ?>" class="form-label">
							<?php 
								$icon = 'fas fa-edit';
								if (strpos($col, 'nome') !== false) $icon = 'fas fa-user';
								elseif (strpos($col, 'email') !== false) $icon = 'fas fa-envelope';
								elseif (strpos($col, 'data') !== false) $icon = 'fas fa-calendar';
								elseif (strpos($col, 'cod') !== false) $icon = 'fas fa-hashtag';
								elseif (strpos($col, 'ano') !== false) $icon = 'fas fa-calendar-alt';
								elseif (strpos($col, 'pais') !== false) $icon = 'fas fa-globe';
								elseif (strpos($col, 'morada') !== false) $icon = 'fas fa-map-marker-alt';
								elseif (strpos($col, 'tlm') !== false) $icon = 'fas fa-phone';
							?>
							<i class="<?php echo $icon; ?> me-1"></i>
							<?php echo htmlspecialchars($cfg['label'] ?? $col); ?>
							<?php echo !empty($cfg['required']) ? '<span class="text-danger">*</span>' : ''; ?>
						</label>
						<?php if (isset($cfg['fk'])): ?>
							<?php
								$fk = $cfg['fk'];
								$fkOptions = [];
								$sqlFk = "SELECT `{$fk['value']}` as v, `{$fk['label']}` as l FROM `{$fk['table']}` ORDER BY `{$fk['label']}`";
								if ($resFk = $mysqli->query($sqlFk)) {
									while ($r = $resFk->fetch_assoc()) { $fkOptions[] = $r; }
									$resFk->free();
								}
							?>
							<?php if ($fk['table'] === 'livros'): ?>
								<div class="input-group">
	 								<?php 
	 									$livro_nome = '';
	 									if ($valRaw !== '') {
	 										$stmtLivro = $mysqli->prepare('SELECT Li_titulo FROM livros WHERE Li_cod = ?');
	 										$stmtLivro->bind_param('i', $valRaw);
	 										if ($stmtLivro->execute()) {
	 											$resLivro = $stmtLivro->get_result();
	 											if ($rowLivro = $resLivro->fetch_assoc()) {
	 												$livro_nome = $rowLivro['Li_titulo'];
	 											}
	 											$resLivro->free();
	 										}
	 										$stmtLivro->close();
	 									}
	 								?>
	 								<input type="text" class="form-control" id="livro_nome_display_<?php echo $col; ?>" value="<?php echo htmlspecialchars($livro_nome); ?>" placeholder="Selecione um livro..." readonly>
									<input type="hidden" id="<?php echo $col; ?>" name="<?php echo $col; ?>" value="<?php echo $val; ?>">
									<button type="button" class="btn btn-outline-primary" onclick="window.open('pesquisar_livro.php?target=<?php echo $col; ?>', 'popupLivro', 'width=700,height=500,scrollbars=yes');">
										<i class="fas fa-search"></i> Pesquisar 
									</button>
								</div>
							<?php else: ?>
								<select class="form-select" id="<?php echo $col; ?>" name="<?php echo $col; ?>" <?php echo !empty($cfg['required']) ? 'required' : ''; ?> <?php echo !empty($cfg['readonly']) ? 'disabled' : ''; ?> >
									<option value="">-- selecione --</option>
									<?php foreach ($fkOptions as $opt): ?>
										<option value="<?php echo htmlspecialchars($opt['v']); ?>" <?php echo ($valRaw !== '' && $valRaw == $opt['v']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($opt['l']); ?></option>
									<?php endforeach; ?>
								</select>
							<?php endif; ?>
						<?php else: ?>
							<?php if (($cfg['type'] ?? 'text') === 'boolean'): ?>
								<div class="form-check form-switch mt-2">
									<input class="form-check-input" type="checkbox" role="switch" id="<?php echo $col; ?>" name="<?php echo $col; ?>" value="1" <?php echo ($valRaw === '1' || strtolower($valRaw) === 'true') ? 'checked' : ''; ?> <?php echo !empty($cfg['readonly']) ? 'disabled' : ''; ?> >
									<label class="form-check-label" for="<?php echo $col; ?>">Ativo</label>
								</div>
							<?php else: ?>
								<input
									type="<?php echo htmlspecialchars($cfg['type'] ?? 'text'); ?>"
									class="form-control"
									id="<?php echo $col; ?>"
									name="<?php echo $col; ?>"
									value="<?php echo $val; ?>"
									<?php echo !empty($cfg['required']) ? 'required' : ''; ?>
									<?php echo !empty($cfg['readonly']) ? 'readonly' : ''; ?>
									placeholder="Digite <?php echo strtolower($cfg['label'] ?? $col); ?>..."
								>
							<?php endif; ?>
						<?php endif; ?>
						<?php if (!empty($cfg['required'])): ?>
							<div class="form-text text-danger">
								<i class="fas fa-exclamation-circle me-1"></i>Campo obrigatório
							</div>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
				<div class="col-12">
					<button type="submit" class="btn btn-primary friendly-btn">
						<i class="fas fa-save me-2"></i><?php echo $id === '' ? 'Criar Registo' : 'Guardar Alterações'; ?>
					</button>
					<button type="reset" class="btn btn-outline-secondary friendly-btn ms-2">
						<i class="fas fa-undo me-2"></i>Limpar Campos
					</button>
				</div>
			</form>
		</div>
	</div>
</div>


<script>
function setLivro(target, cod, titulo) {
	var nomeInput = document.getElementById('livro_nome_display_' + target);
	var codInput = document.getElementById(target);
	if (nomeInput) nomeInput.value = titulo;
	if (codInput) codInput.value = cod;
}
</script>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>


