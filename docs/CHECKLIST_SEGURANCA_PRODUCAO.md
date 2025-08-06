# 🔒 CHECKLIST DE SEGURANÇA PARA PRODUÇÃO

## ✅ PRÉ-DEPLOY (ANTES DE SUBIR)

### **🔧 Arquivos e Configurações**
- [ ] Arquivo `config/config.php` configurado com dados reais do servidor
- [ ] Debug mode = `false` em todas as configurações
- [ ] Senhas padrão alteradas para senhas fortes
- [ ] Arquivo `.htaccess` incluído na raiz
- [ ] Arquivos desnecessários removidos (tests, _old, _dev, etc.)
- [ ] Script SQL único `install_sistema_completo.sql` validado

### **🗄️ Banco de Dados**
- [ ] Banco criado com nome apropriado para produção
- [ ] Usuário MySQL específico criado (não usar root)
- [ ] Permissões mínimas concedidas ao usuário
- [ ] Script SQL importado com sucesso
- [ ] Dados de teste removidos ou validados
- [ ] Backup inicial do banco criado

---

## ✅ PÓS-DEPLOY (DEPOIS DE SUBIR)

### **🌐 Acesso e Funcionamento**
- [ ] Sistema acessível via URL de produção
- [ ] Login funcionando com usuários padrão
- [ ] Redirecionamento de HTTP para HTTPS (se aplicável)
- [ ] Páginas carregando corretamente
- [ ] CSS e JavaScript funcionando

### **🧪 Testes Funcionais**
- [ ] Criar novo chamado
- [ ] Editar chamado existente
- [ ] Adicionar atividade ao histórico
- [ ] Alterar status dos chamados
- [ ] Gestão de usuários funcionando
- [ ] Diferentes níveis de acesso funcionando

### **🔒 Segurança Validada**
- [ ] Arquivos `.sql` não acessíveis via browser
- [ ] Arquivo `config.php` não acessível via browser
- [ ] Listagem de diretórios bloqueada
- [ ] Arquivos sensíveis protegidos
- [ ] Headers de segurança aplicados
- [ ] Sem erros PHP visíveis

---

## ⚠️ VULNERABILIDADES VERIFICADAS

### **🔍 Testes de Segurança**
- [ ] Acesso direto a `config/config.php` bloqueado
- [ ] Acesso direto a `database/*.sql` bloqueado
- [ ] Não é possível acessar o sistema sem login
- [ ] Sessões expirando corretamente
- [ ] Tentativas de login sendo limitadas
- [ ] SQL injection prevenida (PDO com bind)

### **📁 Proteção de Arquivos**
- [ ] `.htaccess` funcionando
- [ ] Diretórios não listáveis
- [ ] Arquivos de backup protegidos
- [ ] Logs não acessíveis externamente
- [ ] Código fonte não exposto

---

## 🛠️ CONFIGURAÇÕES DO SERVIDOR

### **🔧 PHP (Recomendado)**
```ini
display_errors = Off
log_errors = On
expose_php = Off
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 300
session.cookie_httponly = 1
session.cookie_secure = 1  # Se HTTPS disponível
```

### **🌐 Apache (.htaccess)**
```apache
# Já incluído no pacote de deploy
Options -Indexes
<Files "config.php">
    Order allow,deny
    Deny from all
</Files>
```

### **🔒 MySQL (Recomendado)**
- Usuário específico para o sistema
- Permissões: SELECT, INSERT, UPDATE, DELETE
- Sem permissões de DROP, CREATE, ALTER (exceto manutenção)
- Backup automático configurado

---

## 📊 MONITORAMENTO E MANUTENÇÃO

### **📈 Logs para Monitorar**
- [ ] Logs de erro do Apache/Nginx
- [ ] Logs de erro do PHP
- [ ] Logs de acesso do sistema
- [ ] Logs de tentativas de login
- [ ] Logs de alterações no banco

### **🔄 Manutenção Regular**
- [ ] Backup diário do banco configurado
- [ ] Atualizações de segurança do servidor
- [ ] Monitoramento de espaço em disco
- [ ] Verificação de integridade dos arquivos
- [ ] Análise de logs de segurança

---

## 🆘 PLANO DE CONTINGÊNCIA

### **📋 Em Caso de Problemas**
1. **Sistema Fora do Ar:**
   - Verificar logs de erro
   - Validar conexão com banco
   - Confirmar permissões de arquivos
   - Testar `public/test_connection.php`

2. **Banco de Dados Corrompido:**
   - Restaurar do backup mais recente
   - Verificar integridade das tabelas
   - Re-importar `install_sistema_completo.sql`

3. **Invasão de Segurança:**
   - Alterar todas as senhas
   - Verificar arquivos modificados
   - Analisar logs de acesso
   - Atualizar arquivo `.htaccess`

### **📞 Contatos de Emergência**
- Suporte do hosting: _____________
- Administrador do sistema: _______
- Backup de acesso FTP/SSH: ______

---

## 🎯 CREDENCIAIS PADRÃO (ALTERAR!)

### **👥 Usuários do Sistema**
```
Desenvolvedor:
- Usuário: Renan.duarte
- Senha: 123456 ⚠️ ALTERAR!

Administradores:
- Usuário: Eduardo.lima
- Senha: 123456 ⚠️ ALTERAR!
- Usuário: Jorge_gtz  
- Senha: 123456 ⚠️ ALTERAR!
```

### **🔑 Ações Obrigatórias Pós-Deploy**
1. **Alterar senhas padrão**
2. **Criar usuários de produção**
3. **Remover usuários de teste**
4. **Configurar email do sistema**
5. **Testar todas as funcionalidades**

---

## ✅ ASSINATURA DE DEPLOY

**Sistema validado por:** ___________________
**Data do deploy:** ________________________
**Versão implantada:** Sistema de Chamados v1.0
**Ambiente:** Produção
**Status:** [ ] Aprovado [ ] Pendente [ ] Rejeitado

**Observações:**
_________________________________________________
_________________________________________________
_________________________________________________

---

## 🚀 STATUS FINAL

- [ ] **TODOS OS ITENS VERIFICADOS**
- [ ] **SISTEMA PRONTO PARA PRODUÇÃO**
- [ ] **EQUIPE TREINADA**
- [ ] **BACKUP CONFIGURADO**
- [ ] **MONITORAMENTO ATIVO**

**🎉 DEPLOY APROVADO PARA PRODUÇÃO!**
