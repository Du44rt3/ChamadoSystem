@echo off
:: =================================================================
:: UTILITÁRIO PARA LIMPEZA DE CACHE HSTS (HTTPS)
:: =================================================================
::
:: Este script exibe instruções para resolver o problema de cache
:: de HTTPS no navegador, que força o acesso via https://
:: mesmo quando o servidor está configurado para http://.
::
:: Nenhuma ação automática é executada.
::
:: =================================================================

echo.
echo [INFO] GUIA RAPIDO PARA LIMPAR CACHE HSTS DO NAVEGADOR
echo.
echo O seu navegador provavelmente guardou a regra de que este site
echo so pode ser acessado com HTTPS (HSTS).
echo.
echo Siga os passos abaixo para o seu navegador:
echo.
echo -----------------------------------------------------------------
echo  PARA CHROME / EDGE
echo -----------------------------------------------------------------
echo 1. Abra uma nova aba e cole:
echo    - Chrome: chrome://net-internals/#hsts
echo    - Edge:   edge://net-internals/#hsts
echo.
echo 2. Em "Delete domain security policies", digite o dominio:
echo    (Ex: localhost ou 192.168.0.173)
echo.
echo 3. Clique em "Delete".
echo.
echo 4. Feche o site e tente acessar novamente com http://
echo.
echo -----------------------------------------------------------------
echo  PARA FIREFOX
echo -----------------------------------------------------------------
echo 1. Pressione Ctrl + Shift + H para abrir o historico.
echo.
echo 2. Encontre o site (ex: 192.168.0.173).
echo.
echo 3. Clique com o botao direito e selecione "Esquecer este site".
echo    (Isso remove todo o cache e dados apenas para este site).
echo.
echo 4. Reinicie o Firefox.
echo.
echo -----------------------------------------------------------------
echo.
echo Para mais detalhes, consulte o arquivo:
echo docs/HTTPS_CACHE_BROWSER.md
echo.

pause
