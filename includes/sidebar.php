<nav class="sidebar">
    <div class="sidebar-nav">
        <a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt"></i>
            Dashboard
        </a>
        
        <a href="pedidos.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'pedidos.php' ? 'active' : ''; ?>">
            <i class="fas fa-utensils"></i>
            Pedidos
        </a>
        
        <a href="mesas.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'mesas.php' ? 'active' : ''; ?>">
            <i class="fas fa-table"></i>
            Mesas
        </a>
        
        <a href="reservas.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'reservas.php' ? 'active' : ''; ?>">
            <i class="fas fa-calendar-check"></i>
            Reservas
        </a>
        
        <a href="produtos.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'produtos.php' ? 'active' : ''; ?>">
            <i class="fas fa-box"></i>
            Produtos
        </a>
        
        <a href="categorias.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'categorias.php' ? 'active' : ''; ?>">
            <i class="fas fa-tags"></i>
            Categorias
        </a>
        
        <?php if ($auth->isGerente()): ?>
        <a href="usuarios.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'usuarios.php' ? 'active' : ''; ?>">
            <i class="fas fa-users"></i>
            Usuários
        </a>
        
        <a href="relatorios.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'relatorios.php' ? 'active' : ''; ?>">
            <i class="fas fa-chart-bar"></i>
            Relatórios
        </a>
        
        <a href="configuracoes.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'configuracoes.php' ? 'active' : ''; ?>">
            <i class="fas fa-cog"></i>
            Configurações
        </a>
        
        <a href="backups.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'backups.php' ? 'active' : ''; ?>">
            <i class="fas fa-database"></i>
            Backups
        </a>
        <?php endif; ?>
    </div>
</nav>
