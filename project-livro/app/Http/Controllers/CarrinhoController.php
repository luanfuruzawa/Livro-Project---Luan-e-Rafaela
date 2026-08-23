<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Carrinho;
use Illuminate\Http\Request;

class CarrinhoController extends Controller
{
    public function verCarrinho()
    {
        $userId = Auth::id();
        $usuario = session('user');
        $userId = $usuario['id'] ?? null;


        $carrinho = Carrinho::with('livro')
            ->where('user_id', $userId)
            ->get();

        return view('usuario.carrinho', compact('carrinho'));
    }

    public function adicionarCarrinho(Request $request)
    {
        $usuario = session('user');
        $userId = $usuario['id'] ?? null;

        if (!$userId) {
            return redirect()->route('login')->with('error', 'Faça login para adicionar ao carrinho.');
        }

        $livroId = $request->input('livro_id');
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
            } else {
                $item->delete();
            }
        }

        return redirect()->route('carrinho.index');
    }

    public function removerCarrinho(Request $request)
    {
        $usuario = session('user');
        $userId = $usuario['id'] ?? null;
        $livroId = $request->input('livro_id');

        Carrinho::where('user_id', $userId)->where('livro_id', $livroId)->delete();

        return redirect()->route('carrinho.index');
    }

    public function limparCarrinho()
    {
        $usuario = session('user');
        $userId = $usuario['id'] ?? null;

        Carrinho::where('user_id', $userId)->delete();

        return redirect()->route('carrinho.index');
    }
}