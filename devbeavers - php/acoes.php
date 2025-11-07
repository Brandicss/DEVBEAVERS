<?php
session_start();
require 'conexao.php';

// DEBUG: Verificar se os dados estão chegando
error_log("DEBUG ACESSO: Página acoes.php acessada");
error_log("DEBUG POST: " . print_r($_POST, true));
error_log("DEBUG SESSION: " . print_r($_SESSION, true));

// Verificar conexão com banco
if (!$conexao) {
    $_SESSION['mensagem'] = 'Erro de conexão com o banco de dados.';
    if(isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1){
        header('Location: usuario-create.php');
    } else {
        header('Location: cadastro.html');
    }
    exit;
}

if (isset($_POST['create_usuario'])){
    error_log("DEBUG: Iniciando criação de usuário");
    
    $email = mysqli_real_escape_string($conexao, trim($_POST['email']));
    $nome = mysqli_real_escape_string($conexao, trim($_POST['nome']));
    $senha_input = trim($_POST['senha']);
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;

    error_log("DEBUG DADOS: Email: $email, Nome: $nome, Admin: $is_admin, Senha length: " . strlen($senha_input));

    // Validar campos obrigatórios
    if (empty($email) || empty($nome) || empty($senha_input)) {
        $_SESSION['mensagem'] = 'Todos os campos são obrigatórios.';
        error_log("DEBUG ERRO: Campos obrigatórios não preenchidos");
        header('Location: usuario-create.php');
        exit;
    }

    // Validar formato de email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['mensagem'] = 'Formato de email inválido.';
        error_log("DEBUG ERRO: Email inválido");
        header('Location: usuario-create.php');
        exit;
    }

    // Validar força da senha (AGORA FUNCIONANDO CORRETAMENTE)
    if (strlen($senha_input) < 6) {
        $_SESSION['mensagem'] = 'A senha deve ter pelo menos 6 caracteres.';
        error_log("DEBUG ERRO: Senha muito curta - " . strlen($senha_input) . " caracteres");
        header('Location: usuario-create.php');
        exit;
    }

    // Hash da senha
    $senha_hash = password_hash($senha_input, PASSWORD_DEFAULT);

    // Verificar se email já existe
    $check_sql = "SELECT id FROM usuarios WHERE email = '$email'";
    $check_result = mysqli_query($conexao, $check_sql);
    
    if(!$check_result) {
        $_SESSION['mensagem'] = 'Erro ao verificar email: ' . mysqli_error($conexao);
        error_log("DEBUG ERRO: Falha na query de verificação: " . mysqli_error($conexao));
        header('Location: usuario-create.php');
        exit;
    }
    
    if(mysqli_num_rows($check_result) > 0) {
        $_SESSION['mensagem'] = 'Erro: Este email já está cadastrado.';
        error_log("DEBUG ERRO: Email já existe");
        header('Location: usuario-create.php');
        exit;
    }

    // Inserir usuário
    $sql = "INSERT INTO usuarios (email, nome, senha, curso_escolhido, is_admin, data_criacao) 
            VALUES ('$email', '$nome', '$senha_hash', '', '$is_admin', NOW())";

    error_log("DEBUG SQL: " . $sql);

    if(mysqli_query($conexao, $sql)){
        $novo_usuario_id = mysqli_insert_id($conexao);
        error_log("DEBUG SUCESSO: Usuário criado com ID: " . $novo_usuario_id . ", Admin: " . $is_admin);
        
        // Registrar log usando o ID do admin que criou
        if(isset($_SESSION['usuario_id'])) {
            logAtividade($_SESSION['usuario_id'], 'USUARIO_CRIADO', 'Usuário criado: ' . $email . ' (Admin: ' . $is_admin . ')');
        }
        
        $_SESSION['mensagem'] = 'Usuário criado com sucesso! ' . ($is_admin ? '(Administrador)' : '');
        header('Location: crud.php');
        exit;
    } else {
        $erro = mysqli_error($conexao);
        $_SESSION['mensagem'] = 'Erro ao criar usuário: ' . $erro;
        error_log("DEBUG ERRO BANCO: " . $erro);
        header('Location: usuario-create.php');
        exit;
    }
}

