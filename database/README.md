# Sistema de Banco de Dados - ELUS Facilities

## 📁 Estrutura Organizada

### `install_elus_complete.sql`
**Script principal de instalação** - Use este arquivo para instalar o sistema completo!

- ✅ Todas as tabelas essenciais
- ✅ Triggers automáticos
- ✅ Views analíticas
- ✅ Dados iniciais
- ✅ Dashboard analytics
- ✅ Sistema de níveis avançado
- ✅ Configuração limpa e otimizada

---

### 📂 `legacy/`
Arquivos SQL antigos mantidos para referência:
- `anexos_images.sql` - Sistema de anexos (incluído no principal)
- `chamados_db.sql` - Estrutura antiga (substituída)
- `dashboard_analytics_structure.sql` - Analytics antigo (incluído no principal)
- `install_sistema_completo.sql` - Instalação antiga (substituída)
- `fix_triggers.sql` - Correções de triggers (aplicadas)
- `fix_trigger_conflict.sql` - Resolução de conflitos (aplicada)

### 📂 `utilities/`
Utilitários PHP para manutenção:
- `atualizar_senhas_usuarios.php` - Atualização de senhas
- `gerar_hashes_senhas.php` - Geração de hashes
- `migrate_passwords.php` - Migração de senhas
- `update_db.php` - Atualizações do banco

---

## 🚀 Como Usar

### Instalação Limpa
```bash
# 1. Acesse phpMyAdmin ou cliente MySQL
# 2. Execute apenas o arquivo:
install_elus_complete.sql
```

### Credenciais Padrão
```
Usuário: admin
Senha: admin123
```

⚠️ **ALTERE A SENHA IMEDIATAMENTE EM PRODUÇÃO!**

---

## 📊 Funcionalidades Incluídas

### Core System
- [x] Sistema completo de chamados
- [x] Controle de usuários e níveis
- [x] Sistema de anexos de imagens
- [x] Histórico detalhado de atividades

### Analytics & Dashboard
- [x] Métricas pré-calculadas (MTTR, SLA, FCR)
- [x] Cache inteligente de relatórios
- [x] Dashboard configurável por usuário
- [x] Views analíticas otimizadas

### Automação
- [x] Triggers automáticos para histórico
- [x] Sistema de alertas
- [x] Templates de email
- [x] Configurações flexíveis

---

## 🔧 Próximos Passos

1. **Instalar** - Execute `install_elus_complete.sql`
2. **Configurar** - Acesse o sistema e altere a senha
3. **Personalizar** - Configure templates e níveis
4. **Testar** - Crie chamados de teste
5. **Produção** - Configure HTTPS e backup

---

*Sistema limpo e otimizado - Pronto para produção!*
