<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/partials/header.php';


$success_message = '';
$error_message = '';
$id = isset($_GET['id']) ? trim($_GET['id']) : '';

// Carregar dados do livro para edição
if ($id !== '') {
	$stmt = $mysqli->prepare('SELECT Li_ISBN, Li_titulo, Li_genero, Li_ano, Li_edicao, Li_editora FROM livros WHERE Li_cod = ?');
	$stmt->bind_param('i', $id);
	if ($stmt->execute()) {
		$res = $stmt->get_result();
		if ($row = $res->fetch_assoc()) {
			$Li_ISBN = $row['Li_ISBN'];
			$Li_titulo = $row['Li_titulo'];
			$Li_genero = $row['Li_genero'];
			$Li_ano = $row['Li_ano'];
			$Li_edicao = $row['Li_edicao'];
			$Li_editora = $row['Li_editora'];
		}
		$res->free();
	}
	$stmt->close();
	// Carregar autores associados
	$Li_autor_array = [];
	$stmt = $mysqli->prepare('SELECT a.au_nome FROM livros_autores la JOIN autores a ON la.la_au_cod = a.au_cod WHERE la.la_li_cod = ?');
	$stmt->bind_param('i', $id);
	if ($stmt->execute()) {
		$res = $stmt->get_result();
		while ($row = $res->fetch_assoc()) {
			$Li_autor_array[] = $row['au_nome'];
		}
		$res->free();
	}
	$stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$Li_ISBN = trim($_POST['Li_ISBN'] ?? '');
	$Li_titulo = trim($_POST['Li_titulo'] ?? '');
	if (isset($_POST['Li_autor']) && is_array($_POST['Li_autor'])) {
		$Li_autor_array = array_map('trim', $_POST['Li_autor']);
		$Li_autor_array = array_filter($Li_autor_array, fn($v) => $v !== '');
		$Li_autor = implode(', ', $Li_autor_array);
	} else {
		$Li_autor = trim($_POST['Li_autor'] ?? '');
		$Li_autor_array = $Li_autor === '' ? [] : [$Li_autor];
	}
	$Li_genero = trim($_POST['Li_genero'] ?? '');
	$Li_ano = trim($_POST['Li_ano'] ?? '');
	$Li_edicao = trim($_POST['Li_edicao'] ?? '');
	$Li_editora = trim($_POST['Li_editora'] ?? '');
	$num_exemplares = (int)($_POST['num_exemplares'] ?? 0);
	$perm_requisicao = isset($_POST['perm_requisicao']) ? 1 : 0;

	if ($Li_ISBN === '' || $Li_titulo === '' || empty($Li_autor_array)) {
		$error_message = 'Por favor, preencha todos os campos obrigatórios (ISBN, Título e pelo menos um Autor).';
	} else {
		if ($id === '') {
			// INSERIR NOVO
			$stmt = $mysqli->prepare('INSERT INTO livros (Li_ISBN, Li_titulo, Li_genero, Li_ano, Li_edicao, Li_editora) VALUES (?, ?, ?, ?, ?, ?)');
			if (!$stmt) {
				$error_message = 'Erro a preparar a instrução: ' . $mysqli->error;
			} else {
				$stmt->bind_param('sssiss', $Li_ISBN, $Li_titulo, $Li_genero, $Li_ano, $Li_edicao, $Li_editora);
				if ($stmt->execute()) {
					$li_cod_novo = $mysqli->insert_id;
					// Inserir autores
					foreach ($Li_autor_array as $autor_nome) {
						$stmtAutor = $mysqli->prepare('SELECT au_cod FROM autores WHERE au_nome = ?');
						if ($stmtAutor) {
							$stmtAutor->bind_param('s', $autor_nome);
							$stmtAutor->execute();
							$stmtAutor->bind_result($au_cod);
							if ($stmtAutor->fetch()) {
								$stmtAutor->close();
								$stmtInsert = $mysqli->prepare('INSERT INTO livros_autores (la_li_cod, la_au_cod) VALUES (?, ?)');
								if ($stmtInsert) {
									$stmtInsert->bind_param('ii', $li_cod_novo, $au_cod);
									$stmtInsert->execute();
									$stmtInsert->close();
								}
							} else {
								$stmtAutor->close();
							}
						}
					}
					$exemplares_criados = 0;
					if ($li_cod_novo && $num_exemplares > 0) {
						$insEx = $mysqli->prepare('INSERT INTO livro_exemplar (Lex_Li_cod, Lex_estado, Lex_disponivel, Lex_permrequisicao) VALUES (?, ?, ?, ?)');
						if ($insEx) {
							$estado_default = 'Disponível';
							$disponivel_default = 1;
							for ($i = 0; $i < $num_exemplares; $i++) {
								$insEx->bind_param('isii', $li_cod_novo, $estado_default, $disponivel_default, $perm_requisicao);
								if ($insEx->execute()) {
									$exemplares_criados++;
								}
							}
							$insEx->close();
						}
					}
					$success_message = '✅ Livro registado com sucesso!' . ($num_exemplares > 0 ? " (" . $exemplares_criados . " exemplar(es) criado(s))" : '');
					$Li_ISBN = $Li_titulo = $Li_autor = $Li_genero = $Li_ano = $Li_edicao = $Li_editora = '';
					$Li_autor_array = [];
				} else {
					$error_message = '❌ Erro ao inserir: ' . $stmt->error;
				}
				$stmt->close();
			}
		} else {
			// EDITAR EXISTENTE
			$stmt = $mysqli->prepare('UPDATE livros SET Li_ISBN=?, Li_titulo=?, Li_genero=?, Li_ano=?, Li_edicao=?, Li_editora=? WHERE Li_cod=?');
			if (!$stmt) {
				$error_message = 'Erro a preparar a instrução: ' . $mysqli->error;
			} else {
				$stmt->bind_param('sssissi', $Li_ISBN, $Li_titulo, $Li_genero, $Li_ano, $Li_edicao, $Li_editora, $id);
				if ($stmt->execute()) {
					// Atualizar autores
					$mysqli->query('DELETE FROM livros_autores WHERE la_li_cod = ' . intval($id));
					foreach ($Li_autor_array as $autor_nome) {
						$stmtAutor = $mysqli->prepare('SELECT au_cod FROM autores WHERE au_nome = ?');
						if ($stmtAutor) {
							$stmtAutor->bind_param('s', $autor_nome);
							$stmtAutor->execute();
							$stmtAutor->bind_result($au_cod);
							if ($stmtAutor->fetch()) {
								$stmtAutor->close();
								$stmtInsert = $mysqli->prepare('INSERT INTO livros_autores (la_li_cod, la_au_cod) VALUES (?, ?)');
								if ($stmtInsert) {
									$stmtInsert->bind_param('ii', $id, $au_cod);
									$stmtInsert->execute();
									$stmtInsert->close();
								}
							} else {
								$stmtAutor->close();
							}
						}
					}
					$success_message = '✅ Livro atualizado com sucesso!';
				} else {
					$error_message = '❌ Erro ao atualizar: ' . $stmt->error;
				}
				$stmt->close();
			}
		}
	}
}

