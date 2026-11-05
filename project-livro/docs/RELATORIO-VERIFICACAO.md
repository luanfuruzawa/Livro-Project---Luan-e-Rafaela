# Verificação funcional

Branch: `adicionar-logs-e-verificacao`.

## Resultados

| Verificação | Resultado |
| --- | --- |
| Suíte normal | 34 testes aprovados, 236 verificações. |
| Testes específicos dos logs | 17 aprovados, 132 verificações. |
| Diagnóstico de problemas conhecidos | 12 cenários reproduzidos em execução separada. |
| Build do frontend | Concluído. |
| Sintaxe PHP e compilação das views | Sem erros. |
| Navegação | Login, catálogo, pesquisa, paginação, carrinho, pedido e logout verificados. |

Os testes utilizaram SQLite e dados temporários. A navegação utilizou um banco de teste separado. Não foram alterados o `.env` ou o banco MySQL da aplicação.

## Comportamentos observados

- O banco `project_livro` não existe na conexão MySQL local configurada.
- As rotas de criação, edição e exclusão de livros verificam login, mas não exigem perfil de administrador.
- O carrinho aceita quantidades negativas e quantidades acima do estoque.
- A finalização do pedido não valida nem reduz o estoque.
- Um livro excluído após ser adicionado ao carrinho pode causar erro 500 na finalização.
- Erros de login ou validação podem manter o campo `senha` em `_old_input` na sessão.
- Preço e estoque negativos são aceitos na edição de livros.
- O layout referencia `public/assets/base.css`, que não existe.
- Os botões dos cards podem se sobrepor em janelas estreitas.

Esses comportamentos permanecem no código e devem ser considerados conforme o fluxo escolhido para a demonstração. Os testes em `tests/Audit` documentam as regressões e são executados separadamente.

## Dependências

Na consulta realizada, `npm audit` retornou 4 pacotes afetados na árvore de desenvolvimento; a consulta com `--omit=dev` não retornou avisos. `composer audit` retornou 13 avisos em 6 pacotes. Não foram realizadas atualizações de versão.

## Alterações

- Canal diário de logs de atividade.
- Identificador de requisição e resumo HTTP.
- Eventos de autenticação, livros, carrinho, pedidos e exceções.
- Testes dos fluxos e dos logs.

As regras de negócio listadas acima não foram alteradas. As capturas e saídas detalhadas dos testes ficam na pasta local de relatórios, fora do repositório.

## Execução

```powershell
php -d extension=pdo_sqlite vendor/bin/pest --compact
php -d extension=pdo_sqlite vendor/bin/pest tests/Audit --compact
```

O segundo comando reproduz os problemas conhecidos e retorna falhas enquanto eles permanecerem no código. A opção `-d extension=pdo_sqlite` é necessária apenas quando o driver não estiver habilitado no PHP.

Configuração e consulta dos logs: [Logs de atividade](REANALISE-LOGS.md).
