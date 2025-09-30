<?php
include 'check_auth.php';
include '../config/database.php';

// Este script só deve processar requisições POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: categorias_manager.php');
    exit();
}

$action = $_POST['action'] ?? '';

// --- AÇÃO: ADICIONAR NOVA CATEGORIA ---
if ($action === 'add') {
    $name = trim($_POST['name'] ?? '');

    // Validação: Garante que o nome não está vazio.
    if (empty($name)) {
        header('Location: categorias_manager.php?status=error&message=' . urlencode('O nome da categoria não pode estar vazio.'));
        exit();
    }

    try {
        // Tenta inserir a nova categoria no banco de dados.
        $stmt = $pdo->prepare("INSERT INTO product_categories (name) VALUES (?)");
        $stmt->execute([$name]);

        // Se deu tudo certo, redireciona com mensagem de sucesso.
        header('Location: categorias_manager.php?status=success');
        exit();

    } catch (PDOException $e) {
        // A tabela tem uma restrição UNIQUE no nome. Se o código do erro for 23000, significa duplicata.
        if ($e->getCode() == 23000) {
            $message = 'Essa categoria já existe.';
        } else {
            $message = 'Ocorreu um erro no banco de dados: ' . $e->getMessage();
        }
        header('Location: categorias_manager.php?status=error&message=' . urlencode($message));
        exit();
    }
}

// --- AÇÃO: DELETAR CATEGORIA EXISTENTE ---
elseif ($action === 'delete') {
    $id = $_POST['id'] ?? 0;

    // Validação: Garante que o ID é válido.
    if (empty($id) || !is_numeric($id)) {
        header('Location: categorias_manager.php?status=error&message=' . urlencode('ID de categoria inválido.'));
        exit();
    }

    try {
        // Inicia uma transação para garantir a integridade dos dados.
        $pdo->beginTransaction();

        // 1. Descobre o nome da categoria que será deletada.
        $stmt_find = $pdo->prepare("SELECT name FROM product_categories WHERE id = ?");
        $stmt_find->execute([$id]);
        $category_name = $stmt_find->fetchColumn();

        if ($category_name) {
            // 2. Atualiza todos os produtos que usam esta categoria para NULL.
            $stmt_update = $pdo->prepare("UPDATE products SET category = NULL WHERE category = ?");
            $stmt_update->execute([$category_name]);

            // 3. Deleta a categoria da tabela de categorias.
            $stmt_delete = $pdo->prepare("DELETE FROM product_categories WHERE id = ?");
            $stmt_delete->execute([$id]);
        }
        
        // Se todas as operações foram bem-sucedidas, confirma a transação.
        $pdo->commit();
        header('Location: categorias_manager.php?status=success');
        exit();

    } catch (PDOException $e) {
        // Se qualquer operação falhar, desfaz tudo.
        $pdo->rollBack();
        header('Location: categorias_manager.php?status=error&message=' . urlencode('Erro ao deletar a categoria: ' . $e->getMessage()));
        exit();
    }
}

// Se nenhuma ação válida foi passada, apenas redireciona de volta.
header('Location: categorias_manager.php');
exit();
?>