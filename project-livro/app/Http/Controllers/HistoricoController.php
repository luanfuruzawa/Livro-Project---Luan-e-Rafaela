<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Activitylog\Models\Activity;

class HistoricoController extends Controller
{
    public const AREAS = [
        'livros' => 'Livros',
        'carrinho' => 'Carrinho',
        'pedidos' => 'Pedidos',
        'itens' => 'Itens do pedido',
        'usuarios' => 'Usuários',
        'cartoes' => 'Cartões',
        'acesso' => 'Acesso',
    ];

    public const ACOES = [
        'created' => 'Criação',
        'updated' => 'Alteração',
        'deleted' => 'Exclusão',
        'restored' => 'Restauração',
        'auth.registered' => 'Cadastro',
        'auth.logged_in' => 'Login',
        'auth.login_failed' => 'Falha no login',
        'auth.logged_out' => 'Logout',
    ];

    public function index(Request $request)
    {
        $regrasFim = ['nullable', 'date_format:Y-m-d'];
        if ($request->filled('inicio')) {
            $regrasFim[] = 'after_or_equal:inicio';
        }

        $filtros = $request->validate([
            'area' => ['nullable', Rule::in(array_keys(self::AREAS))],
            'acao' => ['nullable', Rule::in(array_keys(self::ACOES))],
            'usuario' => ['nullable', 'integer', 'min:1'],
            'inicio' => ['nullable', 'date_format:Y-m-d'],
            'fim' => $regrasFim,
        ]);

        $consulta = Activity::query()->with('causer');
        if (!empty($filtros['area'])) {
            $consulta->where('log_name', $filtros['area']);
        }
        if (!empty($filtros['acao'])) {
            $consulta->where('event', $filtros['acao']);
        }
        if (!empty($filtros['usuario'])) {
            $consulta->where('causer_type', (new User())->getMorphClass())
                ->where('causer_id', $filtros['usuario']);
        }
        if (!empty($filtros['inicio'])) {
            $consulta->where('created_at', '>=', $filtros['inicio'].' 00:00:00');
        }
        if (!empty($filtros['fim'])) {
            $consulta->where('created_at', '<', \Carbon\Carbon::parse($filtros['fim'])->addDay()->startOfDay());
        }

        return view('historico.index', [
            'registros' => $consulta->orderByDesc('id')->paginate(15)->withQueryString(),
            'usuarios' => User::select('id', 'username')->orderBy('username')->get(),
            'areas' => self::AREAS,
            'acoes' => self::ACOES,
            'campos' => [
                'titulo' => 'Título', 'genero' => 'Gênero', 'preco' => 'Preço',
                'estoque' => 'Estoque', 'user_id' => 'Usuário', 'livro_id' => 'Livro',
                'quantidade' => 'Quantidade', 'pedido_id' => 'Pedido',
                'valor_total' => 'Total', 'nivel_acesso' => 'Perfil', 'cartao_id' => 'Cartão',
            ],
        ]);
    }
}
