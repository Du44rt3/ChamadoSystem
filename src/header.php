<?php
/**
 * Componente Header Moderno - ELUS Facilities
 * Include este arquivo em todas as páginas do sistema
 */

// Verificar se $current_user está disponível
if (!isset($current_user)) {
    global $current_user;
}

// Definir título da página se não estiver definido
if (!isset($page_title)) {
    $page_title = "Sistema de Chamados";
}

// Definir subtítulo da página se não estiver definido
if (!isset($page_subtitle)) {
    $page_subtitle = "Gestão de Infraestrutura e Tecnologia";
}

// Definir página ativa para destacar no menu
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Header Principal com Branding -->
<header class="main-header">
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <!-- Brand/Logo -->
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <div class="brand-logo">
                    <img src="images/logo-eluss.png" alt="ELUS Logo" class="navbar-logo">
                </div>
                <div class="brand-info">
                    <div class="brand-title">Grupo Elus</div>
                    <div class="brand-subtitle">Operações e Facilities</div>
                    <div class="brand-department">Infraestrutura & Tecnologia</div>
                </div>
            </a>

            <!-- Toggle button para mobile -->
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Conteúdo da Navbar -->
            <div class="collapse navbar-collapse" id="navbarContent">
                <!-- Menu Principal -->
                <div class="navbar-nav me-auto">
                    <div class="nav-items-container">
                        <a class="nav-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>" href="index.php">
                            <i class="fas fa-th-large me-2"></i>
                            <span>Todos</span>
                            <div class="nav-indicator"></div>
                        </a>
                        <a class="nav-link <?php echo ($current_page == 'abertos.php') ? 'active' : ''; ?>" href="abertos.php">
                            <i class="fas fa-clock me-2"></i>
                            <span>Abertos</span>
                            <div class="nav-indicator"></div>
                        </a>
                        <a class="nav-link <?php echo ($current_page == 'em_andamento.php') ? 'active' : ''; ?>" href="em_andamento.php">
                            <i class="fas fa-spinner me-2"></i>
                            <span>Em Andamento</span>
                            <div class="nav-indicator"></div>
                        </a>
                        <a class="nav-link <?php echo ($current_page == 'fechados.php') ? 'active' : ''; ?>" href="fechados.php">
                            <i class="fas fa-check-circle me-2"></i>
                            <span>Fechados</span>
                            <div class="nav-indicator"></div>
                        </a>
                    </div>
                </div>

                <!-- Actions & User Menu -->
                <div class="navbar-nav">
                    <div class="nav-actions d-flex align-items-center">
                        <!-- Botão Novo Chamado -->
                        <a href="add.php" class="btn btn-elus-primary me-3 <?php echo ($current_page == 'add.php') ? 'active' : ''; ?>">
                            <i class="fas fa-plus me-2"></i>
                            <span class="d-none d-lg-inline">Novo Chamado</span>
                            <span class="d-lg-none">Novo</span>
                        </a>

                        <!-- Diagnóstico -->
                        <a class="nav-link nav-tool me-2" href="diagnostico_outlook.php" title="Diagnóstico Outlook" data-bs-toggle="tooltip">
                            <i class="fas fa-stethoscope"></i>
                        </a>

                        <?php if (isset($auth) && $auth->isDeveloper()): ?>
                        <!-- Menu do Desenvolvedor -->
                        <div class="nav-item dropdown me-2">
                            <a class="nav-link dropdown-toggle text-danger" href="#" id="devDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-code"></i> Dev Area
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="devDropdown">
                                <li><h6 class="dropdown-header">
                                    <i class="fas fa-tools me-2"></i>Ferramentas de Desenvolvimento
                                </h6></li>
                                <li><a class="dropdown-item" href="dev_area.php">
                                    <i class="fas fa-terminal me-2"></i>Área Principal
                                </a></li>
                                <li><a class="dropdown-item" href="debug_session.php">
                                    <i class="fas fa-bug me-2"></i>Debug Session
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><h6 class="dropdown-header">
                                    <i class="fas fa-users-cog me-2"></i>Gerenciamento do Sistema
                                </h6></li>
                                <li><a class="dropdown-item" href="user_manager.php">
                                    <i class="fas fa-users me-2"></i>Gerenciar Usuários
                                </a></li>
                                <li><a class="dropdown-item" href="register_user.php">
                                    <i class="fas fa-user-plus me-2"></i>Registrar Usuário
                                </a></li>
                                <li><a class="dropdown-item" href="backup_manager.php">
                                    <i class="fas fa-database me-2"></i>Backup do Sistema
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><h6 class="dropdown-header">
                                    <i class="fas fa-shield-alt me-2"></i>Segurança & Testes
                                </h6></li>
                                <li><a class="dropdown-item" href="../tools/security_check.php">
                                    <i class="fas fa-shield-virus me-2"></i>Verificação de Segurança
                                </a></li>
                                <li><a class="dropdown-item" href="test_connection.php">
                                    <i class="fas fa-network-wired me-2"></i>Teste de Conexão
                                </a></li>
                            </ul>
                        </div>
                        <?php endif; ?>

                        <!-- Menu do Usuário -->
                        <div class="nav-item dropdown user-dropdown">
                            <a class="nav-link dropdown-toggle user-menu" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <div class="user-avatar">
                                    <i class="fas fa-user-circle"></i>
                                </div>
                                <div class="user-info d-none d-md-block">
                                    <div class="user-name"><?php echo isset($current_user) ? htmlspecialchars($current_user['nome']) : 'Usuário'; ?></div>
                                    <div class="user-role">
                                        <?php if (isset($current_user) && isset($current_user['nivel_acesso'])): ?>
                                            <span class="badge bg-<?php 
                                                echo $current_user['nivel_acesso'] == 'desenvolvedor' ? 'danger' : 
                                                    ($current_user['nivel_acesso'] == 'admin' ? 'info' : 'secondary'); 
                                            ?>"><?php echo ucfirst($current_user['nivel_acesso']); ?></span>
                                        <?php else: ?>
                                            Facilities
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <i class="fas fa-chevron-down ms-2"></i>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end user-dropdown-menu" aria-labelledby="userDropdown">
                                <li class="dropdown-header">
                                    <div class="user-header">
                                        <div class="user-avatar-large">
                                            <i class="fas fa-user-circle"></i>
                                        </div>
                                        <div class="user-details">
                                            <div class="user-name"><?php echo isset($current_user) ? htmlspecialchars($current_user['nome']) : 'Usuário'; ?></div>
                                            <div class="user-username text-muted">@<?php echo isset($current_user) ? htmlspecialchars($current_user['username']) : 'usuario'; ?></div>
                                            <div class="user-department">
                                                <small class="text-muted">Facilities - ELUS</small>
                                                <?php if (isset($current_user) && isset($current_user['nivel_acesso'])): ?>
                                                    <span class="badge rounded-pill bg-<?php 
                                                        echo $current_user['nivel_acesso'] == 'desenvolvedor' ? 'danger' : 
                                                            ($current_user['nivel_acesso'] == 'admin' ? 'info' : 'secondary'); 
                                                    ?> ms-2"><?php echo ucfirst($current_user['nivel_acesso']); ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="index.php">
                                        <i class="fas fa-list me-3 text-primary"></i>
                                        <div>
                                            <div class="fw-medium">Todos os Chamados</div>
                                            <small class="text-muted">Visualizar lista completa</small>
                                        </div>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="abertos.php">
                                        <i class="fas fa-clock me-3 text-warning"></i>
                                        <div>
                                            <div class="fw-medium">Chamados Abertos</div>
                                            <small class="text-muted">Aguardando atendimento</small>
                                        </div>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="em_andamento.php">
                                        <i class="fas fa-cog me-3 text-info"></i>
                                        <div>
                                            <div class="fw-medium">Em Andamento</div>
                                            <small class="text-muted">Chamados sendo resolvidos</small>
                                        </div>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="fechados.php">
                                        <i class="fas fa-check-circle me-3 text-success"></i>
                                        <div>
                                            <div class="fw-medium">Fechados</div>
                                            <small class="text-muted">Chamados finalizados</small>
                                        </div>
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center text-danger" href="logout.php">
                                        <i class="fas fa-sign-out-alt me-3"></i>
                                        <div class="fw-medium">Sair do Sistema</div>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</header>

