<?php
require_once 'check_auth.php';
if ($_SESSION['user_level'] !== 'Admin') {
    die("Acesso negado.");
}
include 'templates/header.php';

$option_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$option_id) {
    echo "<div class='bg-red-200 text-red-800 p-4 rounded'>ID da opção inválido.</div>";
    include 'templates/footer.php';
    exit;
}

// Busca a opção a ser editada
$stmt = $pdo->prepare("SELECT * FROM form_options WHERE id = ?");
$stmt->execute([$option_id]);
$option = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$option) {
    echo "<div class='bg-red-200 text-red-800 p-4 rounded'>Opção não encontrada.</div>";
    include 'templates/footer.php';
    exit;
}

// Busca todas as opções para o select de "Pai"
$all_options_stmt = $pdo->query("SELECT id, name, type FROM form_options WHERE type != 'COMMON_PROBLEM' AND id != ? ORDER BY name ASC");
$all_options_stmt->execute([$option_id]);
$all_options = $all_options_stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<h1 class="text-3xl font-bold mb-6">Editar Opção</h1>

<div class="bg-white p-6 rounded-lg shadow-md max-w-xl mx-auto">
    <form action="form_manager_handler.php" method="POST" enctype="multipart/form-data" class="space-y-4">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="id" value="<?php echo $option['id']; ?>">
        
        <div>
            <label class="block text-sm font-medium text-gray-700">Tipo (não pode ser alterado)</label>
            <input type="text" value="<?php echo sanitize_output($option['type']); ?>" readonly class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm">
        </div>

        <div>
            <label for="parent_id" class="block text-sm font-medium text-gray-700">Associar a (Pai)</label>
            <select id="parent_id" name="parent_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                <option value="">Nenhum (se for Categoria Principal)</option>
                <?php foreach($all_options as $opt): ?>
                    <option value="<?php echo $opt['id']; ?>" <?php echo ($option['parent_id'] == $opt['id']) ? 'selected' : ''; ?>>
                        <?php echo sanitize_output($opt['type'] . ' - ' . $opt['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
         <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Nome da Opção</label>
            <input type="text" id="name" name="name" value="<?php echo sanitize_output($option['name']); ?>" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
        </div>

        <?php if ($option['type'] === 'SUBCATEGORY'): ?>
        <div>
            <label for="icon" class="block text-sm font-medium text-gray-700">Alterar Ícone (opcional)</label>
            <input type="file" name="icon" id="icon" class="mt-1 block w-full text-sm">
            <?php if($option['icon_path']): ?>
                <img src="../<?php echo sanitize_output($option['icon_path']);?>" class="inline h-10 w-10 mt-2">
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if ($option['type'] === 'CATEGORY'): ?>
        <div>
             <label class="flex items-center space-x-2">
                <input type="checkbox" name="requires_brand_model" value="1" <?php echo $option['requires_brand_model'] ? 'checked' : ''; ?> class="rounded">
                <span>Exigir Marca/Modelo?</span>
            </label>
        </div>
        <?php endif; ?>

        <div class="flex justify-between items-center pt-4">
             <a href="form_manager.php" class="text-gray-600 hover:underline">Cancelar</a>
            <button type="submit" class="brand-bg text-white font-bold py-2 px-6 rounded-md">Salvar Alterações</button>
        </div>
    </form>
</div>


<?php include 'templates/footer.php'; ?>