<?php
/**
 * Header Limpo e Organizado - ELUS Facilities
 * Sistema de Chamados com Dashboard Analytics
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

// Verificar autenticação e nível de acesso (só inicia sessão se não estiver ativa)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$isLoggedIn = isset($_SESSION['user_id']);
$userLevel = $_SESSION['user_nivel_acesso'] ?? 'user';
$hasAnalyticsAccess = in_array($userLevel, ['admin', 'desenvolvedor']);
$isDeveloper = ($userLevel === 'desenvolvedor');
?>

<!-- Header Principal Limpo -->
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
                </div>
            </a>

            <!-- Toggle button para mobile -->
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Conteúdo da Navbar Limpo -->
            <div class="collapse navbar-collapse" id="navbarContent">
                <!-- Menu Principal em Dropdown -->
                <div class="navbar-nav me-auto">
                    <div class="nav-item dropdown main-menu-dropdown">
                        <a class="nav-link dropdown-toggle main-menu-trigger" href="#" id="mainMenuDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-th-large me-2"></i>
                            <span>Chamados</span>
                            <span class="current-page-indicator" id="currentPageName">
                                <?php 
                                $page_names = [
                                    'index.php' => 'Todos',
                                    'abertos.php' => 'Abertos',
                                    'em_andamento.php' => 'Em Andamento',
                                    'fechados.php' => 'Fechados',
                                    'add.php' => 'Novo',
                                    'edit.php' => 'Editando',
                                    'view.php' => 'Visualizando',
                                    'dashboard.php' => 'Dashboard'
                                ];
                                echo ' - ' . (isset($page_names[$current_page]) ? $page_names[$current_page] : 'Sistema');
                                ?>
                            </span>
                        </a>
                        <div class="dropdown-menu main-menu-dropdown-content" aria-labelledby="mainMenuDropdown">
                            <div class="menu-section">
                                <h6 class="dropdown-header">
                                    <i class="fas fa-list me-2"></i>
                                    Visualizações de Chamados
                                </h6>
                                <a class="dropdown-item <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>" href="index.php">
                                    <i class="fas fa-th-large me-3 text-primary"></i>
                                    <div class="menu-item-content">
                                        <div class="menu-item-title">Todos os Chamados</div>
                                        <div class="menu-item-desc">Visualização completa do sistema</div>
                                    </div>
                                    <div class="menu-item-count" id="menu-count-todos">--</div>
                                </a>
                                <a class="dropdown-item <?php echo ($current_page == 'abertos.php') ? 'active' : ''; ?>" href="abertos.php">
                                    <i class="fas fa-clock me-3 text-warning"></i>
                                    <div class="menu-item-content">
                                        <div class="menu-item-title">Chamados Abertos</div>
                                        <div class="menu-item-desc">Aguardando atendimento</div>
                                    </div>
                                    <div class="menu-item-count text-warning" id="menu-count-abertos">--</div>
                                </a>
                                <a class="dropdown-item <?php echo ($current_page == 'em_andamento.php') ? 'active' : ''; ?>" href="em_andamento.php">
                                    <i class="fas fa-cog me-3 text-info"></i>
                                    <div class="menu-item-content">
                                        <div class="menu-item-title">Em Andamento</div>
                                        <div class="menu-item-desc">Sendo processados</div>
                                    </div>
                                    <div class="menu-item-count text-info" id="menu-count-andamento">--</div>
                                </a>
                                <a class="dropdown-item <?php echo ($current_page == 'fechados.php') ? 'active' : ''; ?>" href="fechados.php">
                                    <i class="fas fa-check-circle me-3 text-success"></i>
                                    <div class="menu-item-content">
                                        <div class="menu-item-title">Chamados Fechados</div>
                                        <div class="menu-item-desc">Finalizados com sucesso</div>
                                    </div>
                                    <div class="menu-item-count text-success" id="menu-count-fechados">--</div>
                                </a>
                            </div>
                            
                            <hr class="dropdown-divider">
                            
                            <div class="menu-section">
                                <h6 class="dropdown-header">
                                    <i class="fas fa-plus me-2"></i>
                                    Ações Rápidas
                                </h6>
                                <a class="dropdown-item <?php echo ($current_page == 'add.php') ? 'active' : ''; ?>" href="add.php">
                                    <i class="fas fa-plus-circle me-3 text-primary"></i>
                                    <div class="menu-item-content">
                                        <div class="menu-item-title">Novo Chamado</div>
                                        <div class="menu-item-desc">Registrar novo atendimento</div>
                                    </div>
                                </a>
                            </div>
                            
                            <?php if ($hasAnalyticsAccess): ?>
                            <hr class="dropdown-divider">
                            
                            <div class="menu-section">
                                <h6 class="dropdown-header">
                                    <i class="fas fa-chart-line me-2"></i>
                                    Analytics
                                </h6>
                                <a class="dropdown-item <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>" href="dashboard.php">
                                    <i class="fas fa-chart-pie me-3 text-purple"></i>
                                    <div class="menu-item-content">
                                        <div class="menu-item-title">Dashboard Completo</div>
                                        <div class="menu-item-desc">Métricas e relatórios avançados</div>
                                    </div>
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="navbar-nav">
                    <div class="nav-actions d-flex align-items-center">

                        <!-- Dev Tools -->
                        <div class="quick-tools me-3">
                            <?php if ($hasAnalyticsAccess): ?>
                            <a class="tool-btn" href="dashboard.php" title="Dashboard Analytics" data-bs-toggle="tooltip">
                                <i class="fas fa-chart-pie"></i>
                            </a>
                            <?php endif; ?>
                            
                            <?php if ($isDeveloper): ?>
                            <a class="tool-btn dev-tool" href="dev_area.php" title="Dev Area" data-bs-toggle="tooltip">
                                <i class="fas fa-terminal"></i>
                                <div class="dev-pulse"></div>
                            </a>
                            <?php endif; ?>
                        </div>

                        <!-- Menu do Usuário Limpo -->
                        <div class="nav-item dropdown user-dropdown-clean">
                            <a class="nav-link user-menu-clean" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <div class="user-avatar">
                                    <i class="fas fa-user-circle"></i>
                                </div>
                                <div class="user-name d-none d-md-block">
                                    <?php echo isset($_SESSION['user_nome']) ? htmlspecialchars($_SESSION['user_nome']) : 'Usuário'; ?>
                                </div>
                                <i class="fas fa-chevron-down ms-1"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end user-dropdown-clean-menu" aria-labelledby="userDropdown">
                                <div class="user-info-header">
                                    <div class="user-avatar-large">
                                        <i class="fas fa-user-circle"></i>
                                    </div>
                                    <div class="user-details">
                                        <div class="user-full-name"><?php echo isset($_SESSION['user_nome']) ? htmlspecialchars($_SESSION['user_nome']) : 'Usuário'; ?></div>
                                        <div class="user-role">
                                            <span class="badge bg-<?php 
                                                echo $userLevel == 'desenvolvedor' ? 'danger' : 
                                                    ($userLevel == 'admin' ? 'info' : 'secondary'); 
                                            ?>"><?php echo ucfirst($userLevel); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <hr class="dropdown-divider">
                                <a class="dropdown-item" href="logout.php">
                                    <i class="fas fa-sign-out-alt me-2 text-danger"></i>
                                    Sair do Sistema
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</header>

<!-- Page Header Simples -->
<div class="page-header-clean">
    <div class="container-fluid">
        <div class="page-header-content">
            <div class="page-info">
                <h1 class="page-title"><?php echo $page_title; ?></h1>
                <p class="page-subtitle"><?php echo $page_subtitle; ?></p>
            </div>
            <div class="page-actions">
                <!-- Breadcrumb Simples -->
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="index.php">Início</a>
                        </li>
                        <?php if ($current_page != 'index.php'): ?>
                            <li class="breadcrumb-item active" aria-current="page">
                                <?php echo isset($page_names[$current_page]) ? $page_names[$current_page] : 'Página'; ?>
                            </li>
                        <?php endif; ?>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<style>
/* Header Prático e Organizado */
.main-header {
    background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
    border-bottom: 2px solid #fbbf24;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    position: sticky;
    top: 0;
    z-index: 1030;
}