<!-- Page Header -->
<div class="page-header">
    <div class="container-fluid">
        <div class="page-header-content">
            <div class="page-info">
                <h1 class="page-title">
                    <?php echo $page_title; ?>
                </h1>
                <p class="page-subtitle"><?php echo $page_subtitle; ?></p>
            </div>
            <div class="page-actions">
                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="index.php">Início</a>
                        </li>
                        <?php if ($current_page != 'index.php'): ?>
                            <li class="breadcrumb-item active" aria-current="page">
                                <?php 
                                $page_names = [
                                    'abertos.php' => 'Chamados Abertos',
                                    'em_andamento.php' => 'Em Andamento',
                                    'fechados.php' => 'Chamados Fechados',
                                    'add.php' => 'Novo Chamado',
                                    'edit.php' => 'Editar Chamado',
                                    'view.php' => 'Visualizar Chamado'
                                ];
                                echo isset($page_names[$current_page]) ? $page_names[$current_page] : 'Página';
                                ?>
                            </li>
                        <?php endif; ?>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<style>
/* Header Styles */
.main-header {
    background: linear-gradient(135deg, var(--elus-blue) 0%, var(--elus-blue-dark) 100%);
    border-bottom: 3px solid var(--elus-yellow);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    position: sticky;
    top: 0;
    z-index: 1030;
}

