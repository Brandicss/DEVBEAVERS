<?php
// Sistema de mensagens com notificações modernas
if (isset($_SESSION['mensagem'])):
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        window.notification.info(
            'Mensagem do Sistema',
            '<?= addslashes($_SESSION['mensagem']); ?>',
            5000
        );
    }, 1000);
});
</script>
<?php
    unset($_SESSION['mensagem']);
endif;

// Mensagens de sucesso específicas
if (isset($_SESSION['mensagem_sucesso'])):
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        window.notification.success(
            'Sucesso!',
            '<?= addslashes($_SESSION['mensagem_sucesso']); ?>',
            5000
        );
    }, 1000);
});
</script>
<?php
    unset($_SESSION['mensagem_sucesso']);
endif;

// Mensagens de erro específicas
if (isset($_SESSION['mensagem_erro'])):
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        window.notification.error(
            'Erro',
            '<?= addslashes($_SESSION['mensagem_erro']); ?>',
            5000
        );
    }, 1000);
});
</script>
<?php
    unset($_SESSION['mensagem_erro']);
endif;
?>