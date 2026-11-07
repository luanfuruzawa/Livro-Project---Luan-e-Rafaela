<?php

namespace Tests\Feature;

use App\Models\Carrinho;
use App\Models\Cartao;
use App\Models\PedidoItem;
use App\Models\User;
use Spatie\Activitylog\Models\Activity;
use Tests\Support\StoreTestCase;

class HistoricoTest extends StoreTestCase
{
    private function card(User $user): Cartao
    {
        return Cartao::create([
            'user_id' => $user->id, 'titular' => 'Titular reservado', 'bandeira' => 'Visa',
            'ultimos_quatro' => '4455', 'validade_mes' => 12, 'validade_ano' => now()->year + 2,
        ]);
    }

    public function test_history_requires_an_administrator(): void
    {
        $this->get('/historico')->assertRedirect('/login');
        $this->loginAs();
        $this->get('/historico')->assertForbidden();
        $this->get('/home_page')->assertDontSee(route('historico.index'), false);
    }

    public function test_administrator_sees_history_link_and_empty_state_without_creating_more_history(): void
    {
        $this->loginAs('Admin');
        Activity::query()->delete();
        $this->get('/home_page')->assertOk()->assertSee(route('historico.index'), false);
        $this->get('/historico')->assertOk()->assertSee('Nenhum registro encontrado.');
        $this->assertDatabaseCount('activity_log', 0);
    }

    public function test_model_trait_tracks_creation_changes_deletion_and_restore(): void
    {
        $book = $this->book(['preco' => 20, 'estoque' => 5]);
        $created = Activity::forSubject($book)->forEvent('created')->sole();
        $this->assertEquals(20, $created->getExtraProperty('attributes.preco'));
        $book->update(['preco' => 30, 'estoque' => 3]);
        $updated = Activity::forSubject($book)->forEvent('updated')->sole();
        $this->assertEquals(['preco' => 20, 'estoque' => 5], $updated->properties['old']);
        $this->assertEquals(['preco' => 30, 'estoque' => 3], $updated->properties['attributes']);
        $book->save();
        $this->assertSame(2, Activity::forSubject($book)->count());
        $book->delete();
        $deleted = Activity::forSubject($book)->forEvent('deleted')->sole();
        $this->assertEquals(30, $deleted->getExtraProperty('old.preco'));
        $this->assertNotNull($deleted->subject);
        $book->restore();
        $this->assertSame(1, Activity::forSubject($book)->forEvent('restored')->count());
    }

    public function test_http_changes_record_actor_and_request_id(): void
    {
        $admin = $this->loginAs('Admin');
        $book = $this->book();
        Activity::query()->delete();
        $response = $this->put('/livros/'.$book->id, [
            'titulo' => 'Título atualizado', 'genero' => 'Romance', 'preco' => 30, 'estoque' => 4,
        ])->assertRedirect('/home_page');
        $entry = Activity::forSubject($book)->forEvent('updated')->sole();
        $this->assertSame($admin->id, $entry->causer_id);
        $this->assertSame(User::class, $entry->causer_type);
        $this->assertSame($response->headers->get('X-Request-ID'), $entry->getExtraProperty('request_id'));
        $this->assertSame('Livro de verificacao', $entry->getExtraProperty('old.titulo'));
        $this->assertSame('Título atualizado', $entry->getExtraProperty('attributes.titulo'));
    }

    public function test_user_credentials_and_card_details_are_excluded(): void
    {
        $user = User::factory()->create(['email' => 'reservado@example.test', 'username' => 'Nome reservado']);
        $card = $this->card($user);
        $user->update(['password' => 'senha-reservada', 'email' => 'outro@example.test']);
        $this->assertSame(1, Activity::forSubject($user)->count());
        $user->update(['nivel_acesso' => $user->nivel_acesso === 'Admin' ? 'User' : 'Admin']);
        $this->assertSame(1, Activity::forSubject($user)->forEvent('updated')->count());
        $card->delete();
        $properties = Activity::query()->pluck('properties')->toJson();
        foreach (['password', 'email', 'username', 'titular', 'bandeira', 'ultimos_quatro', 'validade_mes', 'validade_ano', 'senha-reservada', 'Titular reservado'] as $value) {
            $this->assertStringNotContainsString($value, $properties);
        }
        $this->assertSame(1, Activity::forSubject($card)->forEvent('deleted')->count());
    }

