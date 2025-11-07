<?php
// teste-conexao.php
session_start();
require 'conexao.php';

echo "Teste de Conexão<br>";

// Testar se usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    echo "❌ Usuário não logado<br>";
} else {
    echo "✅ Usuário logado: {$_SESSION['usuario_id']}<br>";
}

// Testar conexão com banco
if (!$conexao) {
    echo "❌ Falha na conexão com banco<br>";
} else {
    echo "✅ Conexão com banco OK<br>";
}

// Testar requisição GET
echo "✅ Script PHP está funcionando<br>";
?>