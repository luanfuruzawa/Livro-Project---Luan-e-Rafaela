<?php

namespace Tests\Feature;

use App\Models\Carrinho;
use App\Models\Cartao;
use App\Models\PedidoItem;
use App\Models\User;
use App\Support\ActivityLog;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Tests\Support\StoreTestCase;

class ActivityLoggingTest extends StoreTestCase
{
    private string $logPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->logPath = sys_get_temp_dir().'/livro-log-test-'.Str::uuid().'.log';
        config(['logging.channels.activity' => ['driver' => 'single', 'path' => $this->logPath, 'level' => 'debug']]);
        Log::forgetChannel('activity');
    }

    protected function tearDown(): void
    {
        Log::forgetChannel('activity');
        File::delete($this->logPath);
        parent::tearDown();
    }

    private function logs(): string
    {
        return File::get($this->logPath);
    }

    private function entries(): array
    {
        return array_map(function (string $line): array {
            $this->assertSame(1, preg_match('/\] \w+\.(\w+): ([\w.]+) (\{.*\})\s*$/', $line, $matches));

            return ['level' => $matches[1], 'event' => $matches[2], 'context' => json_decode($matches[3], true, flags: JSON_THROW_ON_ERROR)];
        }, file($this->logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
    }

    private function demonstrationCardFor(User $user): Cartao
    {
        return $user->cartoes()->create([
            'titular' => 'Pessoa Ficticia',
            'bandeira' => 'Visa',
            'ultimos_quatro' => '1234',
            'validade_mes' => now()->month,
            'validade_ano' => now()->year,
        ]);
    }

    public function test_order_and_error_events_match_their_exact_http_request_without_duplicates(): void
    {
        $user = $this->loginAs();
        $cartao = $this->demonstrationCardFor($user);
        $book = $this->book();
        Carrinho::create(['user_id' => $user->id, 'livro_id' => $book->id, 'quantidade' => 1]);
        $success = $this->post('/pedido/finalizar', ['cartao_id' => $cartao->id])->assertRedirect('/pedidos');
        Carrinho::create(['user_id' => $user->id, 'livro_id' => $book->id, 'quantidade' => 1]);
        PedidoItem::creating(function () {
            throw new \RuntimeException('Falha ao gravar item.');
        });
        $failure = $this->post('/pedido/finalizar', ['cartao_id' => $cartao->id])->assertStatus(500);

        foreach ([[$success, 'order.completed', 'INFO', 302], [$failure, 'application.exception', 'ERROR', 500]] as [$response, $event, $level, $status]) {
            $requestId = $response->headers->get('X-Request-ID');
            $this->assertTrue(Str::isUuid($requestId));
            $entries = array_values(array_filter($this->entries(), fn ($entry) => $entry['context']['request_id'] === $requestId));
            $this->assertCount(2, $entries);
            $this->assertSame($event, $entries[0]['event']);
            $this->assertSame($level, $entries[0]['level']);
            $this->assertSame($user->id, $entries[0]['context']['user_id']);
            $this->assertSame('http.completed', $entries[1]['event']);
            $this->assertSame($status, $entries[1]['context']['status']);
            $this->assertSame($user->id, $entries[1]['context']['user_id']);
        }
    }

    public function test_book_creation_is_logged_with_the_saved_id_without_upload_data(): void
    {
        $this->loginAs('Admin');
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aF9sAAAAASUVORK5CYII=');
        $response = $this->post('/livros/guardar', [
            'titulo' => 'PrivateCreatedBook', 'genero' => 'Romance', 'preco' => 25.50, 'estoque' => 3,
            'imagem' => UploadedFile::fake()->createWithContent('private-upload-name.png', $png),
        ])->assertRedirect('/home_page');

        $events = array_values(array_filter($this->entries(), fn ($entry) => $entry['event'] === 'book.created'));
        $this->assertCount(1, $events);
        $this->assertSame(\App\Models\Livro::firstOrFail()->id, $events[0]['context']['livro_id']);
        $this->assertSame($response->headers->get('X-Request-ID'), $events[0]['context']['request_id']);
        $this->assertStringNotContainsString('PrivateCreatedBook', $this->logs());
        $this->assertStringNotContainsString('private-upload-name', $this->logs());
    }

    public function test_json_validation_failure_logs_warning_status_without_form_values(): void
    {
        $this->postJson('/', ['email' => 'private-invalid-email', 'usuario' => 'PrivateValidationName', 'senha' => 'private-validation-password'])
            ->assertUnprocessable();

        $entries = $this->entries();
        $this->assertCount(1, $entries);
        $this->assertSame('http.completed', $entries[0]['event']);
        $this->assertSame('WARNING', $entries[0]['level']);
        $this->assertSame(422, $entries[0]['context']['status']);
        foreach (['private-invalid-email', 'PrivateValidationName', 'private-validation-password'] as $value) {
            $this->assertStringNotContainsString($value, $this->logs());
        }
    }

    public function test_requests_have_unique_server_generated_ids_and_safe_metadata(): void
    {
        $first = $this->withHeader('X-Request-ID', 'client-controlled')->get('/login?token=private-query-value');
        $second = $this->get('/');
        $this->assertTrue(Str::isUuid($first->headers->get('X-Request-ID')));
        $this->assertNotSame($first->headers->get('X-Request-ID'), $second->headers->get('X-Request-ID'));
        $this->assertStringContainsString('http.completed', $this->logs());
        $this->assertStringContainsString('duration_ms', $this->logs());
        $this->assertStringNotContainsString('private-query-value', $this->logs());
        $this->assertStringNotContainsString('client-controlled', $this->logs());
    }

    public function test_registration_records_user_id_without_credentials(): void
    {
        $this->post('/', ['email' => 'private-email@example.test', 'usuario' => 'PrivateUsername', 'senha' => 'private-password']);
        $this->assertStringContainsString('auth.registered', $this->logs());
        $this->assertStringContainsString('user_id', $this->logs());
        foreach (['private-email', 'PrivateUsername', 'private-password'] as $value) {
            $this->assertStringNotContainsString($value, $this->logs());
        }
    }

    public function test_login_failure_success_and_logout_are_distinct_events(): void
    {
        $user = User::factory()->create();
        $this->post('/login', ['email' => $user->email, 'senha' => 'wrong-password']);
        $this->post('/login', ['email' => $user->email, 'senha' => 'password']);
        $this->get('/logout');
        foreach (['auth.login_failed', 'auth.logged_in', 'auth.logged_out'] as $event) {
            $this->assertStringContainsString($event, $this->logs());
        }
        $this->assertStringNotContainsString($user->email, $this->logs());
        $this->assertStringNotContainsString('wrong-password', $this->logs());
    }

    public function test_cart_mutations_are_logged(): void
    {
        $this->loginAs();
        $book = $this->book();
        $this->post('/carrinho/adicionar', ['livro_id' => $book->id]);
        $this->post('/carrinho/atualizar', ['livro_id' => $book->id, 'quantidade' => 2]);
        $this->post('/carrinho/remover', ['livro_id' => $book->id]);
        $this->post('/carrinho/limpar');
        $this->post('/carrinho/adicionar', ['livro_id' => 999999]);
        foreach (['cart.item_added', 'cart.item_updated', 'cart.item_removed', 'cart.cleared', 'cart.book_unavailable'] as $event) {
            $this->assertStringContainsString($event, $this->logs());
        }
    }

    public function test_book_mutations_are_logged_without_book_title(): void
    {
        $this->loginAs('Admin');
        $book = $this->book(['titulo' => 'PrivateBookTitle']);
        $this->put("/livros/{$book->id}", ['titulo' => $book->titulo, 'genero' => 'Romance', 'preco' => 25, 'estoque' => 5]);
        $this->delete("/livros/{$book->id}");
        $this->assertStringContainsString('book.updated', $this->logs());
        $this->assertStringContainsString('book.deleted', $this->logs());
        $this->assertStringNotContainsString('PrivateBookTitle', $this->logs());
    }

    public function test_order_success_and_empty_cart_are_logged(): void
    {
        $user = $this->loginAs();
        $cartao = $this->demonstrationCardFor($user);
        $book = $this->book();
        Carrinho::create(['user_id' => $user->id, 'livro_id' => $book->id, 'quantidade' => 2]);
        $this->post('/pedido/finalizar', ['cartao_id' => $cartao->id])->assertRedirect('/pedidos');
        $this->post('/pedido/finalizar')->assertSessionHas('error');
        $this->assertStringContainsString('order.completed', $this->logs());
        $this->assertStringContainsString('order.empty_cart', $this->logs());
        $this->assertStringContainsString('"valor_total":51', $this->logs());
    }

    public function test_failed_order_is_rolled_back_and_logs_safe_exception_metadata(): void
    {
        $user = $this->loginAs();
        $cartao = $this->demonstrationCardFor($user);
        $book = $this->book();
        Carrinho::create(['user_id' => $user->id, 'livro_id' => $book->id, 'quantidade' => 1]);
        PedidoItem::creating(function () {
            throw new \RuntimeException('Falha ao gravar item.');
        });
        $this->post('/pedido/finalizar', ['cartao_id' => $cartao->id])->assertStatus(500);
        $this->assertEquals(5, $book->fresh()->estoque);
        $this->assertDatabaseCount('pedidos', 0);
        $this->assertDatabaseCount('pedido_itens', 0);
        $this->assertDatabaseCount('carrinhos', 1);
        $this->assertStringContainsString('application.exception', $this->logs());
        $this->assertStringContainsString('"exception_class":"RuntimeException"', $this->logs());
        $this->assertStringContainsString('"status":500', $this->logs());
        $this->assertStringNotContainsString('order.completed', $this->logs());
        $this->assertStringNotContainsString('Stack trace', $this->logs());
    }

    public function test_unknown_routes_are_logged_without_raw_path(): void
    {
        $this->get('/private-path-sentinel')->assertNotFound();
        $this->assertStringContainsString('"status":404', $this->logs());
        $this->assertStringNotContainsString('private-path-sentinel', $this->logs());
    }

    public function test_logger_drops_fields_outside_allowlist(): void
    {
        ActivityLog::write('audit.test', ['senha' => 'private-sentinel', 'email' => 'private@example.test', 'livro_id' => 123]);
        $this->assertStringNotContainsString('private-sentinel', $this->logs());
        $this->assertStringNotContainsString('private@example.test', $this->logs());
        $this->assertStringContainsString('"livro_id":123', $this->logs());
    }

    public function test_logging_failure_does_not_break_a_successful_order(): void
    {
        $user = $this->loginAs();
        $cartao = $this->demonstrationCardFor($user);
        $book = $this->book();
        Carrinho::create(['user_id' => $user->id, 'livro_id' => $book->id, 'quantidade' => 1]);
        $originalLogger = Log::getFacadeRoot();
        try {
            Log::partialMock()->shouldReceive('channel')->with('activity')
                ->andThrow(new \RuntimeException('Simulated unavailable log'));

            $this->post('/pedido/finalizar', ['cartao_id' => $cartao->id])->assertRedirect('/pedidos');
            $this->assertDatabaseCount('pedidos', 1);
            $this->assertDatabaseCount('carrinhos', 0);
        } finally {
            Log::swap($originalLogger);
        }
    }
}
