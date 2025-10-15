<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/partials/header.php';


$success_message = '';
$error_message = '';

// Carregar utentes para o combobox
$utentes = [];
$res = $mysqli->query('SELECT ut_cod, ut_nome FROM utentes ORDER BY ut_nome');
if ($res) {
	while ($row = $res->fetch_assoc()) {
		$utentes[] = $row;
	}
	$res->free();
}

// Carregar apenas exemplares disponíveis para o combobox, incluindo nome do livro
$exemplares = [];
$res = $mysqli->query('SELECT le.Lex_cod, le.Lex_Li_cod, l.Li_titulo FROM livro_exemplar le JOIN livros l ON le.Lex_Li_cod = l.Li_cod WHERE le.Lex_disponivel = 1 ORDER BY le.Lex_cod');
if ($res) {
	while ($row = $res->fetch_assoc()) {
		$exemplares[] = $row;
	}
	$res->free();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$re_ut_cod = trim($_POST['re_ut_cod'] ?? '');
	$re_lex_cod = trim($_POST['re_lex_cod'] ?? '');
	$re_datarequesicao = trim($_POST['re_datarequesicao'] ?? '');
	$re_datadevolucao = trim($_POST['re_datadevolucao'] ?? '');

	if ($re_ut_cod === '' || $re_lex_cod === '' || $re_datarequesicao === '') {
		$error_message = 'Por favor, preencha todos os campos obrigatórios (Utente, Exemplar e Data de Requisição).';
	} else {
		$stmt = $mysqli->prepare('INSERT INTO requisicoes (re_ut_cod, re_lex_cod, re_datarequesicao, re_datadevolucao) VALUES (?, ?, ?, ?)');
		if (!$stmt) {
			$error_message = 'Erro a preparar a instrução: ' . $mysqli->error;
		} else {
			$stmt->bind_param('iiss', $re_ut_cod, $re_lex_cod, $re_datarequesicao, $re_datadevolucao);
			if ($stmt->execute()) {
				// Atualizar exemplar para indisponível
				$stmtUpd = $mysqli->prepare('UPDATE livro_exemplar SET Lex_disponivel = 0 WHERE Lex_cod = ?');
				if ($stmtUpd) {
					$stmtUpd->bind_param('i', $re_lex_cod);
					$stmtUpd->execute();
					$stmtUpd->close();
				}
				$success_message = '✅ Empréstimo registado com sucesso!';
				$re_ut_cod = $re_lex_cod = $re_datarequesicao = $re_datadevolucao = '';
			} else {
				$error_message = '❌ Erro ao inserir: ' . $stmt->error;
			}
			$stmt->close();
		}
	}
}
?>

