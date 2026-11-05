# Logs de atividade

O canal `activity` registra eventos em `storage/logs/activity-AAAA-MM-DD.log`. Cada requisição recebe um UUID no cabeçalho `X-Request-ID`; os eventos relacionados usam o mesmo identificador.

## Configuração

Os valores padrão são `info` e retenção de até 14 arquivos diários. Para alterar:

```dotenv
ACTIVITY_LOG_LEVEL=info
ACTIVITY_LOG_DAYS=14
```

A data segue o timezone da aplicação, atualmente UTC. A limpeza de arquivos excedentes ocorre durante a rotação.

## Eventos

| Eventos | Conteúdo |
| --- | --- |
| `http.completed` | Método, rota, status HTTP e duração. |
| `auth.registered`, `auth.logged_in`, `auth.logged_out` | Operações de autenticação concluídas. |
| `auth.login_failed` | Credenciais não aceitas. |
| `book.created`, `book.updated`, `book.deleted` | Alterações de livros. |
| `cart.item_added`, `cart.item_updated`, `cart.item_removed`, `cart.cleared` | Alterações do carrinho. |
| `cart.book_unavailable` | Livro indisponível. |
| `order.empty_cart` | Tentativa de finalizar um carrinho vazio. |
| `order.completed` | Pedido salvo após o retorno da transação. |
| `application.exception` | Classe da exceção, arquivo e linha de origem. |

O contexto aceita apenas campos operacionais, como IDs, quantidade e total. Senhas, e-mails, dados completos de formulários, uploads, cookies e parâmetros da URL não são copiados para esse canal.

## Consulta no PowerShell

```powershell
$arquivoLog = Get-ChildItem .\storage\logs\activity-*.log |
    Sort-Object LastWriteTime -Descending |
    Select-Object -First 1

Get-Content -LiteralPath $arquivoLog.FullName -Tail 50
Get-Content -LiteralPath $arquivoLog.FullName -Tail 30 -Wait
Select-String -Path .\storage\logs\activity-*.log -Pattern 'ERROR|WARNING'
```

Use Ctrl+C para interromper a leitura contínua.

## Verificação

17 testes específicos passaram, com 132 verificações. A cobertura inclui gravação diária, retenção, níveis, correlação de IDs, operações de negócio, proteção dos campos e indisponibilidade do arquivo de log. A suíte normal completa passou com 34 testes e 236 verificações.

```powershell
php -d extension=pdo_sqlite vendor/bin/pest tests/Feature/ActivityLoggingTest.php tests/Feature/DailyActivityLoggingTest.php --compact
```

A opção de extensão é necessária apenas se o PDO SQLite não estiver habilitado.

## Observações

- Um redirecionamento HTTP 302 não confirma sucesso. Consulte o evento de negócio correspondente.
- Validações web podem retornar 302; validações JSON retornam 422 e são registradas como `WARNING`.
- O nível `warning` filtra os eventos informativos.
- Uma falha de escrita emite `Activity log unavailable: ...` pelo `error_log` do PHP e não interrompe a operação. Esse aviso é esperado nos testes de indisponibilidade.
- O `laravel.log` padrão mantém seus próprios registros técnicos, que podem incluir mensagens e SQL.
- Não foram realizados testes de carga ou concorrência entre processos.
