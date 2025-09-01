<header class="header">
    <div class="header-flex">
        <button class="hamburger" id="sidebarToggle" aria-label="Abrir menu" tabindex="0">
            <span>
                <div class="bar"></div>
                <div class="bar"></div>
                <div class="bar"></div>
            </span>
        </button>
        <div class="header-brand">
            <h1>Freydy Restaurant</h1>
        </div>
        <div class="header-user">
            <div class="user-info">
                <div class="user-name"><?php echo htmlspecialchars($usuario['nome']); ?></div>
                <div class="user-role"><?php echo ucfirst($usuario['tipo']); ?></div>
            </div>
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Sair
            </a>
        </div>
    </div>
</header>
