-- ===================================================================
-- SISTEMA DE CHAMADOS ELUS FACILITIES
-- Script de Instalação Completo - Versão Limpa
-- ===================================================================
-- 
-- Este script cria todas as tabelas necessárias para o funcionamento
-- completo do sistema de chamados ELUS Facilities
-- 
-- FUNCIONALIDADES INCLUÍDAS:
-- - Sistema de chamados completo
-- - Controle de usuários e níveis de acesso
-- - Sistema de anexos de imagens
-- - Histórico detalhado de atividades
-- - Dashboard analytics avançado
-- - Templates de email
-- - Sistema de alertas
-- ===================================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- Criar banco de dados se não existir
CREATE DATABASE IF NOT EXISTS `chamados_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `chamados_db`;

-- ===================================================================
-- 1. TABELA DE USUÁRIOS
-- ===================================================================
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL COMMENT 'Nome completo do usuário',
  `username` varchar(100) NOT NULL COMMENT 'Nome de usuário único',
  `password` varchar(255) NOT NULL COMMENT 'Senha hash',
  `email` varchar(255) DEFAULT NULL COMMENT 'Email do usuário',
  `nivel_acesso` enum('admin','desenvolvedor','usuario') DEFAULT 'usuario' COMMENT 'Nível de acesso',
  `ativo` tinyint(1) DEFAULT 1 COMMENT 'Se o usuário está ativo',
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `ultimo_login` timestamp NULL DEFAULT NULL,
  `tentativas_login` int(11) DEFAULT 0 COMMENT 'Contador de tentativas de login',
  `bloqueado_ate` timestamp NULL DEFAULT NULL COMMENT 'Data até quando está bloqueado',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `idx_nivel_acesso` (`nivel_acesso`),
  KEY `idx_ativo` (`ativo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Usuários do sistema';

