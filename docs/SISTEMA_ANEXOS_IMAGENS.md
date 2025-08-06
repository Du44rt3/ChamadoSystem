# Sistema de Anexos de Imagens

## Visão Geral

O Sistema de Chamados agora conta com funcionalidade completa para anexar imagens aos chamados. Este sistema permite que usuários façam upload, visualizem, baixem e gerenciem anexos de imagem de forma segura e intuitiva.

## Funcionalidades Implementadas

### 1. Upload de Imagens
- **Múltiplos arquivos**: Possibilidade de enviar várias imagens de uma só vez
- **Tipos aceitos**: JPG, JPEG, PNG, GIF, WEBP, BMP
- **Tamanho máximo**: 5MB por arquivo
- **Validação rigorosa**: Verificação de tipo MIME, extensão e validação real de imagem

### 2. Visualização e Galeria
- **Galeria responsiva**: Exibição em grid responsivo na página do chamado
- **Preview em miniatura**: Thumbnails de 200px de altura com crop automático
- **Modal de visualização**: Clique na imagem para ver em tamanho completo
- **Informações detalhadas**: Nome, tamanho, data de upload e usuário

### 3. Gerenciamento de Anexos
- **Download**: Botão para baixar o arquivo original
- **Exclusão segura**: Confirmação antes de excluir com modal
- **Controle de permissões**: Apenas usuários autenticados podem gerenciar anexos

### 4. Segurança
- **Validação rigorosa**: Múltiplas camadas de validação de arquivo
- **Diretório protegido**: `.htaccess` impede execução de scripts
- **Nomes únicos**: Arquivos renomeados com hash único para evitar conflitos
- **Autenticação**: Todas as operações requerem login

### 5. Histórico Automático
- **Log de atividades**: Registro automático no histórico do chamado
- **Triggers de banco**: Inserção e exclusão são registradas automaticamente
- **Rastreabilidade**: Identificação do usuário que fez upload

## Estrutura de Arquivos

### Backend
```
src/
├── ChamadoAnexo.php          # Classe principal de gerenciamento
public/
├── adicionar_anexos.php      # Página para adicionar anexos
├── download_anexo.php        # Script para download de arquivos
├── excluir_anexo.php        # Script para exclusão de anexos
uploads/
├── .htaccess                # Proteção de segurança
├── anexos/                  # Diretório de armazenamento
database/
├── anexos_images.sql        # Script de criação da tabela
```

### Banco de Dados
```sql
-- Tabela principal
chamado_anexos:
- id (PK)
- chamado_id (FK)
- nome_original
- nome_arquivo
- caminho_arquivo
- tipo_mime
- tamanho_arquivo
- data_upload
- usuario_upload

-- Triggers automáticos
- after_anexo_insert
- after_anexo_delete
```

## Como Usar

### 1. Adicionando Anexos ao Criar Chamado
1. Na página "Novo Chamado" (`add.php`)
2. Preencha os dados normais do chamado
3. Na seção "Anexar Imagens", clique em "Escolher arquivos"
4. Selecione uma ou múltiplas imagens
5. Visualize o preview das imagens selecionadas
6. Clique em "Criar Chamado"

### 2. Adicionando Anexos a Chamado Existente
1. Na página de visualização do chamado (`view.php`)
2. Na seção "Anexos de Imagem", clique em "Adicionar Anexos"
3. Selecione as imagens desejadas
4. Visualize o preview
5. Clique em "Enviar Anexos"

### 3. Visualizando Anexos
1. Na página do chamado, role até a seção "Anexos de Imagem"
2. Clique em qualquer imagem para ver em tamanho completo
3. Use os botões de ação para:
   - 👁️ **Ver**: Abrir imagem em nova aba
   - 📥 **Download**: Baixar arquivo original
   - 🗑️ **Excluir**: Remover anexo (com confirmação)

## Validações e Limitações

### Tipos de Arquivo Aceitos
- ✅ JPG/JPEG
- ✅ PNG
- ✅ GIF
- ✅ WEBP
- ✅ BMP
- ❌ PDF, DOC, TXT (não aceitos)

### Validações de Segurança
1. **Tipo MIME**: Verificação do tipo real do arquivo
2. **Extensão**: Validação da extensão do arquivo
3. **Conteúdo**: Verificação se é realmente uma imagem usando `getimagesize()`
4. **Tamanho**: Limite de 5MB por arquivo
5. **Upload**: Verificação de erros de upload

### Limitações
- Apenas imagens são aceitas (por segurança)
- Tamanho máximo de 5MB por arquivo
- Número de arquivos limitado pela configuração do PHP
- Requer JavaScript habilitado para preview

## Manutenção

### Limpeza de Arquivos Órfãos
Para limpar arquivos não utilizados:
```sql
-- Buscar anexos sem chamado associado
SELECT * FROM chamado_anexos ca 
LEFT JOIN chamados c ON ca.chamado_id = c.id 
WHERE c.id IS NULL;
```

### Verificação de Integridade
Execute o arquivo de teste:
```
/tests/test_anexos_system.php
```

### Backup dos Anexos
Inclua o diretório `uploads/anexos/` no backup regular do sistema.

## Tecnologias Utilizadas

- **PHP 7.4+**: Backend e validações
- **MySQL**: Armazenamento de metadados
- **Bootstrap 5**: Interface responsiva
- **JavaScript**: Preview e interações
- **Font Awesome**: Ícones
- **PDO**: Acesso seguro ao banco

## Próximas Melhorias Sugeridas

1. **Redimensionamento automático**: Criar thumbnails otimizados
2. **Mais tipos de arquivo**: Suporte a PDF para documentos
3. **Compressão**: Otimização automática de imagens grandes
4. **Bulk operations**: Seleção múltipla para exclusão
5. **Visualizador avançado**: Zoom e navegação entre imagens
6. **Watermark**: Marca d'água nas imagens
7. **Versionamento**: Controle de versões de anexos

## Resolução de Problemas

### Erro: "Arquivo não pôde ser enviado"
- Verificar permissões do diretório `uploads/anexos/`
- Conferir configuração `upload_max_filesize` no PHP
- Validar se o arquivo é realmente uma imagem

### Erro: "Imagem não carrega"
- Verificar se o arquivo existe fisicamente
- Confirmar configuração do `.htaccess`
- Testar acesso direto ao arquivo

### Performance
- Para muitos anexos, considerar implementar paginação
- Otimizar carregamento com lazy loading
- Implementar cache de thumbnails
