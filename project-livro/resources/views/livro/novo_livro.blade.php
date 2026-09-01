@extends('layout.main_layout')

@section('content')
    <div class="d-flex w-100">
        @include('componentes.sidebar')
        <div class="flex-grow-1 p-4 p-md-5 min-vh-100 bg-light">
            <div class="container-fluid" style="max-width: 1000px;">
                @php $isEdit = isset($livro); @endphp

                <div class="bg-white p-3 rounded-3 shadow-sm mb-4 border d-flex align-items-center">
                    @include('componentes.botao-voltar')
                    <h2 class="fw-bold text-dark m-0 ms-3">
                        {{ $isEdit ? 'Editar Livro: ' . $livro->titulo : 'Cadastrar Novo Livro' }}
                    </h2>
                </div>
                <hr>
                <div class="card border-0 shadow-sm rounded-3 bg-white p-4 p-md-5">
                    <form action="{{ $isEdit ? route('livros.update', $livro->id) : route('livros.guardar') }}"
                        method="POST" enctype="multipart/form-data">
                        @csrf
                        @if($isEdit)
                            @method('PUT')
                        @endif
                        <div class="mb-4">
                            <label for="titulo" class="form-label fw-semibold text-dark fs-6">Nome do Livro</label>
                            <input type="text" class="form-control form-control-lg @error('titulo') is-invalid @enderror"
                                id="titulo" name="titulo" value="{{ old('titulo', $livro->titulo ?? '') }}"
                                placeholder="Ex: O Senhor dos Anéis" required>
                            @error('titulo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label for="genero" class="form-label fw-semibold text-dark fs-6">Categoria / Gênero</label>
                                <select class="form-select form-select-lg @error('genero') is-invalid @enderror" id="genero"
                                    name="genero" required>
                                    @php $generoSelecionado = old('genero', $livro->genero ?? ''); @endphp
                                    <option value="" disabled {{ $generoSelecionado == '' ? 'selected' : '' }}>Selecione uma
                                        categoria...</option>
                                    @foreach(['Ficcao', 'Romance', 'Suspense', 'Terror', 'Drama', 'Fantasia', 'Misterio', 'Biografia', 'Aventura'] as $cat)
                                        <option value="{{ $cat }}" {{ $generoSelecionado == $cat ? 'selected' : '' }}>{{ $cat }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('genero')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label for="preco" class="form-label fw-semibold text-dark fs-6">Preço (R$)</label>
                                <input type="number" step="0.01"
                                    class="form-control form-control-lg @error('preco') is-invalid @enderror" id="preco"
                                    name="preco" value="{{ old('preco', $livro->preco ?? '') }}" placeholder="0,00"
                                    required>
                                @error('preco')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label for="estoque" class="form-label fw-semibold text-dark fs-6">Estoque</label>
                                <input type="number"
                                    class="form-control form-control-lg @error('estoque') is-invalid @enderror" id="estoque"
                                    name="estoque" value="{{ old('estoque', $livro->estoque ?? '') }}" placeholder="0"
                                    required>
                                @error('estoque')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="mb-5">
                            <label for="imagem" class="form-label fw-semibold text-dark fs-6">
                                Capa do Livro {{ $isEdit ? '(Opcional)' : '' }}
                            </label>
                            <input type="file" class="form-control form-control-lg @error('imagem') is-invalid @enderror"
                                id="imagem" name="imagem" accept="image/*" {{ $isEdit ? '' : 'required' }}>
                            <div class="form-text mt-2">Formatos aceitos: JPG, PNG, WEBP</div>
                            @error('imagem')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-3 pt-3 border-top">
                            <a href="{{ route('home_page') }}"
                                class="btn btn-light border px-4 py-2 fw-semibold fs-6">Cancelar</a>
                            <button type="submit" class="btn btn-dark px-5 py-2 fw-semibold fs-6 shadow-sm">
                                <i class="fa-solid {{ $isEdit ? 'fa-check' : 'fa-plus' }} me-2"></i>
                                {{ $isEdit ? 'Atualizar Livro' : 'Salvar Livro' }}
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
@endsection