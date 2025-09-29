<?php
header('Content-Type: application/json');
require_once '../config/database.php';

session_start();

$response = ['new_orders_count' => 0];

try {
    // Se a ação for "limpar", atualiza a sessão e encerra.
    if (isset($_GET['clear']) && $_GET['clear'] === 'true') {
        $_SESSION['last_seen_os_id'] = $pdo->query("SELECT MAX(id) FROM ordens_servico")->fetchColumn() ?? 0;
        echo json_encode(['success' => true]);
        exit;
    }

    $latest_os_id = $pdo->query("SELECT MAX(id) FROM ordens_servico")->fetchColumn() ?? 0;
    $last_seen_id = $_SESSION['last_seen_os_id'] ?? 0;

    if ($latest_os_id > $last_seen_id) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM ordens_servico WHERE id > ?");
        $stmt->execute([$last_seen_id]);
        $response['new_orders_count'] = $stmt->fetchColumn();
    }
} catch (PDOException $e) {
    error_log('Notification Check Error: ' . $e->getMessage());
}

echo json_encode($response);
?>