@extends('layout.main_layout')
@section('content')
    <div class="container-fluid d-flex justify-content-center align-items-center min-vh-100 bg-dark">
        <div class="row w-100 justify-content-center">
            <div class="col-md-4">
                <div class="card p-4 shadow bg-white border-0 rounded-3">
                    <h3 class="text-center mb-4 text-dark fw-bold">Login</h3>
                    @if (session('login_error'))
                        <div class="alert alert-danger p-2 small text-center">
                            {{ session('login_error') }}
                        </div>
                    @endif
                    <form action="{{ route('envio.login') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="email" class="form-label text-dark fw-semibold">E-mail:</label>
                            <input type="text" name="email" id="email" 
                                   class="form-control bg-light text-dark @error('email') is-invalid @enderror" placeholder="Digite seu e-mail" value="{{ old('email') }}">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="senha" class="form-label text-dark fw-semibold">Senha:</label>
                            <input type="password" name="senha" id="senha" 
                                   class="form-control bg-light text-dark @error('senha') is-invalid @enderror"
                                   placeholder="Digite sua senha">
                            @error('senha')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-dark fw-bold py-2 shadow-sm">Entrar</button>
                        </div>
                        <div class="text-center mt-3">
                            <a href="/" class="text-secondary small text-decoration-none">
                                Não tem uma conta? <span class="text-success fw-bold">Cadastre-se</span>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection