<?php

namespace App\Http\Controllers;

use App\Models\User;
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
        $user->nivel_acesso = 'user';

        //$user->last_login = now();  -> desnecessário
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

        // $user = User::where('email', $request->input('email'))
        //     ->whereNull('deleted_at')
        //     ->first();

        // if (!$user || !password_verify($request->input('senha'), $user->password)) {
        //     return redirect()->back()->withInput()->with('login_error', 'Username ou password incorretos!');
        // }

        // $user->last_login = now();
        // $user->save();
        // session(['user' => ['id' => $user->id, 'username' => $user->username]]);

        return redirect()->route('home_page');
    }
    public function homePage()
    {
        return view('home_page');
    }
    public function novoLivro()
    {
        return view('livro.novo_livro');
    }
    public function pesquisarLivro()
    {
        return view('livro.pesquisar_livro');
    }
    public function create()
    {
        return view('register');
    }

}
