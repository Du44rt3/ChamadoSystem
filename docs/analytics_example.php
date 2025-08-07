<?php
/**
 * EXEMPLO PRÁTICO - Implementação Básica do Dashboard Analytics
 * Este arquivo demonstra como seria a implementação inicial
 */

// ============================================================================
// 1. ANALYTICS MANAGER - Classe Principal
// ============================================================================

class AnalyticsManager {
    private $db;
    private $cache;
    
    public function __construct($db) {
        $this->db = $db;
        $this->cache = new CacheManager();
    }
    
    /**
     * Retorna métricas principais para o dashboard
     */
    public function getDashboardMetrics($period = '30days') {
        $cache_key = "analytics_dashboard_{$period}_" . date('Y-m-d-H');
        
        return $this->cache->rememberQuery($cache_key, function() use ($period) {
            $days = $this->getPeriodDays($period);
            
            return [
                'kpis' => [
                    'mttr' => $this->calculateMTTR($days),
                    'sla_compliance' => $this->calculateSLACompliance($days),
                    'volume' => $this->getTicketVolume($days),
                    'fcr' => $this->calculateFCR($days)
                ],
                'trends' => $this->getTrends($days),
                'distribution' => $this->getDistribution($days)
            ];
        }, 1800); // Cache por 30 minutos
    }
    
    /**
     * MTTR - Mean Time To Resolution (em horas)
     */
    private function calculateMTTR($days) {
        $sql = "SELECT 
                    AVG(TIMESTAMPDIFF(HOUR, data_abertura, data_fechamento)) as mttr_hours
                FROM chamados 
                WHERE status = 'fechado' 
                AND data_fechamento >= DATE_SUB(NOW(), INTERVAL ? DAY)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$days]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return round($result['mttr_hours'] ?? 0, 1);
    }
    
    /**
     * SLA Compliance Rate (percentual)
     */
    private function calculateSLACompliance($days) {
        $sql = "SELECT 
                    COUNT(*) as total,
                    COUNT(CASE WHEN 
                        (status = 'fechado' AND data_fechamento <= data_limite_sla) OR
                        (status != 'fechado' AND NOW() <= data_limite_sla)
                    THEN 1 END) as compliant
                FROM chamados 
                WHERE data_abertura >= DATE_SUB(NOW(), INTERVAL ? DAY)";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$days]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['total'] > 0) {
            return round(($result['compliant'] / $result['total']) * 100, 1);
        }
        return 0;
    }
    
    /**
     * Volume de Chamados por Status
     */
    private function getTicketVolume($days) {
        $sql = "SELECT 
                    COUNT(*) as total,
                    COUNT(CASE WHEN status = 'aberto' THEN 1 END) as abertos,
                    COUNT(CASE WHEN status = 'em_andamento' THEN 1 END) as em_andamento,
                    COUNT(CASE WHEN status = 'fechado' THEN 1 END) as fechados
                FROM chamados 
                WHERE data_abertura >= DATE_SUB(NOW(), INTERVAL ? DAY)";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$days]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * First Call Resolution Rate
     */
    private function calculateFCR($days) {
        // Chamados fechados com apenas 1 atividade no histórico (abertura)
        $sql = "SELECT 
                    COUNT(c.id) as total_fechados,
                    COUNT(CASE WHEN h.atividade_count <= 1 THEN 1 END) as primeira_resolucao
                FROM chamados c
                LEFT JOIN (
                    SELECT chamado_id, COUNT(*) as atividade_count
                    FROM chamado_historico 
                    WHERE atividade NOT LIKE '%Abertura do chamado%'
                    GROUP BY chamado_id
                ) h ON c.id = h.chamado_id
                WHERE c.status = 'fechado' 
                AND c.data_fechamento >= DATE_SUB(NOW(), INTERVAL ? DAY)";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$days]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['total_fechados'] > 0) {
            return round(($result['primeira_resolucao'] / $result['total_fechados']) * 100, 1);
        }
        return 0;
    }
    
    /**
     * Dados para gráfico de tendências (últimos 7 dias)
     */
    private function getTrends($days) {
        $sql = "SELECT 
                    DATE(data_abertura) as data,
                    COUNT(*) as abertos,
                    COUNT(CASE WHEN status = 'fechado' THEN 1 END) as fechados
                FROM chamados 
                WHERE data_abertura >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                GROUP BY DATE(data_abertura)
                ORDER BY data";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Distribuição por gravidade
     */
    private function getDistribution($days) {
        $sql = "SELECT 
                    gravidade,
                    COUNT(*) as quantidade,
                    ROUND((COUNT(*) * 100.0 / (SELECT COUNT(*) FROM chamados WHERE data_abertura >= DATE_SUB(NOW(), INTERVAL ? DAY))), 1) as percentual
                FROM chamados 
                WHERE data_abertura >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY gravidade";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$days, $days]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function getPeriodDays($period) {
        switch($period) {
            case '7days': return 7;
            case '30days': return 30;
            case '90days': return 90;
            case '6months': return 180;
            case '1year': return 365;
            default: return 30;
        }
    }
}

