<?php
session_start();
require 'conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.html');
    exit;
}

// Sistema de mensagens
$mensagem_sucesso = $_SESSION['mensagem_sucesso'] ?? '';
$mensagem_erro = $_SESSION['mensagem_erro'] ?? '';

// Limpar mensagens após usar
unset($_SESSION['mensagem_sucesso']);
unset($_SESSION['mensagem_erro']);

// Buscar progresso do usuário
$usuario_id = $_SESSION['usuario_id'];
$sql_progresso = "SELECT progresso_html, estrelas_html, progresso_css, estrelas_css, progresso_logica, estrelas_logica, curso_escolhido, is_admin FROM usuarios WHERE id = '$usuario_id'";
$result_progresso = mysqli_query($conexao, $sql_progresso);
$progresso = mysqli_fetch_assoc($result_progresso);

$curso_escolhido = $progresso['curso_escolhido'] ?? 'html';
$is_admin = $progresso['is_admin'] ?? 0;

// Determinar progresso baseado no curso escolhido
switch($curso_escolhido) {
    case 'css':
        $progresso_curso = $progresso['progresso_css'] ?? 0;
        $estrelas_curso = $progresso['estrelas_css'] ?? 0;
        $curso_nome = 'CSS';
        $curso_cor = '#264de4';
        break;
    case 'logica':
        $progresso_curso = $progresso['progresso_logica'] ?? 0;
        $estrelas_curso = $progresso['estrelas_logica'] ?? 0;
        $curso_nome = 'Lógica de Programação';
        $curso_cor = '#8e44ad';
        break;
    case 'html':
    default:
        $progresso_curso = $progresso['progresso_html'] ?? 0;
        $estrelas_curso = $progresso['estrelas_html'] ?? 0;
        $curso_nome = 'HTML';
        $curso_cor = '#F78008';
        break;
}

$porcentagem = min(100, ($progresso_curso / 5) * 100);

