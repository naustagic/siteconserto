<?php
include 'check_auth.php';
include '../config/database.php';

if ($_SESSION['user_level'] !== 'Admin') {
    header("Location: index.php");
    exit();
}

$stmt = $pdo->query("SELECT * FROM payment_methods ORDER BY sort_order ASC, name ASC");
$payment_methods = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include 'templates/header.php'; ?>

<div class="container mx-auto">
    <h1 class="text-2xl font-bold mb-4">Métodos de Pagamento</h1>

    <div class="card mb-6">
        <div class="card-header"><i class="fas fa-plus-circle"></i>Adicionar Novo Método</div>
        <div class="p-4">
            <form action="pagamentos_handler.php" method="POST" enctype="multipart/form-data" class="flex items-end space-x-4">
                <input type="hidden" name="action" value="add">
                <div class="flex-grow">
                    <label for="name" class="block text-sm font-medium mb-1">Nome do Método</label>
                    <input type="text" class="form-control" name="name" id="name" placeholder="Ex: Cartão de Crédito" required>
                </div>
                <div class="flex-grow">
                    <label for="logo" class="block text-sm font-medium mb-1">Logo (PNG, JPG ou SVG)</label>
                    <input type="file" class="form-control" name="logo" id="logo" accept="image/*">
                </div>
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded transition-colors h-10">Adicionar</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><i class="fas fa-list-alt"></i>Métodos Cadastrados</div>
        <div class="p-4">
            <table class="table">
                <thead>
                    <tr>
                        <th class="text-center">Logo</th>
                        <th>Nome</th>
                        <th class="text-center">Status</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payment_methods as $method): ?>
                        <tr>
                            <td class="text-center">
                                <?php if (!empty($method['logo_path'])): ?>
                                    <img src="../<?= htmlspecialchars($method['logo_path']) ?>" alt="<?= htmlspecialchars($method['name']) ?>" class="payment-logo-table">
                                <?php else: ?>
                                    <i class="fas fa-credit-card text-gray-400 fa-lg"></i>
                                <?php endif; ?>
                            </td>
                            <td><strong><?= htmlspecialchars($method['name']) ?></strong></td>
                            <td class="text-center">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full <?= $method['is_active'] ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' ?>">
                                    <?= $method['is_active'] ? 'Ativo' : 'Inativo' ?>
                                </span>
                            </td>
                            <td class="text-right space-x-2">
                                <form action="pagamentos_handler.php" method="POST" class="inline-block">
                                    <input type="hidden" name="action" value="toggle_status">
                                    <input type="hidden" name="id" value="<?= $method['id'] ?>">
                                    <button type="submit" class="text-gray-500 hover:text-gray-700" title="<?= $method['is_active'] ? 'Desativar' : 'Ativar' ?>">
                                        <i class="fas fa-power-off"></i>
                                    </button>
                                </form>
                                <form action="pagamentos_handler.php" method="POST" class="inline-block" onsubmit="return confirm('Tem certeza?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $method['id'] ?>">
                                    <button type="submit" class="text-red-500 hover:text-red-700" title="Excluir"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'templates/footer.php'; ?>