// notification.js - Sistema de notificações moderno
class NotificationSystem {
    constructor() {
        this.container = null;
        this.init();
    }

    init() {
        // Criar container se não existir
        if (!document.getElementById('notification-container')) {
            this.container = document.createElement('div');
            this.container.id = 'notification-container';
            this.container.className = 'notification-container';
            document.body.appendChild(this.container);
        } else {
            this.container = document.getElementById('notification-container');
        }
    }

    show(options) {
        const {
            title,
            message,
            type = 'info',
            duration = 5000,
            closeable = true
        } = options;

        // Criar elemento da notificação
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        
        // Ícones para cada tipo
        const icons = {
            success: '✅',
            error: '❌',
            warning: '⚠️',
            info: 'ℹ️'
        };

        notification.innerHTML = `
            <div class="notification-icon">${icons[type]}</div>
            <div class="notification-content">
                <div class="notification-title">${title}</div>
                <div class="notification-message">${message}</div>
            </div>
            ${closeable ? '<button class="notification-close">&times;</button>' : ''}
            <div class="notification-progress"></div>
        `;

        // Adicionar ao container
        this.container.appendChild(notification);

        // Animar entrada
        setTimeout(() => {
            notification.classList.add('show');
            
            // Iniciar progress bar
            const progressBar = notification.querySelector('.notification-progress');
            if (progressBar && duration > 0) {
                setTimeout(() => {
                    progressBar.classList.add('hide');
                }, 100);
            }
        }, 100);

        // Configurar auto-remover
        if (duration > 0) {
            setTimeout(() => {
                this.hide(notification);
            }, duration);
        }

        // Configurar botão de fechar
        if (closeable) {
            const closeBtn = notification.querySelector('.notification-close');
            closeBtn.addEventListener('click', () => {
                this.hide(notification);
            });
        }

        return notification;
    }

    hide(notification) {
        notification.classList.remove('show');
        notification.classList.add('hide');
        
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 400);
    }

    // Métodos rápidos
    success(title, message, duration = 5000) {
        return this.show({ title, message, type: 'success', duration });
    }

    error(title, message, duration = 5000) {
        return this.show({ title, message, type: 'error', duration });
    }

    warning(title, message, duration = 5000) {
        return this.show({ title, message, type: 'warning', duration });
    }

    info(title, message, duration = 5000) {
        return this.show({ title, message, type: 'info', duration });
    }

    // Limpar todas as notificações
    clearAll() {
        const notifications = this.container.querySelectorAll('.notification');
        notifications.forEach(notification => {
            this.hide(notification);
        });
    }
}

// Sistema de confirmação personalizado
class ConfirmationSystem {
    constructor() {
        this.container = null;
        this.init();
    }

    init() {
        // Criar container se não existir
        if (!document.getElementById('confirmation-container')) {
            this.container = document.createElement('div');
            this.container.id = 'confirmation-container';
            this.container.className = 'confirmation-container';
            document.body.appendChild(this.container);
        } else {
            this.container = document.getElementById('confirmation-container');
        }
    }

