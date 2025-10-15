
<?php
// Redireciona para o novo formulário unificado
header('Location: /GM_Biblioteca/livros_create.php' . (isset($_GET['id']) ? '?id=' . urlencode($_GET['id']) : ''));
exit;
?>