.navbar-brand {
    padding: 0.5rem 0;
}

.brand-logo .navbar-logo {
    height: 45px;
    width: auto;
    margin-right: 1rem;
    transition: transform 0.2s ease;
}

.brand-logo .navbar-logo:hover {
    transform: scale(1.02);
}

.brand-info {
    line-height: 1.2;
}

.brand-title {
    font-size: 1.2rem;
    font-weight: 600;
    color: white;
    margin-bottom: 2px;
}

.brand-subtitle {
    font-size: 0.8rem;
    color: #fbbf24;
    font-weight: 500;
}

/* Menu Principal Simples */
.main-menu-dropdown {
    margin-right: 2rem;
}

.main-menu-trigger {
    padding: 0.6rem 1.2rem !important;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 20px;
    color: white !important;
    font-weight: 600;
    transition: all 0.2s ease;
    min-width: 180px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.main-menu-trigger:hover {
    background: rgba(255, 255, 255, 0.15);
    color: white !important;
    transform: translateY(-1px);
}

.current-page-indicator {
    color: #fbbf24;
    font-weight: 500;
    font-size: 0.85rem;
}

/* Dropdown Simples */
.main-menu-dropdown-content {
    min-width: 300px;
    max-height: 400px; /* Altura máxima do dropdown */
    border: none;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
    padding: 0;
    overflow-y: auto; /* Scroll interno */
    overflow-x: hidden;
    margin-top: 8px;
    position: fixed; /* Fixar posição para não rolar com a página */
    z-index: 1050; /* Z-index alto para ficar sobre outros elementos */
}

/* Customizar scrollbar do dropdown */
.main-menu-dropdown-content::-webkit-scrollbar {
    width: 6px;
}

.main-menu-dropdown-content::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.main-menu-dropdown-content::-webkit-scrollbar-thumb {
    background: #1e40af;
    border-radius: 3px;
}

.main-menu-dropdown-content::-webkit-scrollbar-thumb:hover {
    background: #3b82f6;
}

.menu-section {
    padding: 0.5rem 0;
}

.menu-section:not(:last-child) {
    border-bottom: 1px solid #e9ecef;
}

.dropdown-header {
    background: #f8f9fa;
    color: #1e40af;
    font-weight: 600;
    font-size: 0.8rem;
    padding: 0.5rem 1rem;
    margin: 0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.dropdown-item {
    padding: 0.8rem 1rem;
    transition: all 0.2s ease;
    border-left: 3px solid transparent;
    display: flex;
    align-items: center;
    gap: 0.8rem;
}

.dropdown-item:hover {
    background: #f8f9fa;
    border-left-color: #1e40af;
}

.dropdown-item.active {
    background: rgba(30, 64, 175, 0.1);
    border-left-color: #fbbf24;
    color: #1e40af;
    font-weight: 600;
}

.menu-item-content {
    flex: 1;
}

.menu-item-title {
    font-weight: 600;
    color: #1e40af;
    font-size: 0.9rem;
    margin-bottom: 2px;
}

.menu-item-desc {
    font-size: 0.75rem;
    color: #6b7280;
    line-height: 1.2;
}

.menu-item-count {
    font-weight: 700;
    font-size: 1rem;
    color: #1e40af;
    min-width: 25px;
    text-align: center;
    background: rgba(30, 64, 175, 0.1);
    padding: 0.2rem 0.5rem;
    border-radius: 12px;
}

/* Ferramentas Simples */
.quick-tools {
    display: flex;
    gap: 0.5rem;
}

.tool-btn {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.1);
    color: #fbbf24 !important;
    text-decoration: none;
    transition: all 0.2s ease;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.tool-btn:hover {
    background: #fbbf24;
    color: #1e40af !important;
    transform: scale(1.05);
}

.dev-tool {
    background: rgba(0, 255, 65, 0.1) !important;
    border-color: rgba(0, 255, 65, 0.3) !important;
    color: #00ff41 !important;
}

.dev-tool:hover {
    background: #00ff41 !important;
    color: #1e40af !important;
}

.dev-pulse {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 100%;
    height: 100%;
    background: rgba(0, 255, 65, 0.2);
    border-radius: 50%;
    transform: translate(-50%, -50%);
    animation: devPulse 2s infinite ease-in-out;
    z-index: -1; /* Colocar atrás do botão para não interferir */
    pointer-events: none; /* Não interceptar cliques */
}

@keyframes devPulse {
    0%, 100% { 
        transform: translate(-50%, -50%) scale(0.8);
        opacity: 0.7;
    }
    50% { 
        transform: translate(-50%, -50%) scale(1.1);
        opacity: 0.3;
    }
}

/* Menu do Usuário Simples */
.user-dropdown-clean {
    margin-left: 1rem;
    padding-left: 1rem;
    border-left: 1px solid rgba(255, 255, 255, 0.2);
}

.user-menu-clean {
    padding: 0.5rem 1rem !important;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 20px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    transition: all 0.2s ease;
    color: white !important;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.user-menu-clean:hover {
    background: rgba(255, 255, 255, 0.15);
    color: white !important;
}

.user-avatar i {
    font-size: 1.5rem;
    color: #fbbf24;
}

.user-name {
    font-size: 0.85rem;
    font-weight: 600;
    color: white;
}

.user-dropdown-clean-menu {
    min-width: 220px;
    max-height: 300px; /* Altura máxima */
    border: none;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
    border-radius: 10px;
    padding: 0;
    overflow-y: auto; /* Scroll interno */
    overflow-x: hidden;
    position: fixed; /* Fixar posição */
    z-index: 1050; /* Z-index alto */
}

/* Scrollbar para user dropdown */
.user-dropdown-clean-menu::-webkit-scrollbar {
    width: 6px;
}

.user-dropdown-clean-menu::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.user-dropdown-clean-menu::-webkit-scrollbar-thumb {
    background: #1e40af;
    border-radius: 3px;
}

.user-dropdown-clean-menu::-webkit-scrollbar-thumb:hover {
    background: #3b82f6;
}

.user-info-header {
    display: flex;
    align-items: center;
    padding: 1rem;
    background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
    color: white;
}

.user-avatar-large i {
    font-size: 2rem;
    color: #fbbf24;
    margin-right: 0.8rem;
}

.user-full-name {
    font-size: 0.9rem;
    font-weight: 600;
    margin-bottom: 0.2rem;
}

.user-role {
    font-size: 0.75rem;
}

/* Page Header Simples */
.page-header-clean {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border-bottom: 2px solid #fbbf24;
    padding: 1rem 0;
    margin-bottom: 1rem;
    box-shadow: 0 1px 5px rgba(0, 0, 0, 0.05);
}

.page-header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.page-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #1e40af;
    margin: 0;
}

.page-subtitle {
    color: #6b7280;
    margin: 0.2rem 0 0 0;
    font-size: 0.9rem;
    font-weight: 500;
}

.breadcrumb {
    background: rgba(30, 64, 175, 0.08);
    padding: 0.4rem 0.8rem;
    margin: 0;
    border-radius: 15px;
    border: 1px solid rgba(30, 64, 175, 0.1);
}

.breadcrumb-item a {
    color: #1e40af;
    text-decoration: none;
    font-weight: 500;
    font-size: 0.8rem;
}

.breadcrumb-item a:hover {
    color: #3b82f6;
}

.breadcrumb-item.active {
    color: #6b7280;
    font-weight: 600;
    font-size: 0.8rem;
}

/* Responsive Prático */
@media (max-width: 991.98px) {
    .brand-title { font-size: 1.1rem; }
    .brand-subtitle { font-size: 0.75rem; }
    
    .main-menu-trigger {
        min-width: 150px;
        padding: 0.5rem 1rem !important;
    }
    
    .current-page-indicator {
        font-size: 0.8rem;
    }
    
    .main-menu-dropdown-content { min-width: 280px; }
    .analytics-compact-trigger { min-width: 70px; padding: 0.5rem 0.8rem; }
    .analytics-compact-dropdown { min-width: 240px; margin-left: -80px; }
    .quick-metrics { grid-template-columns: 1fr; gap: 0.5rem; }
}

@media (max-width: 767.98px) {
    .page-title { font-size: 1.3rem; }
    .brand-logo .navbar-logo { height: 40px; }
    .main-menu-trigger { min-width: 130px; }
    .current-page-indicator { display: none; }
    .user-name { display: none; }
    .analytics-compact-trigger { min-width: auto; padding: 0.5rem; }
    .analytics-icon { font-size: 1rem; }
    .metric-value { font-size: 0.9rem; }
    .tool-btn { width: 32px; height: 32px; }
}
</style>

<script>
// Initialize tooltips and analytics
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Load analytics data if widget exists
    <?php if ($hasAnalyticsAccess): ?>
    loadAnalyticsData();
    setInterval(loadAnalyticsData, 300000); // Refresh every 5 minutes
    <?php endif; ?>
    
    // Load menu counts
    loadMenuCounts();
    setInterval(loadMenuCounts, 300000); // Refresh every 5 minutes
    
    // Configurar posicionamento dos dropdowns
    setupDropdownPositioning();
    
    // Debug: verificar se dropdowns foram encontrados
    console.log('Dropdowns setup:', {
        mainTrigger: document.querySelector('#mainMenuDropdown'),
        analyticsTrigger: document.querySelector('#analyticsDropdownTrigger'),
        userTrigger: document.querySelector('#userDropdown')
    });
});