-- ===================================================================
-- 2. TABELA DE NÍVEIS DE ACESSO AVANÇADOS
-- ===================================================================
CREATE TABLE `niveis_acesso` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(50) NOT NULL COMMENT 'Nome do nível',
  `descricao` text DEFAULT NULL COMMENT 'Descrição do nível',
  `nivel_sistema` enum('sistema','customizado') DEFAULT 'customizado' COMMENT 'Se é nível do sistema',
  `permissoes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`permissoes`)) COMMENT 'Permissões em JSON',
  `ativo` tinyint(1) DEFAULT 1,
  `cor` varchar(7) DEFAULT '#6c757d' COMMENT 'Cor para exibição',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nome` (`nome`),
  KEY `created_by` (`created_by`),
  KEY `idx_niveis_nome` (`nome`),
  KEY `idx_niveis_ativo` (`ativo`),
  CONSTRAINT `niveis_acesso_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Níveis de acesso personalizáveis';

-- ===================================================================
-- 3. TABELA PRINCIPAL DE CHAMADOS
-- ===================================================================
CREATE TABLE `chamados` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codigo_chamado` varchar(100) NOT NULL COMMENT 'Código único do chamado',
  `nome_colaborador` varchar(255) NOT NULL COMMENT 'Nome do solicitante',
  `email` varchar(255) DEFAULT NULL COMMENT 'Email do solicitante',
  `email_colaborador` varchar(255) DEFAULT NULL COMMENT 'Email adicional',
  `setor` varchar(255) NOT NULL COMMENT 'Setor do solicitante',
  `descricao_problema` text NOT NULL COMMENT 'Descrição detalhada do problema',
  `nome_projeto` varchar(255) DEFAULT NULL COMMENT 'Nome do projeto relacionado',
  `data_abertura` datetime DEFAULT current_timestamp() COMMENT 'Data de abertura',
  `data_limite_sla` datetime DEFAULT NULL COMMENT 'Data limite SLA',
  `gravidade` enum('baixa','media','alta') NOT NULL COMMENT 'Nível de gravidade',
  `status` enum('aberto','em_andamento','fechado') DEFAULT 'aberto' COMMENT 'Status atual',
  `solucao` text DEFAULT NULL COMMENT 'Descrição da solução',
  `data_fechamento` datetime DEFAULT NULL COMMENT 'Data de fechamento',
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo_chamado` (`codigo_chamado`),
  KEY `idx_email` (`email`),
  KEY `idx_codigo_chamado` (`codigo_chamado`),
  KEY `idx_status` (`status`),
  KEY `idx_gravidade` (`gravidade`),
  KEY `idx_data_abertura` (`data_abertura`),
  KEY `idx_data_limite_sla` (`data_limite_sla`),
  KEY `idx_setor` (`setor`),
  KEY `idx_nome_projeto` (`nome_projeto`),
  KEY `idx_chamados_analytics` (`data_abertura`, `status`, `gravidade`),
  KEY `idx_chamados_sla` (`data_limite_sla`, `status`, `data_fechamento`),
  KEY `idx_chamados_setor_data` (`setor`, `data_abertura`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Chamados do sistema';

-- ===================================================================
-- 4. TABELA DE HISTÓRICO DE CHAMADOS
-- ===================================================================
CREATE TABLE `chamado_historico` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `chamado_id` int(11) NOT NULL COMMENT 'ID do chamado',
  `atividade` text NOT NULL COMMENT 'Descrição da atividade',
  `data_atividade` datetime DEFAULT current_timestamp() COMMENT 'Data da atividade',
  `usuario` varchar(255) DEFAULT 'Sistema' COMMENT 'Usuário que executou',
  PRIMARY KEY (`id`),
  KEY `idx_chamado_historico_chamado_id` (`chamado_id`),
  KEY `idx_historico_analytics` (`data_atividade`, `usuario`, `chamado_id`),
  CONSTRAINT `chamado_historico_ibfk_1` FOREIGN KEY (`chamado_id`) REFERENCES `chamados` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Histórico de atividades dos chamados';

-- ===================================================================
-- 5. TABELA DE ANEXOS DE IMAGENS
-- ===================================================================
CREATE TABLE `chamado_anexos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `chamado_id` int(11) NOT NULL COMMENT 'ID do chamado',
  `nome_original` varchar(255) NOT NULL COMMENT 'Nome original do arquivo',
  `nome_arquivo` varchar(255) NOT NULL COMMENT 'Nome do arquivo no servidor',
  `caminho_arquivo` varchar(500) NOT NULL COMMENT 'Caminho completo do arquivo',
  `tipo_mime` varchar(100) NOT NULL COMMENT 'Tipo MIME do arquivo',
  `tamanho_arquivo` int(11) NOT NULL COMMENT 'Tamanho em bytes',
  `data_upload` datetime DEFAULT current_timestamp() COMMENT 'Data do upload',
  `usuario_upload` varchar(255) DEFAULT NULL COMMENT 'Usuário que fez upload',
  PRIMARY KEY (`id`),
  KEY `chamado_id` (`chamado_id`),
  KEY `idx_chamado_id` (`chamado_id`),
  CONSTRAINT `chamado_anexos_ibfk_1` FOREIGN KEY (`chamado_id`) REFERENCES `chamados` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Anexos de imagens dos chamados';