    show(options) {
        const {
            title,
            message,
            confirmText = 'Sim',
            cancelText = 'Cancelar',
            onConfirm,
            onCancel
        } = options;

        return new Promise((resolve) => {
            // Criar overlay
            const overlay = document.createElement('div');
            overlay.className = 'confirmation-overlay';
            
            // Criar modal de confirmação
            const modal = document.createElement('div');
            modal.className = 'confirmation-modal';
            
            modal.innerHTML = `
                <div class="confirmation-header">
                    <div class="confirmation-icon">⚠️</div>
                    <h3 class="confirmation-title">${title}</h3>
                </div>
                <div class="confirmation-message">${message}</div>
                <div class="confirmation-buttons">
                    <button class="confirmation-btn confirmation-btn-cancel">${cancelText}</button>
                    <button class="confirmation-btn confirmation-btn-confirm">${confirmText}</button>
                </div>
            `;

            overlay.appendChild(modal);
            this.container.appendChild(overlay);

            // Animar entrada
            setTimeout(() => {
                overlay.classList.add('show');
                modal.classList.add('show');
            }, 100);

            // Configurar botões
            const confirmBtn = modal.querySelector('.confirmation-btn-confirm');
            const cancelBtn = modal.querySelector('.confirmation-btn-cancel');

            const closeModal = (result) => {
                overlay.classList.remove('show');
                modal.classList.remove('show');
                
                setTimeout(() => {
                    if (overlay.parentNode) {
                        overlay.parentNode.removeChild(overlay);
                    }
                    resolve(result);
                    
                    if (result && onConfirm) onConfirm();
                    if (!result && onCancel) onCancel();
                }, 300);
            };

            confirmBtn.addEventListener('click', () => closeModal(true));
            cancelBtn.addEventListener('click', () => closeModal(false));
            
            // Fechar clicando no overlay
            overlay.addEventListener('click', (e) => {
                if (e.target === overlay) {
                    closeModal(false);
                }
            });

            // Tecla ESC
            const handleEsc = (e) => {
                if (e.key === 'Escape') {
                    closeModal(false);
                    document.removeEventListener('keydown', handleEsc);
                }
            };
            document.addEventListener('keydown', handleEsc);
        });
    }

    danger(options) {
        const {
            title = 'Atenção!',
            message,
            confirmText = 'Sim, Excluir',
            cancelText = 'Cancelar',
            onConfirm,
            onCancel
        } = options;

        return new Promise((resolve) => {
            // Criar overlay
            const overlay = document.createElement('div');
            overlay.className = 'confirmation-overlay';
            
            // Criar modal de confirmação com classe danger
            const modal = document.createElement('div');
            modal.className = 'confirmation-modal danger';
            
            modal.innerHTML = `
                <div class="confirmation-header">
                    <div class="confirmation-icon">🚨</div>
                    <h3 class="confirmation-title">${title}</h3>
                </div>
                <div class="confirmation-message">${message}</div>
                <div class="confirmation-buttons">
                    <button class="confirmation-btn confirmation-btn-cancel">${cancelText}</button>
                    <button class="confirmation-btn confirmation-btn-confirm">${confirmText}</button>
                </div>
            `;

            overlay.appendChild(modal);
            this.container.appendChild(overlay);

            // Animar entrada
            setTimeout(() => {
                overlay.classList.add('show');
                modal.classList.add('show');
            }, 100);

            // Configurar botões
            const confirmBtn = modal.querySelector('.confirmation-btn-confirm');
            const cancelBtn = modal.querySelector('.confirmation-btn-cancel');

            const closeModal = (result) => {
                overlay.classList.remove('show');
                modal.classList.remove('show');
                
                setTimeout(() => {
                    if (overlay.parentNode) {
                        overlay.parentNode.removeChild(overlay);
                    }
                    resolve(result);
                    
                    if (result && onConfirm) onConfirm();
                    if (!result && onCancel) onCancel();
                }, 300);
            };

            confirmBtn.addEventListener('click', () => closeModal(true));
            cancelBtn.addEventListener('click', () => closeModal(false));
            
            // Fechar clicando no overlay
            overlay.addEventListener('click', (e) => {
                if (e.target === overlay) {
                    closeModal(false);
                }
            });

            // Tecla ESC
            const handleEsc = (e) => {
                if (e.key === 'Escape') {
                    closeModal(false);
                    document.removeEventListener('keydown', handleEsc);
                }
            };
            document.addEventListener('keydown', handleEsc);
        });
    }

    // Método rápido para exclusões
    confirmDelete(message, onConfirm, onCancel) {
        return this.danger({
            title: 'Confirmar Exclusão',
            message,
            confirmText: 'Sim, Excluir',
            cancelText: 'Cancelar',
            onConfirm,
            onCancel
        });
    }
}

// Instâncias globais
window.notification = new NotificationSystem();
window.confirmation = new ConfirmationSystem();

// Funções globais para compatibilidade
window.showNotification = function(type, title, message, duration) {
    return window.notification[type](title, message, duration);
};

window.showConfirmation = function(title, message, confirmText, cancelText) {
    return window.confirmation.show({ title, message, confirmText, cancelText });
};