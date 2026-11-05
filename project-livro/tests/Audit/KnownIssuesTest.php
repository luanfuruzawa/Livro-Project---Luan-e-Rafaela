<?php

namespace Tests\Audit;

use App\Models\Carrinho;
use Tests\Support\StoreTestCase;

/**
 * Regressões conhecidas. Executar separadamente da suíte padrão.
 */
class KnownIssuesTest extends StoreTestCase
{
    public function test_regular_user_cannot_open_admin_book_form(): void
    {
        $this->loginAs();
        $this->get('/novo-livro')->assertForbidden();
    }

    public function test_regular_user_cannot_edit_book(): void
    {
        $this->loginAs();
        $book = $this->book();
        $this->put("/livros/{$book->id}", ['titulo' => 'Alterado sem permissao', 'genero' => 'Romance', 'preco' => 1, 'estoque' => 1])
            ->assertForbidden();
    }

    public function test_regular_user_cannot_delete_book(): void
    {
        $this->loginAs();
        $book = $this->book();
        $this->delete("/livros/{$book->id}")->assertForbidden();
    }

    public function test_cart_rejects_negative_quantity(): void
    {
        $this->loginAs();
        $book = $this->book();
        $this->postJson('/carrinho/adicionar', ['livro_id' => $book->id, 'quantidade' => -2])
            ->assertUnprocessable();
    }

    public function test_cart_rejects_quantity_above_stock(): void
    {
        $this->loginAs();
        $book = $this->book(['estoque' => 1]);
        $this->postJson('/carrinho/adicionar', ['livro_id' => $book->id, 'quantidade' => 999])
            ->assertUnprocessable();
    }

    public function test_cart_update_rejects_quantity_above_stock(): void
    {
        $user = $this->loginAs();
        $book = $this->book(['estoque' => 1]);
        Carrinho::create(['user_id' => $user->id, 'livro_id' => $book->id, 'quantidade' => 1]);
        $this->postJson('/carrinho/atualizar', ['livro_id' => $book->id, 'quantidade' => 999])
            ->assertUnprocessable();
    }

    public function test_checkout_decrements_stock(): void
    {
        $user = $this->loginAs();
        $book = $this->book(['estoque' => 5]);
        Carrinho::create(['user_id' => $user->id, 'livro_id' => $book->id, 'quantidade' => 2]);
        $this->post('/pedido/finalizar')->assertRedirect('/pedidos');
        $this->assertEquals(3, $book->fresh()->estoque);
    }

    public function test_checkout_rejects_unavailable_stock(): void
    {
        $user = $this->loginAs();
        $book = $this->book(['estoque' => 0]);
        Carrinho::create(['user_id' => $user->id, 'livro_id' => $book->id, 'quantidade' => 2]);
        $this->postJson('/pedido/finalizar')->assertUnprocessable();
    }

    public function test_checkout_handles_book_removed_after_adding_to_cart(): void
    {
        $user = $this->loginAs();
        $book = $this->book();
        Carrinho::create(['user_id' => $user->id, 'livro_id' => $book->id, 'quantidade' => 1]);
        $book->delete();
        $this->post('/pedido/finalizar')->assertRedirect('/carrinho');
    }

    public function test_failed_login_does_not_flash_plaintext_password(): void
    {
        $this->post('/login', ['email' => 'missing@example.test', 'senha' => 'secret-audit-value'])
            ->assertSessionMissing('_old_input.senha');
    }

    public function test_validation_does_not_flash_plaintext_password(): void
    {
        $this->post('/', ['email' => 'invalid', 'usuario' => 'Audit', 'senha' => 'secret-audit-value'])
            ->assertSessionMissing('_old_input.senha');
    }

    public function test_negative_price_and_stock_are_rejected(): void
    {
        $this->loginAs('Admin');
        $book = $this->book();
        $this->putJson("/livros/{$book->id}", ['titulo' => 'Teste', 'genero' => 'Romance', 'preco' => -1, 'estoque' => -5])
            ->assertUnprocessable();
    }
}
