<?php
/**
 * Dashboard Analytics - Sistema ELUS Facilities
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<!-- Debug: Iniciando dashboard -->";

require_once '../config/config.php';
echo "<!-- Debug: Config carregado -->";

require_once '../src/DB.php';
echo "<!-- Debug: DB carregado -->";

require_once '../src/Auth.php';
echo "<!-- Debug: Auth carregado -->";

require_once '../src/AuthMiddleware.php';
echo "<!-- Debug: AuthMiddleware carregado -->";

require_once '../src/analytics/AnalyticsManager.php';
echo "<!-- Debug: AnalyticsManager carregado -->";

// Verificar autenticação
$database = new DB();
$db = $database->getConnection();
$auth = new Auth($db);
$auth->requireAuth();

// Verificar se é admin ou desenvolvedor
$current_user = $auth->getLoggedUser();
if (!$current_user || !in_array($current_user['nivel_acesso'], ['admin', 'desenvolvedor'])) {
    header("Location: index.php?error=acesso_negado");
    exit();
}

// Obter período
$period = $_GET['period'] ?? '30days';
$validPeriods = ['all', '7days', '30days', '90days', '6months', '1year'];
if (!in_array($period, $validPeriods)) {
    $period = '30days';
}

// Obter dados
$analyticsManager = new AnalyticsManager();
$metrics = $analyticsManager->getDashboardMetrics($period);

// Garantir estrutura dos dados
$overview = $metrics['overview'] ?? [];
$timeline = $metrics['timeline'] ?? [];
$sectors = $metrics['sectors'] ?? [];

// Dados padrão
$defaultOverview = [
    'total_chamados' => 0, 'abertos' => 0, 'em_andamento' => 0, 'fechados' => 0,
    'mttr_hours' => 0, 'sla_compliance' => 0,
    'gravidade' => ['alta' => 0, 'media' => 0, 'baixa' => 0]
];
$overview = array_merge($defaultOverview, $overview);

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Analytics - Sistema ELUS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="../css/style.css" rel="stylesheet">
</head>
<body>
    <?php 
    // Definir variáveis globais para o header
    global $auth, $current_user;
    $page_title = "Dashboard Analytics";
    $page_subtitle = "Métricas e estatísticas do sistema";
    
    // Incluir header moderno
    require_once '../src/header.php'; 
    ?>

<div class="container-fluid py-4">
    <!-- Header do Dashboard -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">
                <i class="fas fa-chart-pie me-2 text-primary"></i>
                Dashboard Analytics
            </h2>
            <small class="text-muted">Métricas atualizadas em tempo real</small>
        </div>
        <div class="btn-group">
            <?php foreach ($validPeriods as $p): ?>
                <a href="?period=<?php echo $p; ?>" 
                   class="btn btn-outline-primary <?php echo $p === $period ? 'active' : ''; ?>">
                    <?php 
                    $labels = [
                        'all' => 'Todos', '7days' => '7 Dias', '30days' => '30 Dias', 
                        '90days' => '90 Dias', '6months' => '6 Meses', '1year' => '1 Ano'
                    ];
                    echo $labels[$p];
                    ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if (isset($metrics['error'])): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle me-2"></i>
            Erro ao carregar métricas: <?php echo htmlspecialchars($metrics['error']); ?>
        </div>
    <?php else: ?>
        
        <!-- KPI Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h3 class="card-title mb-1"><?php echo number_format($overview['total_chamados']); ?></h3>
                                <p class="card-text">Total de Chamados</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-ticket-alt fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h3 class="card-title mb-1"><?php echo number_format($overview['abertos']); ?></h3>
                                <p class="card-text">Chamados Abertos</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-clock fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h3 class="card-title mb-1"><?php echo number_format($overview['em_andamento']); ?></h3>
                                <p class="card-text">Em Andamento</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-cog fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h3 class="card-title mb-1"><?php echo number_format($overview['fechados']); ?></h3>
                                <p class="card-text">Resolvidos</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-check-circle fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Métricas de Performance -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-stopwatch me-2"></i>MTTR - Tempo Médio</h5>
                    </div>
                    <div class="card-body text-center">
                        <h3 class="text-primary"><?php echo number_format($overview['mttr_hours'], 1); ?> horas</h3>
                        <?php if ($overview['mttr_hours'] <= 24): ?>
                            <span class="badge bg-success">Excelente</span>
                        <?php elseif ($overview['mttr_hours'] <= 48): ?>
                            <span class="badge bg-warning">Bom</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Atenção</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-target me-2"></i>SLA Compliance</h5>
                    </div>
                    <div class="card-body text-center">
                        <h3 class="text-primary"><?php echo number_format($overview['sla_compliance'], 1); ?>%</h3>
                        <?php if ($overview['sla_compliance'] >= 95): ?>
                            <span class="badge bg-success">Excelente</span>
                        <?php elseif ($overview['sla_compliance'] >= 85): ?>
                            <span class="badge bg-warning">Bom</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Crítico</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráficos -->
        <div class="row mb-4">
            <!-- Timeline Chart -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-chart-line me-2"></i>Timeline de Chamados</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($timeline)): ?>
                            <canvas id="timelineChart" width="400" height="200"></canvas>
                        <?php else: ?>
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-chart-line fa-3x mb-3"></i>
                                <p>Nenhum dado disponível para o período selecionado</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Gravidade Chart -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-chart-pie me-2"></i>Por Gravidade</h5>
                    </div>
                    <div class="card-body">
                        <?php if (($overview['gravidade']['alta'] + $overview['gravidade']['media'] + $overview['gravidade']['baixa']) > 0): ?>
                            <canvas id="gravidadeChart" width="300" height="300"></canvas>
                        <?php else: ?>
                            <div class="text-center text-muted py-5">
                                <i class="fas fa-chart-pie fa-3x mb-3"></i>
                                <p>Sem dados de gravidade</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabela de Setores -->
        <?php if (!empty($sectors)): ?>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-building me-2"></i>Distribuição por Setor</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Setor</th>
                                        <th>Total</th>
                                        <th>Fechados</th>
                                        <th>Taxa de Resolução</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($sectors as $sector): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($sector['setor']); ?></td>
                                        <td><?php echo $sector['total']; ?></td>
                                        <td><?php echo $sector['fechados']; ?></td>
                                        <td>
                                            <?php 
                                            $rate = $sector['total'] > 0 ? ($sector['fechados'] / $sector['total']) * 100 : 0;
                                            echo number_format($rate, 1) . '%';
                                            ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

    <?php endif; ?>
    
</div>

<script>
// Variáveis globais para os gráficos
let timelineChart = null;
let gravidadeChart = null;

// Inicializar gráficos
document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
});

function initializeCharts() {
    // Destruir gráficos existentes se houver
    if (timelineChart) {
        timelineChart.destroy();
        timelineChart = null;
    }
    if (gravidadeChart) {
        gravidadeChart.destroy();
        gravidadeChart = null;
    }
    
    // Timeline Chart
    const timelineData = <?php echo json_encode($timeline); ?>;
    if (timelineData && timelineData.length > 0) {
        createTimelineChart(timelineData);
    }
    
    // Gravidade Chart
    const gravidadeData = <?php echo json_encode($overview['gravidade']); ?>;
    if (gravidadeData && (gravidadeData.alta + gravidadeData.media + gravidadeData.baixa > 0)) {
        createGravidadeChart(gravidadeData);
    }
}

function createTimelineChart(data) {
    const ctx = document.getElementById('timelineChart');
    if (!ctx || !data || data.length === 0) return;
    
    const labels = data.map(item => {
        const date = new Date(item.date);
        return date.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit' });
    });
    
    const totalData = data.map(item => item.total || 0);
    const fechadosData = data.map(item => item.fechados || 0);
    
    timelineChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Chamados Abertos',
                data: totalData,
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Chamados Fechados',
                data: fechadosData,
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { 
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 15
                    }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                }
            },
            scales: {
                y: { 
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0,0,0,0.1)'
                    }
                },
                x: {
                    grid: {
                        color: 'rgba(0,0,0,0.1)'
                    }
                }
            },
            interaction: {
                mode: 'nearest',
                axis: 'x',
                intersect: false
            }
        }
    });
}

function createGravidadeChart(data) {
    const ctx = document.getElementById('gravidadeChart');
    if (!ctx || !data) return;
    
    const alta = data.alta || 0;
    const media = data.media || 0;
    const baixa = data.baixa || 0;
    
    if (alta + media + baixa === 0) return;
    
    gravidadeChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Alta Gravidade', 'Média Gravidade', 'Baixa Gravidade'],
            datasets: [{
                data: [alta, media, baixa],
                backgroundColor: [
                    '#dc3545', // Vermelho para alta
                    '#ffc107', // Amarelo para média  
                    '#28a745'  // Verde para baixa
                ],
                borderWidth: 3,
                borderColor: '#fff',
                hoverBorderWidth: 4,
                hoverBorderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { 
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 15
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            },
            cutout: '60%'
        }
    });
}

// Adicionar animação nos cards
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.card');
    cards.forEach((card, index) => {
        setTimeout(() => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'all 0.3s ease';
            
            setTimeout(() => {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 50);
        }, index * 100);
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
