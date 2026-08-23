<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Livro;
use Hash;
use Illuminate\Http\Request;

class MainController extends Controller
{
    public function cadastro()
    {
        return view('usuario.cadastro');
    }
    public function envioCadastro(Request $request)
    {
        $request->validate(
            [
                'email' => 'required|email|min:3|unique:users,email',
                'usuario' => 'required|min:2|unique:users,username',
                'senha' => 'required|min:6',
            ],
            [
                'email.required' => 'O campo e-mail é obrigatório.',
                'email.email' => 'O campo de e-mail deve conter um endereço válido.',
                'email.min' => 'O campo e-mail deve ter no mínimo 3 caracteres.',

                'usuario.required' => 'O campo usuário é obrigatório.',
                'usuario.min' => 'O campo usuário deve ter no mínimo 3 caracteres.',

                'senha.required' => 'O campo senha é obrigatório.',
                'senha.min' => 'O campo senha deve ter no mínimo 6 caracteres.',

            ]

        );
        $user = new User();
        $user->email = $request->input('email');
        $user->username = $request->input('usuario');
        $user->password = Hash::make($request->input('senha'));

        // nivel de acesso definido
        $user->nivel_acesso = 'User';

        $user->save();
        session([
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'nivel_acesso' => $user->nivel_acesso
            ]
        ]);

        return redirect()->route('home_page');
    }

    public function login()
    {
        return view('usuario.login');
    }

    public function logout()
    {

        session()->forget('user');

        return redirect()->route('login');
    }

    public function envioLogin(Request $request)
    {
        $request->validate(
            ['email' => 'required|email|min:3', 'senha' => 'required|min:6'],
            [
                'email.required' => 'O campo e-mail é obrigatório.',
                'email.email' => 'O campo de e-mail deve conter um endereço válido.',
                'email.min' => 'O campo e-mail deve ter no mínimo 3 caracteres.',
                'senha.required' => 'O campo senha é obrigatório.',
                'senha.min' => 'O campo senha deve ter no mínimo 6 caracteres.',
            ]
        );

        $user = User::where('email', $request->input('email'))->first();

        if (!$user || !password_verify($request->input('senha'), $user->password)) {
            return redirect()->back()->withInput()->with('login_error', 'E-mail ou senha incorretos!');
        }

        $user->save();

        session([
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'nivel_acesso' => $user->nivel_acesso,
            ]
        ]);

        return redirect()->route('home_page');
    }
    public function homePage()
    {
        $nivel_acesso = (session('user')['nivel_acesso']) ?? 'user';
        $livros = Livro::all();
        return view('home_page', compact('livros', 'nivel_acesso'));
        return view('home_page');
    }
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
        // daqui pra frente adiciona como se fosse um 'AND' no banco de dados
        if (!empty($genero)) {
            $query->where('genero', $genero);
        }
        $livros = $query->get();


        // Passa $titulo e $genero para manter o estado do input/select na view
        return view('livro.pesquisar_livro', compact('livros', 'titulo', 'genero'));
    }

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
