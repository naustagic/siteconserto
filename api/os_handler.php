<?php
// Define o cabeçalho como JSON para todas as respostas
header('Content-Type: application/json');

// Inclui os arquivos necessários
require_once '../config/database.php';
require_once '../includes/functions.php';

// Ativa o log de erros em um arquivo, em vez de exibi-los na tela.
ini_set('log_errors', 1);
ini_set('error_log', dirname(__FILE__) . '/../php-errors.log');
ini_set('display_errors', 0);

function send_error_response($message) {
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_error_response('Método não permitido.');
}

try {
    // --- Sanitização de Dados ---
    $dados = [
        'nome' => htmlspecialchars($_POST['nome'] ?? '', ENT_QUOTES, 'UTF-8'),
        'whatsapp' => htmlspecialchars($_POST['whatsapp'] ?? '', ENT_QUOTES, 'UTF-8'),
        'email' => filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL),
        'cep' => htmlspecialchars($_POST['cep'] ?? '', ENT_QUOTES, 'UTF-8'),
        'rua' => htmlspecialchars($_POST['rua'] ?? '', ENT_QUOTES, 'UTF-8'),
        'numero' => htmlspecialchars($_POST['numero'] ?? '', ENT_QUOTES, 'UTF-8'),
        'bairro' => htmlspecialchars($_POST['bairro'] ?? '', ENT_QUOTES, 'UTF-8'),
        'cidade' => htmlspecialchars($_POST['cidade'] ?? '', ENT_QUOTES, 'UTF-8'),
        'estado' => htmlspecialchars($_POST['estado'] ?? '', ENT_QUOTES, 'UTF-8'),
        'dispositivo_final' => htmlspecialchars($_POST['dispositivo_final'] ?? '', ENT_QUOTES, 'UTF-8'), // <- CAMPO ATUALIZADO
        'marca' => htmlspecialchars($_POST['marca'] ?? '', ENT_QUOTES, 'UTF-8'),
        'modelo' => htmlspecialchars($_POST['modelo'] ?? '', ENT_QUOTES, 'UTF-8'),
        'descricao' => htmlspecialchars($_POST['descricao'] ?? '', ENT_QUOTES, 'UTF-8'),
        'problemas' => isset($_POST['problemas']) ? json_encode(array_map('htmlspecialchars', $_POST['problemas'])) : null
    ];
    
    if (empty($dados['nome']) || empty($dados['whatsapp']) || empty($dados['dispositivo_final'])) {
        send_error_response('Campos obrigatórios não foram preenchidos.');
    }
    
    // --- Lógica de Upload de Arquivo ---
    $media_path = null;
    if (isset($_FILES['media']) && $_FILES['media']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../assets/uploads/os_media/';
        if (!is_dir($upload_dir)) {
            if (!mkdir($upload_dir, 0755, true)) {
                send_error_response('Falha ao criar diretório de upload.');
            }
        }
        $extension = strtolower(pathinfo($_FILES['media']['name'], PATHINFO_EXTENSION));
        $filename = 'os_' . uniqid() . '.' . $extension;
        $destination = $upload_dir . $filename;
        if (move_uploaded_file($_FILES['media']['tmp_name'], $destination)) {
            $media_path = 'assets/uploads/os_media/' . $filename;
        }
    }

    // --- Inserção no Banco de Dados ---
    // ATENÇÃO: O campo `dispositivo_categoria` agora recebe o nome final do dispositivo
    $sql = "INSERT INTO ordens_servico (cliente_nome, cliente_whatsapp, cliente_email, cep, rua, numero, bairro, cidade, estado, dispositivo_categoria, dispositivo_marca, dispositivo_modelo, problemas_selecionados, descricao_problema, media_path, status) 
            VALUES (:nome, :whatsapp, :email, :cep, :rua, :numero, :bairro, :cidade, :estado, :dispositivo, :marca, :modelo, :problemas, :descricao, :media_path, 'Aguardando Chegada')";
    
    $stmt = $pdo->prepare($sql);
    
    $stmt->execute([
        ':nome' => $dados['nome'],
        ':whatsapp' => $dados['whatsapp'],
        ':email' => $dados['email'],
        ':cep' => $dados['cep'],
        ':rua' => $dados['rua'],
        ':numero' => $dados['numero'],
        ':bairro' => $dados['bairro'],
        ':cidade' => $dados['cidade'],
        ':estado' => $dados['estado'],
        ':dispositivo' => $dados['dispositivo_final'], // <- DADO ATUALIZADO
        ':marca' => $dados['marca'],
        ':modelo' => $dados['modelo'],
        ':problemas' => $dados['problemas'],
        ':descricao' => $dados['descricao'],
        ':media_path' => $media_path
    ]);

    $os_id = $pdo->lastInsertId();

    $stmt_hist = $pdo->prepare("INSERT INTO os_historico (os_id, status_novo, observacao) VALUES (?, ?, ?)");
    $stmt_hist->execute([$os_id, 'Aguardando Chegada', 'OS criada pelo cliente via site.']);

    // --- Enviar Notificação via Telegram ---
    $token = get_config($pdo, 'telegram_token');
    $chat_id = get_config($pdo, 'telegram_chat_id');

    if ($token && $chat_id) {
        $message  = "🚨 *Nova Ordem de Serviço!* 🔥\n\n";
        $message .= "*OS:* `" . $os_id . "`\n";
        $message .= "*Cliente:* " . $dados['nome'] . "\n";
        $message .= "*Dispositivo:* " . $dados['dispositivo_final'] . "\n";
        $message .= "*Descrição:* " . substr($dados['descricao'], 0, 100) . "...\n";
        
        $url = "https://api.telegram.org/bot{$token}/sendMessage";
        $post_fields = ['chat_id' => $chat_id, 'text' => $message, 'parse_mode' => 'Markdown'];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_exec($ch);
        curl_close($ch);
    }
    
    echo json_encode(['success' => true, 'os_id' => $os_id]);

} catch (Exception $e) {
    error_log("Erro Crítico ao criar OS: " . $e->getMessage());
    send_error_response('Ocorreu um erro inesperado no servidor. A equipe técnica foi notificada.');
}
?>