<?php
/**
 * Analytics Manager Simplificado - Gestão de Métricas
 * Sistema ELUS Facilities
 */

require_once __DIR__ . '/../DB.php';

class AnalyticsManager {
    private $db;
    
    public function __construct($db = null) {
        if ($db) {
            $this->db = $db;
        } else {
            $database = new DB();
            $this->db = $database->getConnection();
        }
    }
    
    /**
     * Retorna métricas para o header
     */
    public function getHeaderMetrics() {
        try {
            $sql = "SELECT 
                COUNT(CASE WHEN status = 'aberto' THEN 1 END) as abertos,
                COUNT(CASE WHEN status = 'em_andamento' THEN 1 END) as em_andamento,
                COUNT(CASE WHEN status = 'fechado' AND DATE(data_fechamento) = CURDATE() THEN 1 END) as fechados_hoje,
                COUNT(*) as total
            FROM chamados";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return [
                'abertos' => (int)$result['abertos'],
                'em_andamento' => (int)$result['em_andamento'], 
                'fechados_hoje' => (int)$result['fechados_hoje'],
                'total' => (int)$result['total'],
                'mttr_7dias' => 0,
                'sla_compliance' => 100,
                'trend' => ['hoje' => 0, 'ontem' => 0, 'direction' => 'stable'],
                'updated_at' => date('H:i')
            ];
        } catch (Exception $e) {
            return [
                'abertos' => 0, 'em_andamento' => 0, 'fechados_hoje' => 0, 'total' => 0,
                'mttr_7dias' => 0, 'sla_compliance' => 0,
                'trend' => ['hoje' => 0, 'ontem' => 0, 'direction' => 'stable'],
                'updated_at' => date('H:i'), 'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Retorna dados para o dashboard
     */
    public function getDashboardMetrics($period = '30days') {
        try {
            // Definir filtro de período
            $date_filter = $this->getDateFilter($period);
            
            // Métricas principais
            $sql = "SELECT 
                COUNT(*) as total_chamados,
                COUNT(CASE WHEN status = 'aberto' THEN 1 END) as abertos,
                COUNT(CASE WHEN status = 'em_andamento' THEN 1 END) as em_andamento,
                COUNT(CASE WHEN status = 'fechado' THEN 1 END) as fechados,
                COUNT(CASE WHEN gravidade = 'alta' THEN 1 END) as alta_gravidade,
                COUNT(CASE WHEN gravidade = 'media' THEN 1 END) as media_gravidade,
                COUNT(CASE WHEN gravidade = 'baixa' THEN 1 END) as baixa_gravidade
            FROM chamados WHERE 1=1" . $date_filter;
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $metrics = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Calcular MTTR real
            $mttr_sql = "SELECT 
                AVG(TIMESTAMPDIFF(HOUR, data_abertura, data_fechamento)) as mttr_hours
                FROM chamados 
                WHERE status = 'fechado' 
                AND data_fechamento IS NOT NULL 
                AND data_abertura IS NOT NULL" . $date_filter;
            
            $mttr_stmt = $this->db->prepare($mttr_sql);
            $mttr_stmt->execute();
            $mttr_result = $mttr_stmt->fetch(PDO::FETCH_ASSOC);
            $mttr_hours = $mttr_result['mttr_hours'] ? round($mttr_result['mttr_hours'], 1) : 0;
            
            // Calcular SLA real (considerando 48h como meta)
            $sla_sql = "SELECT 
                COUNT(*) as total,
                COUNT(CASE WHEN TIMESTAMPDIFF(HOUR, data_abertura, COALESCE(data_fechamento, NOW())) <= 48 THEN 1 END) as dentro_sla
                FROM chamados WHERE 1=1" . $date_filter;
            
            $sla_stmt = $this->db->prepare($sla_sql);
            $sla_stmt->execute();
            $sla_result = $sla_stmt->fetch(PDO::FETCH_ASSOC);
            $sla_compliance = $sla_result['total'] > 0 ? round(($sla_result['dentro_sla'] / $sla_result['total']) * 100, 1) : 0;
            
            // Timeline dos dados baseado no período
            $timeline_days = $this->getTimelineDays($period);
            $timeline_sql = "SELECT 
                DATE(data_abertura) as data,
                COUNT(*) as total,
                COUNT(CASE WHEN status = 'fechado' THEN 1 END) as fechados
            FROM chamados
            WHERE data_abertura >= DATE_SUB(CURDATE(), INTERVAL $timeline_days DAY)
            GROUP BY DATE(data_abertura) 
            ORDER BY data ASC";
            
            $timeline_stmt = $this->db->prepare($timeline_sql);
            $timeline_stmt->execute();
            $timeline = [];
            while ($row = $timeline_stmt->fetch(PDO::FETCH_ASSOC)) {
                $timeline[] = [
                    'date' => $row['data'],
                    'total' => (int)$row['total'],
                    'fechados' => (int)$row['fechados']
                ];
            }
            
            // Setores
            $sectors_sql = "SELECT 
                setor,
                COUNT(*) as total,
                COUNT(CASE WHEN status = 'fechado' THEN 1 END) as fechados
            FROM chamados 
            WHERE setor IS NOT NULL AND setor != ''" . $date_filter . "
            GROUP BY setor 
            ORDER BY total DESC
            LIMIT 10";
            
            $sectors_stmt = $this->db->prepare($sectors_sql);
            $sectors_stmt->execute();
            $sectors = [];
            while ($row = $sectors_stmt->fetch(PDO::FETCH_ASSOC)) {
                $sectors[] = [
                    'setor' => $row['setor'],
                    'total' => (int)$row['total'],
                    'fechados' => (int)$row['fechados'],
                    'mttr' => 0
                ];
            }
            
            return [
                'overview' => [
                    'total_chamados' => (int)$metrics['total_chamados'],
                    'abertos' => (int)$metrics['abertos'],
                    'em_andamento' => (int)$metrics['em_andamento'],
                    'fechados' => (int)$metrics['fechados'],
                    'mttr_hours' => $mttr_hours,
                    'sla_compliance' => $sla_compliance,
                    'gravidade' => [
                        'alta' => (int)$metrics['alta_gravidade'],
                        'media' => (int)$metrics['media_gravidade'],
                        'baixa' => (int)$metrics['baixa_gravidade']
                    ]
                ],
                'timeline' => $timeline,
                'sectors' => $sectors,
                'period' => $period,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
        } catch (Exception $e) {
            return [
                'overview' => [
                    'total_chamados' => 0, 'abertos' => 0, 'em_andamento' => 0, 'fechados' => 0,
                    'mttr_hours' => 0, 'sla_compliance' => 0,
                    'gravidade' => ['alta' => 0, 'media' => 0, 'baixa' => 0]
                ],
                'timeline' => [], 'sectors' => [], 'period' => $period,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Retorna filtro de data baseado no período
     */
    private function getDateFilter($period) {
        switch ($period) {
            case '7days':
                return " AND data_abertura >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
            case '30days':
                return " AND data_abertura >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
            case '90days':
                return " AND data_abertura >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)";
            case '6months':
                return " AND data_abertura >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)";
            case '1year':
                return " AND data_abertura >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)";
            case 'all':
            default:
                return "";
        }
    }
    
    /**
     * Retorna número de dias para timeline baseado no período
     */
    private function getTimelineDays($period) {
        switch ($period) {
            case '7days':
                return 7;
            case '30days':
                return 30;
            case '90days':
                return 90;
            case '6months':
                return 180;
            case '1year':
                return 365;
            case 'all':
            default:
                return 365; // Máximo 1 ano para performance
        }
    }
    
    /**
     * Retorna dados para API
     */
    public function getApiData($type, $period = '30days') {
        switch ($type) {
            case 'header':
                return $this->getHeaderMetrics();
            case 'dashboard':
                return $this->getDashboardMetrics($period);
            default:
                throw new InvalidArgumentException("Tipo não suportado: {$type}");
        }
    }
}
?>