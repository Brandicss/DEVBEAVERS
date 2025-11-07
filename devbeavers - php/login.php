<?php
session_start();

// Verificar se o arquivo de conexão existe
if (!file_exists('conexao.php')) {
    die("Erro: Arquivo de conexão não encontrado.");
}

require 'conexao.php';

if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conexao, trim($_POST['email']));
    $senha = trim($_POST['senha']);

    // Validar campos
    if (empty($email) || empty($senha)) {
        $_SESSION['mensagem'] = 'Por favor, preencha todos os campos.';
        header('Location: login.html');
        exit;
    }

    $sql = "SELECT * FROM usuarios WHERE email = '$email'";
    $result = mysqli_query($conexao, $sql);

    if (!$result) {
        $_SESSION['mensagem'] = 'Erro na consulta ao banco de dados.';
        header('Location: login.html');
        exit;
    }

    if (mysqli_num_rows($result) > 0) {
        $usuario = mysqli_fetch_assoc($result);
        
        if (password_verify($senha, $usuario['senha'])) {
            // Login bem-sucedido
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['usuario_email'] = $usuario['email'];
            $_SESSION['curso_escolhido'] = $usuario['curso_escolhido'] ?? '';
            $_SESSION['is_admin'] = $usuario['is_admin'] ?? 0;
            $_SESSION['progresso_html'] = $usuario['progresso_html'] ?? 0;
            $_SESSION['estrelas_html'] = $usuario['estrelas_html'] ?? 0;
            
            $_SESSION['mensagem'] = 'Login realizado com sucesso! Bem-vindo, ' . $usuario['nome'] . '!';
            
            // Registrar último acesso
            $sql_update = "UPDATE usuarios SET ultimo_acesso = NOW() WHERE id = '{$usuario['id']}'";
            mysqli_query($conexao, $sql_update);
            
            // Redirecionar conforme perfil
            if ($_SESSION['is_admin']) {
                header('Location: crud.php');
            } else if (!empty($_SESSION['curso_escolhido'])) {
                header('Location: tela-inicial.php');
            } else {
                header('Location: curso.html');
            }
            exit;
        } else {
            $_SESSION['mensagem'] = 'Senha incorreta!';
            header('Location: login.html');
            exit;
        }
    } else {
        $_SESSION['mensagem'] = 'Usuário não encontrado!';
        header('Location: login.html');
        exit;
    }
} else {
    // Acesso direto ao arquivo
    header('Location: login.html');
    exit;
}
?>