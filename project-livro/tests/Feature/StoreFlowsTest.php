<?php

namespace Tests\Feature;

use App\Models\Carrinho;
use App\Models\Pedido;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Tests\Support\StoreTestCase;

class StoreFlowsTest extends StoreTestCase
{
    public function test_guest_is_redirected_from_all_private_pages(): void
    {
        foreach (['/home_page', '/novo-livro', '/pesquisar-livro', '/carrinho', '/pedidos', '/livros/1/editar'] as $url) {
            $this->get($url)->assertRedirect('/login');
        }
    }

    public function test_guest_cannot_change_store_data(): void
    {
        foreach (['/livros/guardar', '/carrinho/adicionar', '/carrinho/atualizar', '/carrinho/remover', '/carrinho/limpar', '/pedido/finalizar'] as $url) {
            $this->post($url)->assertRedirect('/login');
        }
        $this->put('/livros/1')->assertRedirect('/login');
        $this->delete('/livros/1')->assertRedirect('/login');
    }

    public function test_registration_creates_a_regular_user_and_session(): void
    {
        $this->post('/', ['email' => 'audit@example.test', 'usuario' => 'Audit', 'senha' => 'test-password'])
            ->assertRedirect('/home_page')->assertSessionHas('user.nivel_acesso', 'User');
        $this->assertDatabaseHas('users', ['email' => 'audit@example.test']);
        $this->assertNotSame('test-password', User::first()->password);
    }

    public function test_duplicate_email_is_rejected(): void
    {
        $user = User::factory()->create();
        $this->post('/', ['email' => $user->email, 'usuario' => 'Audit', 'senha' => 'test-password'])
            ->assertSessionHasErrors('email');
    }

    public function test_login_and_logout(): void
    {
        $user = User::factory()->create();
        $this->post('/login', ['email' => $user->email, 'senha' => 'password'])
            ->assertRedirect('/home_page')->assertSessionHas('user.id', $user->id);
        $this->get('/login')->assertRedirect('/home_page');
        $this->get('/logout')->assertRedirect('/login')->assertSessionMissing('user');
    }

    public function test_wrong_password_does_not_authenticate(): void
    {
        $user = User::factory()->create();
        $this->post('/login', ['email' => $user->email, 'senha' => 'incorrect-password'])
            ->assertSessionHas('login_error')->assertSessionMissing('user');
    }

    public function test_catalog_and_pagination(): void
    {
        $this->loginAs();
        $this->book(['titulo' => 'Primeiro livro']);
        for ($i = 2; $i <= 13; $i++) {
            $this->book(['titulo' => "Livro numero $i"]);
        }
        $this->get('/home_page')->assertOk()->assertSee('Primeiro livro')->assertDontSee('Livro numero 13');
        $this->get('/home_page?page=2')->assertOk()->assertSee('Livro numero 13');
    }

    public function test_search_combines_title_and_genre(): void
    {
        $this->loginAs();
        $this->book(['titulo' => 'Romance escolhido']);
        $this->book(['titulo' => 'Outro titulo', 'genero' => 'Terror']);
        $this->get('/pesquisar-livro?titulo-livro=escolhido&genero=Romance')
            ->assertOk()->assertSee('Romance escolhido')->assertDontSee('Outro titulo');
    }

    public function test_admin_can_create_edit_and_soft_delete_a_book(): void
    {
        $this->loginAs('Admin');
        $this->get('/novo-livro')->assertOk();
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aF9sAAAAASUVORK5CYII=');
        $this->post('/livros/guardar', [
            'titulo' => 'Novo livro teste', 'genero' => 'Romance', 'preco' => 25.50, 'estoque' => 3,
            'imagem' => UploadedFile::fake()->createWithContent('audit.png', $png),
        ])->assertRedirect('/home_page');
        $livro = \App\Models\Livro::firstOrFail();
        $this->assertFileExists(public_path('images/'.$livro->caminho_imagem));
        $this->get("/livros/{$livro->id}/editar")->assertOk();
        $this->put("/livros/{$livro->id}", [
            'titulo' => 'Titulo editado', 'genero' => 'Romance', 'preco' => 30, 'estoque' => 4,
        ])->assertRedirect('/home_page');
        $this->assertDatabaseHas('livros', ['titulo' => 'Titulo editado']);
        $this->delete("/livros/{$livro->id}")->assertRedirect();
        $this->assertSoftDeleted($livro);
    }