    public function test_cart_increment_remove_and_clear_trigger_model_events(): void
    {
        $user = $this->loginAs();
        $one = $this->book();
        $two = $this->book();
        Activity::query()->delete();
        $this->post('/carrinho/adicionar', ['livro_id' => $one->id]);
        $this->post('/carrinho/adicionar', ['livro_id' => $one->id]);
        $this->post('/carrinho/adicionar', ['livro_id' => $two->id]);
        $updated = Activity::inLog('carrinho')->forEvent('updated')->sole();
        $this->assertEquals(1, $updated->getExtraProperty('old.quantidade'));
        $this->assertEquals(2, $updated->getExtraProperty('attributes.quantidade'));
        $this->post('/carrinho/remover', ['livro_id' => $one->id]);
        $this->post('/carrinho/limpar');
        $this->assertSame(2, Activity::inLog('carrinho')->forEvent('deleted')->count());
        $this->assertSame(5, Activity::inLog('carrinho')->causedBy($user)->count());
        $this->assertDatabaseCount('carrinhos', 0);
    }

    public function test_checkout_records_order_items_and_cart_removal(): void
    {
        $user = $this->loginAs();
        $card = $this->card($user);
        $book = $this->book();
        Carrinho::create(['user_id' => $user->id, 'livro_id' => $book->id, 'quantidade' => 2]);
        Activity::query()->delete();
        $response = $this->post('/pedido/finalizar', ['cartao_id' => $card->id])->assertRedirect('/pedidos');
        $this->assertSame(1, Activity::inLog('pedidos')->forEvent('created')->count());
        $this->assertSame(1, Activity::inLog('pedidos')->forEvent('updated')->count());
        $this->assertSame(1, Activity::inLog('itens')->forEvent('created')->count());
        $this->assertSame(1, Activity::inLog('carrinho')->forEvent('deleted')->count());
        $this->assertEquals(3, $book->fresh()->estoque);
        $this->assertSame(1, Activity::inLog('livros')->forEvent('updated')->count());
        foreach (Activity::all() as $entry) {
            $this->assertSame($user->id, $entry->causer_id);
            $this->assertSame($response->headers->get('X-Request-ID'), $entry->getExtraProperty('request_id'));
        }
    }

    public function test_failed_order_rolls_back_its_history_too(): void
    {
        $user = $this->loginAs();
        $card = $this->card($user);
        $book = $this->book();
        Carrinho::create(['user_id' => $user->id, 'livro_id' => $book->id, 'quantidade' => 1]);
        Activity::query()->delete();
        PedidoItem::creating(function () {
            throw new \RuntimeException('Falha ao gravar item.');
        });
        $this->post('/pedido/finalizar', ['cartao_id' => $card->id])->assertStatus(500);
        $this->assertEquals(5, $book->fresh()->estoque);
        $this->assertDatabaseCount('pedidos', 0);
        $this->assertDatabaseCount('activity_log', 0);
        $this->assertDatabaseCount('carrinhos', 1);
    }

    public function test_login_failure_success_and_logout_have_correct_actors(): void
    {
        $user = User::factory()->create();
        Activity::query()->delete();
        $this->post('/login', ['email' => $user->email, 'senha' => 'senha-incorreta']);
        $failure = Activity::forEvent('auth.login_failed')->sole();
        $this->assertNull($failure->causer_id);
        $login = $this->post('/login', ['email' => $user->email, 'senha' => 'password']);
        $entry = Activity::forEvent('auth.logged_in')->sole();
        $this->assertSame($user->id, $entry->causer_id);
        $this->assertSame($login->headers->get('X-Request-ID'), $entry->getExtraProperty('request_id'));
        $this->get('/logout');
        $this->assertSame($user->id, Activity::forEvent('auth.logged_out')->sole()->causer_id);
        $this->assertSame(3, Activity::inLog('acesso')->count());
        $this->assertStringNotContainsString($user->email, Activity::inLog('acesso')->pluck('properties')->toJson());
    }

