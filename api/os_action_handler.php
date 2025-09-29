<?php
header('Content-Type: application/json');
require_once '../config/database.php';

ini_set('log_errors', 1);
ini_set('error_log', dirname(__FILE__) . '/../php-errors.log');
ini_set('display_errors', 0);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
    exit;
}

$action = $_POST['action'] ?? '';
$os_id = filter_input(INPUT_POST, 'os_id', FILTER_VALIDATE_INT);
$whatsapp = preg_replace('/[^0-9]/', '', $_POST['whatsapp'] ?? '');

if (!$action || !$os_id || !$whatsapp) {
     echo json_encode(['success' => false, 'message' => 'Dados insuficientes para executar a ação.']);
    exit;
}

// Função para verificar se a OS pertence ao cliente
function verify_owner($pdo, $os_id, $whatsapp) {
    $stmt = $pdo->prepare("SELECT id FROM ordens_servico WHERE id = ? AND REPLACE(REPLACE(REPLACE(REPLACE(cliente_whatsapp, '(', ''), ')', ''), '-', ''), ' ', '') = ?");
    $stmt->execute([$os_id, $whatsapp]);
    return $stmt->fetch() !== false;
}

try {
    if (!verify_owner($pdo, $os_id, $whatsapp)) {
        throw new Exception('Acesso negado a esta Ordem de Serviço.');
    }

    switch ($action) {
        case 'approve_quote':
            $pdo->prepare("UPDATE ordens_servico SET orcamento_status = 'Aprovado', status = 'Aprovado | Em Reparo' WHERE id = ? AND status = 'Orçamento Enviado'")->execute([$os_id]);
            $pdo->prepare("INSERT INTO os_historico (os_id, status_novo, observacao) VALUES (?, ?, ?)")->execute([$os_id, 'Aprovado | Em Reparo', 'Orçamento aprovado pelo cliente via site.']);
            echo json_encode(['success' => true, 'message' => 'Orçamento aprovado com sucesso!']);
            break;

        case 'decline_quote':
            $pdo->prepare("UPDATE ordens_servico SET orcamento_status = 'Recusado', status = 'Cancelado' WHERE id = ? AND status = 'Orçamento Enviado'")->execute([$os_id]);
            $pdo->prepare("INSERT INTO os_historico (os_id, status_novo, observacao) VALUES (?, ?, ?)")->execute([$os_id, 'Cancelado', 'Orçamento recusado pelo cliente via site.']);
            echo json_encode(['success' => true, 'message' => 'Orçamento recusado.']);
            break;

        case 'submit_review':
            $nome = htmlspecialchars($_POST['nome'] ?? '', ENT_QUOTES, 'UTF-8');
            $estrelas = filter_input(INPUT_POST, 'estrelas', FILTER_VALIDATE_INT);
            $comentario = htmlspecialchars($_POST['comentario'] ?? '', ENT_QUOTES, 'UTF-8');

            if(empty($nome) || $estrelas < 1 || $estrelas > 5) {
                throw new Exception('Dados da avaliação inválidos.');
            }
            
            $pdo->prepare("INSERT INTO avaliacoes (os_id, cliente_nome, nota_estrelas, comentario, status) VALUES (?, ?, ?, ?, 'Pendente')")->execute([$os_id, $nome, $estrelas, $comentario]);
            echo json_encode(['success' => true, 'message' => 'Avaliação enviada com sucesso! Obrigado.']);
            break;
            
        default:
            throw new Exception('Ação desconhecida.');
    }

} catch (Exception $e) {
    error_log("Erro de ação do cliente: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>