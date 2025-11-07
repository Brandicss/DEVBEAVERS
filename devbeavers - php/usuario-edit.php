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
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Editar Usuário - DevBeaver</title>
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
            max-width: 1000px;
            margin: 0 auto;
        }

        .card-header-custom {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            border-radius: 12px 12px 0 0 !important;
            padding: 20px 25px;
        }

        .edit-user-card {
            border: none;
            border-radius: 12px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .form-control-custom {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }

        .form-control-custom:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(247, 128, 8, 0.1);
        }

        .btn-admin {
            background: linear-gradient(135deg, #F78008 0%, #ffae42 100%);
            border: none;
            color: white;
            padding: 12px 25px;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-weight: 600;
        }

        .btn-admin:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(247, 128, 8, 0.3);
            color: white;
        }

        .user-avatar-edit {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #F78008 0%, #ffae42 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            margin: 0 auto 15px;
        }

        .password-note {
            background: #f8f9fa;
            border-left: 4px solid var(--primary-color);
            padding: 15px;
            border-radius: 4px;
            font-size: 0.9rem;
            color: var(--text-light);
        }

        .form-label {
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 8px;
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-light);
            cursor: pointer;
        }

        .progress-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid var(--primary-color);
        }

        .progress-title {
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .progress-bar-custom {
            border-radius: 10px;
            height: 20px;
        }

        .progress-html { background: linear-gradient(90deg, #F78008, #ffae42); }
        .progress-css { background: linear-gradient(90deg, #264de4, #2965f1); }
        .progress-logica { background: linear-gradient(90deg, #8e44ad, #9b59b6); }

        .stars-input {
            display: flex;
            gap: 5px;
            align-items: center;
        }

        .star-icon {
            font-size: 1.5rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .star-icon:hover {
            transform: scale(1.2);
        }

        .curso-badge {
            font-size: 0.8rem;
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: 600;
        }

        .badge-html { background: linear-gradient(135deg, #F78008 0%, #ffae42 100%); color: white; }
        .badge-css { background: linear-gradient(135deg, #264de4 0%, #2965f1 100%); color: white; }
        .badge-logica { background: linear-gradient(135deg, #8e44ad 0%, #9b59b6 100%); color: white; }
    </style>
</head>
<body>
    <div class="admin-container mt-4">
        <?php include('mensagem.php'); ?>
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-dark"><i class="bi bi-pencil-fill me-2"></i>Editar Usuário</h2>
            <a href="crud.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-2"></i>Voltar à Lista
            </a>
        </div>

        <div class="card edit-user-card">
            <div class="card-header card-header-custom">
                <div class="d-flex align-items-center">
                    <i class="bi bi-person-gear me-3 fs-4"></i>
                    <h4 class="mb-0">Editar Informações do Usuário</h4>
                </div>
            </div>
            
            <div class="card-body p-4">
                <!-- Cabeçalho do Usuário -->
                <div class="text-center mb-4">
                    <div class="user-avatar-edit">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <h5><?= htmlspecialchars($usuario['nome']); ?></h5>
                    <p class="text-muted">ID: <?= $usuario['id']; ?> | Email: <?= htmlspecialchars($usuario['email']); ?></p>
                </div>

                <form action="acoes.php" method="POST">
                    <input type="hidden" name="usuario_id" value="<?= $usuario['id']; ?>">
                    
                    <div class="row">
                        <!-- Informações Básicas -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" value="<?= htmlspecialchars($usuario['email']); ?>" 
                                   class="form-control form-control-custom" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nome de Usuário</label>
                            <input type="text" name="nome" value="<?= htmlspecialchars($usuario['nome']); ?>" 
                                   class="form-control form-control-custom" required>
                        </div>
                    </div>

                    <!-- Senha -->
                    <div class="mb-4">
                        <label class="form-label">Nova Senha</label>
                        <div class="position-relative">
                            <input type="password" name="senha" id="senha" 
                                   class="form-control form-control-custom" 
                                   placeholder="Deixe em branco para manter a senha atual">
                            <button type="button" class="toggle-password" onclick="togglePassword()">
                                <i class="bi bi-eye-fill" id="toggleIcon"></i>
                            </button>
                        </div>
                        <div class="password-note mt-2">
                            <i class="bi bi-info-circle me-2"></i>
                            A senha deve ter no mínimo 6 caracteres. Deixe em branco para manter a senha atual.
                        </div>
                    </div>

                    <!-- Progresso dos Cursos -->
                    <div class="progress-section">
                        <h5 class="progress-title">
                            <i class="bi bi-graph-up"></i>Progresso nos Cursos
                        </h5>
                        
                        <div class="row">
                            <!-- Progresso HTML -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Progresso HTML</label>
                                <div class="mb-2">
                                    <div class="progress progress-bar-custom mb-2">
                                        <div class="progress-bar progress-html" 
                                             style="width: <?= (($usuario['progresso_html'] ?? 0)/5)*100 ?>%">
                                            <?= $usuario['progresso_html'] ?? 0 ?>/5
                                        </div>
                                    </div>
                                </div>
                                <div class="stars-input">
                                    <span class="me-2">Estrelas:</span>
                                    <input type="hidden" name="estrelas_html" id="estrelas_html" value="<?= $usuario['estrelas_html'] ?? 0 ?>">
                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                        <i class="bi bi-star-fill star-icon <?= $i <= ($usuario['estrelas_html'] ?? 0) ? 'text-warning' : 'text-muted' ?>" 
                                           data-value="<?= $i ?>" 
                                           data-target="estrelas_html"
                                           onclick="setStars(this)"></i>
                                    <?php endfor; ?>
                                </div>
                                <input type="number" name="progresso_html" class="form-control form-control-custom mt-2" 
                                       value="<?= $usuario['progresso_html'] ?? 0 ?>" min="0" max="5" 
                                       placeholder="Progresso (0-5)">
                            </div>

                            <!-- Progresso CSS -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Progresso CSS</label>
                                <div class="mb-2">
                                    <div class="progress progress-bar-custom mb-2">
                                        <div class="progress-bar progress-css" 
                                             style="width: <?= (($usuario['progresso_css'] ?? 0)/5)*100 ?>%">
                                            <?= $usuario['progresso_css'] ?? 0 ?>/5
                                        </div>
                                    </div>
                                </div>
                                <div class="stars-input">
                                    <span class="me-2">Estrelas:</span>
                                    <input type="hidden" name="estrelas_css" id="estrelas_css" value="<?= $usuario['estrelas_css'] ?? 0 ?>">
                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                        <i class="bi bi-star-fill star-icon <?= $i <= ($usuario['estrelas_css'] ?? 0) ? 'text-warning' : 'text-muted' ?>" 
                                           data-value="<?= $i ?>" 
                                           data-target="estrelas_css"
                                           onclick="setStars(this)"></i>
                                    <?php endfor; ?>
                                </div>
                                <input type="number" name="progresso_css" class="form-control form-control-custom mt-2" 
                                       value="<?= $usuario['progresso_css'] ?? 0 ?>" min="0" max="5" 
                                       placeholder="Progresso (0-5)">
                            </div>

                            <!-- Progresso Lógica -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Progresso Lógica</label>
                                <div class="mb-2">
                                    <div class="progress progress-bar-custom mb-2">
                                        <div class="progress-bar progress-logica" 
                                             style="width: <?= (($usuario['progresso_logica'] ?? 0)/5)*100 ?>%">
                                            <?= $usuario['progresso_logica'] ?? 0 ?>/5
                                        </div>
                                    </div>
                                </div>
                                <div class="stars-input">
                                    <span class="me-2">Estrelas:</span>
                                    <input type="hidden" name="estrelas_logica" id="estrelas_logica" value="<?= $usuario['estrelas_logica'] ?? 0 ?>">
                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                        <i class="bi bi-star-fill star-icon <?= $i <= ($usuario['estrelas_logica'] ?? 0) ? 'text-warning' : 'text-muted' ?>" 
                                           data-value="<?= $i ?>" 
                                           data-target="estrelas_logica"
                                           onclick="setStars(this)"></i>
                                    <?php endfor; ?>
                                </div>
                                <input type="number" name="progresso_logica" class="form-control form-control-custom mt-2" 
                                       value="<?= $usuario['progresso_logica'] ?? 0 ?>" min="0" max="5" 
                                       placeholder="Progresso (0-5)">
                            </div>
                        </div>
                    </div>

                    <!-- Configurações -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Curso Escolhido</label>
                            <select class="form-control form-control-custom" disabled>
                                <option><?= strtoupper($usuario['curso_escolhido'] ?? 'html'); ?></option>
                            </select>
                            <small class="text-muted">O curso só pode ser alterado pelo próprio usuário</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Último Acesso</label>
                            <input type="text" class="form-control form-control-custom" 
                                   value="<?= $usuario['ultimo_acesso'] ? date('d/m/Y H:i', strtotime($usuario['ultimo_acesso'])) : 'Nunca acessou' ?>" 
                                   disabled>
                        </div>
                    </div>

                    <!-- Permissões de Administrador -->
                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_admin" value="1" 
                                   id="is_admin" <?= $usuario['is_admin'] ? 'checked' : '' ?> 
                                   style="transform: scale(1.2);">
                            <label class="form-check-label fw-bold" for="is_admin">
                                <i class="bi bi-shield-check me-2"></i>É administrador?
                            </label>
                        </div>
                        <small class="text-muted">
                            Administradores têm acesso ao painel de controle e podem gerenciar outros usuários.
                        </small>
                    </div>

                    <!-- Ações -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="d-flex gap-2 justify-content-end">
                                <a href="crud.php" class="btn btn-secondary">
                                    <i class="bi bi-x-circle me-2"></i>Cancelar
                                </a>
                                <button type="submit" name="update_usuario" class="btn btn-admin">
                                    <i class="bi bi-check-circle me-2"></i>Atualizar Usuário
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('senha');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.replace('bi-eye-fill', 'bi-eye-slash-fill');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.replace('bi-eye-slash-fill', 'bi-eye-fill');
            }
        }

        function setStars(starElement) {
            const value = parseInt(starElement.getAttribute('data-value'));
            const target = starElement.getAttribute('data-target');
            const hiddenInput = document.getElementById(target);
            
            // Atualizar o valor do input hidden
            hiddenInput.value = value;
            
            // Atualizar a aparência das estrelas
            const stars = document.querySelectorAll(`.star-icon[data-target="${target}"]`);
            stars.forEach((star, index) => {
                if (index < value) {
                    star.classList.remove('text-muted');
                    star.classList.add('text-warning');
                } else {
                    star.classList.remove('text-warning');
                    star.classList.add('text-muted');
                }
            });
        }

        // Validação do formulário
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            
            form.addEventListener('submit', function(e) {
                const email = form.querySelector('input[name="email"]');
                const nome = form.querySelector('input[name="nome"]');
                
                // Validação básica
                if (!email.value || !nome.value) {
                    e.preventDefault();
                    window.notification.warning(
                        'Campos Obrigatórios',
                        'Por favor, preencha todos os campos obrigatórios.',
                        4000
                    );
                    return;
                }
                
                // Validação de email
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email.value)) {
                    e.preventDefault();
                    window.notification.warning(
                        'Email Inválido',
                        'Por favor, insira um email válido.',
                        4000
                    );
                    email.focus();
                    return;
                }

                // Validação do progresso (0-5)
                const progressoHtml = form.querySelector('input[name="progresso_html"]');
                const progressoCss = form.querySelector('input[name="progresso_css"]');
                const progressoLogica = form.querySelector('input[name="progresso_logica"]');
                
                const progressos = [progressoHtml, progressoCss, progressoLogica];
                for (let progresso of progressos) {
                    if (progresso.value && (progresso.value < 0 || progresso.value > 5)) {
                        e.preventDefault();
                        window.notification.warning(
                            'Progresso Inválido',
                            'O progresso deve ser um número entre 0 e 5.',
                            4000
                        );
                        progresso.focus();
                        return;
                    }
                }
            });
        });
    </script>

    <script src="notification.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>