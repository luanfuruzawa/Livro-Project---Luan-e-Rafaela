<?php

use App\Http\Controllers\MainController;
use App\Http\Controllers\LivroController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CheckIsLogged;
use App\Http\Middleware\CheckIsNotLogged;

Route::middleware([CheckIsLogged::class])->group(function () {
    Route::get('/home_page', [MainController::class, 'homePage'])->name('home_page');
    Route::get('/logout', [MainController::class, 'logout'])->name('logout');
    Route::post('/carrinho/adicionar', [MainController::class, 'carrinhoAdicionar'])->name('carrinho.adicionar');
    Route::get('/novo-livro', [LivroController::class, 'novoLivro'])->name('novo_livro');
    Route::post('/livros/guardar', [LivroController::class, 'guardarLivro'])->name('livros.guardar');
    Route::get('/pesquisar-livro', [LivroController::class, 'pesquisarLivro'])->name('pesquisar.livro');
    Route::delete('/livros/{id}', [LivroController::class, 'destroy'])->name('livros.destroy');
});

Route::middleware([CheckIsNotLogged::class])->group(function () {
    Route::get('/', [MainController::class, 'cadastro']);
    Route::post('/', [MainController::class, 'envioCadastro'])->name('envio.cadastro');
    Route::get('/login', [MainController::class, 'login'])->name('login');
    Route::post('/login', [MainController::class, 'envioLogin'])->name('envio.login');
});