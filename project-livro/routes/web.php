<?php

use App\Http\Controllers\MainController;
use Illuminate\Support\Facades\Route;

Route::get('/',[MainController::class, 'cadastro']);
Route::get('/login',[MainController::class, 'login']);
Route::get('/home_page',[MainController::class, 'homePage']);
Route::get('/novo_livro',[MainController::class, 'novoLivro']);
Route::get('/pesquisar_livro',[MainController::class, 'pesquisarLivro']);