// Função para configurar posicionamento dos dropdowns
function setupDropdownPositioning() {
    // Configurar todos os dropdowns Bootstrap
    const dropdowns = [
        {
            trigger: '#mainMenuDropdown',
            dropdown: '.main-menu-dropdown-content'
        },
        {
            trigger: '#analyticsDropdownTrigger', 
            dropdown: '.analytics-compact-dropdown'
        },
        {
            trigger: '#userDropdown',
            dropdown: '.user-dropdown-clean-menu'
        }
    ];
    
    dropdowns.forEach(config => {
        const triggerEl = document.querySelector(config.trigger);
        const dropdownEl = document.querySelector(config.dropdown);
        
        if (triggerEl && dropdownEl) {
            // Usar evento Bootstrap para posicionamento correto
            triggerEl.addEventListener('shown.bs.dropdown', function() {
                const rect = triggerEl.getBoundingClientRect();
                const dropdown = this.nextElementSibling || document.querySelector(config.dropdown);
                
                if (dropdown) {
                    // Remover posicionamento fixo se já aplicado
                    dropdown.style.position = 'absolute';
                    dropdown.style.left = '';
                    dropdown.style.top = '';
                    
                    // Aplicar posicionamento correto após o Bootstrap calcular
                    setTimeout(() => {
                        const newRect = triggerEl.getBoundingClientRect();
                        dropdown.style.position = 'fixed';
                        dropdown.style.zIndex = '1050';
                        
                        // Posicionamento específico para cada dropdown
                        if (config.trigger === '#userDropdown') {
                            dropdown.style.left = (newRect.right - dropdown.offsetWidth) + 'px';
                        } else if (config.trigger === '#analyticsDropdownTrigger') {
                            dropdown.style.left = (newRect.left + (newRect.width / 2) - (dropdown.offsetWidth / 2)) + 'px';
                        } else {
                            dropdown.style.left = newRect.left + 'px';
                        }
                        
                        dropdown.style.top = (newRect.bottom + 5) + 'px';
                    }, 10);
                }
            });
            
            // Reposicionar no redimensionamento
            window.addEventListener('resize', function() {
                if (triggerEl.getAttribute('aria-expanded') === 'true') {
                    triggerEl.click();
                    setTimeout(() => triggerEl.click(), 100);
                }
            });
        }
    });
}

<?php if ($hasAnalyticsAccess): ?>
async function loadAnalyticsData() {
    console.log('Iniciando carregamento de analytics...');
    
    // Primeiro, tenta o fallback direto para garantir que algo apareça
    loadAnalyticsDataFallback();
    
    // Depois tenta a API para dados em tempo real
    try {
        const apiUrl = window.location.origin + '/chamados_system0/public/api/analytics.php?type=header';
        console.log('Tentando carregar de:', apiUrl);
        
        const response = await fetch(apiUrl);
        
        if (response.ok) {
            const result = await response.json();
            console.log('Dados da API recebidos:', result);
            
            if (result.success && result.data) {
                // Analytics widget removido - apenas log
                console.log('Métricas disponíveis:', result.data);
            }
        }
    } catch (error) {
        console.log('API não disponível:', error.message);
    }
}

// Fallback removido - analytics widget não mais necessário
function loadAnalyticsDataFallback() {
    console.log('Analytics widget desabilitado');
}

<?php endif; ?>

// Load menu counts
async function loadMenuCounts() {
    try {
        // Usar caminho relativo à pasta public
        const basePath = window.location.pathname.includes('/public/') ? '' : 'public/';
        const response = await fetch(basePath + 'api/analytics.php?type=header');
        const result = await response.json();
        
        if (result.success && result.data) {
            updateMenuCounts(result.data);
        }
    } catch (error) {
        console.warn('Erro ao carregar contadores:', error);
    }
}

function updateMenuCounts(counts) {
    const menuCountTodos = document.getElementById('menu-count-todos');
    const menuCountAbertos = document.getElementById('menu-count-abertos');
    const menuCountAndamento = document.getElementById('menu-count-andamento');
    const menuCountFechados = document.getElementById('menu-count-fechados');
    
    if (menuCountTodos) menuCountTodos.textContent = counts.total || '--';
    if (menuCountAbertos) menuCountAbertos.textContent = counts.abertos || '--';
    if (menuCountAndamento) menuCountAndamento.textContent = counts.em_andamento || '--';
    if (menuCountFechados) menuCountFechados.textContent = counts.fechados_hoje || '--';
}

// Dev area shortcut
<?php if ($isDeveloper): ?>
document.addEventListener('keydown', function(e) {
    if (e.ctrlKey && e.key === 'd') {
        e.preventDefault();
        window.location.href = 'dev_area.php';
    }
});
<?php endif; ?>
</script>