    public function test_registration_records_the_new_user_as_actor(): void
    {
        $response = $this->post('/', ['email' => 'novo@example.test', 'usuario' => 'Novo usuário', 'senha' => 'senha-cadastro']);
        $entry = Activity::forEvent('auth.registered')->sole();
        $this->assertSame(User::firstOrFail()->id, $entry->causer_id);
        $this->assertSame($response->headers->get('X-Request-ID'), $entry->getExtraProperty('request_id'));
        $this->assertStringNotContainsString('senha-cadastro', Activity::query()->pluck('properties')->toJson());
    }

    public function test_history_filters_by_area_action_actor_and_dates(): void
    {
        $admin = $this->loginAs('Admin');
        $other = User::factory()->create();
        Activity::query()->delete();
        activity('livros')->causedBy($admin)->event('updated')->createdAt(now()->startOfDay())
            ->withProperties(['attributes' => ['titulo' => 'Registro escolhido']])->log('updated');
        activity('livros')->causedBy($other)->event('updated')->createdAt(now()->startOfDay())
            ->withProperties(['attributes' => ['titulo' => 'Outro autor']])->log('updated');
        activity('livros')->causedBy($admin)->event('updated')->createdAt(now()->subDays(2))
            ->withProperties(['attributes' => ['titulo' => 'Registro antigo']])->log('updated');
        activity('livros')->causedBy($admin)->event('created')->createdAt(now()->startOfDay())
            ->withProperties(['attributes' => ['titulo' => 'Outra ação']])->log('created');
        activity('carrinho')->causedBy($admin)->event('updated')->createdAt(now()->startOfDay())
            ->withProperties(['attributes' => ['titulo' => 'Outra área']])->log('updated');

        $url = '/historico?'.http_build_query([
            'area' => 'livros', 'acao' => 'updated', 'usuario' => $admin->id,
            'inicio' => now()->toDateString(), 'fim' => now()->toDateString(),
        ]);
        $this->get($url)->assertOk()->assertSee('Registro escolhido')
            ->assertDontSee('Outro autor')->assertDontSee('Registro antigo')
            ->assertDontSee('Outra ação')->assertDontSee('Outra área');
        $this->get('/historico?fim='.now()->subDay()->toDateString())->assertOk()->assertSee('Registro antigo')->assertDontSee('Registro escolhido');
    }

    public function test_invalid_filters_are_rejected(): void
    {
        $this->loginAs('Admin');
        $this->getJson('/historico?area=invalida')->assertUnprocessable()->assertJsonValidationErrors('area');
        $this->getJson('/historico?usuario=abc')->assertUnprocessable()->assertJsonValidationErrors('usuario');
        $this->getJson('/historico?inicio=2026-11-08&fim=2026-11-07')->assertUnprocessable()->assertJsonValidationErrors('fim');
    }

    public function test_history_pagination_keeps_filters(): void
    {
        $admin = $this->loginAs('Admin');
        Activity::query()->delete();
        for ($number = 1; $number <= 18; $number++) {
            activity('livros')->causedBy($admin)->event('created')
                ->withProperties(['attributes' => ['titulo' => sprintf('Registro %02d', $number)]])->log('created');
        }
        $this->get('/historico?area=livros')->assertOk()->assertSee('Registro 18')->assertDontSee('Registro 01')->assertSee('area=livros');
        $this->get('/historico?area=livros&page=2')->assertOk()->assertSee('Registro 01')->assertDontSee('Registro 18');
    }

    public function test_history_escapes_html_and_hides_unapproved_fields(): void
    {
        $admin = $this->loginAs('Admin');
        $payload = '<script>alert("teste")</script>';
        activity('livros')->causedBy($admin)->event('created')->withProperties([
            'attributes' => ['titulo' => $payload, 'password' => 'senha-oculta', 'numero' => '4111111111111111'],
        ])->log('created');
        $this->get('/historico')->assertOk()->assertSee($payload)->assertDontSee($payload, false)
            ->assertDontSee('senha-oculta')->assertDontSee('4111111111111111');
    }
}
