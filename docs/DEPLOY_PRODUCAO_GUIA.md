# 📦 DEPLOY PARA PRODUÇÃO - GUIA COMPLETO

## 🚀 Opções de Deploy (Escolha a melhor para seu caso)

### **OPÇÃO 1: Deploy Manual (Mais Simples)**
### **OPÇÃO 2: Deploy via FTP Automatizado**
### **OPÇÃO 3: Deploy via ZIP + Upload**

---

## 🎯 OPÇÃO 1: DEPLOY MANUAL (RECOMENDADO)

### **📋 Preparação Rápida:**

1. **Copie todos os arquivos** da pasta `chamados_system` para o servidor
2. **Configure o banco de dados** no servidor
3. **Ajuste as configurações** do sistema

### **📁 Estrutura para Copiar:**
```
📁 chamados_system/          → Copiar TUDO para o servidor
├── 📁 config/               → Configurações (IMPORTANTE!)
├── 📁 css/                  → Estilos
├── 📁 database/             → Scripts SQL
├── 📁 public/               → Páginas principais
├── 📁 src/                  → Classes PHP
├── 📁 scripts/              → Scripts utilitários
├── 📁 tools/                → Ferramentas de segurança
└── 📄 .htaccess            → Configurações Apache (se existir)
```

### **🔧 Passos no Servidor:**

#### **1. Upload dos Arquivos**
- Via **FTP/SFTP**: WinSCP, FileZilla, etc.
- Via **Painel de Controle**: cPanel, Plesk, etc.
- Via **SSH**: rsync, scp, etc.

#### **2. Configurar Banco de Dados**
```sql
-- No phpMyAdmin ou MySQL do servidor:
1. Criar banco: CREATE DATABASE chamados_db;
2. Importar: database/install_sistema_completo.sql
3. Verificar se importou corretamente
```

#### **3. Configurar config/config.php**
```php
// Ajustar para o servidor de produção:
$host = 'localhost';        // ou IP do servidor MySQL
$database = 'chamados_db';  // nome do banco no servidor
$username = 'seu_usuario';  // usuário MySQL do servidor
$password = 'sua_senha';    // senha MySQL do servidor
```

#### **4. Testar Acesso**
- Acesse: `http://seudominio.com/chamados_system`
- Login: `Renan.duarte` / Senha: `123456`

---

## 🔄 OPÇÃO 2: DEPLOY AUTOMATIZADO VIA FTP

### **📝 Configurações Necessárias:**
- **Servidor FTP/SFTP** do hosting
- **Credenciais** de acesso
- **Caminho** da pasta web no servidor

### **🤖 Script Automático** (será criado)

---

## 📦 OPÇÃO 3: DEPLOY VIA ZIP

### **📋 Preparação:**
1. **Criar ZIP** com todos os arquivos
2. **Upload do ZIP** para o servidor
3. **Extrair** no servidor
4. **Configurar** banco e config.php

---

## ⚙️ CONFIGURAÇÕES IMPORTANTES

### **🔒 Segurança em Produção:**

#### **1. Arquivo .htaccess (Proteção)**
```apache
# Bloquear acesso direto a arquivos sensíveis
<Files "config.php">
    Order allow,deny
    Deny from all
</Files>

<Files "*.sql">
    Order allow,deny
    Deny from all
</Files>

# Forçar HTTPS (se disponível)
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

#### **2. Configuração PHP (php.ini ou .htaccess)**
```ini
# Configurações de segurança
expose_php = Off
display_errors = Off
log_errors = On
upload_max_filesize = 10M
max_execution_time = 300
```

### **🗄️ Banco de Dados:**

#### **Credenciais Seguras:**
- Use **senhas fortes** para o MySQL
- Crie **usuário específico** para o banco
- Conceda apenas **permissões necessárias**

#### **Backup Automático:**
- Configure **backup diário** do banco
- Use **ferramentas do hosting** ou cron jobs

---

## 🧪 TESTES APÓS DEPLOY

### **✅ Checklist de Validação:**

1. **Acesso ao Sistema**
   - [ ] Página inicial carrega
   - [ ] Login funciona
   - [ ] Menu aparece corretamente

2. **Funcionalidades Principais**
   - [ ] Criar chamado
   - [ ] Editar chamado
   - [ ] Histórico de atividades
   - [ ] Gestão de usuários

3. **Segurança**
   - [ ] Não há erros PHP visíveis
   - [ ] Arquivos config.php não acessíveis
   - [ ] Login obrigatório funcionando

4. **Performance**
   - [ ] Páginas carregam rapidamente
   - [ ] Banco responde bem
   - [ ] CSS e JS carregam

---

## 🆘 SOLUÇÃO DE PROBLEMAS

### **❌ Erro de Conexão com Banco:**
- Verificar credenciais em `config/config.php`
- Confirmar se banco foi criado
- Testar conexão com `public/test_connection.php`

### **❌ Página em Branco:**
- Ativar `display_errors = On` temporariamente
- Verificar logs de erro do servidor
- Confirmar permissões de arquivos

### **❌ CSS/Estilos não Carregam:**
- Verificar caminho relativo dos arquivos
- Confirmar upload da pasta `css/`
- Verificar permissões dos arquivos

### **❌ Login não Funciona:**
- Verificar se tabela `usuarios` foi criada
- Confirmar se dados foram inseridos
- Testar com usuário padrão: `Renan.duarte`

---

## 📞 CONTATOS DE SUPORTE

### **🔧 Para Problemas Técnicos:**
- Verificar logs do servidor
- Contatar suporte do hosting
- Revisar documentação do painel

### **💾 Para Problemas de Banco:**
- Usar phpMyAdmin do servidor
- Verificar privilégios do usuário
- Revisar script SQL de instalação

---

## 🎯 PRÓXIMOS PASSOS

**Escolha sua opção preferida e me informe:**

1. **Manual**: "Quero fazer upload manual"
2. **Automatizado**: "Preciso de script FTP"
3. **ZIP**: "Prefiro criar um pacote ZIP"

**Também me informe:**
- Tipo de hosting (cPanel, Plesk, VPS, etc.)
- Se tem acesso FTP/SSH
- Se precisa de ajuda com configurações específicas

**🚀 Vamos fazer seu sistema rodar em produção!**
