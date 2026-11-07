<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Carrinho;
use App\Models\Cartao;
use App\Models\Livro;
use Illuminate\Http\Request;
use App\Support\ActivityLog;

class CarrinhoController extends Controller
{
    public function verCarrinho()
    {
        $userId = Auth::id();
        $usuario = session('user');
        $userId = $usuario['id'] ?? null;


        $carrinho = Carrinho::whereHas('livro')
        ->with('livro')
        ->where('user_id', $userId)
        ->get();

        $cartoes = Cartao::where('user_id', $userId)->latest()->get();

        return view('usuario.carrinho', compact('carrinho', 'cartoes'));
    }

    public function adicionarCarrinho(Request $request)
    {
        $usuario = session('user');
        $userId = $usuario['id'];
        $livroId = $request->input('livro_id');
        $livroExiste = Livro::where('id', $livroId)->exists();
    if (!$livroExiste) {
        ActivityLog::write('cart.book_unavailable', [], 'warning');
        return redirect()->back()->with('error', 'Este livro não está mais disponível.');
    }
        $quantidade = (int) $request->input('quantidade', 1);

        $itemExistente = Carrinho::where('user_id', $userId)
            ->where('livro_id', $livroId)
            ->first();

        if ($itemExistente) {
            $itemExistente->increment('quantidade', $quantidade);
        } else {
            Carrinho::create([
                'user_id' => $userId,
                'livro_id' => $livroId,
                'quantidade' => $quantidade,
            ]);
        }

        ActivityLog::write('cart.item_added', [
            'livro_id' => (int) $livroId,
            'quantidade' => $quantidade,
        ]);
        return redirect()->route('carrinho.index')->with('success', 'Livro adicionado ao carrinho!');
    }

    public function atualizarCarrinho(Request $request)
    {
        $usuario = session('user');
        $userId = $usuario['id'] ?? null;

        $livroId = $request->input('livro_id');
        $quantidade = (int) $request->input('quantidade');

        $item = Carrinho::where('user_id', $userId)->where('livro_id', $livroId)->first();

        if ($item) {
            if ($quantidade > 0) {
                $item->update(['quantidade' => $quantidade]);
                ActivityLog::write('cart.item_updated', ['livro_id' => $item->livro_id, 'quantidade' => $quantidade]);
            } else {
                $item->delete();
                ActivityLog::write('cart.item_removed', ['livro_id' => $item->livro_id]);
            }
        }

        return redirect()->route('carrinho.index');
    }

    public function removerCarrinho(Request $request)
    {
        $usuario = session('user');
        $userId = $usuario['id'] ?? null;
        $livroId = $request->input('livro_id');

        $items = Carrinho::where('user_id', $userId)->where('livro_id', $livroId)->get();
        $items->each->delete();
        $removed = $items->count();
        if ($removed) {
            ActivityLog::write('cart.item_removed', ['livro_id' => (int) $livroId]);
        }

        return redirect()->route('carrinho.index');
    }

    public function limparCarrinho()
    {
        $usuario = session('user');
        $userId = $usuario['id'] ?? null;

        $items = Carrinho::where('user_id', $userId)->get();
        $items->each->delete();
        $removed = $items->count();
        ActivityLog::write('cart.cleared', ['item_count' => $removed]);

        return redirect()->route('carrinho.index');
    }
}
