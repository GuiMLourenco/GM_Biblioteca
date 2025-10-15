<?php
// Configuração da base de dados (XAMPP MySQL)
// Ajuste as credenciais conforme o seu ambiente

$db_host = '127.0.0.1';
$db_user = 'root';
$db_pass = '';
$db_name = 'gm_biblioteca'; // altere para o nome real da sua BD

$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($mysqli->connect_errno) {
	die('Falha na ligação MySQL: ' . $mysqli->connect_error);
}

// Forçar charset utf8mb4
if (!$mysqli->set_charset('utf8mb4')) {
	// não interrompe, mas regista erro
	error_log('Não foi possível definir charset utf8mb4: ' . $mysqli->error);
}

?>
