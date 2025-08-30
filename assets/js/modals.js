/**
 * Sistema de Modais Reutilizável
 * Freydy Restaurant App
 */

class ModalSystem {
    constructor() {
        this.activeModal = null;
        this.init();
    }
    
    init() {
        // Criar container de modais se não existir
        if (!document.getElementById('modalContainer')) {
            const container = document.createElement('div');
            container.id = 'modalContainer';
            document.body.appendChild(container);
        }
    }
    
    /**
     * Abre um modal
     */
    open(modalId, title, content, options = {}) {
        const modal = this.createModal(modalId, title, content, options);
        document.getElementById('modalContainer').appendChild(modal);
        
        // Animar entrada
        setTimeout(() => {
            modal.classList.add('show');
        }, 10);
        
        this.activeModal = modal;
        
        // Fechar com ESC
        document.addEventListener('keydown', this.handleEscape.bind(this));
        
        return modal;
    }
    
    /**
     * Fecha o modal ativo
     */
    close() {
        if (this.activeModal) {
            this.activeModal.classList.remove('show');
            setTimeout(() => {
                if (this.activeModal.parentNode) {
                    this.activeModal.parentNode.removeChild(this.activeModal);
                }
            }, 300);
            this.activeModal = null;
        }
        
        document.removeEventListener('keydown', this.handleEscape.bind(this));
    }
    
    /**
     * Cria estrutura do modal
     */
    createModal(id, title, content, options) {
        const modal = document.createElement('div');
        modal.className = 'modal-overlay';
        modal.id = id;
        
        const modalContent = document.createElement('div');
        modalContent.className = 'modal-content';
        modalContent.style.maxWidth = options.maxWidth || '600px';
        
        // Header
        const header = document.createElement('div');
        header.className = 'modal-header';
        header.innerHTML = `
            <h3>${title}</h3>
            <button type="button" class="modal-close" onclick="modalSystem.close()">
                <i class="fas fa-times"></i>
            </button>
        `;
        
        // Body
        const body = document.createElement('div');
        body.className = 'modal-body';
        body.innerHTML = content;
        
        // Footer (se houver botões)
        let footer = '';
        if (options.buttons) {
            footer = `
                <div class="modal-footer">
                    ${options.buttons.map(btn => `
                        <button type="button" class="btn ${btn.class || 'btn-secondary'}" 
                                onclick="${btn.onclick}">
                            ${btn.icon ? `<i class="fas fa-${btn.icon}"></i> ` : ''}${btn.text}
                        </button>
                    `).join('')}
                </div>
            `;
        }
        
        modalContent.innerHTML = `
            ${header.outerHTML}
            ${body.outerHTML}
            ${footer}
        `;
        
        modal.appendChild(modalContent);
        
        // Fechar ao clicar fora
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                this.close();
            }
        });
        
        return modal;
    }
    
    /**
     * Manipula tecla ESC
     */
    handleEscape(e) {
        if (e.key === 'Escape') {
            this.close();
        }
    }
    
    /**
     * Abre modal de formulário
     */
    openForm(modalId, title, formContent, submitCallback, options = {}) {
        const buttons = [
            {
                text: 'Cancelar',
                class: 'btn-secondary',
                onclick: 'modalSystem.close()'
            },
            {
                text: options.submitText || 'Salvar',
                class: 'btn-primary',
                icon: 'save',
                onclick: submitCallback
            }
        ];
        
        return this.open(modalId, title, formContent, { ...options, buttons });
    }
    
    /**
     * Abre modal de confirmação
     */
    confirm(title, message, onConfirm, onCancel) {
        const content = `
            <div class="confirm-dialog">
                <i class="fas fa-question-circle"></i>
                <p>${message}</p>
            </div>
        `;
        
        const buttons = [
            {
                text: 'Cancelar',
                class: 'btn-secondary',
                onclick: () => {
                    modalSystem.close();
                    if (onCancel) onCancel();
                }
            },
            {
                text: 'Confirmar',
                class: 'btn-danger',
                icon: 'check',
                onclick: () => {
                    modalSystem.close();
                    if (onConfirm) onConfirm();
                }
            }
        ];
        
        return this.open('confirmModal', title, content, { buttons });
    }
}

// Instância global
const modalSystem = new ModalSystem();
