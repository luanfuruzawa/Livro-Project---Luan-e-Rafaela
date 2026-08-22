@extends('layout.main_layout')
@section('content')

    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="row w-100 justify-content-center">
            <div class="col-md-4">
                <div class="card p-4 shadow-sm">
                    <h3 class="text-center mb-4">Login</h3>

                    <form action="home_page">
                        @csrf
                        <div class="mb-3">
                            <label for="usuario" class="form-label">Usuário:</label>
                            <input type="text" name="usuario" id="usuario" class="form-control"
                                placeholder="Digite seu usuário">
                        </div>
                        <div class="mb-3">
                            <label for="senha" class="form-label">Senha:</label>
                            <input type="password" name="senha" id="senha" class="form-control"
                                placeholder="Digite sua senha">
                        </div>
                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary">Entrar</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

@endsection