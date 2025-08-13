# Como Resolver o Problema de Cache HTTPS (HSTS) no Navegador

## O Problema

Você desativou o HTTPS no seu ambiente de desenvolvimento (XAMPP), mas o navegador continua forçando o acesso via `https://`, resultando em erros de conexão. Isso acontece porque o navegador armazenou em cache uma política de segurança chamada **HSTS (HTTP Strict Transport Security)**.

Quando o HSTS está ativo, o navegador "lembra" que um site deve ser acessado *apenas* por HTTPS por um determinado período e recusa qualquer tentativa de conexão via HTTP.

**Sintomas Comuns:**
- O site funciona em aba anônima, mas não na normal.
- O endereço `http://` é automaticamente trocado por `https://`.
- Aparece um erro de "Conexão Segura Falhou" ou "Sua conexão não é particular".

---

## Solução Rápida: Limpar o Cache HSTS

Para resolver, você precisa instruir o navegador a "esquecer" a política HSTS para o seu domínio de desenvolvimento (`localhost`, `192.168.0.173`, etc.).

### Para Google Chrome e Microsoft Edge

1.  Abra uma nova aba no seu navegador (Chrome ou Edge).
2.  Copie e cole o endereço correspondente:
    -   **Chrome:** `chrome://net-internals/#hsts`
    -   **Edge:** `edge://net-internals/#hsts`
3.  Na seção **"Delete domain security policies"**, digite o domínio que você usa para desenvolver. Por exemplo:
    -   `192.168.0.173`
    -   `localhost`
4.  Clique no botão **"Delete"**.
5.  Feche todas as abas do site e tente acessá-lo novamente com `http://`.

### Para Mozilla Firefox

O Firefox não possui uma página dedicada para isso. O processo é um pouco diferente:

1.  Pressione `Ctrl + Shift + H` para abrir a barra lateral de histórico.
2.  Use a barra de pesquisa no topo para encontrar o seu site (ex: `192.168.0.173`).
3.  Clique com o botão direito do mouse sobre o endereço do site na lista.
4.  Selecione a opção **"Esquecer este site"**.
    -   **Atenção:** Isso irá remover todo o histórico, cookies, cache e senhas salvas *apenas para este site*.
5.  Reinicie o Firefox e tente acessar novamente.

---

## Solução Permanente: Script Utilitário

Para facilitar esse processo no futuro, foi criado um script em `scripts/limpar_cache_hsts.bat`. Basta executá-lo para ver as instruções rapidamente.

Este script não faz nada automático, apenas exibe as mesmas instruções deste guia para sua conveniência.
