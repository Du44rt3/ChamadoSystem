<?php
/**
 * Analytics Widget - Componente compacto para Header
 * Sistema ELUS Facilities
 */

require_once __DIR__ . '/../analytics/AnalyticsManager.php';

class AnalyticsWidget {
    private $analyticsManager;
    private $metrics;
    
    public function __construct() {
        $this->analyticsManager = new AnalyticsManager();
        $this->metrics = $this->analyticsManager->getHeaderMetrics();
    }
    
    /**
     * Renderiza o widget analytics compacto para o header
     */
    public function renderHeaderWidget() {
        $metrics = $this->metrics;
        
        // Determinar cor do SLA
        $slaColor = $metrics['sla_compliance'] >= 95 ? 'success' : 
                   ($metrics['sla_compliance'] >= 85 ? 'warning' : 'danger');
        
        // Determinar ícone da tendência
        $trendIcon = $metrics['trend']['direction'] === 'up' ? 'fa-arrow-up text-warning' : 
                    ($metrics['trend']['direction'] === 'down' ? 'fa-arrow-down text-success' : 'fa-minus text-muted');
        
        ob_start();
        ?>
        <!-- Analytics Widget Compacto -->
        <div class="analytics-widget me-3" id="analyticsWidget">
            <div class="analytics-trigger" data-bs-toggle="dropdown" aria-expanded="false" title="Dashboard Analytics">
                <div class="analytics-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="analytics-summary">
                    <div class="analytics-primary">
                        <span class="metric-value"><?php echo $metrics['abertos']; ?></span>
                        <span class="metric-label">Abertos</span>
                    </div>
                    <div class="analytics-secondary">
                        <span class="sla-indicator bg-<?php echo $slaColor; ?>"><?php echo number_format($metrics['sla_compliance'], 1); ?>%</span>
                    </div>
                </div>
                <div class="analytics-trend">
                    <i class="fas <?php echo $trendIcon; ?>"></i>
                </div>
            </div>
            
            <!-- Dropdown com métricas detalhadas -->
            <div class="dropdown-menu analytics-dropdown" aria-labelledby="analyticsWidget">
                <div class="analytics-header">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>Dashboard Analytics
                    </h6>
                    <small class="text-muted">Atualizado às <?php echo $metrics['updated_at']; ?></small>
                </div>
                
                <div class="analytics-content">
                    <!-- KPIs Principais -->
                    <div class="kpi-row">
                        <div class="kpi-item">
                            <div class="kpi-icon bg-primary">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="kpi-data">
                                <span class="kpi-value"><?php echo $metrics['abertos']; ?></span>
                                <span class="kpi-label">Abertos</span>
                            </div>
                        </div>
                        
                        <div class="kpi-item">
                            <div class="kpi-icon bg-info">
                                <i class="fas fa-cog"></i>
                            </div>
                            <div class="kpi-data">
                                <span class="kpi-value"><?php echo $metrics['em_andamento']; ?></span>
                                <span class="kpi-label">Em Andamento</span>
                            </div>
                        </div>
                        
                        <div class="kpi-item">
                            <div class="kpi-icon bg-success">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="kpi-data">
                                <span class="kpi-value"><?php echo $metrics['fechados_hoje']; ?></span>
                                <span class="kpi-label">Hoje</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Métricas de Performance -->
                    <div class="performance-row">
                        <div class="performance-item">
                            <div class="performance-label">
                                <i class="fas fa-stopwatch me-1"></i>MTTR (7 dias)
                            </div>
                            <div class="performance-value">
                                <?php echo $metrics['mttr_7dias']; ?>h
                            </div>
                        </div>
                        
                        <div class="performance-item">
                            <div class="performance-label">
                                <i class="fas fa-target me-1"></i>SLA Compliance
                            </div>
                            <div class="performance-value text-<?php echo $slaColor; ?>">
                                <?php echo number_format($metrics['sla_compliance'], 1); ?>%
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tendência -->
                    <div class="trend-row">
                        <div class="trend-item">
                            <span class="trend-label">Hoje vs Ontem:</span>
                            <span class="trend-value">
                                <?php echo $metrics['trend']['hoje']; ?> vs <?php echo $metrics['trend']['ontem']; ?>
                                <i class="fas <?php echo $trendIcon; ?> ms-1"></i>
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="analytics-footer">
                    <a href="dashboard.php" class="btn btn-primary btn-sm w-100">
                        <i class="fas fa-chart-pie me-2"></i>Dashboard Completo
                    </a>
                </div>
            </div>
        </div>
        
        <style>
        /* Analytics Widget Styles */
        .analytics-widget {
            position: relative;
        }
        
        .analytics-trigger {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 140px;
        }
        
        .analytics-trigger:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        
        .analytics-icon {
            font-size: 1.1rem;
            color: var(--elus-yellow);
        }
        
        .analytics-summary {
            display: flex;
            flex-direction: column;
            gap: 2px;
            flex: 1;
        }
        
        .analytics-primary {
            display: flex;
            align-items: baseline;
            gap: 4px;
        }
        
        .metric-value {
            font-size: 1.1rem;
            font-weight: 700;
            color: white;
        }
        
        .metric-label {
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.8);
            font-weight: 500;
        }
        
