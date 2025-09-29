<?php
header('Content-Type: application/json');
require_once '../config/database.php';

// Pega o ID da opção "pai" que foi enviada pela requisição
$parent_id = filter_input(INPUT_GET, 'parent_id', FILTER_VALIDATE_INT);

if ($parent_id === false || $parent_id <= 0) {
    echo json_encode(['error' => 'ID de parente inválido.']);
    exit;
}

try {
    // Busca todos os "filhos" (sub-categorias ou problemas) do ID fornecido
    $stmt = $pdo->prepare("SELECT id, type, name, icon_path, requires_brand_model FROM form_options WHERE parent_id = ? ORDER BY name ASC");
    $stmt->execute([$parent_id]);
    $options = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($options);

} catch (PDOException $e) {
    // Em caso de erro, loga e retorna uma mensagem genérica
    error_log('Erro na API de Opções: ' . $e->getMessage());
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Erro ao consultar o banco de dados.']);
}
?>