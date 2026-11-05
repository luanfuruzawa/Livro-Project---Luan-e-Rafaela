<?php

namespace Tests\Support;

use App\Models\Livro;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;

abstract class StoreTestCase extends TestCase
{
    use RefreshDatabase;

    private string $temporaryPublicPath;

    protected function setUp(): void
    {
        parent::setUp();
        // Uploads de teste nunca sobrescrevem imagens do projeto.
        $this->temporaryPublicPath = sys_get_temp_dir().'/livro-audit-'.Str::uuid();
        File::ensureDirectoryExists($this->temporaryPublicPath.'/images');
        $this->app->usePublicPath($this->temporaryPublicPath);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->temporaryPublicPath);
        parent::tearDown();
    }

    protected function loginAs(string $role = 'User'): User
    {
        $user = User::factory()->create(['nivel_acesso' => $role]);
        $this->withSession(['user' => [
            'id' => $user->id,
            'username' => $user->username,
            'nivel_acesso' => $role,
        ]]);

        return $user;
    }

    protected function book(array $attributes = []): Livro
    {
        return Livro::factory()->create(array_merge([
            'titulo' => 'Livro de verificacao',
            'genero' => 'Romance',
            'preco' => '25.50',
            'estoque' => 5,
        ], $attributes));
    }
}
