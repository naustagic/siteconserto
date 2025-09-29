<?php
require_once 'check_auth.php';
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: os_list.php');
    exit;
}

$os_id = filter_input(INPUT_POST, 'os_id', FILTER_VALIDATE_INT);
$new_status = trim($_POST['status']);
$valor_orcamento = $_POST['valor_orcamento'] ?: null;
$codigo_rastreio = trim($_POST['codigo_rastreio_devolucao']);
$observacao = trim($_POST['observacao']);

if (!$os_id) {
    die("ID da OS inválido.");
}

try {
    // Pega o status atual para comparar se houve mudança
    $stmt = $pdo->prepare("SELECT status FROM ordens_servico WHERE id = ?");
    $stmt->execute([$os_id]);
    $current_status = $stmt->fetchColumn();

    // Atualiza os dados principais da OS
    $sql_update = "UPDATE ordens_servico SET status = ?, valor_orcamento = ?, codigo_rastreio_devolucao = ? WHERE id = ?";
    $stmt_update = $pdo->prepare($sql_update);
    $stmt_update->execute([$new_status, $valor_orcamento, $codigo_rastreio, $os_id]);
    
    // Se o status mudou, ou se há uma observação, insere no histórico
    if ($new_status !== $current_status || !empty($observacao)) {
        $sql_hist = "INSERT INTO os_historico (os_id, status_novo, observacao) VALUES (?, ?, ?)";
        $stmt_hist = $pdo->prepare($sql_hist);
        $stmt_hist->execute([$os_id, $new_status, $observacao ?: 'Status alterado pelo técnico.']);
    }

    $_SESSION['success_message'] = "Ordem de Serviço #" . $os_id . " atualizada com sucesso!";

} catch (PDOException $e) {
    // Em um app real, logaríamos o erro
    $_SESSION['error_message'] = "Erro ao atualizar a OS: " . $e->getMessage();
}

// Redireciona de volta para a página de detalhes
header('Location: os_details.php?id=' . $os_id);
exit;

?>