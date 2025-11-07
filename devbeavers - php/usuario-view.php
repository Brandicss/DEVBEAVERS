<?php
session_start();
require 'conexao.php';

// Verificar se é admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    $_SESSION['mensagem'] = 'Acesso negado. Área restrita para administradores.';
    header('Location: login.html');
    exit;
}

if (!isset($_GET['id'])) {
    $_SESSION['mensagem_erro'] = 'ID do usuário não especificado.';
    header('Location: crud.php');
    exit;
}

$usuario_id = mysqli_real_escape_string($conexao, $_GET['id']);
$sql = "SELECT * FROM usuarios WHERE id='$usuario_id'";
$query = mysqli_query($conexao, $sql);

if (mysqli_num_rows($query) == 0) {
    $_SESSION['mensagem_erro'] = 'Usuário não encontrado.';
    header('Location: crud.php');
    exit;
}

$usuario = mysqli_fetch_assoc($query);

// Calcular progresso total
$progresso_total = (
    ($usuario['progresso_html'] ?? 0) + 
    ($usuario['progresso_css'] ?? 0) + 
    ($usuario['progresso_logica'] ?? 0)
);
$max_progresso = 15; // 5 fases por curso × 3 cursos
$percentual_total = ($progresso_total / $max_progresso) * 100;

