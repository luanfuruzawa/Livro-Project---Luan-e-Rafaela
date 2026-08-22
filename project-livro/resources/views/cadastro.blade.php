@extends('layout.main_layout')
@section('content')
    <div class="container-fluid d-flex justify-content-center align-items-center min-vh-100 bg-dark">
        <div class="row w-100 justify-content-center">
            <div class="col-md-4">
                <div class="card p-4 shadow bg-white border-0 rounded-3">
                    <h3 class="text-center mb-4 text-dark fw-bold">Cadastro</h3>

                    <form action="home_page" method="">
                        @csrf
                        <div class="mb-3">
                            <label for="email" class="form-label text-dark fw-semibold">E-mail:</label>
                            <input type="email" name="email" id="email" class="form-control bg-light text-dark"
                                placeholder="Digite seu e-mail" required>
                        </div>
                        <div class="mb-3">
                            <label for="usuario" class="form-label text-dark fw-semibold">Usuário:</label>
                            <input type="text" name="usuario" id="usuario" class="form-control bg-light text-dark"
                                placeholder="Digite seu usuário" required>
                        </div>
                        <div class="mb-3">
                            <label for="senha" class="form-label text-dark fw-semibold">Senha:</label>
                            <input type="password" name="senha" id="senha" class="form-control bg-light text-dark"
                                placeholder="Digite sua senha" required>
                        </div>
                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-dark fw-bold py-2 shadow-sm">Entrar</button>
                        </div>
                        <div class="text-center mt-3">
                            <a href="login" class="text-secondary small text-decoration-none">
                                Já tem uma conta? <span class="text-success fw-bold">login</span>
                            </a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

@endsection