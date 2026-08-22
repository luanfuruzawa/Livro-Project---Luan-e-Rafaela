<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MainController extends Controller
{
    public function login(){
        return view('login');
    }public function cadastro(){
        return view('cadastro');
    }
    public function homePage(){
        return view('home_page');
    }public function novoLivro(){
        return view('novo_livro');
    }public function pesquisarLivro(){
        return view('pesquisar_livro');
    }
}
