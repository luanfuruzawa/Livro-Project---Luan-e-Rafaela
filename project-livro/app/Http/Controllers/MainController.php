<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MainController extends Controller
{
    public function index(){
        return view('login');
    }
    public function homePage(){
        return view('home_page');
    }public function novoLivro(){
        return view('novo_livro');
    }public function pesquisarLivro(){
        return view('pesquisar_livro');
    }
}