// ============================================================================
// 2. DASHBOARD VIEW COMPONENT
// ============================================================================

class DashboardView {
    private $analyticsManager;
    
    public function __construct($analyticsManager) {
        $this->analyticsManager = $analyticsManager;
    }
    
    public function renderDashboard($metrics, $period) {
        ?>
        <div class="dashboard-analytics">
            <?php $this->renderHeader($period); ?>
            <?php $this->renderKPICards($metrics['kpis']); ?>
            <?php $this->renderChartsSection($metrics); ?>
            <?php $this->renderDetailsSection($metrics); ?>
        </div>
        
        <script>
        // Dados para gráficos (passados do PHP para JavaScript)
        window.analyticsData = <?php echo json_encode($metrics); ?>;
        </script>
        <?php
    }
    
    private function renderHeader($period) {
        ?>
        <div class="analytics-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h2><i class="fas fa-chart-line me-2"></i>Dashboard Analytics</h2>
                    <p class="text-muted">Métricas e indicadores de performance</p>
                </div>
                <div class="col-md-6">
                    <div class="period-selector">
                        <select class="form-select" onchange="changePeriod(this.value)" style="display: inline-block; width: auto;">
                            <option value="7days" <?php echo $period === '7days' ? 'selected' : ''; ?>>Últimos 7 dias</option>
                            <option value="30days" <?php echo $period === '30days' ? 'selected' : ''; ?>>Últimos 30 dias</option>
                            <option value="90days" <?php echo $period === '90days' ? 'selected' : ''; ?>>Últimos 90 dias</option>
                            <option value="6months" <?php echo $period === '6months' ? 'selected' : ''; ?>>Últimos 6 meses</option>
                            <option value="1year" <?php echo $period === '1year' ? 'selected' : ''; ?>>Último ano</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    private function renderKPICards($kpis) {
        ?>
        <div class="kpi-section mb-4">
            <div class="row">
                <!-- MTTR -->
                <div class="col-md-3 mb-3">
                    <div class="kpi-card bg-primary text-white">
                        <div class="kpi-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="kpi-content">
                            <h3 class="kpi-value"><?php echo $kpis['mttr']; ?>h</h3>
                            <p class="kpi-title">MTTR (Tempo Médio)</p>
                            <small class="kpi-description">Meta: &lt; 24h</small>
                        </div>
                    </div>
                </div>
                
                <!-- SLA Compliance -->
                <div class="col-md-3 mb-3">
                    <div class="kpi-card bg-success text-white">
                        <div class="kpi-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="kpi-content">
                            <h3 class="kpi-value"><?php echo $kpis['sla_compliance']; ?>%</h3>
                            <p class="kpi-title">SLA Compliance</p>
                            <small class="kpi-description">Meta: &gt; 95%</small>
                        </div>
                    </div>
                </div>
                
                <!-- Volume Total -->
                <div class="col-md-3 mb-3">
                    <div class="kpi-card bg-info text-white">
                        <div class="kpi-icon">
                            <i class="fas fa-ticket-alt"></i>
                        </div>
                        <div class="kpi-content">
                            <h3 class="kpi-value"><?php echo $kpis['volume']['total']; ?></h3>
                            <p class="kpi-title">Total de Chamados</p>
                            <small class="kpi-description">Período selecionado</small>
                        </div>
                    </div>
                </div>
                
                <!-- FCR -->
                <div class="col-md-3 mb-3">
                    <div class="kpi-card bg-warning text-white">
                        <div class="kpi-icon">
                            <i class="fas fa-bullseye"></i>
                        </div>
                        <div class="kpi-content">
                            <h3 class="kpi-value"><?php echo $kpis['fcr']; ?>%</h3>
                            <p class="kpi-title">First Call Resolution</p>
                            <small class="kpi-description">Meta: &gt; 70%</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    private function renderChartsSection($metrics) {
        ?>
        <div class="charts-section mb-4">
            <div class="row">
                <!-- Gráfico de Tendências -->
                <div class="col-md-8 mb-4">
                    <div class="chart-container">
                        <h5><i class="fas fa-chart-line me-2"></i>Tendência de Chamados</h5>
                        <canvas id="trendsChart" height="100"></canvas>
                    </div>
                </div>
                
                <!-- Distribuição por Status -->
                <div class="col-md-4 mb-4">
                    <div class="chart-container">
                        <h5><i class="fas fa-chart-pie me-2"></i>Status dos Chamados</h5>
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <!-- Distribuição por Gravidade -->
                <div class="col-md-6 mb-4">
                    <div class="chart-container">
                        <h5><i class="fas fa-chart-bar me-2"></i>Distribuição por Gravidade</h5>
                        <canvas id="gravityChart"></canvas>
                    </div>
                </div>
                
                <!-- SLA Performance -->
                <div class="col-md-6 mb-4">
                    <div class="chart-container">
                        <h5><i class="fas fa-tachometer-alt me-2"></i>Performance SLA</h5>
                        <div class="sla-gauge">
                            <div class="gauge-value"><?php echo $metrics['kpis']['sla_compliance']; ?>%</div>
                            <div class="gauge-label">SLA Compliance</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    private function renderDetailsSection($metrics) {
        ?>
        <div class="details-section">
            <div class="row">
                <!-- Resumo Detalhado -->
                <div class="col-md-6">
                    <div class="detail-card">
                        <h5><i class="fas fa-list me-2"></i>Resumo Detalhado</h5>
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Chamados Abertos:</strong></td>
                                <td><?php echo $metrics['kpis']['volume']['abertos']; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Em Andamento:</strong></td>
                                <td><?php echo $metrics['kpis']['volume']['em_andamento']; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Fechados:</strong></td>
                                <td><?php echo $metrics['kpis']['volume']['fechados']; ?></td>
                            </tr>
                            <tr>
                                <td><strong>MTTR Atual:</strong></td>
                                <td><?php echo $metrics['kpis']['mttr']; ?> horas</td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <!-- Alertas e Insights -->
                <div class="col-md-6">
                    <div class="detail-card">
                        <h5><i class="fas fa-exclamation-triangle me-2"></i>Insights</h5>
                        <?php
                        $insights = $this->generateInsights($metrics['kpis']);
                        if (empty($insights)) {
                            echo '<p class="text-muted">Nenhum alerta no momento. Sistema funcionando bem!</p>';
                        } else {
                            echo '<ul class="list-unstyled">';
                            foreach ($insights as $insight) {
                                echo "<li class='mb-2'>";
                                echo "<i class='fas fa-{$insight['icon']} me-2 text-{$insight['color']}'></i>";
                                echo $insight['message'];
                                echo "</li>";
                            }
                            echo '</ul>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    private function generateInsights($kpis) {
        $insights = [];
        
        // MTTR muito alto
        if ($kpis['mttr'] > 48) {
            $insights[] = [
                'icon' => 'exclamation-triangle',
                'color' => 'danger',
                'message' => 'MTTR acima de 48h. Considere revisar o processo de atendimento.'
            ];
        }
        
        // SLA baixo
        if ($kpis['sla_compliance'] < 85) {
            $insights[] = [
                'icon' => 'clock',
                'color' => 'warning',
                'message' => 'SLA Compliance abaixo de 85%. Verifique chamados em risco.'
            ];
        }
        
        // FCR baixo
        if ($kpis['fcr'] < 50) {
            $insights[] = [
                'icon' => 'redo',
                'color' => 'info',
                'message' => 'FCR baixo. Considere melhorar a documentação de soluções.'
            ];
        }
        
        // Volume alto de chamados abertos
        if ($kpis['volume']['abertos'] > ($kpis['volume']['total'] * 0.3)) {
            $insights[] = [
                'icon' => 'tasks',
                'color' => 'warning',
                'message' => 'Alto volume de chamados abertos. Considere redistribuir a carga.'
            ];
        }
        
        return $insights;
    }
}

// ============================================================================
// 3. PÁGINA DASHBOARD PRINCIPAL
// ============================================================================

// Esta seria a implementação em dashboard.php:
/*
<?php
// Proteção de autenticação
require_once '../src/AuthMiddleware.php';
$auth->requireAdminOrDeveloper();

// Incluir dependências
require_once '../src/DB.php';
require_once '../src/CacheManager.php';
require_once 'analytics_example.php'; // Este arquivo

// Inicializar
$database = new DB();
$db = $database->getConnection();
$analyticsManager = new AnalyticsManager($db);
$dashboardView = new DashboardView($analyticsManager);

// Processar período
$period = $_GET['period'] ?? '30days';
$validPeriods = ['7days', '30days', '90days', '6months', '1year'];
if (!in_array($period, $validPeriods)) {
    $period = '30days';
}

// Obter dados
$metrics = $analyticsManager->getDashboardMetrics($period);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Analytics - ELUS Facilities</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .dashboard-analytics { padding: 20px; }
        .kpi-card { 
            border-radius: 15px; 
            padding: 20px; 
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            min-height: 120px;
        }
        .kpi-icon { 
            font-size: 3rem; 
            margin-right: 20px; 
            opacity: 0.8; 
        }
        .kpi-value { 
            font-size: 2.5rem; 
            font-weight: bold; 
            margin: 0; 
        }
        .kpi-title { 
            margin: 5px 0 0 0; 
            opacity: 0.9; 
        }
        .kpi-description { 
            font-size: 0.8rem; 
            opacity: 0.7; 
        }
        .chart-container { 
            background: white; 
            border-radius: 10px; 
            padding: 20px; 
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            height: 100%;
        }
        .detail-card { 
            background: white; 
            border-radius: 10px; 
            padding: 20px; 
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .sla-gauge {
            text-align: center;
            padding: 40px 0;
        }
        .gauge-value {
            font-size: 4rem;
            font-weight: bold;
            color: #28a745;
        }
        .gauge-label {
            font-size: 1.2rem;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <?php $dashboardView->renderDashboard($metrics, $period); ?>
    </div>
    
    <script>
        function changePeriod(period) {
            window.location.href = `dashboard.php?period=${period}`;
        }
        
        // Inicializar gráficos
        document.addEventListener('DOMContentLoaded', function() {
            initializeCharts();
        });
        
        function initializeCharts() {
            const data = window.analyticsData;
            
            // Gráfico de Tendências
            const trendsCtx = document.getElementById('trendsChart').getContext('2d');
            new Chart(trendsCtx, {
                type: 'line',
                data: {
                    labels: data.trends.map(t => t.data),
                    datasets: [{
                        label: 'Abertos',
                        data: data.trends.map(t => t.abertos),
                        borderColor: '#007bff',
                        backgroundColor: 'rgba(0,123,255,0.1)',
                        tension: 0.4
                    }, {
                        label: 'Fechados',
                        data: data.trends.map(t => t.fechados),
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40,167,69,0.1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
            
            // Gráfico de Status
            const statusCtx = document.getElementById('statusChart').getContext('2d');
            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Abertos', 'Em Andamento', 'Fechados'],
                    datasets: [{
                        data: [
                            data.kpis.volume.abertos,
                            data.kpis.volume.em_andamento,
                            data.kpis.volume.fechados
                        ],
                        backgroundColor: ['#dc3545', '#ffc107', '#28a745']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
            
            // Gráfico de Gravidade
            const gravityCtx = document.getElementById('gravityChart').getContext('2d');
            new Chart(gravityCtx, {
                type: 'bar',
                data: {
                    labels: data.distribution.map(d => d.gravidade.charAt(0).toUpperCase() + d.gravidade.slice(1)),
                    datasets: [{
                        label: 'Quantidade',
                        data: data.distribution.map(d => d.quantidade),
                        backgroundColor: ['#dc3545', '#ffc107', '#17a2b8']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }
    </script>
</body>
</html>
*/

echo "✅ Exemplo de implementação criado com sucesso!\n";
echo "📁 Arquivo: analytics_example.php\n";
echo "🎯 Status: Pronto para adaptação no projeto real\n";
?>
