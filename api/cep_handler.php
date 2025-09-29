<?php
header('Content-Type: application/json');

if (!isset($_GET['cep'])) {
    echo json_encode(['erro' => 'CEP não fornecido.']);
    exit;
}

// Limpa o CEP para conter apenas números
$cep = preg_replace('/[^0-9]/', '', $_GET['cep']);

if (strlen($cep) !== 8) {
    echo json_encode(['erro' => 'Formato de CEP inválido.']);
    exit;
}

$url = "https://viacep.com.br/ws/{$cep}/json/";

// Usa cURL para fazer a requisição, é mais robusto que file_get_contents
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Apenas para ambiente de desenvolvimento local, se necessário
$response = curl_exec($ch);
curl_close($ch);

echo $response;
?>