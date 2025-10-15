<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../partials/header.php';

$q = trim($_GET['q'] ?? '');
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 20;
$offset = ($page - 1) * $per_page;
$exemplares = [];
$total = 0;
$sql_count = 'SELECT COUNT(*) as total FROM livro_exemplar le JOIN livros l ON le.Lex_Li_cod = l.Li_cod WHERE le.Lex_disponivel = 1';
$sql = 'SELECT le.Lex_cod, le.Lex_Li_cod, l.Li_titulo FROM livro_exemplar le JOIN livros l ON le.Lex_Li_cod = l.Li_cod WHERE le.Lex_disponivel = 1';
if ($q !== '') {
    if (is_numeric($q)) {
        $sql_count .= ' AND le.Lex_cod = ' . intval($q);
        $sql .= ' AND le.Lex_cod = ' . intval($q);
    } else {
        $sql_count .= ' AND l.Li_titulo LIKE ?';
        $sql .= ' AND l.Li_titulo LIKE ?';
    }
}
$sql .= ' ORDER BY le.Lex_cod LIMIT ' . $per_page . ' OFFSET ' . $offset;
if ($q !== '' && !is_numeric($q)) {
    $stmt_count = $mysqli->prepare($sql_count);
    $like = '%' . $q . '%';
    $stmt_count->bind_param('s', $like);
    $stmt_count->execute();
    $res_count = $stmt_count->get_result();
    $row = $res_count->fetch_assoc();
    $total = $row['total'] ?? 0;
    $stmt_count->close();
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('s', $like);
    $stmt->execute();
    $res = $stmt->get_result();
} else {
    $res_count = $mysqli->query($sql_count);
    $row = $res_count->fetch_assoc();
    $total = $row['total'] ?? 0;
    $res_count->free();
    $res = $mysqli->query($sql);
}
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $exemplares[] = $row;
    }
    if (isset($stmt)) $stmt->close();
    $res->free();
}
?>
<div class="container py-4">
    <h2 class="text-primary mb-4"><i class="fas fa-search me-2"></i>Pesquisar Exemplar (Indisponível)</h2>
    <form method="get" class="mb-3 row g-2">
        <div class="col">
            <input type="text" name="q" class="form-control" placeholder="Pesquisar por título ou código" value="<?php echo htmlspecialchars($q); ?>">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i>Pesquisar</button>
        </div>
    </form>
    <div class="card">
        <div class="card-header">Resultados</div>
        <div class="card-body">
            <?php if (count($exemplares) === 0): ?>
                <div class="alert alert-warning">Exemplar não encontrado ou indisponível.</div>
            <?php else: ?>
                <ul class="list-group">
                    <?php foreach ($exemplares as $exemplar): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><?php echo htmlspecialchars($exemplar['Li_titulo']); ?> <span class="text-muted">(Exemplar: <?php echo $exemplar['Lex_cod']; ?>)</span></span>
                            <button class="btn btn-success btn-sm" onclick="selectExemplar('<?php echo $exemplar['Lex_cod']; ?>', '<?php echo htmlspecialchars($exemplar['Li_titulo']); ?>')">
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
function selectExemplar(cod, titulo) {
    if (window.opener && !window.opener.closed) {
        if (typeof window.opener.setExemplar === 'function') {
            window.opener.setExemplar(cod, titulo);
        } else {
            window.opener.document.getElementById('re_lex_cod').value = cod;
            window.opener.document.getElementById('exemplar_nome_display').value = titulo + ' (Exemplar: ' + cod + ')';
        }
        window.close();
    } else {
        alert('Não foi possível enviar o valor para o formulário principal.');
    }
}
</script>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