// Carregar listas para comboboxes
$lista_autores = [];
$lista_generos = [];
$lista_edicoes = [];
$lista_editoras = [];

if ($res = $mysqli->query("SELECT au_nome FROM autores ORDER BY au_nome")) {
	while ($row = $res->fetch_assoc()) { $lista_autores[] = $row['au_nome']; }
	$res->free();
}
if ($res = $mysqli->query("SELECT genero FROM generos ORDER BY genero")) {
	while ($row = $res->fetch_assoc()) { $lista_generos[] = $row['genero']; }
	$res->free();
}
if ($res = $mysqli->query("SELECT edc_edicao FROM edicoes ORDER BY edc_edicao")) {
	while ($row = $res->fetch_assoc()) { $lista_edicoes[] = $row['edc_edicao']; }
	$res->free();
}
if ($res = $mysqli->query("SELECT edt_nome FROM editoras ORDER BY edt_nome")) {
	while ($row = $res->fetch_assoc()) { $lista_editoras[] = $row['edt_nome']; }
	$res->free();
}
?>

<div class="container py-4">
	<div class="d-flex justify-content-between align-items-center mb-4">
		<div>
			<h2 class="text-primary">
				<i class="fas fa-book me-2"></i>Registar Novo Livro
			</h2>
			<p class="text-muted">Preencha as informações do livro para adicionar à biblioteca</p>
		</div>
		<button type="button" class="btn btn-secondary friendly-btn" onclick="window.history.back();">
			<i class="fas fa-arrow-left me-2"></i>Voltar
		</button>
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
			<h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informações do Livro</h5>
		</div>
		<div class="card-body">
			<form method="post" class="row g-3">
				<div class="col-md-4">
					<label for="Li_ISBN" class="form-label">
						<i class="fas fa-barcode me-1"></i>ISBN *
					</label>
					<input type="text" class="form-control" id="Li_ISBN" name="Li_ISBN" value="<?php echo htmlspecialchars($Li_ISBN ?? ''); ?>" required placeholder="Ex: 978-1234567890">
					<div class="form-text">Código único do livro (obrigatório)</div>
				</div>
				<div class="col-md-8">
					<label for="Li_titulo" class="form-label">
						<i class="fas fa-heading me-1"></i>Título *
					</label>
					<input type="text" class="form-control" id="Li_titulo" name="Li_titulo" value="<?php echo htmlspecialchars($Li_titulo ?? ''); ?>" required placeholder="Ex: O Pequeno Príncipe">
					<div class="form-text">Nome do livro (obrigatório)</div>
				</div>
		<div class="col-md-6">
			<label class="form-label">
				<i class="fas fa-user-edit me-1"></i>Autor(es) *
			</label>
			<div class="input-group">
				<button type="button" class="btn btn-outline-primary" id="btn-pesquisar-autor">
					<i class="fas fa-search me-1"></i>Pesquisar Autor
				</button>
			</div>
			<div class="form-text">Clique em pesquisar para adicionar autores à lista abaixo</div>
			<div class="mt-2" id="selected-authors"></div>
		</div>
				<div class="col-md-6">
					<label for="Li_genero" class="form-label">
						<i class="fas fa-tags me-1"></i>Género
					</label>
					<select class="form-select" id="Li_genero" name="Li_genero">
						<option value="">-- Escolha o género --</option>
						<?php foreach ($lista_generos as $genero): ?>
							<option value="<?php echo htmlspecialchars($genero); ?>" <?php echo (isset($Li_genero) && $Li_genero === $genero) ? 'selected' : ''; ?>><?php echo htmlspecialchars($genero); ?></option>
						<?php endforeach; ?>
					</select>
					<div class="form-text">Tipo de livro (opcional)</div>
				</div>
				<div class="col-md-4">
					<label for="Li_ano" class="form-label">
						<i class="fas fa-calendar me-1"></i>Ano de Publicação
					</label>
					<input type="number" class="form-control" id="Li_ano" name="Li_ano" value="<?php echo htmlspecialchars($Li_ano ?? ''); ?>" placeholder="Ex: 2023" min="1000" max="2100">
					<div class="form-text">Ano em que foi publicado (opcional)</div>
				</div>
				<div class="col-md-4">
					<label for="Li_edicao" class="form-label">
						<i class="fas fa-layer-group me-1"></i>Edição
					</label>
					<select class="form-select" id="Li_edicao" name="Li_edicao">
						<option value="">-- Escolha a edição --</option>
						<?php foreach ($lista_edicoes as $edicao): ?>
							<option value="<?php echo htmlspecialchars($edicao); ?>" <?php echo (isset($Li_edicao) && $Li_edicao === $edicao) ? 'selected' : ''; ?>><?php echo htmlspecialchars($edicao); ?></option>
						<?php endforeach; ?>
					</select>
					<div class="form-text">Número da edição (opcional)</div>
				</div>
				<div class="col-md-4">
					<label for="Li_editora" class="form-label">
						<i class="fas fa-building me-1"></i>Editora
					</label>
					<select class="form-select" id="Li_editora" name="Li_editora">
						<option value="">-- Escolha a editora --</option>
						<?php foreach ($lista_editoras as $editora): ?>
							<option value="<?php echo htmlspecialchars($editora); ?>" <?php echo (isset($Li_editora) && $Li_editora === $editora) ? 'selected' : ''; ?>><?php echo htmlspecialchars($editora); ?></option>
						<?php endforeach; ?>
					</select>
					<div class="form-text">Casa editora (opcional)</div>
				</div>
		<div class="col-md-4">
			<label for="num_exemplares" class="form-label">
				<i class="fas fa-copy me-1"></i>Exemplares a criar
			</label>
			<input type="number" class="form-control" id="num_exemplares" name="num_exemplares" value="<?php echo htmlspecialchars($num_exemplares ?? '0'); ?>" min="0" placeholder="Ex: 3">
			<div class="form-text">Quantos exemplares físicos deseja criar automaticamente</div>
		</div>
		<div class="col-md-4 d-flex align-items-end">
			<div class="form-check form-switch mt-2">
				<input class="form-check-input" type="checkbox" value="1" id="perm_requisicao" name="perm_requisicao" <?php echo !empty($perm_requisicao) ? 'checked' : ''; ?>>
				<label class="form-check-label" for="perm_requisicao">
					<i class="fas fa-hand-holding me-1"></i>Permitir requisição dos exemplares
				</label>
			</div>
		</div>
				<div class="col-12">
					<button type="submit" class="btn btn-primary friendly-btn">
						<i class="fas fa-save me-2"></i>Guardar Livro
					</button>
					<button type="reset" class="btn btn-outline-secondary friendly-btn ms-2">
						<i class="fas fa-undo me-2"></i>Limpar Campos
					</button>
				</div>
			</form>
		</div>
	</div>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
	const container = document.getElementById('selected-authors');

	// Estado interno: lista de autores selecionados
	let selectedAuthors = <?php echo json_encode($Li_autor_array ?? []); ?>;

	function renderChips() {
		container.innerHTML = '';
		selectedAuthors.forEach(name => {
			const chip = document.createElement('span');
			chip.className = 'badge rounded-pill text-bg-light border me-2 mb-2 d-inline-flex align-items-center';
			chip.textContent = name + ' ';
			const btn = document.createElement('button');
			btn.type = 'button';
			btn.className = 'btn btn-sm btn-link text-danger p-0 ms-1';
			btn.innerHTML = '<i class="fas fa-times"></i>';
			btn.addEventListener('click', () => {
				selectedAuthors = selectedAuthors.filter(a => a !== name);
				updateHiddenInputs();
				renderChips();
			});
			chip.appendChild(btn);
			container.appendChild(chip);
		});
	}

	function updateHiddenInputs() {
		// remove existentes
		document.querySelectorAll('input[name="Li_autor[]"]').forEach(el => el.remove());
		// adiciona inputs hidden para enviar no POST
		selectedAuthors.forEach(name => {
			const input = document.createElement('input');
			input.type = 'hidden';
			input.name = 'Li_autor[]';
			input.value = name;
			container.parentElement.appendChild(input);
		});
	}

	document.getElementById('btn-pesquisar-autor').addEventListener('click', function() {
		window.open('crud/pesquisar_autor.php', 'popupAutor', 'width=700,height=500,scrollbars=yes');
	});

	window.addAutor = function(nome) {
		if (nome && !selectedAuthors.includes(nome)) {
			selectedAuthors.push(nome);
			updateHiddenInputs();
			renderChips();
		}
	};

	updateHiddenInputs();
	renderChips();
});
</script>