if (isset($_POST['update_usuario'])){
    $usuario_id = mysqli_real_escape_string($conexao, $_POST['usuario_id']);

    $email = mysqli_real_escape_string($conexao, trim($_POST['email']));
    $nome = mysqli_real_escape_string($conexao, trim($_POST['nome']));
    $senha = mysqli_real_escape_string($conexao, trim($_POST['senha']));
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;

    // Validar formato de email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['mensagem'] = 'Formato de email inválido.';
        header('Location: usuario-edit.php?id=' . $usuario_id);
        exit;
    }

    $sql = "UPDATE usuarios SET nome = '$nome', email = '$email', is_admin = '$is_admin'";

    if (!empty($senha)) {
        // Validar força da senha
        if (strlen($senha) < 6) {
            $_SESSION['mensagem'] = 'A senha deve ter pelo menos 6 caracteres.';
            header('Location: usuario-edit.php?id=' . $usuario_id);
            exit;
        }
        $sql .= ", senha='" . password_hash($senha, PASSWORD_DEFAULT) . "'";
    }

    $sql .= " WHERE id = '$usuario_id'";

    mysqli_query($conexao, $sql);

    if(mysqli_affected_rows($conexao) > 0){
        // Registrar log usando o ID do admin que atualizou
        if(isset($_SESSION['usuario_id'])) {
            logAtividade($_SESSION['usuario_id'], 'USUARIO_ATUALIZADO', 'Usuário ID ' . $usuario_id . ' atualizado');
        }
        
        $_SESSION['mensagem'] = 'Usuário atualizado com sucesso';
        header('Location: crud.php');
        exit;
    } else {
        $_SESSION['mensagem'] = 'Usuário não foi atualizado';
        header('Location: crud.php');
        exit;
    }
}

if (isset($_POST['delete_usuario'])){
    $usuario_id = mysqli_real_escape_string($conexao, $_POST['delete_usuario']);

    // Impedir que admin se delete
    if($usuario_id == $_SESSION['usuario_id']){
        $_SESSION['mensagem'] = 'Você não pode excluir sua própria conta!';
        header('Location: crud.php');
        exit;
    }

    // **CORREÇÃO: Registrar log PRIMEIRO usando o ID do admin que está executando**
    if(isset($_SESSION['usuario_id'])) {
        logAtividade($_SESSION['usuario_id'], 'USUARIO_EXCLUIDO', 'Usuário ID ' . $usuario_id . ' excluído');
    }

    $sql = "DELETE FROM usuarios WHERE id = '$usuario_id'";
    mysqli_query($conexao, $sql);

    if(mysqli_affected_rows($conexao) > 0){
        $_SESSION['mensagem'] = 'Usuário deletado com sucesso';
        header('Location: crud.php');
        exit;
    } else {
        $_SESSION['mensagem'] = 'Usuário não foi deletado';
        header('Location: crud.php');
        exit; 
    }
}

// CORREÇÃO na função escolher_curso - Linha ~150
if (isset($_POST['escolher_curso'])){
    $usuario_id = $_SESSION['usuario_id'];
    $curso = mysqli_real_escape_string($conexao, $_POST['curso']);
    
    $sql = "UPDATE usuarios SET curso_escolhido = '$curso' WHERE id = '$usuario_id'";
    
    if (mysqli_query($conexao, $sql)) {
        $_SESSION['curso_escolhido'] = $curso;
        
        // ATUALIZAR também as variáveis de sessão do progresso específico do curso
        $coluna_progresso = 'progresso_' . $curso;
        $coluna_estrelas = 'estrelas_' . $curso;
        
        // Buscar progresso atual do curso selecionado
        $sql_progresso = "SELECT $coluna_progresso, $coluna_estrelas FROM usuarios WHERE id = '$usuario_id'";
        $result_progresso = mysqli_query($conexao, $sql_progresso);
        
        if ($result_progresso && $dados = mysqli_fetch_assoc($result_progresso)) {
            $_SESSION[$coluna_progresso] = $dados[$coluna_progresso] ?? 0;
            $_SESSION[$coluna_estrelas] = $dados[$coluna_estrelas] ?? 0;
        }
        
        $_SESSION['mensagem_sucesso'] = "Curso de " . strtoupper($curso) . " selecionado com sucesso!";
        header('Location: tela-inicial.php');
        
    } else {
        $_SESSION['mensagem_erro'] = "Erro ao selecionar curso.";
        header('Location: curso.html');
    }
    exit;
}

