// login.js - Versão com notificações modernas
function mostrarSenha(){
    var inputPass = document.getElementById('senha')
    var btnShowPass = document.getElementById('btn-senha')
    var castor_olho_aberto = document.getElementById('olhoaberto')
    var castor_olho_fechado = document.getElementById('olhofechado')

    if(inputPass.type === 'password'){
        inputPass.setAttribute('type','text')
        btnShowPass.classList.replace('bi-eye-fill','bi-eye-slash-fill') 
        castor_olho_aberto.classList.replace('hide', 'show')
        castor_olho_fechado.classList.replace('show', 'hide')
    }
    else{
        inputPass.setAttribute('type','password')
        btnShowPass.classList.replace('bi-eye-slash-fill','bi-eye-fill') 
        castor_olho_aberto.classList.replace('show', 'hide')
        castor_olho_fechado.classList.replace('hide', 'show')
    }
}

// Validação do lado do cliente com notificações
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.login-form');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            const email = document.getElementById('email');
            const senha = document.getElementById('senha');
            
            // Validação básica
            if (!email.value || !senha.value) {
                e.preventDefault();
                window.notification.warning(
                    'Campos Obrigatórios',
                    'Por favor, preencha todos os campos.',
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
        });
    }

    // Melhorar UX - foco automático no primeiro campo
    const emailField = document.getElementById('email');
    if (emailField) {
        setTimeout(() => emailField.focus(), 500);
    }

    // Enter para submeter formulário
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            const activeElement = document.activeElement;
            if (activeElement && activeElement.type !== 'submit' && activeElement.type !== 'textarea') {
                e.preventDefault();
                const form = activeElement.closest('form');
                if (form) {
                    const submitBtn = form.querySelector('button[type="submit"]');
                    if (submitBtn) submitBtn.click();
                }
            }
        }
    });
});