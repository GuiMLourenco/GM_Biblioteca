<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../partials/header.php';

$q = trim($_GET['q'] ?? '');
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 20;
$offset = ($page - 1) * $per_page;
$utentes = [];
$total = 0;
if ($q !== '') {
    if (is_numeric($q)) {
        $stmt = $mysqli->prepare('SELECT COUNT(*) FROM utentes WHERE ut_cod = ?');
        $stmt->bind_param('i', $q);
        $stmt->execute();
        $stmt->bind_result($total);
        $stmt->fetch();
        $stmt->close();
        $stmt = $mysqli->prepare('SELECT ut_cod, ut_nome FROM utentes WHERE ut_cod = ? LIMIT ? OFFSET ?');
        $stmt->bind_param('iii', $q, $per_page, $offset);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $utentes[] = $row;
        }
        $stmt->close();
    } else {
        $stmt = $mysqli->prepare('SELECT COUNT(*) FROM utentes WHERE ut_nome LIKE ?');
        $like = '%' . $q . '%';
        $stmt->bind_param('s', $like);
        $stmt->execute();
        $stmt->bind_result($total);
        $stmt->fetch();
        $stmt->close();
        $stmt = $mysqli->prepare('SELECT ut_cod, ut_nome FROM utentes WHERE ut_nome LIKE ? ORDER BY ut_nome LIMIT ? OFFSET ?');
        $stmt->bind_param('sii', $like, $per_page, $offset);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $utentes[] = $row;
        }
        $stmt->close();
    }
} else {
    $res = $mysqli->query('SELECT COUNT(*) as total FROM utentes');
    $row = $res->fetch_assoc();
    $total = $row['total'] ?? 0;
    $res->free();
    $res = $mysqli->query('SELECT ut_cod, ut_nome FROM utentes ORDER BY ut_nome LIMIT ' . $per_page . ' OFFSET ' . $offset);
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $utentes[] = $row;
        }
        $res->free();
    }
}
?>
<div class="container py-4">
    <h2 class="text-primary mb-4"><i class="fas fa-search me-2"></i>Pesquisar Utente</h2>
    <form method="get" class="mb-3 row g-2">
        <div class="col">
            <input type="text" name="q" class="form-control" placeholder="Pesquisar por nome ou código" value="<?php echo htmlspecialchars($q); ?>">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i>Pesquisar</button>
        </div>
    </form>
    <div class="card">
        <div class="card-header">Resultados</div>
        <div class="card-body">
            <?php if (count($utentes) === 0): ?>
                <div class="alert alert-warning">Nenhum utente encontrado.</div>
            <?php else: ?>
                <ul class="list-group">
                    <?php foreach ($utentes as $utente): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><?php echo htmlspecialchars($utente['ut_nome']); ?> <span class="text-muted">(ID: <?php echo $utente['ut_cod']; ?>)</span></span>
                            <button class="btn btn-success btn-sm" onclick="selectUtente('<?php echo $utente['ut_cod']; ?>', '<?php echo htmlspecialchars($utente['ut_nome']); ?>')">
                                <i class="fas fa-check"></i> Selecionar
                            </button>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <?php
                $num_pages = ceil($total / $per_page);
                if ($num_pages > 1): ?>
                <nav aria-label="Paginação">
                    <ul class="pagination mt-3">
                        <?php for ($i = 1; $i <= $num_pages; $i++): ?>
                            <li class="page-item<?php echo $i == $page ? ' active' : ''; ?>">
                                <a class="page-link" href="?q=<?php echo urlencode($q); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<script>
function selectUtente(cod, nome) {
    if (window.opener && !window.opener.closed) {
        if (typeof window.opener.setUtente === 'function') {
            window.opener.setUtente(cod, nome);
        } else {
            window.opener.document.getElementById('re_ut_cod').value = cod;
            window.opener.document.getElementById('utente_nome_display').value = nome + ' (ID: ' + cod + ')';
        }
        window.close();
    } else {
        alert('Não foi possível enviar o valor para o formulário principal.');
    }
}
</script>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
