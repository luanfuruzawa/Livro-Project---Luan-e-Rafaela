<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\Carrinho;
use App\Models\Livro;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Support\ActivityLog;

class PedidoController extends Controller
{
    /**
     * Exibe o histórico de pedidos do usuário logado.
     */
    public function index()
    {
        $userId = session('user')['id'] ?? null;

        if (!$userId) {
            return redirect()->route('login')->with('error', 'Inicie sessão para ver os seus pedidos.');
        }

        $pedidos = Pedido::with(['itens.livro', 'cartao'])
            ->where('user_id', $userId)
            ->latest()
            ->get();

        return view('pedidos.index', compact('pedidos'));
    }

    /**
     * Finaliza o pedido transformando os itens do carrinho em registros.
     */
    public function finalizar(Request $request)
    {
        $userId = session('user')['id'] ?? null; 

        if (!$userId) {
            return redirect()->route('login')->with('error', 'Você precisa estar logado para finalizar o pedido.');
        }

        if (!Carrinho::where('user_id', $userId)->exists()) {
            ActivityLog::write('order.empty_cart', [], 'warning');
            return redirect()->route('carrinho.index')->with('error', 'Seu carrinho está vazio.');
        }

        $validated = $request->validate([
            'cartao_id' => [
                'required',
                'integer',
                Rule::exists('cartoes', 'id')->where(fn ($query) => $query->where('user_id', $userId)),
            ],
        ], [
            'cartao_id.required' => 'Selecione um cartão para finalizar a compra.',
            'cartao_id.integer' => 'Selecione um cartão válido.',
            'cartao_id.exists' => 'O cartão selecionado não pertence à sua conta.',
        ]);

        $resultado = DB::transaction(function () use ($userId, $validated) {
            // Serializa a finalização do mesmo carrinho.
            User::whereKey($userId)->lockForUpdate()->firstOrFail();
            $itensCarrinho = Carrinho::where('user_id', $userId)
                ->orderBy('livro_id')->orderBy('id')->lockForUpdate()->get();

            if ($itensCarrinho->isEmpty()) {
                return null;
            }

            $quantidades = [];
            foreach ($itensCarrinho as $item) {
                if ($item->quantidade < 1) {
                    throw ValidationException::withMessages([
                        'estoque' => 'Há uma quantidade inválida no carrinho. Atualize os itens antes de finalizar.',
                    ])->redirectTo(route('carrinho.index'));
                }
                $quantidades[$item->livro_id] = ($quantidades[$item->livro_id] ?? 0) + $item->quantidade;
            }

            // Todos os pedidos bloqueiam os livros na mesma ordem.
            $livros = Livro::whereIn('id', array_keys($quantidades))
                ->orderBy('id')->lockForUpdate()->get()->keyBy('id');

            foreach ($quantidades as $livroId => $quantidade) {
                $livro = $livros->get($livroId);
                if (!$livro) {
                    throw ValidationException::withMessages([
                        'estoque' => 'Um dos livros do carrinho não está mais disponível.',
                    ])->redirectTo(route('carrinho.index'));
                }
                if ($livro->estoque < $quantidade) {
                    throw ValidationException::withMessages([
                        'estoque' => "Estoque insuficiente para \"{$livro->titulo}\". Disponível: {$livro->estoque}.",
                    ])->redirectTo(route('carrinho.index'));
                }
            }

            foreach ($quantidades as $livroId => $quantidade) {
                $livros->get($livroId)->decrement('estoque', $quantidade);
            }

            $pedido = Pedido::create([
                'user_id' => $userId,
                'cartao_id' => $validated['cartao_id'],
                'valor_total' => 0,
            ]);

            $valorTotalGeral = 0;

            foreach ($itensCarrinho as $item) {
                $subtotal = $livros->get($item->livro_id)->preco * $item->quantidade;
                $valorTotalGeral += $subtotal;

                PedidoItem::create([
                    'pedido_id'   => $pedido->id,
                    'livro_id'    => $item->livro_id,
                    'quantidade'  => $item->quantidade,
                    'valor_total' => $subtotal,
                ]);
            }

            $pedido->update(['valor_total' => $valorTotalGeral]);
            $itensCarrinho->each->delete();

            return ['pedido' => $pedido, 'itens' => $itensCarrinho->count()];
        }, 3);

        if (!$resultado) {
            ActivityLog::write('order.empty_cart', [], 'warning');
            return redirect()->route('carrinho.index')->with('error', 'Seu carrinho está vazio.');
        }

        $pedido = $resultado['pedido'];
        ActivityLog::write('order.completed', [
            'pedido_id' => $pedido->id,
            'item_count' => $resultado['itens'],
            'valor_total' => $pedido->valor_total,
        ]);
        return redirect()->route('pedidos.index')->with('success', "Pedido #{$pedido->id} finalizado com sucesso!");
    }
}
