<?php
session_start();
header('Content-Type: application/json');
require_once '../config/database.php';

try {
    // Pega o ID da OS mais recente no banco de dados e salva na sessão
    $latest_os_id = $pdo->query("SELECT MAX(id) FROM ordens_servico")->fetchColumn();
    $_SESSION['last_seen_os_id'] = $latest_os_id;
    echo json_encode(['status' => 'success', 'latest_os_id' => $latest_os_id]);
} catch (PDOException $e) {
    error_log('Clear Notifications Error: ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Erro ao limpar notificações.']);
}
?>