.navbar-brand {
    padding: 0.5rem 0;
}

.brand-logo .navbar-logo {
    height: 60px;
    width: auto;
    max-height: 60px;
    margin-right: 1rem;
    transition: transform 0.3s ease;
}

.brand-logo .navbar-logo:hover {
    transform: scale(1.05);
}

.brand-info {
    line-height: 1.2;
}

.brand-title {
    font-size: 1.4rem;
    font-weight: 700;
    color: white;
    margin-bottom: 2px;
}

.brand-subtitle {
    font-size: 0.9rem;
    color: var(--elus-yellow);
    font-weight: 600;
    margin-bottom: 1px;
}

.brand-department {
    font-size: 0.75rem;
    color: rgba(255, 255, 255, 0.8);
    font-weight: 400;
}

/* Navigation Items */
.nav-items-container {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.nav-link {
    position: relative;
    padding: 0.75rem 1rem !important;
    color: rgba(255, 255, 255, 0.85) !important;
    text-decoration: none;
    border-radius: 8px;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    font-weight: 500;
    overflow: hidden;
}

.nav-link:hover {
    color: white !important;
    background: rgba(255, 255, 255, 0.1);
    transform: translateY(-1px);
}

.nav-link.active {
    color: var(--elus-blue-dark) !important;
    background: var(--elus-yellow);
    font-weight: 600;
}

.nav-link.active .nav-indicator {
    width: 100%;
}

.nav-indicator {
    position: absolute;
    bottom: 0;
    left: 0;
    height: 3px;
    background: var(--elus-yellow);
    width: 0;
    transition: width 0.3s ease;
}

/* Actions */
.nav-actions {
    gap: 0.5rem;
}

.btn-elus-primary {
    background: var(--elus-yellow);
    color: var(--elus-blue-dark);
    border: none;
    padding: 0.5rem 1.2rem;
    border-radius: 25px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 2px 10px rgba(255, 215, 0, 0.3);
}

.btn-elus-primary:hover {
    background: var(--elus-yellow-dark);
    color: var(--elus-blue-dark);
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(255, 215, 0, 0.4);
}

.btn-elus-primary.active {
    background: white;
    color: var(--elus-blue);
}

.nav-tool {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.1);
    color: var(--elus-yellow) !important;
    transition: all 0.3s ease;
}

.nav-tool:hover {
    background: var(--elus-yellow);
    color: var(--elus-blue-dark) !important;
    transform: scale(1.1);
}

/* User Menu */
.user-dropdown {
    margin-left: 1rem;
    padding-left: 1rem;
    border-left: 1px solid rgba(255, 255, 255, 0.2);
}

.user-menu {
    padding: 0.5rem 1rem !important;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 25px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    transition: all 0.3s ease;
}

.user-menu:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: none;
}

.user-avatar i {
    font-size: 2rem;
    color: var(--elus-yellow);
}

.user-info {
    margin-left: 0.75rem;
    text-align: left;
}

.user-name {
    font-size: 0.9rem;
    font-weight: 600;
    color: white;
    line-height: 1.2;
}

.user-role {
    font-size: 0.75rem;
    color: var(--elus-yellow);
    line-height: 1;
}

/* User Dropdown Menu */
.user-dropdown-menu {
    min-width: 280px;
    border: none;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    border-radius: 15px;
    padding: 0;
    overflow: hidden;
}

.user-header {
    display: flex;
    align-items: center;
    padding: 1.5rem;
    background: linear-gradient(135deg, var(--elus-blue) 0%, var(--elus-blue-dark) 100%);
    color: white;
}

.user-avatar-large i {
    font-size: 3rem;
    color: var(--elus-yellow);
    margin-right: 1rem;
}