        .analytics-secondary {
            display: flex;
            align-items: center;
        }
        
        .sla-indicator {
            font-size: 0.7rem;
            padding: 2px 6px;
            border-radius: 10px;
            font-weight: 600;
            color: white;
        }
        
        .analytics-trend {
            font-size: 0.9rem;
            display: flex;
            align-items: center;
        }
        
        /* Dropdown Styles */
        .analytics-dropdown {
            min-width: 320px;
            border: none;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            padding: 0;
            overflow: hidden;
            margin-top: 10px;
        }
        
        .analytics-header {
            background: linear-gradient(135deg, var(--elus-blue) 0%, var(--elus-blue-dark) 100%);
            color: white;
            padding: 15px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .analytics-header h6 {
            color: white;
            font-weight: 600;
        }
        
        .analytics-content {
            padding: 20px;
            background: white;
        }
        
        /* KPI Row */
        .kpi-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .kpi-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .kpi-icon {
            width: 35px;
            height: 35px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.9rem;
        }
        
        .kpi-data {
            display: flex;
            flex-direction: column;
        }
        
        .kpi-value {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--elus-blue-dark);
            line-height: 1;
        }
        
        .kpi-label {
            font-size: 0.75rem;
            color: #666;
            font-weight: 500;
        }
        
        /* Performance Row */
        .performance-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        
        .performance-item {
            text-align: center;
        }
        
        .performance-label {
            font-size: 0.8rem;
            color: #666;
            font-weight: 500;
            margin-bottom: 5px;
        }
        
