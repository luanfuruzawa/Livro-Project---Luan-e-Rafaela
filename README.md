# Projeto Livro — Luan e Rafaela
> O código Laravel está dentro da pasta `projeto-livro/`. Execute os comandos de instalação e do Artisan a partir dessa pasta.

## Instalação local

Abra um terminal na pasta em que deseja guardar o projeto e clone o repositório:
```
git clone https://github.com/luanfuruzawa/Livro-Project---Luan-e-Rafaela.git
cd Livro-Project---Luan-e-Rafaela/projeto-livro
```
Após ter o repositório localmente, é necessário ter as dependências do composer, por isso, se usa o comando: 
```
composer update
composer require spatie/laravel-activitylog
```
Crie o arquivo local de configuração `.env` a partir do env.example, copiando o arquivo e colando deixando o nome apenas '.env'
Gere a chave da aplicação:
```
php artisan key:generate
```

Crie o banco de dados com o Mysql utilizando, se quiser, os seeders para ter exemplos.

```
php artisan migrate --seed
```
Por fim, inicie o servidor local com a aplicação:
```
php artisan serve
```

Acesse o endereço mostrado pelo Artisan — normalmente <http://127.0.0.1:8000> — no navegador. Para encerrar o servidor, pressione `Ctrl+C` no terminal.

## Configuração do `.env`
A configuração recomendada para executar localmente com Mysql é:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nome_do_banco
DB_USERNAME=usuario_do_banco
DB_PASSWORD=sua_senha_do_banco
```
Substitua os valores de exemplo pelos dados do seu banco. Depois execute `php artisan config:clear` e `php artisan migrate`.

## Outros comandos úteis
Execute-os dentro de `projeto-livro/`:
| `composer install` | Instala as dependências PHP do projeto.                          |
| `php artisan serve` | Inicia o servidor de desenvolvimento local.                     |
| `php artisan migrate` | Cria/aplica as tabelas definidas nas migrations.              |
| `php artisan config:clear` | Limpa o cache de configuração após alterações no `.env`. |


## Estrutura e exemplos do projeto

Os arquivos principais da aplicação ficam organizados assim:
```
projeto-livro/
├── app/Http/Controllers/   # Lógica das telas e operações
├── app/Models/             # Modelos usados para acessar os dados
├── database/migrations/    # Definição das tabelas do banco
├── resources/views/        # Páginas e componentes Blade
├── routes/web.php          # Rotas web
├── .env                    # Modelo de configuração local
└── composer.json           # Requisitos e dependências PHP
```

Exemplos concretos:
- `routes/web.php` registra rotas de cadastro e login, catálogo de livros, carrinho, pedidos, cartões e histórico.
- `app/Http/Controllers/LivroController.php` implementa ações ligadas aos livros; `CarrinhoController.php` trata operações do carrinho e `PedidoController.php`, os pedidos.
- `resources/views/home_page.blade.php` e as pastas `resources/views/livro/`, `carrinho/`, `pedidos/` e `cartoes/` contêm as telas Blade correspondentes.
- `database/migrations/` define tabelas como `livros`, `carrinhos`, `pedidos`, itens de pedido e cartões. `php artisan migrate` cria essas tabelas no banco configurado.
- `composer.json` informa a versão do PHP e do Laravel esperadas e lista as dependências instaladas pelo Composer.

## Problemas comuns
- **`php` não é reconhecido:** o PHP não está instalado ou não está no `PATH`. O Composer depende de uma instalação de PHP; instale/configure o PHP 8.2+ e abra um novo terminal.
- **Erro de extensão SQLite ou `could not find driver`:** habilite a extensão SQLite/PDO SQLite na instalação do PHP usada no terminal.
- **Erro de `APP_KEY`:** confirme que `.env` existe e execute `php artisan key:generate`.
- **Erro ao abrir o banco:** confira se `database/database.sqlite` foi criado, se `DB_CONNECTION=sqlite` está definido e se o processo PHP tem permissão para gravar no arquivo e na pasta `database/`.
- **Mudanças no `.env` não aparecem:** execute `php artisan config:clear`.
- **Dependências incompatíveis:** confira `php -v` e use PHP 8.2 ou superior, conforme `composer.json`.
