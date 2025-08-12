# ELUS Facilities - Sistema de Chamados Corporativo

```
████████╗██╗ ██████╗██╗  ██╗███████╗████████╗    ███████╗██╗   ██╗███████╗████████╗███████╗███╗   ███╗
╚══██╔══╝██║██╔════╝██║ ██╔╝██╔════╝╚══██╔══╝    ██╔════╝╚██╗ ██╔╝██╔════╝╚══██╔══╝██╔════╝████╗ ████║
   ██║   ██║██║     █████╔╝ █████╗     ██║       ███████╗ ╚████╔╝ ███████╗   ██║   █████╗  ██╔████╔██║
   ██║   ██║██║     ██╔═██╗ ██╔══╝     ██║       ╚════██║  ╚██╔╝  ╚════██║   ██║   ██╔══╝  ██║╚██╔╝██║
   ██║   ██║╚██████╗██║  ██╗███████╗   ██║       ███████║   ██║   ███████║   ██║   ███████╗██║ ╚═╝ ██║
   ╚═╝   ╚═╝ ╚═════╝╚═╝  ╚═╝╚══════╝   ╚═╝       ╚══════╝   ╚═╝   ╚══════╝   ╚═╝   ╚══════╝╚═╝     ╚═╝
```

![Status](https://img.shields.io/badge/Status-Produção-brightgreen)
![PHP](https://img.shields.io/badge/PHP-8.1+-777BB4)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1)
![License](https://img.shields.io/badge/License-MIT-blue)

Sistema empresarial de gestão de chamados, facilities e infraestrutura corporativa. Arquitetura robusta com cache inteligente, analytics avançado, segurança enterprise e integração completa com sistemas de email.

---

## Características Técnicas

**Core System**
- Gestão completa de chamados com workflow automatizado
- Sistema de usuários hierárquico (4 níveis de acesso)
- Analytics dashboard com métricas em tempo real
- Sistema de cache inteligente com invalidação automática
- Integração nativa com Outlook Classic e webmail

**Security & Performance**
- Autenticação ARGON2ID com proteção CSRF
- Sanitização XSS completa em todas as entradas
- Cache Manager reduzindo 80% das consultas ao banco
- Progressive Disclosure UI para otimização de interface
- Rate limiting e logs de segurança

**Integration & Scalability**
- API REST para integrações externas
- Sistema de templates de email personalizáveis
- Upload de anexos com validação de segurança
- Backup automático e scripts de deploy
- Monitoramento de performance e debug tools

---

## Arquitetura Completa do Sistema

```
chamados_system/
├── .env                                # Configurações ambiente
├── .env.example                        # Template configurações
├── .gitignore                          # Controle versão
├── iniciar_xampp.bat                   # Inicialização XAMPP
├── README.md                           # Documentação principal
├── PONTOS_FRACOS_CODIGO.txt           # Análise técnica
├── ROADMAP_MELHORIAS.txt              # Planejamento evolução
│
├── assets/                             # Recursos frontend
│   ├── css/
│   │   ├── chamados-list.css          # Estilos listagem
│   │   └── progressive-disclosure.css  # UI otimizada
│   └── js/
│       └── chamados-list.js           # JavaScript principal
│
├── cache/                              # Sistema cache
│   ├── .htaccess                      # Proteção Apache
│   └── *.cache                        # Arquivos cache
│
├── config/                             # Configurações sistema
│   └── config.php                     # Configuração principal
│
├── css/                                # Estilos customizados
│   └── style.css                      # CSS global
│
├── database/                           # Scripts banco dados
│   ├── anexos_images.sql              # Estrutura anexos
│   ├── atualizar_senhas_usuarios.php  # Migração senhas
│   ├── chamados_db.sql                # Schema principal
│   ├── dashboard_analytics_structure.sql # Analytics DB
│   ├── fix_trigger_conflict.sql       # Correção triggers
│   ├── fix_triggers.sql               # Triggers sistema
│   ├── gerar_hashes_senhas.php        # Geração hashes
│   ├── install_sistema_completo.sql   # Instalação completa
│   ├── migrate_passwords.php          # Migração passwords
│   └── update_db.php                  # Atualizações DB
│
├── docs/                               # Documentação técnica
│   ├── analytics_example.php          # Exemplo analytics
│   ├── ATUALIZACAO_INCREMENTAL.md     # Guia atualização
│   ├── ATUALIZACAO_RAPIDA.md          # Atualização rápida
│   ├── CHECKLIST_SEGURANCA_PRODUCAO.md # Checklist segurança
│   ├── CORRECAO_DUPLICACAO_CHAMADOS.md # Correção duplicação
│   ├── DASHBOARD_ANALYTICS_PLANO.md   # Planejamento analytics
│   ├── DASHBOARD_ANALYTICS_README.md  # Documentação analytics
│   ├── DEPLOY_PRODUCAO_GUIA.md        # Guia deploy
│   ├── GUIA_HTTPS.md                  # Configuração HTTPS
│   ├── INSTALACAO_SISTEMA.md          # Guia instalação
│   ├── LIMPEZA_SISTEMA_COMPLETA.md    # Limpeza sistema
│   ├── OUTLOOK_CLASSIC_SOLUCAO.md     # Integração Outlook
│   ├── REFATORACAO_ELIMINACAO_DUPLICACAO.md # Refatoração
│   ├── RELATORIO_SEGURANCA_FINAL.md   # Relatório segurança
│   ├── SECURITY_IMPROVEMENTS.md       # Melhorias segurança
│   ├── SEGURANCA_CORRECOES_IMPLEMENTADAS.md # Correções
│   └── SISTEMA_ANEXOS_IMAGENS.md      # Sistema anexos
│
├── logs/                               # Logs sistema
│   └── debug_export_*.json            # Debug exports
│
├── public/                             # Aplicação web
│   ├── abertos.php                    # Chamados abertos
│   ├── add.php                        # Criar chamado
│   ├── add_atividade.php              # Adicionar atividade
│   ├── adicionar_anexos.php           # Upload anexos
│   ├── backup_manager.php             # Gerenciador backup
│   ├── dashboard.php                  # Dashboard analytics
│   ├── delete.php                     # Excluir chamado
│   ├── delete_atividade.php           # Excluir atividade
│   ├── dev_area.php                   # Área desenvolvimento
│   ├── download_anexo.php             # Download anexos
│   ├── edit.php                       # Editar chamado
│   ├── edit_atividade.php             # Editar atividade
│   ├── em_andamento.php               # Chamados andamento
│   ├── email_template.php             # Templates email
│   ├── email_template_simples.php     # Template simples
│   ├── excluir_anexo.php              # Excluir anexo
│   ├── fechados.php                   # Chamados fechados
│   ├── index.php                      # Dashboard principal
│   ├── login.php                      # Sistema login
│   ├── logout.php                     # Logout
│   ├── manage_levels.php              # Gestão níveis
│   ├── register_user.php              # Registro usuários
│   ├── user_manager.php               # Gestão usuários
│   ├── view.php                       # Visualizar chamado
│   │
│   ├── api/                           # APIs REST
│   │   ├── analytics.php              # API analytics
│   │   └── counts.php                 # API contadores
│   │
│   └── images/                        # Recursos visuais
│       ├── favicon.png                # Ícone site
│       ├── logo-eluss.jpg             # Logo JPEG
│       ├── logo-eluss.png             # Logo PNG
│       └── wpp.jpg                    # WhatsApp icon
│
├── scripts/                            # Scripts automação
│   ├── atualizacao_incremental.ps1    # Atualização incremental
│   ├── atualizar_sistema_seguro.bat   # Atualização segura
│   ├── configurar_https_xampp.bat     # HTTPS XAMPP batch
│   ├── configurar_https_xampp.ps1     # HTTPS XAMPP PowerShell
│   ├── criar_pacote_producao.bat      # Build produção
│   ├── deploy_automatico.ps1          # Deploy automático
│   ├── deploy_ftp.ps1                 # Deploy FTP
│   ├── instalar_sistema_anexos.bat    # Instalação anexos
│   │
│   └── deploy_package/                # Pacote deploy
│       ├── .htaccess                  # Configuração Apache
│       └── README_INSTALACAO.txt      # Instruções instalação
│
├── src/                                # Classes PHP core
│   ├── AssetManager.php               # Gerenciamento assets
│   ├── Auth.php                       # Autenticação
│   ├── AuthMiddleware.php             # Middleware auth
│   ├── CacheManager.php               # Sistema cache
│   ├── Chamado.php                    # CRUD chamados
│   ├── ChamadoAnexo.php               # Gestão anexos
│   ├── ChamadoHistorico.php           # Histórico chamados
│   ├── DB.php                         # Conexão banco
│   ├── EmailTemplate.php              # Templates email
│   ├── EnvLoader.php                  # Carregamento env
│   ├── header.php                     # Header comum
│   ├── LevelManager.php               # Gestão níveis
│   ├── ProgressiveDisclosureUI.php    # UI otimizada
│   ├── SecurityHelper.php             # Helpers segurança
│   ├── SecurityValidator.php          # Validação segurança
│   ├── TemplatePersonalizado.php      # Templates custom
│   │
│   ├── analytics/                     # Módulo analytics
│   │   └── AnalyticsManager.php       # Gerenciador analytics
│   │
│   ├── components/                    # Componentes UI
│   │   ├── AnalyticsWidget.php        # Widget analytics
│   │   ├── ChamadoAnexosView.php      # Visualização anexos
│   │   ├── ChamadoDetailView.php      # Detalhes chamado
│   │   ├── ChamadoHistoricoView.php   # Histórico view
│   │   ├── ChamadosListView.php       # Lista chamados
│   │   ├── ChamadoViewController.php  # Controller view
│   │   └── HomePageView.php           # View homepage
│   │
│   └── templates/                     # Templates sistema
│       ├── ChamadosPageTemplate.php   # Template página
│       ├── ChamadoViewTemplate.php    # Template view
│       └── HomePageTemplate.php      # Template home
│
├── tools/                              # Ferramentas sistema
│   ├── check_levels_table.php         # Verificação níveis
│   ├── clear_cache.php                # Limpeza cache
│   ├── db_check.php                   # Verificação DB
│   ├── dev_actions.php                # Ações desenvolvimento
│   ├── dev_health.php                 # Health check
│   ├── security_check.php             # Verificação segurança
│   └── session_info.php               # Informações sessão
│
└── uploads/                            # Uploads sistema
    ├── .htaccess                      # Proteção Apache
    └── anexos/                        # Anexos chamados
        └── anexo_*.jpg|png|pdf        # Arquivos anexos
```

---

## Requisitos Técnicos

**Servidor**
- PHP 8.1+ com extensões: PDO, mysqli, mbstring, json, session, fileinfo
- MySQL 8.0+ ou MariaDB 10.4+
- Apache 2.4+ com mod_rewrite, mod_expires, mod_deflate
- 512MB RAM mínimo, 2GB recomendado
- 2GB espaço disco (logs + uploads)

**Cliente**
- Navegadores modernos: Chrome 90+, Firefox 88+, Edge 90+, Safari 14+
- JavaScript habilitado
- Resolução mínima: 1024x768
- Conexão internet estável

**Integração Email**
- SMTP configurado ou cliente email padrão
- Outlook 2016+ para integração .eml
- Suporte webmail para fallback

---

## Instalação e Configuração

**Configuração Rápida**
```bash
# 1. Clone repositório
git clone https://github.com/Du44rt3/ChamadoSystem.git
cd ChamadoSystem

# 2. Configure banco de dados
mysql -u root -p < database/install_sistema_completo.sql

# 3. Configure ambiente
cp .env.example .env
# Edite .env com suas configurações

# 4. Configure Apache virtual host ou inicie XAMPP
./iniciar_xampp.bat

# 5. Acesse sistema
# URL: http://localhost/chamados_system/public/
# Login: admin@sistema.com.br | Senha: admin123
```

**Configuração Avançada**
```php
// config/config.php - Configurações principais
define('DB_HOST', 'localhost');
define('DB_NAME', 'chamados_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Cache settings
define('CACHE_TTL', 300);        // 5 minutos
define('CACHE_ENABLED', true);

// Security settings
define('CSRF_TOKEN_TTL', 3600);  // 1 hora
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_TIME', 900);     // 15 minutos
```

---

## Funcionalidades do Sistema

**Gestão de Chamados**
- Workflow completo: Aberto → Em Andamento → Fechado
- Códigos únicos auto-gerados por projeto
- SLA automático baseado na gravidade
- Sistema de anexos com validação de segurança
- Histórico completo de atividades

**Sistema de Usuários**
```
┌─────────────────┬──────────────────────────────────────┐
│ Nível           │ Permissões                           │
├─────────────────┼──────────────────────────────────────┤
│ Usuario         │ Visualizar chamados próprios        │
│ Tecnico         │ Gerenciar chamados, atividades      │
│ Admin           │ Gestão completa, analytics, users   │
│ Desenvolvedor   │ Acesso total + área desenvolvimento │
└─────────────────┴──────────────────────────────────────┘
```

**Analytics Dashboard**
- Contadores em tempo real por status
- MTTR (Mean Time To Resolution)
- SLA Compliance percentual
- Gráficos de evolução temporal
- Distribuição por setor e gravidade
- Exportação para PDF/Excel/CSV

**Sistema de Cache**
- Cache inteligente com TTL configurável
- Invalidação automática em mudanças
- Redução de 80% nas consultas ao banco
- Interface de gestão manual
- Debug mode para desenvolvimento

**Segurança Corporativa**
- Autenticação ARGON2ID com salt
- Proteção CSRF em todos os formulários
- Sanitização XSS completa
- Rate limiting contra brute force
- Logs de segurança detalhados
- Middleware de autenticação

---

## APIs e Integrações

**APIs REST**
```
GET  /public/api/analytics.php?type=header    # Métricas dashboard
GET  /public/api/counts.php                   # Contadores simples
POST /tools/dev_actions.php                   # Ações desenvolvimento
```

**Integração Email**
- Templates automáticos para abertura/andamento/fechamento
- Integração Outlook Classic via arquivos .eml
- Fallback para webmail e clientes padrão
- Personalização completa de templates
- Cópia inteligente com instruções visuais

**Backup e Deploy**
```powershell
# Deploy automático produção
scripts/deploy_automatico.ps1 -Environment Production

# Backup completo sistema
public/backup_manager.php

# Configuração HTTPS XAMPP
scripts/configurar_https_xampp.ps1
```

---

## Desenvolvimento e Debug

**Área de Desenvolvimento**
- URL: `/public/dev_area.php`
- Health check sistema
- Verificação banco de dados
- Informações de sessão
- Logs em tempo real
- Ferramentas de debug

**Ferramentas Disponíveis**
- `tools/clear_cache.php` - Limpeza cache
- `tools/security_check.php` - Verificação segurança
- `tools/db_check.php` - Status banco dados
- `tools/check_levels_table.php` - Diagnóstico níveis

**Debug Mode**
```php
// Habilitar debug em config/config.php
define('APP_DEBUG', true);
define('CACHE_DEBUG', true);
```

---

## Performance e Otimização

**Métricas de Performance**
- Page load time: < 2 segundos
- Time to interactive: < 3 segundos
- Cache hit rate: > 85%
- Database queries: < 10 por página

**Otimizações Implementadas**
- Sistema de cache com invalidação inteligente
- Progressive Disclosure UI
- Lazy loading de componentes
- CSS/JS minificado
- GZIP compression
- Browser cache otimizado

**Monitoramento**
- Logs de performance automáticos
- Métricas de cache em tempo real
- Monitoramento de queries SQL
- Alertas de performance degradada

---

## Segurança e Compliance

**Proteções Implementadas**
- SQL Injection: Prepared statements obrigatórios
- XSS: Sanitização em todas as entradas/saídas
- CSRF: Tokens únicos por sessão
- Session Hijacking: Regeneração automática de ID
- File Upload: Validação MIME type e extensão
- Brute Force: Rate limiting com lockout

**Compliance Standards**
- LGPD: Tratamento adequado de dados pessoais
- ISO 27001: Controles de segurança implementados
- OWASP Top 10: Mitigação completa de vulnerabilidades

**Auditoria e Logs**
- Log completo de todas as ações
- Tracking de tentativas de login
- Auditoria de mudanças em chamados
- Monitoramento de atividades suspeitas

---

## Documentação Técnica

**Guias Disponíveis**
- `docs/INSTALACAO_SISTEMA.md` - Instalação completa
- `docs/DEPLOY_PRODUCAO_GUIA.md` - Deploy produção
- `docs/SECURITY_IMPROVEMENTS.md` - Melhorias segurança
- `docs/OUTLOOK_CLASSIC_SOLUCAO.md` - Integração Outlook
- `docs/DASHBOARD_ANALYTICS_README.md` - Analytics avançado

**Recursos Técnicos**
- Documentação API completa
- Exemplos de integração
- Scripts de automação
- Troubleshooting guides
- Best practices corporativas

---

## Suporte e Desenvolvimento

**Repositório**: [Du44rt3/ChamadoSystem](https://github.com/Du44rt3/ChamadoSystem)
**Licença**: MIT License
**Versão**: 2.0 Enterprise

**Para Suporte Técnico**
- Issues: Reporte bugs e solicitações
- Wiki: Documentação técnica detalhada
- Releases: Atualizações e changelog

---

```
  ╔══════════════════════════════════════════════════════════════╗
  ║                   ELUS FACILITIES                            ║
  ║               Sistema Corporativo v2.0                      ║
  ║                                                              ║
  ║  Desenvolvido para gestão empresarial de infraestrutura     ║
  ║  e facilities com padrões corporativos de segurança,        ║
  ║  performance e escalabilidade.                              ║
  ╚══════════════════════════════════════════════════════════════╝
```
