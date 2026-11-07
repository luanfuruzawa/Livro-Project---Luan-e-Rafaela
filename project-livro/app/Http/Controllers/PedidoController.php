<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\Carrinho;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
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

        // Busca itens do carrinho com os dados dos livros relacionados
        $itensCarrinho = Carrinho::with('livro')->where('user_id', $userId)->get();

        if ($itensCarrinho->isEmpty()) {
            ActivityLog::write('order.empty_cart', [], 'warning');
            return redirect()->back()->with('error', 'Seu carrinho está vazio.');
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

        // Executa tudo dentro de uma transação sem blindagem de try/catch para expor o erro real
        $pedido = DB::transaction(function () use ($userId, $itensCarrinho, $validated) {
            
            // 1. Cria o Pedido pai
            $pedido = Pedido::create([
                'user_id' => $userId,
                'cartao_id' => $validated['cartao_id'],
                'valor_total' => 0,
            ]);

            $valorTotalGeral = 0;

            // 2. Transfere os itens para a tabela PedidoItem
            foreach ($itensCarrinho as $item) {
                $subtotal = $item->livro->preco * $item->quantidade;
                $valorTotalGeral += $subtotal;

                PedidoItem::create([
                    'pedido_id'   => $pedido->id,
                    'livro_id'    => $item->livro_id,
                    'quantidade'  => $item->quantidade,
                    'valor_total' => $subtotal,
                ]);
            }

            // 3. Atualiza o valor consolidado do pedido
            $pedido->update(['valor_total' => $valorTotalGeral]);

            // 4. Limpa o carrinho
            $itensCarrinho->each->delete();

            return $pedido;
        });

        // Redireciona para a listagem se tudo correr bem
        ActivityLog::write('order.completed', [
            'pedido_id' => $pedido->id,
            'item_count' => $itensCarrinho->count(),
            'valor_total' => $pedido->valor_total,
        ]);
        return redirect()->route('pedidos.index')->with('success', "Pedido #{$pedido->id} finalizado com sucesso!");
    }
}