    public function test_book_validation_rejects_missing_fields(): void
    {
        $this->loginAs('Admin');
        $this->post('/livros/guardar', [])->assertSessionHasErrors(['titulo', 'genero', 'preco', 'estoque', 'imagem']);
    }

    public function test_cart_add_increment_update_remove_and_clear(): void
    {
        $user = $this->loginAs();
        $book = $this->book();
        $this->post('/carrinho/adicionar', ['livro_id' => $book->id, 'quantidade' => 1])->assertRedirect('/carrinho');
        $this->post('/carrinho/adicionar', ['livro_id' => $book->id, 'quantidade' => 1])->assertRedirect('/carrinho');
        $this->assertDatabaseHas('carrinhos', ['user_id' => $user->id, 'quantidade' => 2]);
        $this->get('/carrinho')->assertOk()->assertSee($book->titulo);
        $this->post('/carrinho/atualizar', ['livro_id' => $book->id, 'quantidade' => 3])->assertRedirect('/carrinho');
        $this->assertDatabaseHas('carrinhos', ['user_id' => $user->id, 'quantidade' => 3]);
        $this->post('/carrinho/remover', ['livro_id' => $book->id])->assertRedirect('/carrinho');
        $this->assertDatabaseCount('carrinhos', 0);
        $this->post('/carrinho/adicionar', ['livro_id' => $book->id]);
        $this->post('/carrinho/limpar')->assertRedirect('/carrinho');
        $this->get('/carrinho')->assertOk()->assertSee('Seu carrinho está vazio');
    }

    public function test_cart_changes_are_scoped_to_current_user(): void
    {
        $this->loginAs();
        $other = User::factory()->create();
        $book = $this->book();
        Carrinho::create(['user_id' => $other->id, 'livro_id' => $book->id, 'quantidade' => 2]);
        $this->post('/carrinho/atualizar', ['livro_id' => $book->id, 'quantidade' => 99]);
        $this->post('/carrinho/remover', ['livro_id' => $book->id]);
        $this->post('/carrinho/limpar');
        $this->assertDatabaseHas('carrinhos', ['user_id' => $other->id, 'quantidade' => 2]);
    }

    public function test_checkout_saves_total_items_and_clears_cart(): void
    {
        $user = $this->loginAs();
        $book = $this->book();
        Carrinho::create(['user_id' => $user->id, 'livro_id' => $book->id, 'quantidade' => 2]);
        $this->post('/pedido/finalizar')->assertRedirect('/pedidos')->assertSessionHas('success');
        $order = Pedido::firstOrFail();
        $this->assertEquals(51.00, $order->valor_total);
        $this->assertDatabaseHas('pedido_itens', ['pedido_id' => $order->id, 'quantidade' => 2, 'valor_total' => 51.00]);
        $this->assertDatabaseCount('carrinhos', 0);
        $this->get('/pedidos')->assertOk()->assertSee($book->titulo)->assertSee('51,00');
    }

    public function test_empty_checkout_does_not_create_order(): void
    {
        $this->loginAs();
        $this->from('/carrinho')->post('/pedido/finalizar')->assertRedirect('/carrinho')->assertSessionHas('error');
        $this->assertDatabaseCount('pedidos', 0);
    }

    public function test_order_history_is_private(): void
    {
        $this->loginAs();
        $other = User::factory()->create();
        Pedido::create(['user_id' => $other->id, 'valor_total' => 987.65]);
        $this->get('/pedidos')->assertOk()->assertDontSee('987,65');
    }
}
