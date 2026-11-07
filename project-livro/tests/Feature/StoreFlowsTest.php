<?php

namespace Tests\Feature;

use App\Models\Carrinho;
use App\Models\Cartao;
use App\Models\Pedido;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Tests\Support\StoreTestCase;

class StoreFlowsTest extends StoreTestCase
{
    public function test_guest_is_redirected_from_all_private_pages(): void
    {
        foreach (['/home_page', '/novo-livro', '/pesquisar-livro', '/carrinho', '/pedidos', '/cartoes', '/livros/1/editar'] as $url) {
            $this->get($url)->assertRedirect('/login');
        }
    }

    public function test_guest_cannot_change_store_data(): void
    {
        foreach (['/livros/guardar', '/carrinho/adicionar', '/carrinho/atualizar', '/carrinho/remover', '/carrinho/limpar', '/pedido/finalizar', '/cartoes'] as $url) {
            $this->post($url)->assertRedirect('/login');
        }
        $this->put('/livros/1')->assertRedirect('/login');
        $this->delete('/livros/1')->assertRedirect('/login');
        $cartao = Cartao::create([
            'user_id' => User::factory()->create()->id,
            'titular' => 'Pessoa Ficticia',
            'bandeira' => 'Visa',
            'ultimos_quatro' => '1234',
            'validade_mes' => now()->month,
            'validade_ano' => now()->year,
        ]);
        $this->delete("/cartoes/{$cartao->id}")->assertRedirect('/login');
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
        $cartao = $user->cartoes()->create([
            'titular' => 'Pessoa Ficticia',
            'bandeira' => 'Visa',
            'ultimos_quatro' => '1234',
            'validade_mes' => now()->month,
            'validade_ano' => now()->year,
        ]);
        $book = $this->book();
        Carrinho::create(['user_id' => $user->id, 'livro_id' => $book->id, 'quantidade' => 2]);
        $this->get('/carrinho')->assertOk()->assertSee('Visa')->assertSee('1234');
        $this->post('/pedido/finalizar', ['cartao_id' => $cartao->id])
            ->assertRedirect('/pedidos')->assertSessionHas('success');
        $order = Pedido::firstOrFail();
        $this->assertSame($cartao->id, $order->cartao_id);
        $this->assertEquals(51.00, $order->valor_total);
        $this->assertDatabaseHas('pedido_itens', ['pedido_id' => $order->id, 'quantidade' => 2, 'valor_total' => 51.00]);
        $this->assertDatabaseCount('carrinhos', 0);
        $this->get('/pedidos')->assertOk()->assertSee($book->titulo)->assertSee('51,00')->assertSee('1234');
    }

    public function test_checkout_requires_a_card_owned_by_the_logged_in_user(): void
    {
        $user = $this->loginAs();
        $otherUser = User::factory()->create();
        $otherCard = $otherUser->cartoes()->create([
            'titular' => 'Outra Pessoa',
            'bandeira' => 'Mastercard',
            'ultimos_quatro' => '5678',
            'validade_mes' => now()->month,
            'validade_ano' => now()->year,
        ]);
        $book = $this->book();
        Carrinho::create(['user_id' => $user->id, 'livro_id' => $book->id, 'quantidade' => 1]);

        $this->from('/carrinho')->post('/pedido/finalizar')->assertRedirect('/carrinho')
            ->assertSessionHasErrors('cartao_id');
        $this->from('/carrinho')->post('/pedido/finalizar', ['cartao_id' => $otherCard->id])
            ->assertRedirect('/carrinho')->assertSessionHasErrors('cartao_id');

        $this->assertDatabaseCount('pedidos', 0);
        $this->assertDatabaseHas('carrinhos', ['user_id' => $user->id, 'livro_id' => $book->id]);
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

    public function test_user_can_register_and_list_a_demonstration_card(): void
    {
        $user = $this->loginAs();

        $this->post('/cartoes', [
            'titular' => 'Pessoa Ficticia',
            'bandeira' => 'Visa',
            'ultimos_quatro' => '1234',
            'validade_mes' => now()->month,
            'validade_ano' => now()->year,
        ])->assertRedirect('/cartoes')->assertSessionHas('success');

        $this->assertDatabaseHas('cartoes', [
            'user_id' => $user->id,
            'titular' => 'Pessoa Ficticia',
            'bandeira' => 'Visa',
            'ultimos_quatro' => '1234',
        ]);
        $this->assertFalse(Schema::hasColumn('cartoes', 'numero'));
        $this->assertFalse(Schema::hasColumn('cartoes', 'cvv'));
        $this->get('/cartoes')->assertOk()->assertSee('Visa')->assertSee('1234');
    }

    public function test_card_registration_rejects_full_number_and_security_code(): void
    {
        $this->loginAs();

        $this->from('/cartoes')->post('/cartoes', [
            'titular' => 'Pessoa Ficticia',
            'bandeira' => 'Visa',
            'ultimos_quatro' => '1234',
            'validade_mes' => now()->month,
            'validade_ano' => now()->year,
            'numero_cartao' => '4111111111111111',
            'cvv' => '123',
        ])->assertRedirect('/cartoes')
            ->assertSessionHasErrors(['numero_cartao', 'cvv']);

        $this->assertDatabaseCount('cartoes', 0);
    }

    public function test_card_registration_rejects_invalid_demo_data(): void
    {
        $this->loginAs();

        $this->from('/cartoes')->post('/cartoes', [
            'titular' => 'Pessoa Ficticia',
            'bandeira' => 'Outra',
            'ultimos_quatro' => '12345',
            'validade_mes' => 1,
            'validade_ano' => now()->year - 1,
        ])->assertRedirect('/cartoes')
            ->assertSessionHasErrors(['bandeira', 'ultimos_quatro', 'validade_ano']);

        $this->assertDatabaseCount('cartoes', 0);
    }

    public function test_user_cannot_delete_another_users_card(): void
    {
        $this->loginAs();
        $other = User::factory()->create();
        $cartao = $other->cartoes()->create([
            'titular' => 'Pessoa Ficticia',
            'bandeira' => 'Visa',
            'ultimos_quatro' => '1234',
            'validade_mes' => now()->month,
            'validade_ano' => now()->year,
        ]);

        $this->delete("/cartoes/{$cartao->id}")->assertForbidden();
        $this->assertDatabaseHas('cartoes', ['id' => $cartao->id]);
    }

    public function test_user_can_delete_own_card(): void
    {
        $user = $this->loginAs();
        $cartao = $user->cartoes()->create([
            'titular' => 'Pessoa Ficticia',
            'bandeira' => 'Elo',
            'ultimos_quatro' => '9876',
            'validade_mes' => now()->month,
            'validade_ano' => now()->year,
        ]);

        $this->delete("/cartoes/{$cartao->id}")->assertRedirect('/cartoes');
        $this->assertDatabaseMissing('cartoes', ['id' => $cartao->id]);
    }
}
