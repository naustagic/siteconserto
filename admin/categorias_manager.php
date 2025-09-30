<?php
include 'check_auth.php';
include '../config/database.php';

// Busca todas as categorias existentes para listar na tabela
$stmt = $pdo->query("SELECT * FROM product_categories ORDER BY name ASC");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'templates/header.php'; 
?>

<div class="container mx-auto">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Gerenciador de Categorias</h1>
        <a href="produtos_manager.php" class="flex items-center bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>Voltar para Produtos
        </a>
    </div>

    <?php if (isset($_GET['status'])): ?>
        <?php if ($_GET['status'] === 'success'): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Sucesso!</strong>
                <span class="block sm:inline">A operação foi realizada com sucesso.</span>
            </div>
        <?php elseif ($_GET['status'] === 'error'): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Erro!</strong>
                <span class="block sm:inline"><?= htmlspecialchars($_GET['message'] ?? 'Ocorreu um erro.') ?></span>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        
        <div class="md:col-span-1">
            <div class="card">
                <div class="card-header"><i class="fas fa-plus-circle mr-2"></i>Adicionar Nova Categoria</div>
                <div class="p-4">
                    <form action="categorias_handler.php" method="POST">
                        <input type="hidden" name="action" value="add">
                        <div class="form-group">
                            <label for="name" class="font-semibold">Nome da Categoria</label>
                            <input type="text" name="name" id="name" class="form-control" required placeholder="Ex: Celulares, Consoles...">
                        </div>
                        <div class="text-right">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded transition-colors">
                                <i class="fas fa-save mr-2"></i>Salvar Categoria
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="md:col-span-2">
            <div class="card">
                <div class="card-header"><i class="fas fa-list-alt mr-2"></i>Categorias Existentes</div>
                <div class="p-4">
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Nome da Categoria</th>
                                    <th class="text-right">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($categories)): ?>
                                    <tr>
                                        <td colspan="2" class="text-center text-gray-500 py-4">Nenhuma categoria cadastrada ainda.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($categories as $category): ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($category['name']) ?></strong></td>
                                            <td class="text-right">
                                                <form action="categorias_handler.php" method="POST" class="inline-block" onsubmit="return confirm('Tem certeza que deseja excluir esta categoria? Todos os produtos associados a ela ficarão sem categoria.')">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?= $category['id'] ?>">
                                                    <button type="submit" class="text-red-500 hover:text-red-700" title="Excluir">
                                                        <i class="fas fa-trash"></i> Excluir
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'templates/footer.php'; ?>