<?php
require_once 'check_auth.php';
include 'templates/header.php';

$os_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$os_id) { die("ID da OS inválido."); }

// Busca dados da OS e do Histórico
$stmt_os = $pdo->prepare("SELECT * FROM ordens_servico WHERE id = ?");
$stmt_os->execute([$os_id]);
$os = $stmt_os->fetch(PDO::FETCH_ASSOC);

if (!$os) { die("Ordem de Serviço não encontrada."); }

$stmt_hist = $pdo->prepare("SELECT * FROM os_historico WHERE os_id = ? ORDER BY data_alteracao ASC");
$stmt_hist->execute([$os_id]);
$history = $stmt_hist->fetchAll(PDO::FETCH_ASSOC);

// Busca os status da nova tabela de status
$all_statuses_from_table = $pdo->query("SELECT name FROM os_statuses ORDER BY name ASC")->fetchAll(PDO::FETCH_COLUMN);
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold">Detalhes da OS #<?php echo $os['id']; ?></h1>
    <a href="os_print.php?id=<?php echo $os['id']; ?>" target="_blank" class="bg-gray-200 text-gray-700 font-bold py-2 px-4 rounded-md hover:bg-gray-300">Imprimir OS</a>
</div>

<?php
if (isset($_SESSION['success_message'])) {
    echo '<div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">' . sanitize_output($_SESSION['success_message']) . '</div>';
    unset($_SESSION['success_message']);
}
?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <div class="lg:col-span-1 space-y-6">
        <div class="bg-white p-4 rounded-lg shadow-md card">
            <h2 class="text-xl font-bold mb-2 border-b pb-2">Cliente</h2>
            <p><strong>Nome:</strong> <?php echo sanitize_output($os['cliente_nome']); ?></p>
            <p><strong>WhatsApp:</strong> <a href="https://wa.me/55<?php echo preg_replace('/[^0-9]/', '', $os['cliente_whatsapp']); ?>" target="_blank" class="brand-text hover:underline"><?php echo sanitize_output($os['cliente_whatsapp']); ?></a></p>
            <div>
                <strong>Endereço:</strong> 
                <?php 
                    $full_address = sanitize_output($os['rua'] . ', ' . $os['numero'] . ' - ' . $os['bairro'] . ', ' . $os['cidade'] . ' - ' . $os['estado']);
                    $maps_query = urlencode($full_address);
                ?>
                <a href="https://www.google.com/maps/search/?api=1&query=<?php echo $maps_query; ?>" target="_blank" class="hover:underline brand-text">
                    <?php echo $full_address; ?>
                </a>
            </div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow-md card">
            <h2 class="text-xl font-bold mb-2 border-b pb-2">Dispositivo e Problema</h2>
            <p><strong>Dispositivo:</strong> <?php echo sanitize_output($os['dispositivo_categoria']); ?></p>
            <?php if ($os['dispositivo_marca']): ?>
                <p><strong>Marca/Modelo:</strong> <?php echo sanitize_output($os['dispositivo_marca'] . ' / ' . $os['dispositivo_modelo']); ?></p>
            <?php endif; ?>
            <p class="mt-2"><strong>Descrição do Cliente:</strong></p>
            <p class="text-sm p-2 bg-gray-100 rounded-md"><?php echo nl2br(sanitize_output($os['descricao_problema'])); ?></p>

            <?php if (!empty($os['media_path'])): ?>
            <div class="mt-4 pt-4 border-t">
                <h3 class="font-bold mb-2">Mídia Anexada pelo Cliente</h3>
                <?php
                    $file_path = '../' . $os['media_path'];
                    $file_extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
                    $image_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                ?>
                <?php if (in_array($file_extension, $image_extensions)): ?>
                    <a href="<?php echo sanitize_output($file_path); ?>" target="_blank" title="Clique para ampliar">
                        <img src="<?php echo sanitize_output($file_path); ?>" alt="Mídia anexada" class="rounded-lg max-w-full h-auto border shadow-sm">
                    </a>
                <?php else: ?>
                    <a href="<?php echo sanitize_output($file_path); ?>" target="_blank" class="inline-block brand-bg text-white font-bold py-2 px-4 rounded-md text-sm">
                        Ver/Baixar Anexo (<?php echo sanitize_output($file_extension); ?>)
                    </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            </div>
    </div>

    <div class="lg:col-span-1">
        <div class="bg-white p-4 rounded-lg shadow-md card">
            <h2 class="text-xl font-bold mb-2 border-b pb-2">Gerenciar Reparo</h2>
            <form action="os_handler.php" method="POST" class="space-y-4">
                <input type="hidden" name="os_id" value="<?php echo $os['id']; ?>">
                <div>
                    <label for="status" class="font-medium">Alterar Status</label>
                    <select name="status" id="status" class="w-full mt-1 p-2 border rounded-md">
                        <?php foreach($all_statuses_from_table as $status): ?>
                        <option value="<?php echo sanitize_output($status); ?>" <?php echo $os['status'] === $status ? 'selected' : ''; ?>><?php echo sanitize_output($status); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="valor_orcamento" class="font-medium">Valor do Orçamento (R$)</label>
                    <input type="number" step="0.01" name="valor_orcamento" value="<?php echo sanitize_output($os['valor_orcamento']); ?>" placeholder="Ex: 150.00" class="w-full mt-1 p-2 border rounded-md">
                </div>
                 <div>
                    <label for="codigo_rastreio_devolucao" class="font-medium">Código de Rastreio (Devolução)</label>
                    <input type="text" name="codigo_rastreio_devolucao" value="<?php echo sanitize_output($os['codigo_rastreio_devolucao']); ?>" placeholder="Ex: QB123456789BR" class="w-full mt-1 p-2 border rounded-md">
                </div>
                 <div>
                    <label for="observacao" class="font-medium">Adicionar Observação ao Histórico</label>
                    <textarea name="observacao" rows="3" placeholder="Ex: Peça X trocada. Testes OK." class="w-full mt-1 p-2 border rounded-md"></textarea>
                </div>
                <button type="submit" class="w-full brand-bg text-white font-bold py-2 px-4 rounded-md">Salvar Alterações</button>
            </form>
        </div>
    </div>

    <div class="lg:col-span-1">
        <div class="bg-white p-4 rounded-lg shadow-md card">
            <h2 class="text-xl font-bold mb-2 border-b pb-2">Histórico da OS</h2>
            <ul class="space-y-4">
                <?php foreach($history as $h): ?>
                <li>
                    <p class="font-bold"><?php echo sanitize_output($h['status_novo']); ?></p>
                    <p class="text-sm text-gray-500"><?php echo date('d/m/Y H:i', strtotime($h['data_alteracao'])); ?></p>
                    <?php if($h['observacao']): ?><p class="text-sm mt-1 p-1 bg-yellow-50 rounded"><?php echo sanitize_output($h['observacao']); ?></p><?php endif; ?>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>

<?php include 'templates/footer.php'; ?>