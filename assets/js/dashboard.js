/**
 * JavaScript do Dashboard
 * Freydy Restaurant App
 */

// Função para atualizar status do pedido
function atualizarStatus(pedidoId, novoStatus) {
    if (!confirm('Tem certeza que deseja atualizar o status deste pedido?')) {
        return;
    }
    
    fetch('api/pedidos/atualizar-status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            pedido_id: pedidoId,
            status: novoStatus
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Recarrega a página para atualizar os dados
            location.reload();
        } else {
            alert('Erro ao atualizar status: ' + (data.message || 'Erro desconhecido'));
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao atualizar status do pedido');
    });
}

// Função para imprimir comanda
function imprimirComanda(pedidoId) {
    window.open(`imprimir-comanda.php?pedido_id=${pedidoId}`, '_blank');
}

// Função para visualizar detalhes do pedido
function visualizarPedido(pedidoId) {
    window.open(`pedido-detalhes.php?id=${pedidoId}`, '_blank');
}

// Auto-refresh do dashboard a cada 30 segundos
setInterval(function() {
    // Só recarrega se não houver interação do usuário
    if (!document.hidden) {
        location.reload();
    }
}, 30000);

// Notificações em tempo real (simuladas)
function mostrarNotificacao(mensagem, tipo = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${tipo}`;
    notification.innerHTML = `
        <div class="notification-content">
            <span>${mensagem}</span>
            <button onclick="this.parentElement.parentElement.remove()">&times;</button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Remove a notificação após 5 segundos
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}

// Função para filtrar dados do dashboard
function filtrarDashboard(filtro) {
    const urlParams = new URLSearchParams(window.location.search);
    urlParams.set('filtro', filtro);
    window.location.search = urlParams.toString();
}

// Função para exportar dados
function exportarDados(tipo) {
    const urlParams = new URLSearchParams(window.location.search);
    urlParams.set('export', tipo);
    window.location.href = `exportar.php?${urlParams.toString()}`;
}

// Função para alternar visibilidade da sidebar em dispositivos móveis
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    sidebar.classList.toggle('open');
}

// Adiciona listener para fechar sidebar ao clicar fora
document.addEventListener('click', function(event) {
    const sidebar = document.querySelector('.sidebar');
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    
    if (sidebar && sidebar.classList.contains('open') && 
        !sidebar.contains(event.target) && 
        !sidebarToggle.contains(event.target)) {
        sidebar.classList.remove('open');
    }
});

// Função para formatar valores monetários
function formatarMoeda(valor) {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(valor);
}

// Função para formatar datas
function formatarData(data) {
    return new Date(data).toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Função para validar formulários
function validarFormulario(formulario) {
    const campos = formulario.querySelectorAll('[required]');
    let valido = true;
    
    campos.forEach(campo => {
        if (!campo.value.trim()) {
            campo.classList.add('erro');
            valido = false;
        } else {
            campo.classList.remove('erro');
        }
    });
    
    return valido;
}

// Função para mostrar/esconder loading
function mostrarLoading() {
    const loading = document.createElement('div');
    loading.className = 'loading-overlay';
    loading.innerHTML = '<div class="loading-spinner"></div>';
    document.body.appendChild(loading);
}

function esconderLoading() {
    const loading = document.querySelector('.loading-overlay');
    if (loading) {
        loading.remove();
    }
}

// Função para fazer requisições AJAX
function fazerRequisicao(url, metodo = 'GET', dados = null) {
    return new Promise((resolve, reject) => {
        const options = {
            method: metodo,
            headers: {
                'Content-Type': 'application/json',
            }
        };
        
        if (dados && metodo !== 'GET') {
            options.body = JSON.stringify(dados);
        }
        
        fetch(url, options)
            .then(response => response.json())
            .then(data => resolve(data))
            .catch(error => reject(error));
    });
}

// Inicialização quando o DOM estiver carregado
document.addEventListener('DOMContentLoaded', function() {
    // Adiciona tooltips aos elementos
    const tooltips = document.querySelectorAll('[data-tooltip]');
    tooltips.forEach(elemento => {
        elemento.addEventListener('mouseenter', function() {
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.textContent = this.getAttribute('data-tooltip');
            document.body.appendChild(tooltip);
            
            const rect = this.getBoundingClientRect();
            tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
            tooltip.style.top = rect.top - tooltip.offsetHeight - 10 + 'px';
        });
        
        elemento.addEventListener('mouseleave', function() {
            const tooltip = document.querySelector('.tooltip');
            if (tooltip) {
                tooltip.remove();
            }
        });
    });
    
    // Adiciona confirmação para ações destrutivas
    const botoesDestrutivos = document.querySelectorAll('[data-confirm]');
    botoesDestrutivos.forEach(botao => {
        botao.addEventListener('click', function(e) {
            const mensagem = this.getAttribute('data-confirm');
            if (!confirm(mensagem)) {
                e.preventDefault();
            }
        });
    });
});
