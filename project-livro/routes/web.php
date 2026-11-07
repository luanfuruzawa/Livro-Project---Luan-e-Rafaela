<?php

use App\Http\Controllers\CarrinhoController;
use App\Http\Controllers\CartaoController;
use App\Http\Controllers\MainController;
use App\Http\Controllers\LivroController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\HistoricoController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CheckIsLogged;
use App\Http\Middleware\CheckIsNotLogged;

Route::middleware([CheckIsLogged::class])->group(function () {
    Route::get('/historico', [HistoricoController::class, 'index'])->middleware('can:admin')->name('historico.index');
    Route::post('/pedido/finalizar', [PedidoController::class, 'finalizar'])->name('pedido.finalizar');
    Route::get('/home_page', [MainController::class, 'homePage'])->name('home_page');
    Route::get('/logout', [MainController::class, 'logout'])->name('logout');
    Route::get('/novo-livro', [LivroController::class, 'novoLivro'])->name('novo_livro');
    Route::post('/livros/guardar', [LivroController::class, 'guardarLivro'])->name('livros.guardar');
    Route::get('/pesquisar-livro', [LivroController::class, 'pesquisarLivro'])->name('pesquisar.livro');
    Route::delete('/livros/{id}', [LivroController::class, 'destroy'])->name('livros.destroy');
    Route::get('/carrinho', [CarrinhoController::class, 'verCarrinho'])->name('carrinho.index');
    Route::post('/carrinho/adicionar', [CarrinhoController::class, 'adicionarCarrinho'])->name('carrinho.adicionar');
    Route::post('/carrinho/atualizar', [CarrinhoController::class, 'atualizarCarrinho'])->name('carrinho.atualizar');
    Route::post('/carrinho/remover', [CarrinhoController::class, 'removerCarrinho'])->name('carrinho.remover');
    Route::post('/carrinho/limpar', [CarrinhoController::class, 'limparCarrinho'])->name('carrinho.limpar');
    Route::get('/livros/{id}/editar', [LivroController::class, 'editar'])->name('livros.editar');
    Route::put('/livros/{id}', [LivroController::class, 'update'])->name('livros.update');
    Route::get('/pedidos', [PedidoController::class, 'index'])->name('pedidos.index');
    Route::get('/cartoes', [CartaoController::class, 'index'])->name('cartoes.index');
    Route::post('/cartoes', [CartaoController::class, 'store'])->name('cartoes.store');
    Route::delete('/cartoes/{cartao}', [CartaoController::class, 'destroy'])->name('cartoes.destroy');
});

Route::middleware([CheckIsNotLogged::class])->group(function () {
    Route::get('/', [MainController::class, 'cadastro']);
    Route::post('/', [MainController::class, 'envioCadastro'])->name('envio.cadastro');
    Route::get('/login', [MainController::class, 'login'])->name('login');
    Route::post('/login', [MainController::class, 'envioLogin'])->name('envio.login');
});
