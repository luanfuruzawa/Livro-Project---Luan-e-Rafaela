<?php

namespace Tests\Feature;

use App\Models\Carrinho;
use App\Models\Cartao;
use App\Models\PedidoItem;
use App\Models\User;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Activitylog\Models\Activity;
use Tests\Support\StoreTestCase;

class EstoqueTest extends StoreTestCase
{
    private User $cliente;
    private Cartao $cartao;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cliente = $this->loginAs();
        $this->cartao = $this->cliente->cartoes()->create([
            'titular' => 'Cliente de Teste', 'bandeira' => 'Visa', 'ultimos_quatro' => '1234',
            'validade_mes' => 12, 'validade_ano' => now()->year + 1,
        ]);
    }

    private function item(int $livroId, int $quantidade): Carrinho
    {
        return Carrinho::create(['user_id' => $this->cliente->id, 'livro_id' => $livroId, 'quantidade' => $quantidade]);
    }

    public function test_purchase_decrements_stock_and_records_the_change(): void
    {
        $livro = $this->book(['estoque' => 5]);
        $this->item($livro->id, 2);
        Activity::query()->delete();
        $response = $this->post('/pedido/finalizar', ['cartao_id' => $this->cartao->id])->assertRedirect('/pedidos');
        $this->assertEquals(3, $livro->fresh()->estoque);
        $this->assertDatabaseHas('pedidos', ['valor_total' => 51.00]);
        $this->assertDatabaseCount('carrinhos', 0);
        $registro = Activity::forSubject($livro)->forEvent('updated')->sole();
        $this->assertEquals(5, $registro->getExtraProperty('old.estoque'));
        $this->assertEquals(3, $registro->getExtraProperty('attributes.estoque'));
        $this->assertSame($this->cliente->id, $registro->causer_id);
        $this->assertSame($response->headers->get('X-Request-ID'), $registro->getExtraProperty('request_id'));
    }

    public function test_purchase_of_remaining_units_leaves_stock_at_zero(): void
    {
        $livro = $this->book(['estoque' => 2]);
        $this->item($livro->id, 2);
        $this->post('/pedido/finalizar', ['cartao_id' => $this->cartao->id])->assertRedirect('/pedidos');
        $this->assertEquals(0, $livro->fresh()->estoque);
        $this->get('/home_page')->assertOk()->assertSee('Esgotado');
    }

    public function test_insufficient_stock_keeps_cart_and_does_not_create_order_or_history(): void
    {
        $livro = $this->book(['estoque' => 1]);
        $this->item($livro->id, 2);
        Activity::query()->delete();
        $this->postJson('/pedido/finalizar', ['cartao_id' => $this->cartao->id])
            ->assertUnprocessable()->assertJsonValidationErrors('estoque');
        $this->assertEquals(1, $livro->fresh()->estoque);
        $this->assertDatabaseCount('pedidos', 0);
        $this->assertDatabaseCount('pedido_itens', 0);
        $this->assertDatabaseCount('carrinhos', 1);
        $this->assertDatabaseCount('activity_log', 0);
    }

    public function test_unavailable_stock_is_rejected(): void
    {
        $livro = $this->book(['estoque' => 0]);
        $this->item($livro->id, 1);
        $this->postJson('/pedido/finalizar', ['cartao_id' => $this->cartao->id])
            ->assertUnprocessable()->assertJsonValidationErrors('estoque');
        $this->assertEquals(0, $livro->fresh()->estoque);
        $this->assertDatabaseCount('pedidos', 0);
    }

    public function test_stock_is_rechecked_after_cart_was_created(): void
    {
        $livro = $this->book(['estoque' => 5]);
        $this->item($livro->id, 2);
        $livro->update(['estoque' => 1]);
        $this->postJson('/pedido/finalizar', ['cartao_id' => $this->cartao->id])->assertUnprocessable();
        $this->assertEquals(1, $livro->fresh()->estoque);
        $this->assertDatabaseCount('pedidos', 0);
    }

    #[DataProvider('invalidQuantities')]
    public function test_invalid_quantity_cannot_increase_stock(int $quantidade): void
    {
        $livro = $this->book(['estoque' => 5]);
        $this->item($livro->id, $quantidade);
        $this->postJson('/pedido/finalizar', ['cartao_id' => $this->cartao->id])
            ->assertUnprocessable()->assertJsonValidationErrors('estoque');
        $this->assertEquals(5, $livro->fresh()->estoque);
        $this->assertDatabaseCount('pedidos', 0);
    }

    public static function invalidQuantities(): array
    {
        return ['zero' => [0], 'negativa' => [-2]];
    }

    public function test_removed_book_returns_to_cart_with_message(): void
    {
        $livro = $this->book();
        $this->item($livro->id, 1);
        $livro->delete();
        $this->post('/pedido/finalizar', ['cartao_id' => $this->cartao->id])
            ->assertRedirect('/carrinho')->assertSessionHasErrors('estoque');
        $this->withCookie(config('session.cookie'), session()->getId())->get('/carrinho')
            ->assertOk()->assertSee('Um dos livros do carrinho não está mais disponível.');
        $this->assertDatabaseCount('pedidos', 0);
        $this->assertDatabaseCount('carrinhos', 1);
    }

    public function test_shortage_of_one_book_preserves_all_items_and_stock(): void
    {
        $primeiro = $this->book(['estoque' => 5]);
        $segundo = $this->book(['estoque' => 1]);
        $this->item($primeiro->id, 2);
        $this->item($segundo->id, 2);
        Activity::query()->delete();
        $this->postJson('/pedido/finalizar', ['cartao_id' => $this->cartao->id])->assertUnprocessable();
        $this->assertEquals(5, $primeiro->fresh()->estoque);
        $this->assertEquals(1, $segundo->fresh()->estoque);
        $this->assertDatabaseCount('pedidos', 0);
        $this->assertDatabaseCount('carrinhos', 2);
        $this->assertDatabaseCount('activity_log', 0);
    }

    public function test_duplicate_cart_rows_are_checked_as_a_single_quantity(): void
    {
        $livro = $this->book(['estoque' => 3]);
        $this->item($livro->id, 2);
        $this->item($livro->id, 2);
        $this->postJson('/pedido/finalizar', ['cartao_id' => $this->cartao->id])->assertUnprocessable();
        $this->assertEquals(3, $livro->fresh()->estoque);
        $this->assertDatabaseCount('pedidos', 0);
    }

    public function test_duplicate_cart_rows_decrement_the_combined_quantity_once(): void
    {
        $livro = $this->book(['estoque' => 5]);
        $this->item($livro->id, 2);
        $this->item($livro->id, 2);
        $this->post('/pedido/finalizar', ['cartao_id' => $this->cartao->id])->assertRedirect('/pedidos');
        $this->assertEquals(1, $livro->fresh()->estoque);
        $this->assertDatabaseHas('pedidos', ['valor_total' => 102.00]);
        $this->assertDatabaseCount('pedido_itens', 2);
    }

    public function test_failure_after_decrement_rolls_back_stock_order_and_history(): void
    {
        $livro = $this->book(['estoque' => 5]);
        $this->item($livro->id, 2);
        Activity::query()->delete();
        PedidoItem::creating(function () {
            throw new \RuntimeException('Falha ao gravar item.');
        });
        $this->post('/pedido/finalizar', ['cartao_id' => $this->cartao->id])->assertStatus(500);
        $this->assertEquals(5, $livro->fresh()->estoque);
        $this->assertDatabaseCount('pedidos', 0);
        $this->assertDatabaseCount('pedido_itens', 0);
        $this->assertDatabaseCount('carrinhos', 1);
        $this->assertDatabaseCount('activity_log', 0);
    }

    public function test_repeated_checkout_does_not_decrement_stock_twice(): void
    {
        $livro = $this->book(['estoque' => 5]);
        $this->item($livro->id, 2);
        $this->post('/pedido/finalizar', ['cartao_id' => $this->cartao->id])->assertRedirect('/pedidos');
        $this->post('/pedido/finalizar', ['cartao_id' => $this->cartao->id])
            ->assertRedirect('/carrinho')->assertSessionHas('error');
        $this->assertEquals(3, $livro->fresh()->estoque);
        $this->assertDatabaseCount('pedidos', 1);
    }

    public function test_another_users_cart_is_not_deducted_or_deleted(): void
    {
        $livro = $this->book(['estoque' => 5]);
        $outro = User::factory()->create();
        $this->item($livro->id, 2);
        Carrinho::create(['user_id' => $outro->id, 'livro_id' => $livro->id, 'quantidade' => 1]);
        $this->post('/pedido/finalizar', ['cartao_id' => $this->cartao->id])->assertRedirect('/pedidos');
        $this->assertEquals(3, $livro->fresh()->estoque);
        $this->assertDatabaseHas('carrinhos', ['user_id' => $outro->id, 'quantidade' => 1]);
    }
}
