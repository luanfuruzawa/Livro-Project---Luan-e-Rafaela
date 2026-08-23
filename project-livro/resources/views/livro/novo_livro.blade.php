@extends('layout.main_layout')

@section('content')
    <div class="d-flex w-100">
        @include('componentes.sidebar')

        <div class="flex-grow-1 p-4 p-md-5 min-vh-100 bg-light">
            <div class="container-fluid" style="max-width: 1000px;">

                <!-- Voltar / Cabeçalho -->
                <div class="d-flex align-items-center mb-4">
                    <a href="{{ route('home_page') }}" class="btn btn-outline-secondary px-3 py-2 me-3">
                        <i class="fa-solid fa-arrow-left me-1"></i> Voltar
                    </a>
                    <h2 class="fw-bold text-dark m-0">Cadastrar Novo Livro</h2>
                </div>
                <hr>
                <div class="card border-0 shadow-sm rounded-3 bg-white p-4 p-md-5">
                    <form action="{{ route('livros.guardar') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-4">
                            <label for="titulo" class="form-label fw-semibold text-dark fs-6">Nome do Livro</label>
                            <input type="text" class="form-control form-control-lg @error('titulo') is-invalid @enderror"
                                id="titulo" name="titulo" value="{{ old('titulo') }}" placeholder="Ex: O Senhor dos Anéis"
                                required>
                            @error('titulo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label for="genero" class="form-label fw-semibold text-dark fs-6">Categoria / Gênero</label>
                                <select class="form-select form-select-lg @error('genero') is-invalid @enderror" id="genero"
                                    name="genero" required>
                                    <option value="" selected disabled>Selecione uma categoria...</option>
                                    <option value="Ficção" {{ old('genero') == 'Ficção' ? 'selected' : '' }}>Ficção</option>
                                    <option value="Romance" {{ old('genero') == 'Romance' ? 'selected' : '' }}>Romance
                                    </option>
                                    <option value="Fantasia" {{ old('genero') == 'Fantasia' ? 'selected' : '' }}>Fantasia
                                    </option>
                                    <option value="Aventura" {{ old('genero') == 'Aventura' ? 'selected' : '' }}>Aventura
                                    </option>
                                    <option value="Terror" {{ old('genero') == 'Terror' ? 'selected' : '' }}>Terror</option>
                                    <option value="Biografia" {{ old('genero') == 'Biografia' ? 'selected' : '' }}>Biografia
                                    </option>
                                </select>
                                @error('genero')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label for="preco" class="form-label fw-semibold text-dark fs-6">Preço (R$)</label>
                                <input type="number" step="0.01"
                                    class="form-control form-control-lg @error('preco') is-invalid @enderror" id="preco"
                                    name="preco" value="{{ old('preco') }}" placeholder="0,00" required>
                                @error('preco')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label for="estoque" class="form-label fw-semibold text-dark fs-6">Estoque</label>
                                <input type="number"
                                    class="form-control form-control-lg @error('estoque') is-invalid @enderror" id="estoque"
                                    name="estoque" value="{{ old('estoque') }}" placeholder="0" required>
                                @error('estoque')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="mb-5">
                            <label for="imagem" class="form-label fw-semibold text-dark fs-6">Capa do Livro (Imagem)</label>
                            <input type="file" class="form-control form-control-lg @error('imagem') is-invalid @enderror"
                                id="imagem" name="imagem" accept="image/*" required>
                            <div class="form-text mt-2">Formatos aceitos: JPG, PNG, WEBP (Máx: 2MB)</div>
                            @error('imagem')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="d-flex justify-content-end gap-3 pt-3 border-top">
                            <button type="reset" class="btn btn-light border px-4 py-2 fw-semibold fs-6">Limpar</button>
                            <button type="submit" class="btn btn-dark px-5 py-2 fw-semibold fs-6 shadow-sm">
                                <i class="fa-solid fa-plus me-2"></i> Salvar Livro
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
@endsection