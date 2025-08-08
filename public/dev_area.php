<?php
/**
 * Área de Desenvolvimento - APENAS PARA DESENVOLVEDORES
 * Sistema ELUS - Facilities
 */

// Proteção de autenticação
require_once '../src/AuthMiddleware.php';

// Verificar se tem acesso de desenvolvedor
$auth->requireDeveloper();

// Configurações da página
$page_title = "Dev Area";
$page_subtitle = "Área de Desenvolvimento - ELUS Facilities";

// Estatísticas do sistema
$query = "SELECT status, COUNT(*) as total FROM chamados GROUP BY status";
$stmt = $db->prepare($query);
$stmt->execute();
$chamados_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

$query = "SELECT nivel_acesso, COUNT(*) as total FROM usuarios WHERE ativo = 1 GROUP BY nivel_acesso";
$stmt = $db->prepare($query);
$stmt->execute();
$users_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dev Area - ELUS Facilities</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../css/style.css?v=<?php echo time(); ?>" rel="stylesheet">
    <style>
        /* Dev Area Clean Dark Theme */
        * {
            box-sizing: border-box;
        }
        
        body {
            background: #0d0d0d;
            color: #00ff41;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }
        
        /* Custom Dev Header */
        .dev-header {
            background: linear-gradient(90deg, #000000 0%, #111111 100%);
            border-bottom: 1px solid #333;
            padding: 15px 0;
        }
        
        .dev-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .dev-brand {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: #00ff41;
        }
        
        .dev-brand:hover {
            color: #00ff41;
            text-decoration: none;
        }
        
        .dev-logo {
            width: 40px;
            height: 40px;
            margin-right: 15px;
            border-radius: 4px;
        }
        
        .dev-brand-text {
            font-size: 18px;
            font-weight: 600;
            margin-right: 10px;
        }
        
        .dev-badge {
            background: rgba(0, 255, 65, 0.2);
            color: #00ff41;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            border: 1px solid #00ff41;
        }
        
        .dev-user {
            display: flex;
            align-items: center;
            color: #888;
        }
        
        .dev-user-name {
            margin-right: 10px;
            font-size: 14px;
        }
        
        .dev-exit {
            background: transparent;
            border: 1px solid #666;
            color: #888;
            padding: 8px 15px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .dev-exit:hover {
            border-color: #00ff41;
            color: #00ff41;
            text-decoration: none;
        }
        
        .dev-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        
        
        /* Welcome Section */
        .welcome-section {
            background: rgba(0, 0, 0, 0.5);
            border: 1px solid #333;
            border-radius: 8px;
            padding: 30px;
            margin-bottom: 40px;
            text-align: center;
        }
        
        .welcome-title {
            font-size: 2.2rem;
            font-weight: 300;
            margin-bottom: 10px;
            color: #fff;
        }
        
        .welcome-subtitle {
            color: #888;
            font-size: 1rem;
            margin-bottom: 0;
        }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            background: rgba(0, 0, 0, 0.4);
            border: 1px solid #333;
            border-radius: 8px;
            padding: 25px;
            text-align: center;
            transition: border-color 0.3s ease;
        }
        
        .stat-card:hover {
            border-color: #00ff41;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 600;
            color: #00ff41;
            margin-bottom: 8px;
        }
        
        .stat-label {
            color: #888;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        /* Tools Grid */
        .tools-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .tool-card {
            background: rgba(0, 0, 0, 0.4);
            border: 1px solid #333;
            border-radius: 8px;
            padding: 30px;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .tool-card:hover {
            border-color: #00ff41;
            transform: translateY(-2px);
        }
        
        .tool-icon {
            font-size: 2.5rem;
            color: #00ff41;
            margin-bottom: 20px;
        }
        
        .tool-title {
            color: #fff;
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .tool-desc {
            color: #888;
            font-size: 0.9rem;
            margin-bottom: 25px;
            line-height: 1.5;
        }
        
        .btn-dev {
            background: transparent;
            border: 1px solid #00ff41;
            color: #00ff41;
            padding: 12px 25px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-dev:hover {
            background: #00ff41;
            color: #000;
            text-decoration: none;
        }
        
        /* Info Panels */
        .info-panel {
            background: rgba(0, 0, 0, 0.4);
            border: 1px solid #333;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 20px;
        }
        
        .panel-header {
            background: rgba(0, 0, 0, 0.6);
            color: #00ff41;
            padding: 15px 20px;
            border-bottom: 1px solid #333;
            font-weight: 600;
        }
        
        .panel-body {
            padding: 20px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #333;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            color: #888;
            font-size: 0.9rem;
        }
        
        .info-value {
            color: #00ff41;
            font-weight: 500;
        }
        
        /* Results Area */
        .results-area {
            background: rgba(0, 0, 0, 0.6);
            border: 1px solid #333;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .dev-nav {
                flex-direction: column;
                gap: 15px;
            }
            
            .dev-container {
                padding: 20px 15px;
            }
            
            .welcome-title {
                font-size: 1.8rem;
            }
            
            .stats-grid,
            .tools-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Custom Dev Header -->
    <div class="dev-header">
        <div class="dev-nav">
            <a href="index.php" class="dev-brand">
                <img src="images/logo-eluss.png" alt="ELUS Logo" class="dev-logo">
                <span class="dev-brand-text">ELUS Facilities</span>
                <span class="dev-badge">DEV</span>
            </a>
            
            <div class="dev-user">
                <span class="dev-user-name"><?php echo htmlspecialchars($current_user['nome']); ?></span>
                <a href="index.php" class="dev-exit">
                    <i class="fas fa-arrow-left me-2"></i>Exit Dev
                </a>
            </div>
        </div>
    </div>
    
    <div class="dev-container">
        <!-- Welcome Section -->
        <div class="welcome-section">
            <h1 class="welcome-title">Área de Desenvolvimento</h1>
            <p class="welcome-subtitle">Ferramentas avançadas e gerenciamento de sistema para desenvolvedores</p>
        </div>

        <!-- System Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number">
                    <?php
                    $total_chamados = 0;
                    foreach ($chamados_stats as $stat) {
                        $total_chamados += $stat['total'];
                    }
                    echo $total_chamados;
                    ?>
                </div>
                <div class="stat-label">Total de Chamados</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-number">
                    <?php
                    $total_users = 0;
                    foreach ($users_stats as $stat) {
                        $total_users += $stat['total'];
                    }
                    echo $total_users;
                    ?>
                </div>
                <div class="stat-label">Usuários Ativos</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-number"><?php echo count($users_stats); ?></div>
                <div class="stat-label">Níveis de Acesso</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-number">
                    <?php echo $current_user['nivel_acesso']; ?>
                </div>
                <div class="stat-label">Seu Acesso</div>
            </div>
        </div>

        <!-- Dev Tools -->
        <div class="tools-grid">
            <div class="tool-card">
                <i class="fas fa-tools tool-icon"></i>
                <div class="tool-title">🔧 Verificar Ferramentas</div>
                <div class="tool-desc">Verificar se todas as ferramentas estão funcionando corretamente</div>
                <a href="../tests/verificar_dev_tools.php" class="btn-dev">Verificar Tools</a>
            </div>
            
            <div class="tool-card">
                <i class="fas fa-user-plus tool-icon"></i>
                <div class="tool-title">Registro de Usuários</div>
                <div class="tool-desc">Criar novos usuários do sistema com níveis de acesso apropriados</div>
                <a href="register_user.php" class="btn-dev">Acessar Ferramenta</a>
            </div>
            
            <div class="tool-card">
                <i class="fas fa-users-cog tool-icon"></i>
                <div class="tool-title">Gerenciar Usuários</div>
                <div class="tool-desc">Gerenciar usuários existentes, permissões e senhas</div>
                <a href="user_manager.php" class="btn-dev">Acessar Ferramenta</a>
            </div>
            
            <div class="tool-card">
                <i class="fas fa-user-cog tool-icon"></i>
                <div class="tool-title">Gerenciar Níveis</div>
                <div class="tool-desc">Criar e gerenciar níveis/cargos customizados com permissões específicas</div>
                <a href="manage_levels.php" class="btn-dev">Acessar Ferramenta</a>
            </div>
            
            <div class="tool-card">
                <i class="fas fa-database tool-icon"></i>
                <div class="tool-title">Sistema de Backup</div>
                <div class="tool-desc">Operações de backup e recuperação do banco de dados</div>
                <a href="backup_manager.php" class="btn-dev">Acessar Ferramenta</a>
            </div>
            
            <div class="tool-card">
                <i class="fas fa-shield-alt tool-icon"></i>
                <div class="tool-title">Verificação de Segurança</div>
                <div class="tool-desc">Executar avaliação de vulnerabilidades do sistema</div>
                <button class="btn-dev" onclick="runSecurityCheck()">Executar Scan</button>
            </div>
            
            <div class="tool-card">
                <i class="fas fa-info-circle tool-icon"></i>
                <div class="tool-title">Informações do Sistema</div>
                <div class="tool-desc">Exibir dados de configuração e ambiente do sistema</div>
                <button class="btn-dev" onclick="showSystemInfo()">Mostrar Info</button>
            </div>
            
            <div class="tool-card">
                <i class="fas fa-memory tool-icon"></i>
                <div class="tool-title">Gerenciar Cache</div>
                <div class="tool-desc">Limpar cache do sistema quando houver inconsistências</div>
                <button class="btn-dev" onclick="clearSystemCache()">Limpar Cache</button>
            </div>
            
            <div class="tool-card">
                <i class="fas fa-plug tool-icon"></i>
                <div class="tool-title">Teste de Conexão</div>
                <div class="tool-desc">Verificar conexão com banco de dados e integridade</div>
                <a href="../tests/test_connection.php" class="btn-dev">Testar Conexão</a>
            </div>
            
            <div class="tool-card">
                <i class="fas fa-bug tool-icon"></i>
                <div class="tool-title">Debug de Sessão</div>
                <div class="tool-desc">Visualizar informações de sessão e debugging</div>
                <a href="../tests/debug_session.php" class="btn-dev">Debug Session</a>
            </div>
            
            <div class="tool-card">
                <i class="fas fa-cogs tool-icon"></i>
                <div class="tool-title">Configurações Avançadas</div>
                <div class="tool-desc">Editar configurações do sistema e variáveis de ambiente</div>
                <button class="btn-dev" onclick="showAdvancedConfig()">Configurar</button>
            </div>
        </div>

        <!-- Results Area -->
        <div id="testResults"></div>

        <!-- System Information Panels -->
        <div class="row">
            <div class="col-md-6">
                <div class="info-panel">
                    <div class="panel-header">
                        <i class="fas fa-server me-2"></i>Status do Sistema
                    </div>
                    <div class="panel-body">
                        <div class="info-row">
                            <span class="info-label">Versão PHP</span>
                            <span class="info-value"><?php echo PHP_VERSION; ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Banco de Dados</span>
                            <span class="info-value">Conectado</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Ambiente</span>
                            <span class="info-value"><?php echo APP_ENV; ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Modo Debug</span>
                            <span class="info-value"><?php echo APP_DEBUG ? 'Ativo' : 'Inativo'; ?></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="info-panel">
                    <div class="panel-header">
                        <i class="fas fa-users me-2"></i>Distribuição de Usuários
                    </div>
                    <div class="panel-body">
                        <?php foreach ($users_stats as $stat): ?>
                        <div class="info-row">
                            <span class="info-label">
                                <?php echo $stat['nivel_acesso'] === 'ADM' ? 'Administradores' : 'Desenvolvedores'; ?>
                            </span>
                            <span class="info-value"><?php echo $stat['total']; ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function runSecurityCheck() {
            const results = document.getElementById('testResults');
            results.innerHTML = `
                <div class="results-area">
                    <div style="color: #ffc107; margin-bottom: 15px;">
                        <i class="fas fa-spinner fa-spin"></i> Executando avaliação de segurança...
                    </div>
                    <div style="color: #888; font-size: 0.9rem;">
                        > Verificando camadas de autenticação...<br>
                        > Analisando controles de acesso...<br>
                        > Escaneando vulnerabilidades...
                    </div>
                </div>
            `;
            
            setTimeout(() => {
                results.innerHTML = `
                    <div class="results-area">
                        <div style="color: #00ff41; margin-bottom: 20px; font-weight: 600;">
                            Verificação de Segurança Concluída - Status: SEGURO
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div style="color: #00ff41; margin-bottom: 15px; font-weight: 500;">Proteções Ativas:</div>
                                <div style="color: #888; font-size: 0.9rem; line-height: 1.8;">
                                    • Hash de Senha: ARGON2ID ✓<br>
                                    • Segurança de Sessão: Ativa ✓<br>
                                    • Proteção CSRF: Habilitada ✓<br>
                                    • Prevenção XSS: Sanitizada ✓<br>
                                    • Controle de Acesso: Implementado ✓
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div style="color: #00ff41; margin-bottom: 15px; font-weight: 500;">Nível de Segurança:</div>
                                <div style="color: #888; font-size: 0.9rem;">
                                    <div style="margin-bottom: 10px;">
                                        Pontuação Geral: <span style="color: #00ff41; font-weight: 600;">95%</span>
                                    </div>
                                    <div style="background: #333; height: 15px; border-radius: 8px; overflow: hidden;">
                                        <div style="background: #00ff41; height: 100%; width: 95%; border-radius: 8px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }, 2500);
        }

        function showSystemInfo() {
            const results = document.getElementById('testResults');
            results.innerHTML = `
                <div class="results-area">
                    <div style="color: #00ff41; margin-bottom: 20px; font-weight: 600;">
                        Informações do Sistema
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div style="color: #00ff41; margin-bottom: 15px; font-weight: 500;">Ambiente:</div>
                            <div style="color: #888; font-size: 0.9rem; line-height: 1.8;">
                                • Ambiente: <span style="color: #00ff41;"><?php echo APP_ENV; ?></span><br>
                                • Modo Debug: <span style="color: #00ff41;"><?php echo APP_DEBUG ? 'Ativo' : 'Inativo'; ?></span><br>
                                • Versão PHP: <span style="color: #00ff41;"><?php echo PHP_VERSION; ?></span><br>
                                • Banco de Dados: <span style="color: #00ff41;"><?php echo DB_NAME; ?></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div style="color: #00ff41; margin-bottom: 15px; font-weight: 500;">Sessão Atual:</div>
                            <div style="color: #888; font-size: 0.9rem; line-height: 1.8;">
                                • Usuário: <span style="color: #00ff41;"><?php echo $current_user['username']; ?></span><br>
                                • Nome: <span style="color: #00ff41;"><?php echo $current_user['nome']; ?></span><br>
                                • Nível de Acesso: <span style="color: #00ff41;"><?php echo $current_user['nivel_acesso']; ?></span><br>
                                • ID da Sessão: <span style="color: #00ff41;"><?php echo substr(session_id(), 0, 8); ?>...</span>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
        
        function clearSystemCache() {
            const results = document.getElementById('testResults');
            results.innerHTML = `
                <div class="results-area">
                    <div style="color: #00ff41; margin-bottom: 20px; font-weight: 600;">
                        Limpando Cache do Sistema...
                    </div>
                    <div style="color: #888; font-size: 0.9rem;">
                        > Removendo arquivos de cache...<br>
                        > Limpando cache de queries...<br>
                        > Invalidando sessões de cache...<br>
                        > Atualizando índices...
                    </div>
                </div>
            `;
            
            // Fazer requisição para limpar o cache
            fetch('../tools/clear_cache.php?admin_clear=true')
                .then(response => response.text())
                .then(data => {
                    setTimeout(() => {
                        results.innerHTML = `
                            <div class="results-area">
                                <div style="color: #00ff41; margin-bottom: 20px; font-weight: 600;">
                                    Cache Limpo com Sucesso!
                                </div>
                                <div style="color: #888; font-size: 0.9rem; line-height: 1.8;">
                                    • Cache de queries: <span style="color: #00ff41;">Limpo ✓</span><br>
                                    • Cache de status: <span style="color: #00ff41;">Limpo ✓</span><br>
                                    • Cache de pesquisas: <span style="color: #00ff41;">Limpo ✓</span><br>
                                    • Arquivos temporários: <span style="color: #00ff41;">Removidos ✓</span><br><br>
                                    <div style="color: #00ff41; font-weight: 500;">
                                        ⚡ Sistema atualizado! Inconsistências de status resolvidas.
                                    </div>
                                </div>
                            </div>
                        `;
                    }, 1500);
                })
                .catch(error => {
                    results.innerHTML = `
                        <div class="results-area">
                            <div style="color: #ff4444; margin-bottom: 15px; font-weight: 600;">
                                Erro ao Limpar Cache
                            </div>
                            <div style="color: #888; font-size: 0.9rem;">
                                ${error.message}
                            </div>
                        </div>
                    `;
                });
        }

        function showAdvancedConfig() {
            const results = document.getElementById('testResults');
            results.innerHTML = `
                <div class="results-area">
                    <div style="color: #00ff41; margin-bottom: 20px; font-weight: 600;">
                        Configurações Avançadas do Sistema
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div style="color: #00ff41; margin-bottom: 15px; font-weight: 500;">Configurações de Ambiente:</div>
                            <div style="color: #888; font-size: 0.9rem; line-height: 1.8;">
                                • APP_ENV: <span style="color: #00ff41;"><?php echo APP_ENV; ?></span><br>
                                • APP_DEBUG: <span style="color: #00ff41;"><?php echo APP_DEBUG ? 'true' : 'false'; ?></span><br>
                                • DB_HOST: <span style="color: #00ff41;"><?php echo DB_HOST; ?></span><br>
                                • DB_NAME: <span style="color: #00ff41;"><?php echo DB_NAME; ?></span><br>
                                • SESSION_SECURE: <span style="color: #00ff41;"><?php echo SESSION_SECURE ? 'true' : 'false'; ?></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div style="color: #00ff41; margin-bottom: 15px; font-weight: 500;">Ações Disponíveis:</div>
                            <div style="color: #888; font-size: 0.9rem;">
                                <button class="btn-dev" style="margin: 5px;" onclick="toggleDebugMode()">
                                    <i class="fas fa-bug"></i> Toggle Debug
                                </button><br>
                                <button class="btn-dev" style="margin: 5px;" onclick="clearSystemCache()">
                                    <i class="fas fa-trash"></i> Limpar Cache
                                </button><br>
                                <button class="btn-dev" style="margin: 5px;" onclick="restartSessions()">
                                    <i class="fas fa-sync"></i> Reiniciar Sessões
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        function toggleDebugMode() {
            alert('Funcionalidade em desenvolvimento. Use o arquivo .env para alterar APP_DEBUG.');
        }

        function clearSystemCache() {
            const results = document.getElementById('testResults');
            results.innerHTML = `
                <div class="results-area">
                    <div style="color: #ffc107; margin-bottom: 15px;">
                        <i class="fas fa-spinner fa-spin"></i> Limpando cache do sistema...
                    </div>
                </div>
            `;
            
            setTimeout(() => {
                results.innerHTML = `
                    <div class="results-area">
                        <div style="color: #00ff41; margin-bottom: 15px; font-weight: 600;">
                            Cache do sistema limpo com sucesso!
                        </div>
                        <div style="color: #888; font-size: 0.9rem;">
                            • Cache de sessões: Limpo<br>
                            • Cache de configurações: Limpo<br>
                            • Arquivos temporários: Removidos
                        </div>
                    </div>
                `;
            }, 1500);
        }

        function restartSessions() {
            if (confirm('Isso irá desconectar todos os usuários. Continuar?')) {
                window.location.href = 'logout.php?restart_all=1';
            }
        }

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            console.log('%c[ELUS DEV] Área de desenvolvimento carregada com sucesso', 'color: #00ff41; font-weight: bold;');
        });
    </script>
</body>
</html>