.user-details .user-name {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.user-details .user-username {
    font-size: 0.85rem;
    color: var(--elus-yellow);
    margin-bottom: 0.25rem;
}

.user-details .user-department {
    font-size: 0.75rem;
    color: rgba(255, 255, 255, 0.8);
}

.dropdown-item {
    padding: 0.75rem 1.5rem;
    transition: all 0.3s ease;
    border-left: 3px solid transparent;
}

.dropdown-item:hover {
    background: var(--gray-50);
    border-left-color: var(--elus-blue);
    padding-left: 1.75rem;
}

.dropdown-item.text-danger:hover {
    background: #fef2f2;
    border-left-color: #dc2626;
}

/* Page Header */
.page-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border-bottom: 3px solid var(--elus-yellow);
    padding: 2rem 0;
    margin-bottom: 2rem;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.page-header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.page-title {
    font-size: 2.2rem;
    font-weight: 700;
    color: var(--elus-blue-dark);
    margin: 0;
    letter-spacing: -0.5px;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.page-subtitle {
    color: var(--gray-600);
    margin: 0.5rem 0 0 0;
    font-size: 1.1rem;
    font-weight: 500;
}

.breadcrumb {
    background: rgba(var(--elus-blue-rgb), 0.1);
    padding: 0.5rem 1rem;
    margin: 0;
    border-radius: 25px;
    border: 1px solid rgba(var(--elus-blue-rgb), 0.2);
}

.breadcrumb-item a {
    color: var(--elus-blue);
    text-decoration: none;
    font-weight: 500;
}

.breadcrumb-item a:hover {
    color: var(--elus-blue-dark);
}

.breadcrumb-item.active {
    color: var(--gray-600);
    font-weight: 600;
}

/* User Dropdown Melhorado */
.user-dropdown-menu {
    border: none;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    padding: 0;
    min-width: 300px;
    overflow: hidden;
}

.user-header {
    background: linear-gradient(135deg, var(--elus-blue) 0%, var(--elus-blue-dark) 100%);
    padding: 1.5rem;
    color: white;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.user-avatar-large {
    font-size: 3rem;
    color: var(--elus-yellow);
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

.user-details .user-name {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.user-details .user-username {
    font-size: 0.9rem;
    opacity: 0.8;
    margin-bottom: 0.5rem;
}

.user-details .user-department {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.user-dropdown-menu .dropdown-item {
    padding: 0.75rem 1.5rem;
    border-left: 3px solid transparent;
    transition: all 0.3s ease;
}

.user-dropdown-menu .dropdown-item:hover {
    background: rgba(var(--elus-blue-rgb), 0.05);
    border-left-color: var(--elus-blue);
    transform: translateX(5px);
}

.user-dropdown-menu .dropdown-item i {
    width: 20px;
    text-align: center;
}

.user-dropdown-menu .dropdown-item .fw-medium {
    font-weight: 600;
    color: var(--elus-blue-dark);
}

.user-dropdown-menu .dropdown-item small {
    font-size: 0.8rem;
    opacity: 0.7;
}

.user-dropdown-menu .dropdown-item.text-danger:hover {
    background: rgba(220, 38, 38, 0.05);
    border-left-color: #dc2626;
}

/* Badge melhorado */
.badge.rounded-pill {
    font-size: 0.75rem;
    font-weight: 600;
    padding: 0.4rem 0.8rem;
    letter-spacing: 0.5px;
}

/* Responsive */
@media (max-width: 991.98px) {
    .brand-title {
        font-size: 1.2rem;
    }
    
    .brand-subtitle {
        font-size: 0.8rem;
    }
    
    .brand-department {
        display: none;
    }
    
    .nav-items-container {
        flex-direction: column;
        align-items: stretch;
        gap: 0.25rem;
        margin-top: 1rem;
    }
    
    .nav-link {
        justify-content: flex-start;
    }
    
    .user-dropdown {
        margin-left: 0;
        padding-left: 0;
        border-left: none;
        border-top: 1px solid rgba(255, 255, 255, 0.2);
        padding-top: 1rem;
        margin-top: 1rem;
    }
    
    .user-dropdown-menu {
        min-width: 280px;
    }
    
    .page-header-content {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
}

@media (max-width: 767.98px) {
    .page-title {
        font-size: 1.5rem;
    }
    
    .brand-logo .navbar-logo {
        height: 50px;
    }
}
</style>

<script>
// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
