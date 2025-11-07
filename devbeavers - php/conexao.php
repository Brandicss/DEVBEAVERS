<?php
// Configurações do banco de dados
$servidor = "localhost";
$usuario = "root";
$senha = "Home@spSENAI2025!";
$dbname = "devbeavers";

// Criar conexão
$conexao = mysqli_connect($servidor, $usuario, $senha, $dbname);

// Verificar conexão
if (!$conexao) {
    die("Falha na conexão com o banco de dados: " . mysqli_connect_error());
}

// Definir charset para UTF-8
mysqli_set_charset($conexao, "utf8mb4");

// Criar tabela de usuários com todas as colunas necessárias
$sql_tabela_usuarios = "CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    nome VARCHAR(100) NOT NULL,
    senha VARCHAR(255) NOT NULL,
    curso_escolhido VARCHAR(50) DEFAULT 'html',
    progresso_html INT DEFAULT 0,
    estrelas_html INT DEFAULT 0,
    progresso_css INT DEFAULT 0,
    estrelas_css INT DEFAULT 0,
    progresso_logica INT DEFAULT 0,
    estrelas_logica INT DEFAULT 0,
    is_admin TINYINT DEFAULT 0,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultimo_acesso TIMESTAMP NULL,
    ultima_atualizacao TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (!mysqli_query($conexao, $sql_tabela_usuarios)) {
    error_log("Erro ao criar tabela usuarios: " . mysqli_error($conexao));
} else {
    // Verificar e adicionar colunas faltantes
    $colunas_necessarias = [
        'ultimo_acesso' => "ALTER TABLE usuarios ADD COLUMN ultimo_acesso TIMESTAMP NULL AFTER data_criacao",
        'ultima_atualizacao' => "ALTER TABLE usuarios ADD COLUMN ultima_atualizacao TIMESTAMP NULL AFTER ultimo_acesso",
        'curso_escolhido' => "ALTER TABLE usuarios ADD COLUMN curso_escolhido VARCHAR(50) DEFAULT 'html' AFTER senha",
        'progresso_html' => "ALTER TABLE usuarios ADD COLUMN progresso_html INT DEFAULT 0 AFTER curso_escolhido",
        'estrelas_html' => "ALTER TABLE usuarios ADD COLUMN estrelas_html INT DEFAULT 0 AFTER progresso_html",
        'progresso_css' => "ALTER TABLE usuarios ADD COLUMN progresso_css INT DEFAULT 0 AFTER estrelas_html",
        'estrelas_css' => "ALTER TABLE usuarios ADD COLUMN estrelas_css INT DEFAULT 0 AFTER progresso_css",
        'progresso_logica' => "ALTER TABLE usuarios ADD COLUMN progresso_logica INT DEFAULT 0 AFTER estrelas_css",
        'estrelas_logica' => "ALTER TABLE usuarios ADD COLUMN estrelas_logica INT DEFAULT 0 AFTER progresso_logica",
        'is_admin' => "ALTER TABLE usuarios ADD COLUMN is_admin TINYINT DEFAULT 0 AFTER estrelas_logica"
    ];
    
    foreach ($colunas_necessarias as $coluna => $sql_alter) {
        $result = mysqli_query($conexao, "SHOW COLUMNS FROM usuarios LIKE '$coluna'");
        if (mysqli_num_rows($result) == 0) {
            mysqli_query($conexao, $sql_alter);
        }
    }
}

// Criar tabela de progresso se não existir
$sql_tabela_progresso = "CREATE TABLE IF NOT EXISTS progresso_usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    modulo VARCHAR(50) NOT NULL,
    fase INT NOT NULL,
    concluida BOOLEAN DEFAULT FALSE,
    estrelas INT DEFAULT 0,
    data_conclusao TIMESTAMP NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    UNIQUE KEY unique_progresso (usuario_id, modulo, fase)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (!mysqli_query($conexao, $sql_tabela_progresso)) {
    error_log("Erro ao criar tabela progresso_usuarios: " . mysqli_error($conexao));
}

// Verificar se existe algum admin, se não, criar um padrão
$sql_check_admin = "SELECT COUNT(*) as total FROM usuarios WHERE is_admin = 1";
$result_admin = mysqli_query($conexao, $sql_check_admin);
if ($result_admin) {
    $admin_count = mysqli_fetch_assoc($result_admin)['total'];

    if ($admin_count == 0) {
        // Criar admin padrão
        $admin_email = "admin@devbeavers.com";
        $admin_nome = "Administrador";
        $admin_senha = password_hash("admin123", PASSWORD_DEFAULT);
        
        $sql_insert_admin = "INSERT IGNORE INTO usuarios (email, nome, senha, is_admin) VALUES ('$admin_email', '$admin_nome', '$admin_senha', 1)";
        if (!mysqli_query($conexao, $sql_insert_admin)) {
            error_log("Erro ao criar admin padrão: " . mysqli_error($conexao));
        } else {
            error_log("Admin padrão criado: $admin_email / admin123");
        }
    }
}
?>