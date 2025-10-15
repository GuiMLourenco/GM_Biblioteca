<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../partials/header.php';

$q = trim($_GET['q'] ?? '');
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 20;
$offset = ($page - 1) * $per_page;
$autores = [];
$total = 0;
if ($q !== '') {
    $stmt_count = $mysqli->prepare('SELECT COUNT(*) FROM autores WHERE au_nome LIKE ?');
    $like = '%' . $q . '%';
    $stmt_count->bind_param('s', $like);
    $stmt_count->execute();
    $stmt_count->bind_result($total);
    $stmt_count->fetch();
    $stmt_count->close();
    $stmt = $mysqli->prepare('SELECT au_cod, au_nome FROM autores WHERE au_nome LIKE ? ORDER BY au_nome LIMIT ? OFFSET ?');
    $stmt->bind_param('sii', $like, $per_page, $offset);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $autores[] = $row;
    }
    $stmt->close();
} else {
    $res_count = $mysqli->query('SELECT COUNT(*) as total FROM autores');
    $row = $res_count->fetch_assoc();
    $total = $row['total'] ?? 0;
    $res_count->free();
    $res = $mysqli->query('SELECT au_cod, au_nome FROM autores ORDER BY au_nome LIMIT ' . $per_page . ' OFFSET ' . $offset);
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $autores[] = $row;
        }
        $res->free();
    }
}
?>
<div class="container py-4">
    <h2 class="text-primary mb-4"><i class="fas fa-search me-2"></i>Pesquisar Autor</h2>
    <form method="get" class="mb-3 row g-2">
        <div class="col">
            <input type="text" name="q" class="form-control" placeholder="Pesquisar por nome" value="<?php echo htmlspecialchars($q); ?>">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i>Pesquisar</button>
        </div>
    </form>
    <div class="card">
        <div class="card-header">Resultados</div>
        <div class="card-body">
            <?php if (count($autores) === 0): ?>
                <div class="alert alert-warning">Nenhum autor encontrado.</div>
            <?php else: ?>
                <ul class="list-group">
                    <?php foreach ($autores as $autor): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><?php echo htmlspecialchars($autor['au_nome']); ?> <span class="text-muted">(ID: <?php echo $autor['au_cod']; ?>)</span></span>
                            <button class="btn btn-success btn-sm" onclick="selectAutor('<?php echo $autor['au_cod']; ?>', '<?php echo htmlspecialchars($autor['au_nome']); ?>')">
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
function selectAutor(cod, nome) {
    if (window.opener && !window.opener.closed) {
        if (typeof window.opener.addAutor === 'function') {
            window.opener.addAutor(nome);
        } else {
            alert('Não foi possível enviar o valor para o formulário principal.');
        }
        window.close();
    } else {
        alert('Não foi possível enviar o valor para o formulário principal.');
    }
}
</script>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
