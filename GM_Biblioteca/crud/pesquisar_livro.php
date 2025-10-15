<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../partials/header.php';

$q = trim($_GET['q'] ?? '');
$target = isset($_GET['target']) ? $_GET['target'] : '';
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 20;
$offset = ($page - 1) * $per_page;
$livros = [];
$total = 0;
if ($q !== '') {
    $stmt_count = $mysqli->prepare('SELECT COUNT(*) FROM livros WHERE Li_titulo LIKE ?');
    $like = '%' . $q . '%';
    $stmt_count->bind_param('s', $like);
    $stmt_count->execute();
    $stmt_count->bind_result($total);
    $stmt_count->fetch();
    $stmt_count->close();
    $stmt = $mysqli->prepare('SELECT Li_cod, Li_titulo FROM livros WHERE Li_titulo LIKE ? ORDER BY Li_titulo LIMIT ? OFFSET ?');
    $stmt->bind_param('sii', $like, $per_page, $offset);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $livros[] = $row;
    }
    $stmt->close();
} else {
    $res_count = $mysqli->query('SELECT COUNT(*) as total FROM livros');
    $row = $res_count->fetch_assoc();
    $total = $row['total'] ?? 0;
    $res_count->free();
    $res = $mysqli->query('SELECT Li_cod, Li_titulo FROM livros ORDER BY Li_titulo LIMIT ' . $per_page . ' OFFSET ' . $offset);
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $livros[] = $row;
        }
        $res->free();
    }
}
?>
<div class="container py-4">
    <h2 class="text-primary mb-4"><i class="fas fa-search me-2"></i>Pesquisar Livro</h2>
    <form method="get" class="mb-3 row g-2">
        <div class="col">
            <input type="text" name="q" class="form-control" placeholder="Pesquisar por título" value="<?php echo htmlspecialchars($q); ?>">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i>Pesquisar</button>
        </div>
    </form>
    <div class="card">
        <div class="card-header">Resultados</div>
        <div class="card-body">
            <?php if (count($livros) === 0): ?>
                <div class="alert alert-warning">Nenhum livro encontrado.</div>
            <?php else: ?>
                <ul class="list-group">
                    <?php foreach ($livros as $livro): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><?php echo htmlspecialchars($livro['Li_titulo']); ?> <span class="text-muted">(ID: <?php echo $livro['Li_cod']; ?>)</span></span>
                            <button class="btn btn-success btn-sm" onclick="selectLivro('<?php echo $livro['Li_cod']; ?>', '<?php echo htmlspecialchars($livro['Li_titulo']); ?>')">
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
function selectLivro(cod, titulo) {
    var target = '<?php echo addslashes($target); ?>';
    if (window.opener && !window.opener.closed) {
        if (typeof window.opener.setLivro === 'function') {
            window.opener.setLivro(target, cod, titulo);
        } else {
            // fallback: tenta preencher campos padrão
            if (target) {
                if (window.opener.document.getElementById('livro_nome_display_' + target)) {
                    window.opener.document.getElementById('livro_nome_display_' + target).value = titulo;
                }
                if (window.opener.document.getElementById(target)) {
                    window.opener.document.getElementById(target).value = cod;
                }
            }
        }
        window.close();
    } else {
        alert('Não foi possível enviar o valor para o formulário principal.');
    }
}
</script>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
