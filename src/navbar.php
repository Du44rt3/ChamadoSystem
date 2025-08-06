<?php
/**
 * Componente de Navbar com autenticação
 * Include este arquivo em todas as páginas que precisam da navbar
 */

// Verificar se $current_user está disponível
if (!isset($current_user)) {
    global $current_user;
}
?>
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <img src="images/logo-eluss.png" alt="ELUS Logo" class="navbar-logo mobile-compact">
            <div class="navbar-brand-text">
                <div class="brand-title">Grupo Elus | Operações e Facilities</div>
                <div class="brand-subtitle">Infraestrutura & Tecnologia</div>
            </div>
        </a>
        
        <!-- Toggle button para mobile -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <div class="navbar-nav me-auto">
                <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>" href="index.php">Todos</a>
                <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'abertos.php') ? 'active' : ''; ?>" href="abertos.php">Abertos</a>
                <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'em_andamento.php') ? 'active' : ''; ?>" href="em_andamento.php">Em Andamento</a>
                <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'fechados.php') ? 'active' : ''; ?>" href="fechados.php">Fechados</a>
                <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'add.php') ? 'active' : ''; ?>" href="add.php">Novo Chamado</a>
                <a class="nav-link text-warning" href="diagnostico_outlook.php" title="Diagnóstico Outlook">
                    <i class="fas fa-stethoscope"></i>
                </a>
                
                <?php if (isset($auth) && $auth->isAdmin()): ?>
                <!-- Links para Administradores -->
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-info" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-cog"></i> Admin
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="adminDropdown">
                        <li><a class="dropdown-item" href="user_manager.php">
                            <i class="fas fa-users me-2"></i>Gerenciar Usuários
                        </a></li>
                        <li><a class="dropdown-item" href="backup_manager.php">
                            <i class="fas fa-database me-2"></i>Backup do Sistema
                        </a></li>
                    </ul>
                </div>
                <?php endif; ?>
                
                <?php if (isset($auth) && $auth->isDeveloper()): ?>
                <!-- Link direto para Dev Area -->
                <a class="nav-link text-danger <?php echo (basename($_SERVER['PHP_SELF']) == 'dev_area.php') ? 'active' : ''; ?>" href="dev_area.php">
                    <i class="fas fa-code me-1"></i>Dev Area
                </a>
                <?php endif; ?>
            </div>
            
            <!-- Menu do usuário -->
            <div class="navbar-nav">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-light d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-circle me-2"></i>
                        <span><?php echo isset($current_user) ? htmlspecialchars($current_user['nome']) : 'Usuário'; ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><h6 class="dropdown-header">
                            <i class="fas fa-user me-2"></i><?php echo isset($current_user) ? htmlspecialchars($current_user['nome']) : 'Usuário'; ?>
                        </h6></li>
                        <li><span class="dropdown-item-text">
                            <small class="text-muted">@<?php echo isset($current_user) ? htmlspecialchars($current_user['username']) : 'usuario'; ?></small>
                        </span></li>
                        <?php if (isset($current_user) && isset($current_user['nivel_acesso'])): ?>
                        <li><span class="dropdown-item-text">
                            <small class="badge bg-<?php 
                                echo $current_user['nivel_acesso'] == 'desenvolvedor' ? 'danger' : 
                                    ($current_user['nivel_acesso'] == 'admin' ? 'info' : 'secondary'); 
                            ?>"><?php 
                                echo ucfirst($current_user['nivel_acesso']); 
                            ?></small>
                        </span></li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>Sair
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</nav>