// Calcular estrelas totais
$estrelas_totais = (
    ($usuario['estrelas_html'] ?? 0) + 
    ($usuario['estrelas_css'] ?? 0) + 
    ($usuario['estrelas_logica'] ?? 0)
);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Visualizar Usuário - DevBeaver</title>
    <link rel="stylesheet" href="notification.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <style>
        :root {
            --primary-color: #F78008;
            --primary-dark: #e67200;
            --secondary-color: #4A90E2;
            --text-color: #333333;
            --text-light: #777777;
            --background-light: #F8F8F8;
            --white: #FFFFFF;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        body {
            background: linear-gradient(135deg, #fff6e9 0%, #ffffff 100%);
            font-family: "B612", sans-serif;
            min-height: 100vh;
        }

        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .card-header-custom {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            border-radius: 12px 12px 0 0 !important;
            padding: 20px 25px;
        }

        .user-profile-card {
            border: none;
            border-radius: 12px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .user-avatar-large {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #F78008 0%, #ffae42 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2.5rem;
            margin: 0 auto 20px;
        }

        .info-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-left: 4px solid var(--primary-color);
        }

        .info-label {
            font-weight: 600;
            color: var(--text-light);
            font-size: 0.9rem;
            margin-bottom: 5px;
        }

        .info-value {
            font-weight: 700;
            color: var(--text-color);
            font-size: 1.1rem;
        }

        .badge-admin {
            background: linear-gradient(135deg, #F78008 0%, #ffae42 100%);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .badge-user {
            background: #6c757d;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .progress-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 25px;
            border-left: 4px solid var(--primary-color);
        }

        .progress-title {
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.2rem;
        }

        .curso-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-top: 4px solid;
        }

        .curso-html { border-color: #F78008; }
        .curso-css { border-color: #264de4; }
        .curso-logica { border-color: #8e44ad; }

        .curso-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 15px;
        }

        .curso-name {
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--text-color);
        }

        .progress-bar-custom {
            border-radius: 10px;
            height: 20px;
        }

        .progress-html { background: linear-gradient(90deg, #F78008, #ffae42); }
        .progress-css { background: linear-gradient(90deg, #264de4, #2965f1); }
        .progress-logica { background: linear-gradient(90deg, #8e44ad, #9b59b6); }

        .stars-container {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-top: 10px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .stat-item {
            text-align: center;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 0.9rem;
            color: var(--text-light);
            font-weight: 600;
        }

        .btn-admin {
            background: linear-gradient(135deg, #F78008 0%, #ffae42 100%);
            border: none;
            color: white;
            padding: 10px 25px;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-weight: 600;
        }

        .btn-admin:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(247, 128, 8, 0.3);
            color: white;
        }

        .badge-curso {
            font-size: 0.8rem;
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: 600;
        }

        .badge-html { background: #F78008; color: white; }
        .badge-css { background: #264de4; color: white; }
        .badge-logica { background: #8e44ad; color: white; }
    </style>
</head>
<body>
    <div class="admin-container mt-4">
        <?php include('mensagem.php'); ?>
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-dark"><i class="bi bi-eye-fill me-2"></i>Visualizar Usuário</h2>
            <a href="crud.php" class="btn btn-admin">
                <i class="bi bi-arrow-left me-2"></i>Voltar à Lista
            </a>
        </div>

        <div class="card user-profile-card">
            <div class="card-header card-header-custom">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="bi bi-person-badge me-2"></i>Detalhes do Usuário</h4>
                    <span class="badge <?php echo $usuario['is_admin'] ? 'badge-admin' : 'badge-user'; ?>">
                        <i class="bi bi-<?php echo $usuario['is_admin'] ? 'shield-check' : 'person'; ?> me-1"></i>
                        <?php echo $usuario['is_admin'] ? 'Administrador' : 'Usuário'; ?>
                    </span>
                </div>
            </div>
            
            <div class="card-body p-4">
                <!-- Informações Básicas -->
                <div class="row mb-4">
                    <div class="col-md-4 text-center">
                        <div class="user-avatar-large">
                            <i class="bi bi-person-fill"></i>
                        </div>
                        <h4 class="mt-2"><?= htmlspecialchars($usuario['nome']); ?></h4>
                        <p class="text-muted">ID: <?= $usuario['id']; ?></p>
                        <span class="badge-curso badge-<?= $usuario['curso_escolhido'] ?? 'html' ?>">
                            Curso Atual: <?= strtoupper($usuario['curso_escolhido'] ?? 'html') ?>
                        </span>
                    </div>
                    
                    <div class="col-md-8">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="info-card">
                                    <div class="info-label">Email</div>
                                    <div class="info-value"><?= htmlspecialchars($usuario['email']); ?></div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="info-card">
                                    <div class="info-label">Data de Criação</div>
                                    <div class="info-value">
                                        <i class="bi bi-calendar-event me-2"></i>
                                        <?= date('d/m/Y H:i', strtotime($usuario['data_criacao'])); ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="info-card">
                                    <div class="info-label">Último Acesso</div>
                                    <div class="info-value">
                                        <i class="bi bi-clock-history me-2"></i>
                                        <?= $usuario['ultimo_acesso'] ? date('d/m/Y H:i', strtotime($usuario['ultimo_acesso'])) : 'Nunca acessou'; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="info-card">
                                    <div class="info-label">Status</div>
                                    <div class="info-value">
                                        <i class="bi bi-circle-fill me-2 text-success"></i>
                                        Usuário Ativo
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estatísticas Gerais -->
                <div class="stats-grid mb-4">
                    <div class="stat-item">
                        <div class="stat-number"><?= $progresso_total ?>/15</div>
                        <div class="stat-label">Fases Concluídas</div>
                    </div>
                    
                    <div class="stat-item">
                        <div class="stat-number"><?= $estrelas_totais ?></div>
                        <div class="stat-label">Estrelas Totais</div>
                    </div>
                    
                    <div class="stat-item">
                        <div class="stat-number"><?= number_format($percentual_total, 1); ?>%</div>
                        <div class="stat-label">Progresso Total</div>
                    </div>
                    
                    <div class="stat-item">
                        <div class="stat-number">3</div>
                        <div class="stat-label">Cursos Disponíveis</div>
                    </div>
                </div>

                <!-- Progresso dos Cursos -->
                <div class="progress-section">
                    <h5 class="progress-title">
                        <i class="bi bi-graph-up-arrow"></i>Progresso nos Cursos
                    </h5>
                    
                    <!-- Curso HTML -->
                    <div class="curso-card curso-html">
                        <div class="curso-header">
                            <span class="curso-name">
                                <i class="bi bi-code-slash me-2"></i>Curso HTML
                            </span>
                            <span class="badge bg-orange"><?= $usuario['progresso_html'] ?? 0 ?>/5</span>
                        </div>
                        
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="info-label">Progresso</span>
                                <span class="info-value">
                                    <?= number_format((($usuario['progresso_html'] ?? 0) / 5) * 100, 1); ?>%
                                </span>
                            </div>
                            <div class="progress progress-bar-custom" style="height: 12px;">
                                <div class="progress-bar progress-html" 
                                     style="width: <?= (($usuario['progresso_html'] ?? 0) / 5) * 100; ?>%">
                                </div>
                            </div>
                        </div>
                        
                        <div class="stars-container">
                            <span class="info-label me-2">Estrelas Conquistadas:</span>
                            <?php 
                            $estrelas_html = $usuario['estrelas_html'] ?? 0;
                            for($i = 0; $i < 5; $i++): 
                                $estrela_class = $i < $estrelas_html ? 'text-warning' : 'text-muted';
                            ?>
                                <i class="bi bi-star-fill <?= $estrela_class ?>"></i>
                            <?php endfor; ?>
                            <span class="ms-2 text-muted">(<?= $estrelas_html ?>/5)</span>
                        </div>
                    </div>

                    <!-- Curso CSS -->
                    <div class="curso-card curso-css">
                        <div class="curso-header">
                            <span class="curso-name">
                                <i class="bi bi-palette me-2"></i>Curso CSS
                            </span>
                            <span class="badge bg-primary"><?= $usuario['progresso_css'] ?? 0 ?>/5</span>
                        </div>
                        
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="info-label">Progresso</span>
                                <span class="info-value">
                                    <?= number_format((($usuario['progresso_css'] ?? 0) / 5) * 100, 1); ?>%
                                </span>
                            </div>
                            <div class="progress progress-bar-custom" style="height: 12px;">
                                <div class="progress-bar progress-css" 
                                     style="width: <?= (($usuario['progresso_css'] ?? 0) / 5) * 100; ?>%">
                                </div>
                            </div>
                        </div>
                        
                        <div class="stars-container">
                            <span class="info-label me-2">Estrelas Conquistadas:</span>
                            <?php 
                            $estrelas_css = $usuario['estrelas_css'] ?? 0;
                            for($i = 0; $i < 5; $i++): 
                                $estrela_class = $i < $estrelas_css ? 'text-warning' : 'text-muted';
                            ?>
                                <i class="bi bi-star-fill <?= $estrela_class ?>"></i>
                            <?php endfor; ?>
                            <span class="ms-2 text-muted">(<?= $estrelas_css ?>/5)</span>
                        </div>
                    </div>

                    <!-- Curso Lógica -->
                    <div class="curso-card curso-logica">
                        <div class="curso-header">
                            <span class="curso-name">
                                <i class="bi bi-cpu me-2"></i>Curso Lógica de Programação
                            </span>
                            <span class="badge bg-purple"><?= $usuario['progresso_logica'] ?? 0 ?>/5</span>
                        </div>
                        
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="info-label">Progresso</span>
                                <span class="info-value">
                                    <?= number_format((($usuario['progresso_logica'] ?? 0) / 5) * 100, 1); ?>%
                                </span>
                            </div>
                            <div class="progress progress-bar-custom" style="height: 12px;">
                                <div class="progress-bar progress-logica" 
                                     style="width: <?= (($usuario['progresso_logica'] ?? 0) / 5) * 100; ?>%">
                                </div>
                            </div>
                        </div>
                        
                        <div class="stars-container">
                            <span class="info-label me-2">Estrelas Conquistadas:</span>
                            <?php 
                            $estrelas_logica = $usuario['estrelas_logica'] ?? 0;
                            for($i = 0; $i < 5; $i++): 
                                $estrela_class = $i < $estrelas_logica ? 'text-warning' : 'text-muted';
                            ?>
                                <i class="bi bi-star-fill <?= $estrela_class ?>"></i>
                            <?php endfor; ?>
                            <span class="ms-2 text-muted">(<?= $estrelas_logica ?>/5)</span>
                        </div>
                    </div>
                </div>

                <!-- Ações -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="d-flex gap-2 justify-content-end">
                            <a href="usuario-edit.php?id=<?= $usuario['id']; ?>" class="btn btn-success">
                                <i class="bi bi-pencil-fill me-2"></i>Editar Usuário
                            </a>
                            <a href="crud.php" class="btn btn-secondary">
                                <i class="bi bi-list-ul me-2"></i>Voltar à Lista
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="notification.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>