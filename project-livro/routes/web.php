<?php

use App\Http\Controllers\MainController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CheckIsLogged;
use App\Http\Middleware\CheckIsNotLogged;


Route::middleware([CheckIsLogged::class])->group(function () {
    Route::get('/home_page', [MainController::class, 'homePage'])->name('home_page');
    Route::get('/logout', [MainController::class, 'logout'])->name('logout');
    Route::get('/novo-livro', [MainController::class, 'novoLivro'])->name('novo.livro');
    Route::get('/pesquisar-livro', [MainController::class, 'pesquisarLivro'])->name('pesquisar.livro');
});

Route::middleware([CheckIsNotLogged::class])->group(function () {
    Route::get('/', [MainController::class, 'cadastro']);
    Route::post('/', [MainController::class, 'envioCadastro'])->name('envio.cadastro');
    Route::get('/login', [MainController::class, 'login'])->name('login');
    Route::post('/login', [MainController::class, 'envioLogin'])->name('envio.login');
});







