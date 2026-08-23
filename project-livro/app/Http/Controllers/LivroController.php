<?php

namespace App\Http\Controllers;

use App\Models\Livro;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LivroController extends Controller
{
    public function novoLivro()
    {
        return view('livro.novo_livro');
    }
    public function guardarLivro(Request $request)
    {
        $request->validate([
            'titulo' => 'required',
            'genero' => 'required',
            'preco' => 'required|numeric',
            'estoque' => 'required|integer',
            'imagem' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',

        ]);

        $imagem = $request->file('imagem');
        // Define um nome único para o arquivo (ex: 171829301_annie.png)
        $nomeImagem = time() . '_' . $imagem->getClientOriginalName();
        // Salva a foto na pasta
        $imagem->move(public_path('images'), $nomeImagem);

        Livro::create([
            'titulo' => $request->titulo,
            'genero' => $request->genero,
            'preco' => $request->preco,
            'estoque' => $request->estoque,
            'caminho_imagem' => $nomeImagem,

        ]);
        return redirect()->route('home_page');

    }
    public function pesquisarLivro(Request $request)
    {
        $titulo = $request->input('titulo-livro');
        $genero = $request->input('genero');

        $query = Livro::query();

        if (!empty($titulo)) {
            $query->where('titulo', 'LIKE', '%' . $titulo . '%');
        }

        if (!empty($genero)) {
            $query->where('genero', $genero);
        }

        $livros = $query->get();

        return view('livro.pesquisar_livro', compact('livros'));
    }
    public function destroy($id)
    {
        $livro = Livro::findOrFail($id);

        if ($livro->caminho_imagem && file_exists(public_path('images/' . $livro->caminho_imagem))) {
            unlink(public_path('images/' . $livro->caminho_imagem));
        }

        $livro->delete();

        return redirect()->back()->with('sucesso', 'Livro removido com sucesso!');
    }
}