        .performance-value {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--elus-blue-dark);
        }
        
        /* Trend Row */
        .trend-row {
            padding: 10px 15px;
            background: rgba(var(--elus-blue-rgb), 0.05);
            border-radius: 8px;
            margin-bottom: 15px;
        }
        
        .trend-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .trend-label {
            font-size: 0.85rem;
            color: #666;
            font-weight: 500;
        }
        
        .trend-value {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--elus-blue-dark);
        }
        
        /* Footer */
        .analytics-footer {
            padding: 15px 20px;
            background: #f8f9fa;
            border-top: 1px solid #e9ecef;
        }
        
        .analytics-footer .btn {
            border-radius: 20px;
            font-weight: 600;
        }
        
        /* Responsive */
        @media (max-width: 991.98px) {
            .analytics-widget {
                margin-right: 0.5rem !important;
            }
            
            .analytics-trigger {
                min-width: 120px;
                padding: 6px 10px;
            }
            
            .metric-value {
                font-size: 1rem;
            }
            
            .analytics-dropdown {
                min-width: 280px;
            }
            
            .kpi-row {
                grid-template-columns: 1fr;
                gap: 10px;
            }
            
            .performance-row {
                grid-template-columns: 1fr;
                gap: 10px;
            }
        }
        
        @media (max-width: 767.98px) {
            .analytics-summary {
                display: none;
            }
            
            .analytics-trigger {
                min-width: auto;
                padding: 8px;
            }
            
            .analytics-dropdown {
                min-width: 260px;
                margin-left: -100px;
            }
        }
        
        /* Animation */
        .analytics-trigger {
            position: relative;
            overflow: hidden;
        }
        
        .analytics-trigger::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.6s ease;
        }
        
        .analytics-trigger:hover::before {
            left: 100%;
        }
        
        /* Loading animation */
        .analytics-loading {
            animation: pulse 1.5s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0% {
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
            100% {
                opacity: 1;
            }
        }
        </style>
        
        <script>
        // Auto-refresh analytics widget
        function refreshAnalyticsWidget() {
            const widget = document.getElementById('analyticsWidget');
            if (!widget) return;
            
            // Add loading class
            widget.classList.add('analytics-loading');
            
            // Fetch new data
            fetch('api/analytics.php?type=header')
                .then(response => response.json())
                .then(data => {
                    if (data && !data.error) {
                        updateWidgetData(data);
                    }
                })
                .catch(error => {
                    console.warn('Erro ao atualizar analytics:', error);
                })
                .finally(() => {
                    widget.classList.remove('analytics-loading');
                });
        }
        
        function updateWidgetData(metrics) {
            // Update main counter
            const metricValue = document.querySelector('.metric-value');
            if (metricValue) {
                metricValue.textContent = metrics.abertos;
            }
            
            // Update SLA indicator
            const slaIndicator = document.querySelector('.sla-indicator');
            if (slaIndicator) {
                slaIndicator.textContent = metrics.sla_compliance.toFixed(1) + '%';
                
                // Update SLA color
                slaIndicator.className = 'sla-indicator bg-' + 
                    (metrics.sla_compliance >= 95 ? 'success' : 
                     metrics.sla_compliance >= 85 ? 'warning' : 'danger');
            }
            
            // Update trend icon
            const trendIcon = document.querySelector('.analytics-trend i');
            if (trendIcon) {
                const direction = metrics.trend.direction;
                trendIcon.className = 'fas ' + 
                    (direction === 'up' ? 'fa-arrow-up text-warning' : 
                     direction === 'down' ? 'fa-arrow-down text-success' : 'fa-minus text-muted');
            }
            
            // Update dropdown values
            updateDropdownValues(metrics);
            
            // Update timestamp
            const timestamp = document.querySelector('.analytics-header small');
            if (timestamp) {
                timestamp.textContent = 'Atualizado às ' + metrics.updated_at;
            }
        }
        
        function updateDropdownValues(metrics) {
            const kpiValues = document.querySelectorAll('.kpi-value');
            if (kpiValues.length >= 3) {
                kpiValues[0].textContent = metrics.abertos;
                kpiValues[1].textContent = metrics.em_andamento;
                kpiValues[2].textContent = metrics.fechados_hoje;
            }
            
            const performanceValues = document.querySelectorAll('.performance-value');
            if (performanceValues.length >= 2) {
                performanceValues[0].textContent = metrics.mttr_7dias + 'h';
                performanceValues[1].textContent = metrics.sla_compliance.toFixed(1) + '%';
            }
            
            const trendValue = document.querySelector('.trend-value');
            if (trendValue) {
                trendValue.innerHTML = metrics.trend.hoje + ' vs ' + metrics.trend.ontem + 
                    ' <i class="fas ' + 
                    (metrics.trend.direction === 'up' ? 'fa-arrow-up text-warning' : 
                     metrics.trend.direction === 'down' ? 'fa-arrow-down text-success' : 'fa-minus text-muted') + 
                    ' ms-1"></i>';
            }
        }
        
        // Initialize auto-refresh
        document.addEventListener('DOMContentLoaded', function() {
            // Refresh every 5 minutes
            setInterval(refreshAnalyticsWidget, 300000);
            
            // Also refresh when clicking on the widget
            const trigger = document.querySelector('.analytics-trigger');
            if (trigger) {
                trigger.addEventListener('click', function(e) {
                    // Don't refresh immediately on click, just show dropdown
                    setTimeout(refreshAnalyticsWidget, 1000);
                });
            }
        });
        </script>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Renderiza apenas o ícone simples (versão mais compacta)
     */
    public function renderSimpleIcon() {
        $metrics = $this->metrics;
        $alertClass = $metrics['abertos'] > 10 ? 'text-warning' : 'text-success';
        
        ob_start();
        ?>
        <a class="nav-link nav-tool analytics-simple me-2" href="dashboard.php" 
           title="Dashboard Analytics - <?php echo $metrics['abertos']; ?> chamados abertos" 
           data-bs-toggle="tooltip">
            <i class="fas fa-chart-line <?php echo $alertClass; ?>"></i>
            <?php if ($metrics['abertos'] > 0): ?>
                <span class="analytics-badge"><?php echo $metrics['abertos']; ?></span>
            <?php endif; ?>
        </a>
        
        <style>
        .analytics-simple {
            position: relative;
        }
        
        .analytics-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #dc3545;
            color: white;
            font-size: 0.7rem;
            font-weight: 600;
            padding: 2px 6px;
            border-radius: 10px;
            min-width: 18px;
            text-align: center;
            border: 2px solid var(--elus-blue);
        }
        </style>
        <?php
        return ob_get_clean();
    }
}
?>
