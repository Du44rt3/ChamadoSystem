# ELUS Facilities - Sistema de Chamados

Sistema de gerenciamento de chamados e facilities para a empresa ELUS.

## Estrutura do Projeto

```
├── config/             # Configurações do sistema
│   └── config.php      # Configuração principal
├── css/                # Arquivos de estilo
│   └── style.css       # Estilos principais
├── database/           # Scripts de banco de dados
│   ├── *.sql          # Scripts SQL de instalação e atualização
│   └── *.php          # Scripts PHP para migração
├── docs/               # Documentação do projeto
│   ├── *.md           # Guias e documentação
│   └── README.md      # Documentação específica
├── logs/               # Arquivos de log do sistema
├── public/             # Arquivos públicos da aplicação
│   ├── index.php      # Página inicial
│   ├── login.php      # Sistema de login
│   ├── dev_area.php   # Área de desenvolvimento
│   └── *.php          # Demais páginas do sistema
├── scripts/            # Scripts de automação
│   ├── *.bat          # Scripts Windows
│   └── *.ps1          # Scripts PowerShell
├── src/                # Código fonte das classes
│   ├── Auth.php       # Sistema de autenticação
│   ├── DB.php         # Conexão com banco
│   └── *.php          # Demais classes
├── tests/              # Arquivos de teste e debug
│   ├── test_*.php     # Arquivos de teste
│   ├── debug_*.php    # Arquivos de debug
│   └── check_*.php    # Arquivos de verificação
└── tools/              # Ferramentas auxiliares
```

## Instalação

1. Execute o script `iniciar_xampp.bat` para inicializar o XAMPP
2. Configure o banco de dados executando os scripts em `database/`
3. Configure o arquivo `.env` baseado no `.env.example`
4. Acesse o sistema através de `public/index.php`

## Desenvolvimento

Para desenvolvedores, acesse a área de desenvolvimento em `public/dev_area.php` com privilégios de desenvolvedor.

### Testes

Todos os arquivos de teste estão organizados na pasta `tests/`. Execute-os através da área de desenvolvimento para maior segurança.

### Documentação

A documentação completa está disponível na pasta `docs/`.

## Segurança

- Sistema com autenticação robusta usando ARGON2ID
- Controle de acesso por níveis
- Proteção contra CSRF e XSS
- Logs de segurança implementados

## Suporte

Para suporte técnico, consulte a documentação em `docs/` ou entre em contato com a equipe de desenvolvimento.
