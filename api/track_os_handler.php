<?php
header('Content-Type: application/json');
require_once '../config/database.php';

// Ativa o log de erros em um arquivo para não quebrar o JSON
ini_set('log_errors', 1);
ini_set('error_log', dirname(__FILE__) . '/../php-errors.log');
ini_set('display_errors', 0);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
    exit;
}

$os_id = filter_input(INPUT_POST, 'os_id', FILTER_VALIDATE_INT);
$whatsapp = preg_replace('/[^0-9]/', '', $_POST['whatsapp'] ?? ''); // Limpa a máscara do WhatsApp

if (!$os_id || empty($whatsapp)) {
    echo json_encode(['success' => false, 'message' => 'Por favor, preencha todos os campos.']);
    exit;
}

try {
    // Busca a OS principal
    $stmt = $pdo->prepare("SELECT * FROM ordens_servico WHERE id = ? AND REPLACE(REPLACE(REPLACE(REPLACE(cliente_whatsapp, '(', ''), ')', ''), '-', ''), ' ', '') = ?");
    $stmt->execute([$os_id, $whatsapp]);
    $os_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$os_data) {
        echo json_encode(['success' => false, 'message' => 'Ordem de Serviço não encontrada ou WhatsApp incorreto. Verifique os dados e tente novamente.']);
        exit;
    }

    // Busca o histórico da OS
    $stmt_hist = $pdo->prepare("SELECT * FROM os_historico WHERE os_id = ? ORDER BY data_alteracao DESC");
    $stmt_hist->execute([$os_id]);
    $os_history = $stmt_hist->fetchAll(PDO::FETCH_ASSOC);

    // Busca se já existe uma avaliação para esta OS
    $stmt_review = $pdo->prepare("SELECT id FROM avaliacoes WHERE os_id = ?");
    $stmt_review->execute([$os_id]);
    $has_review = $stmt_review->fetch() ? true : false;


    $response = [
        'success' => true,
        'data' => [
            'os' => $os_data,
            'history' => $os_history,
            'has_review' => $has_review
        ]
    ];

    echo json_encode($response);

} catch (PDOException $e) {
    error_log("Erro ao buscar OS: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno no servidor.']);
}
?>