# ===================================================================
# GUIA: CONFIGURAR NAVEGADOR PARA CERTIFICADO AUTOASSINADO
# Sistema ELUS - Desenvolvimento Local
# ===================================================================

## 🔷 GOOGLE CHROME / MICROSOFT EDGE

### Método 1 - Aceitar uma vez:
1. Na tela "Sua conexão não é particular"
2. Clique em "Avançado"
3. Clique em "Prosseguir para [IP] (não seguro)"

### Método 2 - Permitir localhost permanente:
1. Abra nova aba
2. Digite: chrome://flags/#allow-insecure-localhost
3. Altere para "Enabled"
4. Clique em "Relaunch"

### Método 3 - Adicionar exceção de certificado:
1. Configurações > Privacidade e segurança > Segurança
2. Gerenciar certificados > Autoridades > Importar
3. Navegue até: C:\xampp\apache\conf\ssl.crt\server.crt
4. Marque "Confiar neste certificado"

## 🦊 MOZILLA FIREFOX

### Método 1 - Aceitar uma vez:
1. Na tela "Aviso: risco potencial de segurança"
2. Clique em "Avançado"
3. Clique em "Aceitar o risco e continuar"

### Método 2 - Configuração permanente:
1. Digite na barra: about:config
2. Aceite o aviso de risco
3. Procure: security.tls.insecure_fallback_hosts
4. Adicione: localhost,127.0.0.1,192.168.0.173

### Método 3 - Importar certificado:
1. Configurações > Privacidade e Segurança
2. Certificados > Ver certificados
3. Autoridades > Importar
4. Selecione: C:\xampp\apache\conf\ssl.crt\server.crt

## 🛡️ KASPERSKY ENDPOINT SECURITY

### Desabilitar verificação HTTPS temporariamente:
1. Abra Kaspersky Endpoint Security
2. Vá em "Configurações"
3. "Proteção da Web" > "Configurações"
4. Desmarque "Verificar conexões criptografadas"
5. Ou adicione 192.168.0.173 às exceções

### Adicionar exceção:
1. Proteção da Web > Configurações avançadas
2. Exceções > Adicionar
3. URL: https://192.168.0.173/*
4. Tipo: "Não verificar"

## 🌐 SAFARI (Mac)

### Aceitar certificado:
1. Na tela de aviso, clique em "Mostrar detalhes"
2. Clique em "visitar este site"
3. Digite senha do sistema quando solicitado

## ⚡ SOLUÇÃO RÁPIDA PARA TODOS

### Usar HTTP em desenvolvimento:
- Acesse: http://192.168.0.173/chamados_system/public/
- Sem certificado, sem aviso, funciona imediatamente

### URLs de teste disponíveis:
- HTTP: http://localhost/chamados_system/public/
- HTTP: http://127.0.0.1/chamados_system/public/
- HTTP: http://192.168.0.173/chamados_system/public/
- HTTPS: https://localhost/chamados_system/public/ (aceitar certificado)

## 🎯 RECOMENDAÇÃO

Para DESENVOLVIMENTO:
- Use HTTP (sem complicações)
- Teste HTTPS apenas quando necessário

Para PRODUÇÃO:
- Configure certificado SSL válido
- Use autoridade certificadora reconhecida

## 🔧 TESTE SE FUNCIONOU

Após configurar, teste:
1. https://192.168.0.173/chamados_system/tools/debug/https_test.php
2. Deve mostrar "✅ CONEXÃO SEGURA (HTTPS)"
3. Sem avisos de segurança