// Função para salvar progresso específico por curso
if (isset($_GET['salvar_progresso_curso'])) {
    if (!isset($_SESSION['usuario_id'])) {
        echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
        exit;
    }

    $usuario_id = $_SESSION['usuario_id'];
    $curso = mysqli_real_escape_string($conexao, $_GET['curso']);
    $fase_concluida = intval($_GET['fase_concluida'] ?? 0);
    $estrelas = intval($_GET['estrelas'] ?? 0);

    // Determinar qual coluna atualizar baseado no curso
    $coluna_progresso = '';
    $coluna_estrelas = '';
    
    switch($curso) {
        case 'css':
            $coluna_progresso = 'progresso_css';
            $coluna_estrelas = 'estrelas_css';
            break;
        case 'logica':
            $coluna_progresso = 'progresso_logica';
            $coluna_estrelas = 'estrelas_logica';
            break;
        case 'html':
        default:
            $coluna_progresso = 'progresso_html';
            $coluna_estrelas = 'estrelas_html';
            break;
    }

    // Buscar progresso atual
    $sql = "SELECT $coluna_progresso, $coluna_estrelas FROM usuarios WHERE id = '$usuario_id'";
    $result = mysqli_query($conexao, $sql);
    
    if ($result && $usuario = mysqli_fetch_assoc($result)) {
        $progresso_atual = $usuario[$coluna_progresso] ?? 0;
        $estrelas_atuais = $usuario[$coluna_estrelas] ?? 0;
        
        // CORREÇÃO: Só atualizar progresso se a fase for maior que a atual
        if ($fase_concluida > $progresso_atual) {
            $progresso_atual = $fase_concluida;
            
            // CORREÇÃO CRÍTICA: Somar estrelas em vez de substituir
            $novas_estrelas = $estrelas_atuais + $estrelas;
            // Garantir que não ultrapasse o máximo possível (5 fases × 3 estrelas = 15)
            $novas_estrelas = min($novas_estrelas, 15);
        } else {
            // Se não há novo progresso, manter as estrelas atuais
            $novas_estrelas = $estrelas_atuais;
        }
        
        // Atualizar banco
        $sql_update = "UPDATE usuarios SET 
                      $coluna_progresso = $progresso_atual, 
                      $coluna_estrelas = $novas_estrelas,
                      ultima_atualizacao = NOW()
                      WHERE id = $usuario_id";
        
        if (mysqli_query($conexao, $sql_update)) {
            // Atualizar sessão
            $_SESSION[$coluna_progresso] = $progresso_atual;
            $_SESSION[$coluna_estrelas] = $novas_estrelas;
            
            echo json_encode([
                'success' => true, 
                'message' => "Progresso salvo com sucesso!",
                'novo_progresso' => $progresso_atual,
                'novas_estrelas' => $novas_estrelas
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => "Erro ao salvar progresso no banco de dados."]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => "Usuário não encontrado."]);
    }
    exit;
}

