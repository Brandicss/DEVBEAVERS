<?php
session_start();
require 'conexao.php';

// Verificar se é admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    $_SESSION['mensagem'] = 'Acesso negado. Área restrita para administradores.';
    header('Location: login.html');
    exit;
}

// Buscar estatísticas
$sql_stats = "SELECT 
    COUNT(*) as total_usuarios,
    SUM(is_admin) as total_admins,
    AVG(progresso_html) as progresso_medio_html,
    AVG(progresso_css) as progresso_medio_css,
    AVG(progresso_logica) as progresso_medio_logica
    FROM usuarios";
$result_stats = mysqli_query($conexao, $sql_stats);
$stats = mysqli_fetch_assoc($result_stats);
?>
<!doctype html>
<html lang="pt-br">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Painel Admin - Usuários</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <style>
        .admin-header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 1rem 0;
            margin-bottom: 2rem;
        }
        .admin-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .admin-actions {
            display: flex;
            gap: 10px;
        }
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            text-align: center;
            border-left: 4px solid #F78008;
        }
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #F78008;
        }
        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }
        .table-responsive {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .btn-admin {
            background: linear-gradient(135deg, #F78008 0%, #ffae42 100%);
            border: none;
            color: white;
        }
        .btn-admin:hover {
            background: linear-gradient(135deg, #e67200 0%, #e69c00 100%);
            color: white;
        }
        .progress-bar {
            background: linear-gradient(90deg, #F78008, #ffae42);
        }
        .curso-badge {
            font-size: 0.7rem;
            padding: 3px 8px;
        }
        .badge-html { background: #F78008; color: white; }
        .badge-css { background: #264de4; color: white; }
        .badge-logica { background: #8e44ad; color: white; }
        .progress-sm {
            height: 20px;
        }
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        .btn-action {
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .bg-purple {
            background-color: #8e44ad !important;
        }
    </style>
  </head>
  <body>
    <div class="admin-header">
        <div class="container">
            <div class="admin-nav">
                <h2><i class="bi bi-shield-lock"></i> Painel Administrativo</h2>
                <div class="admin-actions">
                    <span class="text-light me-3">Olá, <?php echo $_SESSION['usuario_nome']; ?></span>
                    <a href="tela-inicial.php" class="btn btn-outline-light btn-sm">
                        <i class="bi bi-house"></i> Início
                    </a>
                    <a href="logout.php" class="btn btn-outline-light btn-sm">
                        <i class="bi bi-box-arrow-right"></i> Sair
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-4">
      <?php include('mensagem.php'); ?>
      
      <!-- Estatísticas -->
      <div class="stats-cards">
          <div class="stat-card">
              <div class="stat-number"><?php echo $stats['total_usuarios']; ?></div>
              <div class="stat-label">Total de Usuários</div>
          </div>
          <div class="stat-card">
              <div class="stat-number"><?php echo $stats['total_admins']; ?></div>
              <div class="stat-label">Administradores</div>
          </div>
          <div class="stat-card">
              <div class="stat-number"><?php echo number_format($stats['progresso_medio_html'], 1); ?>/5</div>
              <div class="stat-label">Progresso Médio HTML</div>
          </div>
          <div class="stat-card">
              <div class="stat-number"><?php echo number_format($stats['progresso_medio_css'], 1); ?>/5</div>
              <div class="stat-label">Progresso Médio CSS</div>
          </div>
          <div class="stat-card">
              <div class="stat-number"><?php echo number_format($stats['progresso_medio_logica'], 1); ?>/5</div>
              <div class="stat-label">Progresso Médio Lógica</div>
          </div>
      </div>

      <div class="row">
        <div class="col-md-12">
          <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
              <h4 class="mb-0">Gerenciar Usuários</h4>
              <a href="usuario-create.php" class="btn btn-admin">
                <i class="bi bi-person-plus"></i> Adicionar Usuário
              </a>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                  <thead class="table-dark">
                    <tr>
                      <th>ID</th>
                      <th>Usuário</th>
                      <th>Email</th>
                      <th>Curso</th>
                      <th>Progresso HTML</th>
                      <th>Progresso CSS</th>
                      <th>Progresso Lógica</th>
                      <th>Admin</th>
                      <th>Data</th>
                      <th>Ações</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $sql = 'SELECT * FROM usuarios ORDER BY id DESC';
                    $usuarios = mysqli_query($conexao, $sql);
                    if(mysqli_num_rows($usuarios) > 0) {
                      foreach($usuarios as $usuario){
                    ?>
                    <tr>
                      <td><?=$usuario['id']?></td>
                      <td>
                          <div class="d-flex align-items-center">
                              <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" 
                                   style="width: 32px; height: 32px; font-size: 0.8rem;">
                                  <?= strtoupper(substr($usuario['nome'], 0, 1)) ?>
                              </div>
                              <?=$usuario['nome']?>
                          </div>
                      </td>
                      <td><?=$usuario['email']?></td>
                      <td>
                          <span class="badge curso-badge badge-<?= $usuario['curso_escolhido'] ?? 'html' ?>">
                              <?= strtoupper($usuario['curso_escolhido'] ?? 'html') ?>
                          </span>
                      </td>
                      
                      <!-- Progresso HTML -->
                      <td>
                          <div class="progress progress-sm mb-1">
                              <div class="progress-bar" style="width: <?=(($usuario['progresso_html'] ?? 0)/5)*100?>%">
                                  <?=$usuario['progresso_html'] ?? 0?>/5
                              </div>
                          </div>
                          <small class="text-muted">
                              <?php 
                              $estrelas_html = $usuario['estrelas_html'] ?? 0;
                              for($i = 0; $i < 5; $i++): 
                                  $estrela_class = $i < $estrelas_html ? 'text-warning' : 'text-muted';
                              ?>
                                  <i class="bi bi-star-fill <?=$estrela_class?>" style="font-size: 0.7rem;"></i>
                              <?php endfor; ?>
                          </small>
                      </td>
                      
                      <!-- Progresso CSS -->
                      <td>
                          <div class="progress progress-sm mb-1">
                              <div class="progress-bar bg-primary" style="width: <?=(($usuario['progresso_css'] ?? 0)/5)*100?>%">
                                  <?=$usuario['progresso_css'] ?? 0?>/5
                              </div>
                          </div>
                          <small class="text-muted">
                              <?php 
                              $estrelas_css = $usuario['estrelas_css'] ?? 0;
                              for($i = 0; $i < 5; $i++): 
                                  $estrela_class = $i < $estrelas_css ? 'text-warning' : 'text-muted';
                              ?>
                                  <i class="bi bi-star-fill <?=$estrela_class?>" style="font-size: 0.7rem;"></i>
                              <?php endfor; ?>
                          </small>
                      </td>
                      
                      <!-- Progresso Lógica -->
                      <td>
                          <div class="progress progress-sm mb-1">
                              <div class="progress-bar bg-purple" style="width: <?=(($usuario['progresso_logica'] ?? 0)/5)*100?>%">
                                  <?=$usuario['progresso_logica'] ?? 0?>/5
                              </div>
                          </div>
                          <small class="text-muted">
                              <?php 
                              $estrelas_logica = $usuario['estrelas_logica'] ?? 0;
                              for($i = 0; $i < 5; $i++): 
                                  $estrela_class = $i < $estrelas_logica ? 'text-warning' : 'text-muted';
                              ?>
                                  <i class="bi bi-star-fill <?=$estrela_class?>" style="font-size: 0.7rem;"></i>
                              <?php endfor; ?>
                          </small>
                      </td>
                      
                      <td>
                        <?php if($usuario['is_admin']): ?>
                          <span class="badge bg-success">Sim</span>
                        <?php else: ?>
                          <span class="badge bg-secondary">Não</span>
                        <?php endif; ?>
                      </td>
                      <td>
                          <small><?= date('d/m/Y', strtotime($usuario['data_criacao'])) ?></small>
                      </td>
                      <td>
                        <div class="action-buttons">
                            <a href="usuario-view.php?id=<?=$usuario['id']?>" class="btn btn-secondary btn-action btn-sm" title="Visualizar">
                              <i class="bi bi-eye-fill"></i>
                            </a>
                            <a href="usuario-edit.php?id=<?=$usuario['id']?>" class="btn btn-success btn-action btn-sm" title="Editar">
                              <i class="bi bi-pencil-fill"></i>
                            </a>
                            <form action="acoes.php" method="POST" class="d-inline" id="form-delete-<?=$usuario['id']?>">
                              <button type="button" 
                                      onclick="confirmarExclusao(<?=$usuario['id']?>, '<?=addslashes($usuario['nome'])?>')" 
                                      class="btn btn-danger btn-action btn-sm" 
                                      title="Excluir">
                                <i class="bi bi-trash3-fill"></i>
                              </button>
                              <input type="hidden" name="delete_usuario" value="<?=$usuario['id']?>">
                            </form>
                        </div>
                      </td>
                    </tr>
                    <?php
                      }
                    } else {
                      echo '<tr><td colspan="10" class="text-center py-4">Nenhum usuário encontrado</td></tr>';
                    }
                    ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Sistema de Notificações -->
    <link rel="stylesheet" href="notification.css">
    <script src="notification.js"></script>

    <script>
    // Função para confirmar exclusão
    async function confirmarExclusao(usuarioId, usuarioNome) {
        const resultado = await window.confirmation.danger({
            title: '🚨 Confirmar Exclusão',
            message: `Tem certeza que deseja excluir o usuário <strong>"${usuarioNome}"</strong>?<br><br>
                     <small style="color: #666;">Esta ação não pode ser desfeita.</small>`,
            confirmText: 'Sim, Excluir',
            cancelText: 'Cancelar'
        });
        
        if (resultado) {
            document.getElementById(`form-delete-${usuarioId}`).submit();
        }
    }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>