-- ===================================================================
-- 6. TABELA DE TEMPLATES DE EMAIL
-- ===================================================================
CREATE TABLE `email_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL COMMENT 'Nome do template',
  `assunto` varchar(255) NOT NULL COMMENT 'Assunto do email',
  `corpo` text NOT NULL COMMENT 'Corpo do email em HTML',
  `ativo` tinyint(1) DEFAULT 1 COMMENT 'Se está ativo',
  `data_criacao` datetime DEFAULT current_timestamp(),
  `data_modificacao` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_nome` (`nome`),
  KEY `idx_ativo` (`ativo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Templates de email';

-- ===================================================================
-- 7. TABELA DE HISTÓRICO DE NÍVEIS
-- ===================================================================
CREATE TABLE `historico_niveis` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `nivel_anterior` varchar(50) DEFAULT NULL,
  `nivel_novo` varchar(50) DEFAULT NULL,
  `alterado_por` int(11) NOT NULL,
  `data_alteracao` timestamp NOT NULL DEFAULT current_timestamp(),
  `observacoes` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `alterado_por` (`alterado_por`),
  KEY `idx_historico_usuario` (`usuario_id`),
  KEY `idx_historico_data` (`data_alteracao`),
  CONSTRAINT `historico_niveis_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `historico_niveis_ibfk_2` FOREIGN KEY (`alterado_por`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Histórico de alterações de níveis';

-- ===================================================================
-- 8. TABELAS DO DASHBOARD ANALYTICS
-- ===================================================================

-- Métricas pré-calculadas
CREATE TABLE `dashboard_metrics` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `metric_name` VARCHAR(50) NOT NULL COMMENT 'Nome da métrica (mttr, sla_compliance, etc)',
    `metric_value` DECIMAL(10,2) NOT NULL COMMENT 'Valor da métrica',
    `period_type` ENUM('hour', 'day', 'week', 'month', 'year') NOT NULL COMMENT 'Tipo de período',
    `period_date` DATE NOT NULL COMMENT 'Data do período',
    `period_start` DATETIME NULL COMMENT 'Início do período para métricas horárias',
    `metadata` JSON NULL COMMENT 'Dados adicionais em formato JSON',
    `calculated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Quando foi calculado',
    PRIMARY KEY (`id`),
    INDEX `idx_metric_period` (`metric_name`, `period_type`, `period_date`),
    INDEX `idx_calculated_at` (`calculated_at`),
    UNIQUE KEY `uk_metric_period` (`metric_name`, `period_type`, `period_date`, `period_start`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci 
COMMENT='Métricas pré-calculadas para performance do dashboard';

-- Cache de relatórios
CREATE TABLE `analytics_cache` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `cache_key` VARCHAR(255) NOT NULL COMMENT 'Chave única do cache',
    `cache_type` VARCHAR(50) NOT NULL COMMENT 'Tipo de cache (chart, report, kpi)',
    `data` JSON NOT NULL COMMENT 'Dados do cache em formato JSON',
    `parameters` JSON NULL COMMENT 'Parâmetros usados para gerar o cache',
    `expires_at` TIMESTAMP NOT NULL COMMENT 'Quando o cache expira',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `accessed_count` INT(11) DEFAULT 0 COMMENT 'Quantas vezes foi acessado',
    `last_accessed` TIMESTAMP NULL COMMENT 'Último acesso',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_cache_key` (`cache_key`),
    INDEX `idx_cache_type` (`cache_type`),
    INDEX `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
COMMENT='Cache especializado para dados analíticos complexos';

-- Configurações do dashboard
CREATE TABLE `dashboard_config` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `user_id` INT(11) NULL COMMENT 'ID do usuário (NULL = configuração global)',
    `config_name` VARCHAR(100) NOT NULL COMMENT 'Nome da configuração',
    `widget_config` JSON NOT NULL COMMENT 'Configuração dos widgets',
    `layout_config` JSON NULL COMMENT 'Layout personalizado',
    `preferences` JSON NULL COMMENT 'Preferências do usuário',
    `is_default` BOOLEAN DEFAULT FALSE COMMENT 'Se é configuração padrão',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `user_id` (`user_id`),
    INDEX `idx_user_config` (`user_id`, `config_name`),
    INDEX `idx_is_default` (`is_default`),
    CONSTRAINT `dashboard_config_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
COMMENT='Configurações personalizadas do dashboard por usuário';

-- ===================================================================
-- 9. TRIGGERS AUTOMÁTICOS
-- ===================================================================

DELIMITER $$

-- Trigger para registrar abertura de chamado
CREATE TRIGGER `after_chamado_insert` AFTER INSERT ON `chamados` FOR EACH ROW 
BEGIN
    INSERT INTO chamado_historico (chamado_id, atividade, usuario) 
    VALUES (NEW.id, 'Abertura do chamado', 'Sistema');
END$$

-- Trigger para registrar mudanças de status
CREATE TRIGGER `after_chamado_status_update` AFTER UPDATE ON `chamados` FOR EACH ROW 
BEGIN
    IF OLD.status != NEW.status THEN
        CASE NEW.status
            WHEN 'em_andamento' THEN
                INSERT INTO chamado_historico (chamado_id, atividade, usuario) 
                VALUES (NEW.id, 'Chamado iniciado - Status alterado para Em Andamento', 'Sistema');
            WHEN 'fechado' THEN
                INSERT INTO chamado_historico (chamado_id, atividade, usuario) 
                VALUES (NEW.id, 'Chamado finalizado - Status alterado para Fechado', 'Sistema');
            WHEN 'aberto' THEN
                INSERT INTO chamado_historico (chamado_id, atividade, usuario) 
                VALUES (NEW.id, 'Chamado reaberto - Status alterado para Aberto', 'Sistema');
        END CASE;
    END IF;
END$$

-- Trigger para registrar adição de anexos
CREATE TRIGGER `after_anexo_insert` AFTER INSERT ON `chamado_anexos` FOR EACH ROW 
BEGIN
    INSERT INTO chamado_historico (chamado_id, atividade, usuario) 
    VALUES (NEW.chamado_id, CONCAT('Anexo adicionado: ', NEW.nome_original), COALESCE(NEW.usuario_upload, 'Sistema'));
END$$

-- Trigger para registrar remoção de anexos
CREATE TRIGGER `after_anexo_delete` AFTER DELETE ON `chamado_anexos` FOR EACH ROW 
BEGIN
    INSERT INTO chamado_historico (chamado_id, atividade, usuario) 
    VALUES (OLD.chamado_id, CONCAT('Anexo removido: ', OLD.nome_original), 'Sistema');
END$$

DELIMITER ;

-- ===================================================================
-- 10. DADOS INICIAIS ESSENCIAIS
-- ===================================================================

-- Inserir níveis de acesso padrão
INSERT INTO `niveis_acesso` (`id`, `nome`, `descricao`, `nivel_sistema`, `permissoes`, `ativo`, `cor`) VALUES
(1, 'desenvolvedor', 'Acesso total ao sistema + ferramentas de desenvolvimento', 'sistema', '{"chamados": {"criar": true, "editar": true, "excluir": true, "ver_todos": true}, "usuarios": {"criar": true, "editar": true, "excluir": true, "ver_todos": true}, "backup": true, "logs": true, "debug": true, "security": true, "dev_area": true, "manage_levels": true}', 1, '#dc3545'),
(2, 'admin', 'Acesso administrativo completo ao sistema', 'sistema', '{"chamados": {"criar": true, "editar": true, "excluir": true, "ver_todos": true}, "usuarios": {"criar": true, "editar": true, "excluir": false, "ver_todos": true}, "backup": false, "logs": false, "debug": false, "security": false, "dev_area": false, "manage_levels": false}', 1, '#17a2b8'),
(3, 'usuario', 'Acesso básico ao sistema de chamados', 'sistema', '{"chamados": {"criar": true, "editar": false, "excluir": false, "ver_todos": false}, "usuarios": {"criar": false, "editar": false, "excluir": false, "ver_todos": false}, "backup": false, "logs": false, "debug": false, "security": false, "dev_area": false, "manage_levels": false}', 1, '#6c757d');

-- Inserir usuário administrador padrão
-- Senha padrão: admin123 (ALTERE IMEDIATAMENTE EM PRODUÇÃO!)
INSERT INTO `usuarios` (`nome`, `username`, `password`, `email`, `nivel_acesso`, `ativo`) VALUES
('Administrador', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@elus.com.br', 'desenvolvedor', 1);

-- Configuração padrão do dashboard
INSERT INTO `dashboard_config` (`user_id`, `config_name`, `widget_config`, `layout_config`, `is_default`) VALUES
(NULL, 'default_dashboard', 
'{"widgets": ["kpi_cards", "trends_chart", "sla_gauge", "distribution_chart"], "refresh_interval": 300}',
'{"layout": "grid", "columns": 12, "widgets_per_row": 4}',
TRUE);

-- Templates de email padrão
INSERT INTO `email_templates` (`nome`, `assunto`, `corpo`, `ativo`) VALUES
('novo_chamado', 'Novo Chamado Criado - {{codigo_chamado}}', 
'<h2>Novo Chamado Criado</h2>
<p><strong>Código:</strong> {{codigo_chamado}}</p>
<p><strong>Solicitante:</strong> {{nome_colaborador}}</p>
<p><strong>Setor:</strong> {{setor}}</p>
<p><strong>Gravidade:</strong> {{gravidade}}</p>
<p><strong>Descrição:</strong> {{descricao_problema}}</p>
<p><strong>Data de Abertura:</strong> {{data_abertura}}</p>
<hr>
<p>Este é um email automático do Sistema ELUS Facilities.</p>', 1),

('chamado_fechado', 'Chamado Finalizado - {{codigo_chamado}}', 
'<h2>Chamado Finalizado</h2>
<p><strong>Código:</strong> {{codigo_chamado}}</p>
<p><strong>Solicitante:</strong> {{nome_colaborador}}</p>
<p><strong>Solução:</strong> {{solucao}}</p>
<p><strong>Data de Fechamento:</strong> {{data_fechamento}}</p>
<hr>
<p>Este é um email automático do Sistema ELUS Facilities.</p>', 1);

-- ===================================================================
-- 11. VIEWS ANALÍTICAS
-- ===================================================================

-- View para resumo de métricas
CREATE OR REPLACE VIEW `v_metrics_summary` AS
SELECT 
    DATE(period_date) as data,
    period_type,
    MAX(CASE WHEN metric_name = 'mttr' THEN metric_value END) as mttr,
    MAX(CASE WHEN metric_name = 'sla_compliance' THEN metric_value END) as sla_compliance,
    MAX(CASE WHEN metric_name = 'fcr' THEN metric_value END) as fcr,
    MAX(CASE WHEN metric_name = 'ticket_volume' THEN metric_value END) as ticket_volume,
    MAX(CASE WHEN metric_name = 'open_tickets' THEN metric_value END) as open_tickets,
    MAX(CASE WHEN metric_name = 'closed_tickets' THEN metric_value END) as closed_tickets
FROM dashboard_metrics 
WHERE period_type IN ('day', 'week', 'month')
GROUP BY DATE(period_date), period_type
ORDER BY data DESC;

-- View para análise de SLA
CREATE OR REPLACE VIEW `v_sla_analysis` AS
SELECT 
    c.id,
    c.codigo_chamado,
    c.nome_colaborador,
    c.setor,
    c.gravidade,
    c.status,
    c.data_abertura,
    c.data_limite_sla,
    c.data_fechamento,
    CASE 
        WHEN c.status = 'fechado' AND c.data_fechamento <= c.data_limite_sla THEN 'cumprido'
        WHEN c.status = 'fechado' AND c.data_fechamento > c.data_limite_sla THEN 'vencido'
        WHEN c.status != 'fechado' AND NOW() > c.data_limite_sla THEN 'vencido'
        WHEN c.status != 'fechado' AND NOW() <= c.data_limite_sla THEN 'dentro_prazo'
        ELSE 'indefinido'
    END as sla_status,
    CASE 
        WHEN c.status = 'fechado' THEN 
            TIMESTAMPDIFF(HOUR, c.data_abertura, c.data_fechamento)
        ELSE 
            TIMESTAMPDIFF(HOUR, c.data_abertura, NOW())
    END as horas_decorridas,
    TIMESTAMPDIFF(HOUR, c.data_abertura, c.data_limite_sla) as sla_horas
FROM chamados c
WHERE c.data_abertura >= DATE_SUB(NOW(), INTERVAL 6 MONTH);

-- ===================================================================
-- FINALIZAÇÃO
-- ===================================================================

-- Definir AUTO_INCREMENT inicial
ALTER TABLE `chamados` AUTO_INCREMENT = 1;
ALTER TABLE `chamado_historico` AUTO_INCREMENT = 1;
ALTER TABLE `chamado_anexos` AUTO_INCREMENT = 1;
ALTER TABLE `usuarios` AUTO_INCREMENT = 2;
ALTER TABLE `niveis_acesso` AUTO_INCREMENT = 4;
ALTER TABLE `email_templates` AUTO_INCREMENT = 3;
ALTER TABLE `historico_niveis` AUTO_INCREMENT = 1;
ALTER TABLE `dashboard_metrics` AUTO_INCREMENT = 1;
ALTER TABLE `analytics_cache` AUTO_INCREMENT = 1;
ALTER TABLE `dashboard_config` AUTO_INCREMENT = 2;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- ===================================================================
-- INSTALAÇÃO CONCLUÍDA!
-- ===================================================================
-- 
-- ✅ BANCO DE DADOS CRIADO COM SUCESSO!
-- 
-- CREDENCIAIS PADRÃO:
-- Usuário: admin
-- Senha: admin123
-- 
-- ⚠️  IMPORTANTE - SEGURANÇA:
-- 1. ALTERE A SENHA PADRÃO IMEDIATAMENTE!
-- 2. Configure o arquivo .env com dados reais
-- 3. Ajuste permissões das pastas logs/ e uploads/
-- 4. Configure certificado SSL em produção
-- 
-- PRÓXIMOS PASSOS:
-- 1. Acesse: http://seudominio.com/chamados_system/public/
-- 2. Faça login com admin/admin123
-- 3. Altere a senha e crie outros usuários
-- 4. Configure templates de email
-- 5. Teste todas as funcionalidades
-- 
-- FUNCIONALIDADES INSTALADAS:
-- ✅ Sistema completo de chamados
-- ✅ Controle de usuários e níveis
-- ✅ Sistema de anexos
-- ✅ Histórico detalhado
-- ✅ Dashboard analytics
-- ✅ Templates de email
-- ✅ Triggers automáticos
-- ✅ Views analíticas
-- 
-- SUPORTE: Para dúvidas, consulte a documentação em docs/
-- ===================================================================
