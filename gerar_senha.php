<?php
// A senha que queremos criptografar
$senhaPlana = 'admin123';

// Gera o hash seguro
$hash = password_hash($senhaPlana, PASSWORD_DEFAULT);

// Exibe o hash na tela
echo '<h1>Seu novo hash de senha é:</h1>';
echo '<p style="font-family: monospace; background-color: #f0f0f0; padding: 10px; border: 1px solid #ccc; word-wrap: break-word;">' . $hash . '</p>';
echo '<p><strong>Copie a linha de texto acima e siga as instruções.</strong></p>';

?>