<div class="container py-4">
	<div class="d-flex justify-content-between align-items-center mb-4">
		<div>
			<h2 class="text-success">
				<i class="fas fa-hand-holding me-2"></i>Registar Novo Empréstimo
			</h2>
			<p class="text-muted">Registe quando alguém requisita um livro da biblioteca</p>
		</div>
		<a href="index.php" class="btn btn-secondary friendly-btn">
			<i class="fas fa-arrow-left me-2"></i>Voltar ao Início
		</a>
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
			<h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informações do Empréstimo</h5>
		</div>
		<div class="card-body">
			<form method="post" class="row g-3">
				<div class="col-md-6">
					<label for="re_ut_cod" class="form-label">
						<i class="fas fa-user me-1"></i>Utente *
					</label>
					<div class="input-group">
						<input type="hidden" id="re_ut_cod" name="re_ut_cod" value="<?php echo htmlspecialchars($re_ut_cod ?? ''); ?>">
						<input type="text" id="utente_nome_display" class="form-control" value="<?php
							if (isset($re_ut_cod) && $re_ut_cod !== '') {
								$utente_nome = '';
								$res = $mysqli->prepare('SELECT ut_nome FROM utentes WHERE ut_cod = ?');
								$res->bind_param('i', $re_ut_cod);
								$res->execute();
								$result = $res->get_result();
								if ($row = $result->fetch_assoc()) {
									$utente_nome = $row['ut_nome'] . ' (ID: ' . $re_ut_cod . ')';
								}
								$res->close();
								echo htmlspecialchars($utente_nome);
							}
						?>" readonly placeholder="Clique em pesquisar...">
						<button type="button" class="btn btn-outline-primary" onclick="window.open('crud/pesquisar_utente.php', 'pesquisarUtente', 'width=600,height=500');">
							<i class="fas fa-search"></i> Pesquisar
						</button>
					</div>
					<div class="form-text">Pesquise e selecione o utente (obrigatório)</div>
				</div>
				<script>
				// Permite que a janela de pesquisa atualize o nome do utente dinamicamente
				function setUtente(cod, nome) {
					document.getElementById('re_ut_cod').value = cod;
					document.getElementById('utente_nome_display').value = nome + ' (ID: ' + cod + ')';
				}
				</script>
				<div class="col-md-6">
					<label for="re_lex_cod" class="form-label">
						<i class="fas fa-book me-1"></i>Exemplar *
					</label>
					<div class="input-group">
						<input type="hidden" id="re_lex_cod" name="re_lex_cod" value="<?php echo htmlspecialchars($re_lex_cod ?? ''); ?>">
						<input type="text" id="exemplar_nome_display" class="form-control" value="<?php
							if (isset($re_lex_cod) && $re_lex_cod !== '') {
								$exemplar_nome = '';
								$res = $mysqli->prepare('SELECT l.Li_titulo FROM livro_exemplar le JOIN livros l ON le.Lex_Li_cod = l.Li_cod WHERE le.Lex_cod = ?');
								$res->bind_param('i', $re_lex_cod);
								$res->execute();
								$result = $res->get_result();
								if ($row = $result->fetch_assoc()) {
									$exemplar_nome = $row['Li_titulo'] . ' (Exemplar: ' . $re_lex_cod . ')';
								}
								$res->close();
								echo htmlspecialchars($exemplar_nome);
							}
						?>" readonly placeholder="Clique em pesquisar...">
						<button type="button" class="btn btn-outline-primary" onclick="window.open('crud/pesquisar_exemplar.php', 'pesquisarExemplar', 'width=600,height=500');">
							<i class="fas fa-search"></i> Pesquisar
						</button>
					</div>
					<div class="form-text">Pesquise e selecione o exemplar (apenas exemplares indisponíveis)</div>
				</div>
				<script>
				// Permite que a janela de pesquisa atualize o nome do exemplar dinamicamente
				function setExemplar(cod, titulo) {
					document.getElementById('re_lex_cod').value = cod;
					document.getElementById('exemplar_nome_display').value = titulo + ' (Exemplar: ' + cod + ')';
				}
				</script>
				<div class="col-md-6">
					<label for="re_datarequesicao" class="form-label">
						<i class="fas fa-calendar-plus me-1"></i>Data de Requisição *
					</label>
					<input type="date" class="form-control" id="re_datarequesicao" name="re_datarequesicao" value="<?php echo htmlspecialchars($re_datarequesicao ?? ''); ?>" required>
					<div class="form-text">Data em que o livro foi requisitado (obrigatório)</div>
				</div>
				<div class="col-md-6">
					<label for="re_datadevolucao" class="form-label">
						<i class="fas fa-calendar-check me-1"></i>Data de Devolução
					</label>
					<input type="date" class="form-control" id="re_datadevolucao" name="re_datadevolucao" value="<?php echo htmlspecialchars($re_datadevolucao ?? ''); ?>">
					<div class="form-text">Data prevista para devolução (opcional)</div>
				</div>
				<div class="col-12">
					<div class="alert alert-info">
						<i class="fas fa-lightbulb me-2"></i>
						<strong>Dica:</strong> Se não souber o código do utente ou exemplar, pode consultar essas informações na secção "Gerir Dados" do menu principal.
					</div>
				</div>
				<div class="col-12">
					<button type="submit" class="btn btn-success friendly-btn">
						<i class="fas fa-save me-2"></i>Registar Empréstimo
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
