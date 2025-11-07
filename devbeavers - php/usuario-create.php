<?php
session_start();
require 'conexao.php';

// Verificar se é admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    $_SESSION['mensagem'] = 'Acesso negado. Área restrita para administradores.';
    header('Location: login.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Adicionar Usuário - DevBeaver</title>
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
            padding: 20px;
        }

        .admin-container {
            max-width: 600px;
            margin: 0 auto;
        }

        .card-header-custom {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            border-radius: 12px 12px 0 0 !important;
            padding: 25px 30px;
        }

        .create-user-card {
            border: none;
            border-radius: 12px;
            box-shadow: var(--shadow);
            overflow: hidden;
            margin-bottom: 30px;
        }

        .form-control-custom {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 12px 15px;
            font-family: "B612", sans-serif;
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
            padding: 12px 30px;
            border-radius: 8px;
            font-family: "B612", sans-serif;
            font-weight: 700;
            transition: all 0.3s ease;
        }

        .btn-admin:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(247, 128, 8, 0.3);
            color: white;
        }

        .user-avatar-create {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #F78008 0%, #ffae42 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            margin: 0 auto 20px;
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-light);
            cursor: pointer;
        }

        .form-label {
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 8px;
            font-family: "B612", sans-serif;
        }

        .admin-badge {
            background: linear-gradient(135deg, #F78008 0%, #ffae42 100%);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .feature-list {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
            border-left: 4px solid var(--primary-color);
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
            color: var(--text-color);
        }

        .feature-item i {
            color: var(--primary-color);
        }

        .password-strength {
            height: 4px;
            background: #e9ecef;
            border-radius: 2px;
            margin-top: 5px;
            overflow: hidden;
        }

        .password-strength-bar {
            height: 100%;
            border-radius: 2px;
            transition: all 0.3s ease;
        }

        .strength-weak { background: #f44336; width: 33%; }
        .strength-medium { background: #FF9800; width: 66%; }
        .strength-strong { background: #4CAF50; width: 100%; }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include('mensagem.php'); ?>
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-dark"><i class="bi bi-person-plus-fill me-2"></i>Adicionar Novo Usuário</h2>
            <a href="crud.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-2"></i>Voltar à Lista
            </a>
        </div>

        <div class="card create-user-card">
            <div class="card-header card-header-custom">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-person-gear me-3 fs-3"></i>
                        <div>
                            <h4 class="mb-1">Criar Nova Conta</h4>
                            <small>Preencha os dados abaixo para adicionar um novo usuário</small>
                        </div>
                    </div>
                    <span class="admin-badge">
                        <i class="bi bi-shield-check me-1"></i>Admin
                    </span>
                </div>
            </div>
            
            <div class="card-body p-4">
                <!-- Avatar e Informações -->
                <div class="text-center mb-4">
                    <div class="user-avatar-create">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <h5 class="text-muted">Novo Usuário</h5>
                </div>

                <form action="acoes.php" method="POST" id="userForm">
                    <div class="row">
                        <!-- Informações Básicas -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control form-control-custom" 
                                   placeholder="exemplo@email.com" required>
                            <small class="text-muted">Será usado para login</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nome de Usuário</label>
                            <input type="text" name="nome" class="form-control form-control-custom" 
                                   placeholder="Nome completo" required>
                            <small class="text-muted">Como será exibido no sistema</small>
                        </div>
                    </div>

                    <!-- Senha -->
                    <div class="mb-4">
                        <label class="form-label">Senha</label>
                        <div class="position-relative">
                            <input type="password" name="senha" id="senha" 
                                   class="form-control form-control-custom" 
                                   placeholder="Mínimo 6 caracteres" 
                                   required minlength="6">
                            <button type="button" class="password-toggle" onclick="togglePassword()">
                                <i class="bi bi-eye-fill" id="toggleIcon"></i>
                            </button>
                        </div>
                        <div class="password-strength">
                            <div class="password-strength-bar" id="passwordStrength"></div>
                        </div>
                        <small class="text-muted" id="passwordHint">A senha deve ter pelo menos 6 caracteres</small>
                    </div>

                    <!-- Configurações de Administrador -->
                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_admin" value="1" 
                                   id="is_admin" style="transform: scale(1.2);">
                            <label class="form-check-label fw-bold" for="is_admin">
                                <i class="bi bi-shield-check me-2"></i>Tornar administrador
                            </label>
                        </div>
                        <small class="text-muted">
                            Administradores têm acesso completo ao painel de controle e podem gerenciar outros usuários.
                        </small>
                    </div>

                    <!-- Informações do Sistema -->
                    <div class="feature-list">
                        <h6 class="mb-3"><i class="bi bi-info-circle me-2"></i>O que este usuário terá acesso:</h6>
                        <div class="feature-item">
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Cursos completos de HTML, CSS e Lógica</span>
                        </div>
                        <div class="feature-item">
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Sistema de progresso e conquistas</span>
                        </div>
                        <div class="feature-item">
                            <i class="bi bi-check-circle-fill"></i>
                            <span>Quizzes interativos e desafios</span>
                        </div>
                        <div class="feature-item" id="adminFeature" style="display: none;">
                            <i class="bi bi-shield-check"></i>
                            <span class="fw-bold">Painel administrativo completo</span>
                        </div>
                    </div>

                    <!-- Ações -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="d-flex gap-2 justify-content-end">
                                <a href="crud.php" class="btn btn-secondary">
                                    <i class="bi bi-x-circle me-2"></i>Cancelar
                                </a>
                                <button type="submit" name="create_usuario" class="btn btn-admin">
                                    <i class="bi bi-person-plus me-2"></i>Criar Usuário
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Dicas Rápidas -->
        <div class="card create-user-card">
            <div class="card-body">
                <h6><i class="bi bi-lightbulb me-2"></i>Dicas Rápidas</h6>
                <ul class="list-unstyled mb-0">
                    <li class="mb-2"><small>• Use emails válidos para facilitar a recuperação de conta</small></li>
                    <li class="mb-2"><small>• Senhas fortes incluem letras, números e símbolos</small></li>
                    <li><small>• Apenas usuários de confiança devem ser tornados administradores</small></li>
                </ul>
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

        // Verificar força da senha
        document.getElementById('senha').addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('passwordStrength');
            const passwordHint = document.getElementById('passwordHint');
            
            let strength = 'weak';
            let hint = 'Senha fraca';
            
            if (password.length >= 8) {
                strength = 'medium';
                hint = 'Senha média';
            }
            
            if (password.length >= 10 && /[!@#$%^&*(),.?":{}|<>]/.test(password)) {
                strength = 'strong';
                hint = 'Senha forte';
            }
            
            strengthBar.className = 'password-strength-bar strength-' + strength;
            passwordHint.textContent = hint;
            passwordHint.className = strength === 'strong' ? 'text-success' : 'text-muted';
        });

        // Mostrar/ocultar feature de admin
        document.getElementById('is_admin').addEventListener('change', function() {
            const adminFeature = document.getElementById('adminFeature');
            adminFeature.style.display = this.checked ? 'flex' : 'none';
        });

        // Validação do formulário - CORRIGIDA
        document.getElementById('userForm').addEventListener('submit', function(e) {
            const email = this.querySelector('input[name="email"]');
            const nome = this.querySelector('input[name="nome"]');
            const senha = this.querySelector('input[name="senha"]');
            
            // Validação básica
            if (!email.value || !nome.value || !senha.value) {
                e.preventDefault();
                if (window.notification) {
                    window.notification.warning(
                        'Campos Obrigatórios',
                        'Por favor, preencha todos os campos obrigatórios.',
                        4000
                    );
                } else {
                    alert('Por favor, preencha todos os campos obrigatórios.');
                }
                return;
            }
            
            // Validação de email
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email.value)) {
                e.preventDefault();
                if (window.notification) {
                    window.notification.warning(
                        'Email Inválido',
                        'Por favor, insira um email válido.',
                        4000
                    );
                } else {
                    alert('Por favor, insira um email válido.');
                }
                email.focus();
                return;
            }

            // CORREÇÃO: Apenas aviso para senha curta, não impede envio
            if (senha.value.length < 6) {
                if (window.notification) {
                    window.notification.warning(
                        'Senha Fraca',
                        'A senha deve ter pelo menos 6 caracteres.',
                        4000
                    );
                } else {
                    alert('A senha deve ter pelo menos 6 caracteres.');
                }
                // Não usar e.preventDefault() - deixe o PHP validar
                senha.focus();
            }

            // Confirmação para criar admin - CORRIGIDA
            const isAdmin = document.getElementById('is_admin').checked;
            if (isAdmin) {
                if (window.confirmation) {
                    e.preventDefault();
                    window.confirmation.show({
                        title: 'Criar Administrador',
                        message: 'Tem certeza que deseja criar este usuário como administrador?<br><small>Administradores têm acesso completo ao sistema.</small>',
                        confirmText: 'Sim, Criar Admin',
                        cancelText: 'Cancelar',
                        onConfirm: () => {
                            document.getElementById('userForm').submit();
                        },
                        onCancel: () => {
                            if (window.notification) {
                                window.notification.info('Ação Cancelada', 'O usuário não será criado como administrador.', 3000);
                            }
                            // Marcar como não admin e enviar
                            document.getElementById('is_admin').checked = false;
                            document.getElementById('userForm').submit();
                        }
                    });
                }
                // Se não tiver sistema de confirmação, deixar enviar normalmente
            }
        });

        // Foco automático no primeiro campo
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(() => {
                document.querySelector('input[name="email"]').focus();
            }, 500);
        });
    </script>

    <script src="notification.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>