// Atualizar sessão com curso
$_SESSION['curso_escolhido'] = $curso_escolhido;
$_SESSION['is_admin'] = $is_admin;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevBeaver - Área do Aluno</title>
    <link rel="shortcut icon" href="imagens_tamanho_certo/favicon.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=B612:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    <style>
        * {
            padding: 0;
            margin: 0;
            box-sizing: border-box;
            font-family: "B612", sans-serif;
        }

        /* Melhorias de Acessibilidade */
        button:focus,
        a:focus,
        input:focus,
        select:focus,
        textarea:focus {
            outline: 3px solid #F78008;
            outline-offset: 2px;
        }

        button:focus:not(:focus-visible),
        a:focus:not(:focus-visible) {
            outline: none;
        }

        button, 
        .btn,
        .answer {
            min-height: 44px;
            min-width: 44px;
        }

        .skip-link {
            position: absolute;
            top: -40px;
            left: 6px;
            background: #F78008;
            color: white;
            padding: 8px;
            text-decoration: none;
            border-radius: 4px;
            z-index: 10000;
        }

        .skip-link:focus {
            top: 6px;
        }

        body {
            line-height: 1.6;
            text-rendering: optimizeLegibility;
            -webkit-font-smoothing: antialiased;
            background: linear-gradient(135deg, #fff6e9 0%, #ffffff 100%);
            color: #333;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            position: relative;
            overflow-x: hidden;
        }

        /* Sistema de Mensagens */
        .mensagem-flutuante {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
            padding: 15px 20px;
            border-radius: 8px;
            color: white;
            font-weight: bold;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            animation: slideIn 0.5s ease;
            max-width: 400px;
        }

        .mensagem-sucesso {
            background: #4CAF50;
            border-left: 5px solid #2E7D32;
        }

        .mensagem-erro {
            background: #f44336;
            border-left: 5px solid #c62828;
        }

        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        .fechar-mensagem {
            background: none;
            border: none;
            color: white;
            font-size: 18px;
            cursor: pointer;
            margin-left: 15px;
            float: right;
            min-height: auto;
            min-width: auto;
        }

        /* Barra Lateral */
        nav.menu-lateral {
            width: 80px;
            height: 100vh;
            background: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%);
            padding: 25px 0;
            box-shadow: 4px 0px 20px rgba(0, 0, 0, 0.3);
            position: fixed;
            top: 0;
            left: 0;
            overflow: hidden;
            transition: all 0.4s ease;
            z-index: 1000;
            display: flex;
            flex-direction: column;
        }

        nav.menu-lateral:hover {
            width: 260px;
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 0 25px 25px 25px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 25px;
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #F78008 0%, #ffae42 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            flex-shrink: 0;
        }

        .logo-text {
            color: white;
            font-size: 18px;
            font-weight: bold;
            white-space: nowrap;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        nav.menu-lateral:hover .logo-text {
            opacity: 1;
        }

        .logo-text span {
            color: #F78008;
        }

        .user-info-sidebar {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 20px;
            margin: 0 10px 20px 10px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .user-avatar-sidebar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(135deg, #F78008 0%, #ffae42 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
            flex-shrink: 0;
        }

        .user-details-sidebar {
            flex: 1;
            min-width: 0;
        }

        .user-name-sidebar {
            color: white;
            font-weight: bold;
            font-size: 14px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .user-status-sidebar {
            color: #4CAF50;
            font-size: 12px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        nav.menu-lateral:hover .user-name-sidebar,
        nav.menu-lateral:hover .user-status-sidebar {
            opacity: 1;
        }

        .menu-items {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 8px;
            padding: 0 15px;
        }

        .menu-item {
            position: relative;
        }

        .menu-item a {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 14px 15px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .menu-item a::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 3px;
            background: #F78008;
            transform: scaleY(0);
            transition: transform 0.3s ease;
        }

        .menu-item a:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transform: translateX(5px);
        }

        .menu-item a:hover::before {
            transform: scaleY(1);
        }

        .menu-item.active a {
            background: linear-gradient(90deg, rgba(247, 128, 8, 0.2) 0%, rgba(247, 128, 8, 0.1) 100%);
            color: #F78008;
        }

        .menu-item.active a::before {
            transform: scaleY(1);
        }

        .menu-icon {
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
            transition: transform 0.3s ease;
        }

        .menu-item a:hover .menu-icon {
            transform: scale(1.1);
        }

        .menu-text {
            font-size: 14px;
            font-weight: 500;
            white-space: nowrap;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        nav.menu-lateral:hover .menu-text {
            opacity: 1;
        }

        .menu-badge {
            background: #F78008;
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
            margin-left: auto;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        nav.menu-lateral:hover .menu-badge {
            opacity: 1;
        }

        .menu-footer {
            padding: 20px 15px 0 15px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: auto;
        }

        .progress-info {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        nav.menu-lateral:hover .progress-info {
            opacity: 1;
        }

        .progress-text {
            color: rgba(255, 255, 255, 0.8);
            font-size: 12px;
            flex: 1;
        }

        .progress-percent {
            color: #F78008;
            font-weight: bold;
            font-size: 12px;
        }

        .progress-bar-mini {
            height: 4px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 2px;
            overflow: hidden;
            margin-bottom: 15px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        nav.menu-lateral:hover .progress-bar-mini {
            opacity: 1;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #F78008, #ffae42);
            border-radius: 2px;
            transition: width 0.3s ease;
        }

        /* Conteúdo principal */
        .main-content {
            margin-left: 80px;
            padding: 30px;
            width: calc(100% - 80px);
            transition: margin-left 0.3s;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 20px;
        }

        .welcome-message {
            padding: 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .welcome-message h1 {
            font-size: 28px;
            color: #F78008;
            margin-bottom: 10px;
        }

        .welcome-message p {
            color: #666;
            font-size: 16px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
            background: white;
            padding: 15px 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #F78008;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 22px;
        }

        .user-details {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-weight: bold;
            color: #333;
        }

        .user-status {
            color: #4caf50;
            font-size: 14px;
        }

        /* Módulo e progresso */
        .modulo {
            background: linear-gradient(135deg, <?php echo $curso_cor; ?> 0%, <?php echo $curso_cor; ?>dd 100%);
            color: white;
            padding: 20px;
            border-radius: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            max-width: 800px;
            margin: 0 auto 30px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
        }

        .modulo small {
            font-size: 14px;
            opacity: 0.9;
        }

        .modulo span {
            font-size: 22px;
            font-weight: bold;
        }

        .modulo-progress {
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: bold;
            backdrop-filter: blur(5px);
        }

        .progresso {
            background: white;
            padding: 30px;
            border-radius: 15px;
            display: flex;
            flex-direction: column;
            margin: 0 auto;
            width: 100%;
            max-width: 900px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.08);
            position: relative;
            overflow: hidden;
        }

        .progresso::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, <?php echo $curso_cor; ?>, <?php echo $curso_cor; ?>dd);
        }

        .stars-container {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin: 20px 0 30px;
        }

        .star {
            width: 50px;
            height: 50px;
            transition: all 0.3s ease;
        }

        .star.amarela {
            filter: brightness(1) saturate(2) hue-rotate(0deg) !important;
            transform: scale(1.1);
        }

        .buttons-container {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .btn {
            background: linear-gradient(to right, <?php echo $curso_cor; ?>, <?php echo $curso_cor; ?>dd);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            min-width: 120px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            font-size: 14px;
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.4);
        }

        .btn:active {
            transform: translateY(0);
        }

        .btn.disabled {
            background: linear-gradient(to right, #cccccc, #999999) !important;
            cursor: not-allowed !important;
            opacity: 0.6;
            transform: none !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1) !important;
        }

        .btn.disabled:hover {
            transform: none !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1) !important;
        }

        /* Castor animado */
        .castor-container {
            position: fixed;
            right: 30px;
            bottom: 30px;
            z-index: 10;
        }

        .castor {
            width: 180px;
            transform-origin: center bottom;
            animation: float 3s ease-in-out infinite;
            cursor: pointer;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0); }
            50% { transform: translateY(-15px) rotate(2deg); }
        }

        /* Cards de informações */
        .info-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin: 40px auto;
            max-width: 900px;
        }

        .card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            color: <?php echo $curso_cor; ?>;
        }

        .card-header i {
            font-size: 24px;
            margin-right: 15px;
        }

        .card h3 {
            font-size: 18px;
            margin-bottom: 10px;
            color: #333;
        }

        .card p {
            color: #666;
            line-height: 1.5;
        }

        /* Rodapé */
        footer {
            background: linear-gradient(135deg, #ff8c00 0%, #ffae42 100%);
            color: white;
            text-align: center;
            padding: 25px;
            margin-top: auto;
            margin-left: 80px;
            width: calc(100% - 80px);
        }

        .footer-content {
            max-width: 900px;
            margin: 0 auto;
        }

        .footer-links {
            display: flex;
            justify-content: center;
            gap: 25px;
            margin: 15px 0;
            flex-wrap: wrap;
        }

        .footer-links a {
            color: white;
            text-decoration: none;
            transition: opacity 0.3s;
        }

        .footer-links a:hover {
            opacity: 0.8;
        }

        .copyright {
            margin-top: 15px;
            font-size: 14px;
            opacity: 0.9;
        }

        /* Responsividade */
        @media (max-width: 900px) {
            .main-content {
                margin-left: 0;
                width: 100%;
                padding: 20px;
            }
            
            nav.menu-lateral {
                width: 70px;
            }
            
            nav.menu-lateral:hover {
                width: 70px;
            }
            
            footer {
                margin-left: 0;
                width: 100%;
            }
            
            .header {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        @media (max-width: 600px) {
            .modulo {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .stars-container {
                gap: 10px;
            }
            
            .star {
                width: 40px;
                height: 40px;
            }
            
            .castor-container {
                right: 15px;
                bottom: 15px;
            }
            
            .castor {
                width: 120px;
            }
            
            .buttons-container {
                gap: 10px;
            }
            
            .btn {
                padding: 10px 15px;
                min-width: 100px;
            }

            .mensagem-flutuante {
                right: 10px;
                left: 10px;
                max-width: none;
            }
        }

        /* Suporte para modo de alto contraste */
        @media (prefers-contrast: high) {
            .btn {
                background: #b35900;
            }
            
            .modulo {
                background: #b35900;
            }
            
            footer {
                background: #b35900;
            }
        }

        /* Suporte para redução de movimento */
        @media (prefers-reduced-motion: reduce) {
            .castor {
                animation: none;
            }
            
            .menu-item a,
            .btn,
            .card {
                transition: none;
            }
        }
    </style>
</head>
<body>
    <!-- Link para pular navegação (acessibilidade) -->
    <a href="#conteudo-principal" class="skip-link">Pular para o conteúdo principal</a>
    
    <!-- Sistema de Mensagens -->
    <?php if ($mensagem_sucesso): ?>
    <div class="mensagem-flutuante mensagem-sucesso">
        <?php echo $mensagem_sucesso; ?>
        <button class="fechar-mensagem" onclick="this.parentElement.remove()">✕</button>
    </div>
    <?php endif; ?>

    <?php if ($mensagem_erro): ?>
    <div class="mensagem-flutuante mensagem-erro">
        <?php echo $mensagem_erro; ?>
        <button class="fechar-mensagem" onclick="this.parentElement.remove()">✕</button>
    </div>
    <?php endif; ?>

    <nav class="menu-lateral">
        <div class="logo-container">
            <div class="logo-icon">
                <i class="bi bi-code-slash"></i>
            </div>
            <div class="logo-text">Dev<span>Beaver</span></div>
        </div>

        <div class="user-info-sidebar">
            <div class="user-avatar-sidebar">
                <i class="bi bi-person-fill"></i>
            </div>
            <div class="user-details-sidebar">
                <div class="user-name-sidebar"><?php echo $_SESSION['usuario_nome']; ?></div>
                <div class="user-status-sidebar">Online - <?php echo $curso_nome; ?></div>
            </div>
        </div>

        <div class="menu-items">
            <div class="menu-item active">
                <a href="tela-inicial.php">
                    <div class="menu-icon">
                        <i class="bi bi-house-fill"></i>
                    </div>
                    <div class="menu-text">Início</div>
                </a>
            </div>
            
            <div class="menu-item">
                <a href="curso.html">
                    <div class="menu-icon">
                        <i class="bi bi-journals"></i>
                    </div>
                    <div class="menu-text">Trocar de Curso</div>
                    <div class="menu-badge"><?php echo strtoupper($curso_escolhido); ?></div>
                </a>
            </div>

            <?php if ($is_admin == 1): ?>
            <div class="menu-item">
                <a href="crud.php">
                    <div class="menu-icon">
                        <i class="bi bi-shield-lock"></i>
                    </div>
                    <div class="menu-text">Painel Admin</div>
                    <div class="menu-badge">Admin</div>
                </a>
            </div>
            <?php endif; ?>
        </div>

        <div class="menu-footer">
            <div class="progress-info">
                <div class="progress-text">Progresso em <?php echo $curso_nome; ?></div>
                <div class="progress-percent"><?php echo $porcentagem; ?>%</div>
            </div>
            <div class="progress-bar-mini">
                <div class="progress-fill" style="width: <?php echo $porcentagem; ?>%"></div>
            </div>
            
            <div class="menu-item">
                <a href="logout.php">
                    <div class="menu-icon">
                        <i class="bi bi-box-arrow-left"></i>
                    </div>
                    <div class="menu-text">Sair</div>
                </a>
            </div>
        </div>
    </nav>

    <main class="main-content" id="conteudo-principal">
        <div class="header">
            <div class="welcome-message">
                <h1>Bem-vindo, <?php echo $_SESSION['usuario_nome']; ?>!</h1>
                <p>Continue aprendendo <?php echo $curso_nome; ?> de forma divertida e desafiadora</p>
            </div>
            
            <div class="user-info">
                <div class="user-avatar" style="background: <?php echo $curso_cor; ?>;">
                    <i class="bi bi-person-fill"></i>
                </div>
                <div class="user-details">
                    <span class="user-name"><?php echo $_SESSION['usuario_nome']; ?></span>
                    <span class="user-status">
                        Curso: <?php echo $curso_nome; ?>
                        <?php if ($is_admin == 1): ?>
                            <br><small style="color: #F78008;">(Administrador)</small>
                        <?php endif; ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="modulo">
            <div>
                <small>MÓDULO ATUAL</small><br>
                <span><?php echo $curso_nome; ?> - PROGRESSO</span>
            </div>
            <div class="modulo-progress" id="progresso-porcentagem"><?php echo $porcentagem; ?>%</div>
        </div>

        <div class="progresso">
            <div class="stars-container" id="stars-container">
                <?php
                for ($i = 1; $i <= 5; $i++) {
                    $classe_estrela = ($i <= $estrelas_curso) ? 'amarela' : '';
                    $src_estrela = ($i <= $estrelas_curso) ? 'imagens_tamanho_certo/estrela.png' : 'imagens_tamanho_certo/estrela-cinza.png';
                    echo "<img class='star $classe_estrela' src='$src_estrela' alt='Estrela $i' data-estrela='$i'>";
                }
                ?>
            </div>
            
            <div class="buttons-container">
                <?php
                for ($i = 1; $i <= 5; $i++) {
                    $disabled = ($i > 1 && $i > $progresso_curso + 1) ? 'disabled' : '';
                    $btn_class = ($disabled) ? 'btn disabled' : 'btn fase-btn';
                    echo "<button class='$btn_class' data-fase='$i' $disabled onclick=\"iniciarFase($i)\">Fase $i</button>";
                }
                ?>
            </div>
        </div>

        <div class="info-cards">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-trophy-fill"></i>
                    <h3>Seu Progresso</h3>
                </div>
                <p>Você completou <?php echo $progresso_curso; ?> de 5 fases do curso de <?php echo $curso_nome; ?>. Continue assim!</p>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-lightbulb-fill"></i>
                    <h3>Dica de Estudo</h3>
                </div>
                <p>
                    <?php 
                    switch($curso_escolhido) {
                        case 'css':
                            echo "Pratique os seletores CSS e experimente diferentes propriedades para ver os resultados em tempo real.";
                            break;
                        case 'logica':
                            echo "Resolva problemas simples no papel antes de tentar codificar. Isso ajuda a desenvolver o raciocínio lógico.";
                            break;
                        case 'html':
                        default:
                            echo "Revise as tags HTML básicas antes de começar as próximas fases.";
                            break;
                    }
                    ?>
                </p>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-clock-fill"></i>
                    <h3>Tempo Médio</h3>
                </div>
                <p>Alunos levam em média 15-20 minutos para completar cada fase. Vá no seu ritmo!</p>
            </div>
        </div>
    </main>

    <div class="castor-container">
        <img class="castor" src="imagens_tamanho_certo/sentadoacenado-removebg-preview (2).png" alt="Castor mascote da DevBeaver">
    </div>

    <footer>
        <div class="footer-content">
            <div class="footer-links">
                <a href="#">Sobre Nós</a>
                <a href="#">Contato</a>
                <a href="#">Ajuda</a>
                <a href="#">Termos de Uso</a>
            </div>
            <div class="copyright">
                &copy; 2025 DevBeaver - Todos os direitos reservados
            </div>
        </div>
    </footer>

    <!-- Sistema de Notificações -->
    <link rel="stylesheet" href="notification.css">
    <script src="notification.js"></script>

    <script>
// Função para atualizar as estrelas na interface
// Função para atualizar as estrelas na interface
function atualizarEstrelas(estrelasTotais) {
    const starsContainer = document.getElementById('stars-container');
    const stars = starsContainer.querySelectorAll('.star');
    
    stars.forEach((star, index) => {
        const estrelaNumero = index + 1;
        if (estrelaNumero <= estrelasTotais) {
            star.src = 'imagens_tamanho_certo/estrela.png';
            star.classList.add('amarela');
        } else {
            star.src = 'imagens_tamanho_certo/estrela-cinza.png';
            star.classList.remove('amarela');
        }
    });
    
    // Atualizar também o progresso visual se necessário
    const progressoElement = document.getElementById('progresso-porcentagem');
    if (progressoElement) {
        const progressoAtual = <?php echo $progresso_curso; ?>;
        const porcentagem = Math.min(100, (progressoAtual / 5) * 100);
        progressoElement.textContent = porcentagem + '%';
    }
}

// Atualizar estrelas quando a página carregar
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar com o número atual de estrelas
    const estrelasAtuais = <?php echo $estrelas_curso; ?>;
    atualizarEstrelas(estrelasAtuais);
    
    // Verificar se há parâmetros de atualização na URL
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('estrelas_atualizadas')) {
        const novasEstrelas = parseInt(urlParams.get('estrelas_atualizadas'));
        if (!isNaN(novasEstrelas)) {
            atualizarEstrelas(novasEstrelas);
            
            // Mostrar notificação de sucesso
            setTimeout(() => {
                window.notification.success(
                    'Progresso Atualizado!',
                    `Suas estrelas foram atualizadas para ${novasEstrelas}!`,
                    4000
                );
            }, 1000);
            
            // Limpar parâmetros da URL
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    }
});

// Função para iniciar fase
function iniciarFase(numeroFase) {
    const curso = '<?php echo $curso_escolhido; ?>';
    const fases = {
        'html': {
            1: 'questao1.html',
            2: 'fase2.html', 
            3: 'fase3.html',
            4: 'fase4.html',
            5: 'fase5.html'
        },
        'css': {
            1: 'fase1-css.html',
            2: 'fase2-css.html', 
            3: 'fase3-css.html',
            4: 'fase4-css.html',
            5: 'fase5-css.html'
        },
        'logica': {
            1: 'fase1-logica.html',
            2: 'fase2-logica.html', 
            3: 'fase3-logica.html',
            4: 'fase4-logica.html',
            5: 'fase5-logica.html'
        }
    };
    
    if (fases[curso] && fases[curso][numeroFase]) {
        window.location.href = fases[curso][numeroFase];
    } else {
        window.notification.warning(
            'Fase em Desenvolvimento',
            'Esta fase estará disponível em breve!',
            4000
        );
    }
}
    // Auto-remover mensagens após 5 segundos
    setTimeout(() => {
        const mensagens = document.querySelectorAll('.mensagem-flutuante');
        mensagens.forEach(msg => msg.remove());
    }, 5000);

    // Melhorar navegação por teclado
    document.addEventListener('DOMContentLoaded', function() {
        // Foco visível apenas para navegação por teclado
        function handleFirstTab(e) {
            if (e.keyCode === 9) {
                document.body.classList.add('user-is-tabbing');
                window.removeEventListener('keydown', handleFirstTab);
            }
        }
        
        window.addEventListener('keydown', handleFirstTab);
        
        // Prevenir que F11 trave a aplicação
        window.addEventListener('keydown', function(e) {
            if (e.key === 'F11') {
                e.preventDefault();
                // Garantir que elementos críticos permaneçam funcionais
                document.body.style.minWidth = '100%';
                document.body.style.overflowX = 'auto';
                
                // Reforçar acessibilidade em tela cheia
                const elementos = document.querySelectorAll('button, a, input');
                elementos.forEach(el => {
                    el.style.minHeight = '44px';
                    el.style.minWidth = '44px';
                });
            }
        });

        // Adicionar teclas de acesso para navegação rápida
        document.addEventListener('keydown', function(e) {
            // Alt + 1 - Ir para conteúdo principal
            if (e.altKey && e.key === '1') {
                e.preventDefault();
                document.getElementById('conteudo-principal').scrollIntoView();
            }
            // Alt + 2 - Menu lateral
            if (e.altKey && e.key === '2') {
                e.preventDefault();
                const menu = document.querySelector('.menu-lateral');
                if (menu) menu.focus();
            }
        });
    });

    // Melhorar experiência em tela cheia
    document.addEventListener('fullscreenchange', function() {
        if (document.fullscreenElement) {
            // Ajustes específicos para modo tela cheia
            document.body.style.overflow = 'auto';
        }
    });
    </script>
</body>
</html>