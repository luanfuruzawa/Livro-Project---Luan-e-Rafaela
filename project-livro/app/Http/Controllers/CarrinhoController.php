<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CarrinhoController extends Controller
{
    // Exibe a tela do carrinho
    public function verCarrinho()
    {
        $carrinho = session()->get('carrinho', []);
        return view('usuario.carrinho', compact('carrinho'));
    }

    public function adicionarCarrinho(Request $request)
    {
        $carrinho = session()->get('carrinho', []);
        $id = $request->input('livro_id');

        if (isset($carrinho[$id])) {
            $carrinho[$id]['quantidade']++;
        } else {
            $carrinho[$id] = [
                'id' => $id,
                'titulo' => $request->input('titulo'),
                'preco' => (float) $request->input('preco'),
                'imagem' => $request->input('imagem'),
                'quantidade' => 1
            ];
        }

        session()->put('carrinho', $carrinho);
        return redirect()->back()->with('sucesso', 'Livro adicionado ao carrinho!');
    }

    public function atualizarCarrinho(Request $request)
    {
        $carrinho = session()->get('carrinho', []);
        $id = $request->input('livro_id');
        $quantidade = (int) $request->input('quantidade');

        if (isset($carrinho[$id])) {
            if ($quantidade > 0) {
                $carrinho[$id]['quantidade'] = $quantidade;
            } else {
                unset($carrinho[$id]);
            }
            session()->put('carrinho', $carrinho);
        }

        return redirect()->route('carrinho.index');
    }

    public function removerCarrinho(Request $request)
    {
        $carrinho = session()->get('carrinho', []);
        $id = $request->input('livro_id');

        if (isset($carrinho[$id])) {
            unset($carrinho[$id]);
            session()->put('carrinho', $carrinho);
        }

        return redirect()->route('carrinho.index');
    }

    public function limparCarrinho()
    {
        session()->forget('carrinho');
        return redirect()->route('carrinho.index');
    }
}