// Função principal para salvar progresso (mantida para compatibilidade)
if (isset($_GET['fase_concluida'])) {
    if (!isset($_SESSION['usuario_id'])) {
        echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
        exit;
    }

    $usuario_id = $_SESSION['usuario_id'];
    $fase_concluida = intval($_GET['fase_concluida'] ?? 0);
    $estrelas = intval($_GET['estrelas'] ?? 0);
    $curso = mysqli_real_escape_string($conexao, $_GET['curso'] ?? 'html');

    // Determinar qual coluna atualizar baseado no curso
    $coluna_progresso = '';
    $coluna_estrelas = '';
    
    switch($curso) {
        case 'css':
            $coluna_progresso = 'progresso_css';
            $coluna_estrelas = 'estrelas_css';
            break;
        case 'logica':
            $coluna_progresso = 'progresso_logica';
            $coluna_estrelas = 'estrelas_logica';
            break;
        case 'html':
        default:
            $coluna_progresso = 'progresso_html';
            $coluna_estrelas = 'estrelas_html';
            break;
    }

    // Buscar progresso atual
    $sql = "SELECT $coluna_progresso, $coluna_estrelas FROM usuarios WHERE id = '$usuario_id'";
    $result = mysqli_query($conexao, $sql);
    
    if ($result && $usuario = mysqli_fetch_assoc($result)) {
        $progresso_atual = $usuario[$coluna_progresso] ?? 0;
        $estrelas_atuais = $usuario[$coluna_estrelas] ?? 0;
        
        // CORREÇÃO: Só atualizar progresso se a fase for maior que a atual
        if ($fase_concluida > $progresso_atual) {
            $progresso_atual = $fase_concluida;
            
            // CORREÇÃO CRÍTICA: Somar estrelas em vez de substituir
            $novas_estrelas = $estrelas_atuais + $estrelas;
            // Garantir que não ultrapasse o máximo possível (5 fases × 3 estrelas = 15)
            $novas_estrelas = min($novas_estrelas, 15);
        } else {
            // Se não há novo progresso, manter as estrelas atuais
            $novas_estrelas = $estrelas_atuais;
        }
        
        // Atualizar banco
        $sql_update = "UPDATE usuarios SET 
                      $coluna_progresso = $progresso_atual, 
                      $coluna_estrelas = $novas_estrelas,
                      ultima_atualizacao = NOW()
                      WHERE id = $usuario_id";
        
        if (mysqli_query($conexao, $sql_update)) {
            // Atualizar sessão
            $_SESSION[$coluna_progresso] = $progresso_atual;
            $_SESSION[$coluna_estrelas] = $novas_estrelas;
            
            echo json_encode([
                'success' => true, 
                'message' => "Progresso salvo com sucesso!",
                'novo_progresso' => $progresso_atual,
                'novas_estrelas' => $novas_estrelas,
                'curso' => $curso
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => "Erro ao salvar progresso no banco de dados: " . mysqli_error($conexao)]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => "Usuário não encontrado."]);
    }
    exit;
}

// Log de atividades (CORRIGIDA para evitar problemas com FK)
function logAtividade($usuario_id, $acao, $descricao = '') {
    global $conexao;
    
    // Verificar se o usuário ainda existe antes de registrar o log
    $check_sql = "SELECT id FROM usuarios WHERE id = ?";
    $check_stmt = mysqli_prepare($conexao, $check_sql);
    
    if ($check_stmt) {
        mysqli_stmt_bind_param($check_stmt, "i", $usuario_id);
        mysqli_stmt_execute($check_stmt);
        mysqli_stmt_store_result($check_stmt);
        
        // Só registrar log se o usuário existir
        if (mysqli_stmt_num_rows($check_stmt) > 0) {
            $sql = "INSERT INTO logs_sistema (usuario_id, acao, descricao) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($conexao, $sql);
            
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "iss", $usuario_id, $acao, $descricao);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        }
        mysqli_stmt_close($check_stmt);
    }
}

// Criar tabela de logs se não existir (executar apenas uma vez)
$sql_tabela_logs = "CREATE TABLE IF NOT EXISTS logs_sistema (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    acao VARCHAR(100) NOT NULL,
    descricao TEXT,
    data_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

mysqli_query($conexao, $sql_tabela_logs);

// Se nenhuma ação foi detectada
$_SESSION['mensagem'] = 'Nenhuma ação foi solicitada.';
header('Location: crud.php');
exit;
?>