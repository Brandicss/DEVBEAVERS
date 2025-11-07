<?php
session_start();
require 'conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.html');
    exit;
}

// FUNÇÃO MELHORADA para salvar progresso
function salvarProgresso(faseAtual, voltarInicio) {
    const mensagemDiv = document.getElementById('mensagem-salvamento');
    const botoes = document.querySelectorAll('.final-buttons .button');
    
    botoes.forEach(btn => btn.disabled = true);
    mensagemDiv.innerHTML = '<small style="color: #666;">🔄 Salvando progresso...</small>';
    
    // Determinar o curso atual baseado na URL de forma mais precisa
    let curso = 'html';
    const url = window.location.href;
    
    if (url.includes('fase') && url.includes('-')) {
        // Extrair curso do nome do arquivo (ex: fase1-css.html)
        const match = url.match(/fase\d+-(\w+)\.html/);
        if (match && match[1]) {
            curso = match[1];
        }
    } else if (url.includes('css')) {
        curso = 'css';
    } else if (url.includes('logica')) {
        curso = 'logica';
    }
    
    console.log('Curso detectado:', curso);
    
    fetch(`salvar-progresso.php?curso=${curso}&fase_concluida=${faseAtual}&estrelas=${estrelasGanhas}`)
        .then(response => response.json())
        .then(data => {
            console.log('Resposta do servidor:', data);
            
            if (data.success) {
                mensagemDiv.innerHTML = '<small style="color: #4CAF50;">✅ Progresso salvo com sucesso!</small>';
                
                setTimeout(() => {
                    if (voltarInicio) {
                        window.location.href = `tela-inicial.php?estrelas_atualizadas=${data.novas_estrelas}&curso=${curso}`;
                    } else {
                        const proximaFase = faseAtual + 1;
                        if (proximaFase <= 5) {
                            if (curso === 'html') {
                                window.location.href = `fase${proximaFase}.html`;
                            } else {
                                window.location.href = `fase${proximaFase}-${curso}.html`;
                            }
                        } else {
                            // Última fase concluída
                            window.location.href = `tela-inicial.php?curso_concluido=${curso}`;
                        }
                    }
                }, 1000);
            } else {
                mensagemDiv.innerHTML = `<small style="color: #f44336;">❌ Erro: ${data.message}</small>`;
                botoes.forEach(btn => btn.disabled = false);
            }
        })
        .catch(error => {
            console.error('Erro na requisição:', error);
            mensagemDiv.innerHTML = '<small style="color: #f44336;">❌ Erro de conexão</small>';
            botoes.forEach(btn => btn.disabled = false);
        });
}

// Dados da URL - AGORA COM SUPORTE A MÚLTIPLOS CURSOS
$fase_concluida = intval($_GET['fase_concluida'] ?? 0);
$estrelas = intval($_GET['estrelas'] ?? 0);
$curso = mysqli_real_escape_string($conexao, $_GET['curso'] ?? 'html'); // Novo parâmetro

$response = ['success' => false, 'message' => ''];

if ($fase_concluida > 0 && $fase_concluida <= 5) {
    $usuario_id = $_SESSION['usuario_id'];
    
    // DETERMINAR QUAL CURSO ATUALIZAR
    $coluna_progresso = 'progresso_html';
    $coluna_estrelas = 'estrelas_html';
    $sessao_progresso = 'progresso_html';
    $sessao_estrelas = 'estrelas_html';
    
    switch($curso) {
        case 'css':
            $coluna_progresso = 'progresso_css';
            $coluna_estrelas = 'estrelas_css';
            $sessao_progresso = 'progresso_css';
            $sessao_estrelas = 'estrelas_css';
            break;
        case 'logica':
            $coluna_progresso = 'progresso_logica';
            $coluna_estrelas = 'estrelas_logica';
            $sessao_progresso = 'progresso_logica';
            $sessao_estrelas = 'estrelas_logica';
            break;
        case 'html':
        default:
            // Já está configurado como padrão
            break;
    }
    
    // Buscar progresso atual DO CURSO ESPECÍFICO
    $sql = "SELECT $coluna_progresso, $coluna_estrelas FROM usuarios WHERE id = '$usuario_id'";
    $result = mysqli_query($conexao, $sql);
    
    if ($result && $usuario = mysqli_fetch_assoc($result)) {
        $progresso_atual = $usuario[$coluna_progresso] ?? 0;
        $estrelas_atuais = $usuario[$coluna_estrelas] ?? 0;
        
        // Atualizar progresso se a fase for maior que a atual
        if ($fase_concluida > $progresso_atual) {
            $progresso_atual = $fase_concluida;
        }
        
        // CORREÇÃO CRÍTICA: Somar estrelas em vez de substituir
        $novas_estrelas = $estrelas_atuais + $estrelas;
        // Garantir que não ultrapasse o máximo possível (5 fases × 3 estrelas = 15)
        $novas_estrelas = min($novas_estrelas, 15);
        
        // Atualizar banco - COLUNAS DINÂMICAS
        $sql_update = "UPDATE usuarios SET 
                      $coluna_progresso = $progresso_atual, 
                      $coluna_estrelas = $novas_estrelas,
                      ultima_atualizacao = NOW()
                      WHERE id = $usuario_id";
        
        if (mysqli_query($conexao, $sql_update)) {
            $response['success'] = true;
            $response['message'] = "Fase $fase_concluida de " . strtoupper($curso) . " concluída! Progresso salvo.";
            $response['novo_progresso'] = $progresso_atual;
            $response['novas_estrelas'] = $novas_estrelas;
            $response['curso'] = $curso;
            
            // Atualizar sessão - VARIÁVEIS DINÂMICAS
            $_SESSION[$sessao_progresso] = $progresso_atual;
            $_SESSION[$sessao_estrelas] = $novas_estrelas;
        } else {
            $response['message'] = "Erro ao salvar progresso no banco de dados: " . mysqli_error($conexao);
        }
    } else {
        $response['message'] = "Usuário não encontrado.";
    }
} else {
    $response['message'] = "Fase inválida.";
}

// Retornar JSON
header('Content-Type: application/json');
echo json_encode($response);
exit;
?>