@extends('layout.main_layout')
@section('content')
    <div class="d-flex w-100">
        @include('componentes.sidebar')
        <div class="flex-grow-1 p-4 min-vh-100">
            <h2 class="fw-bold text-white mb-4">Criar Novo Livro</h2>
            <div class="card bg-white border-0 shadow-sm p-4 rounded-3" style="max-width: 800px;">
                <!-- Adicionado action e enctype para permitir upload -->
                <form action="{{ route('livros.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="titulo" class="form-label fw-bold text-dark fs-5">Nome do Livro</label>
                            <!-- name alterado para 'titulo' -->
                            <input type="text" id="titulo" name="titulo"
                                class="form-control form-control-lg text-dark bg-light" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="genero" class="form-label fw-bold text-dark fs-5">Categoria</label>
                            <!-- name alterado para 'genero' -->
                            <select id="genero" name="genero" class="form-select form-control-lg text-dark bg-light"
                                required>
                                <option value="" selected disabled>Selecione uma categoria...</option>
                                <option value="Ficção Científica">Ficção Científica</option>
                                <option value="Romance">Romance</option>
                                <option value="Suspense">Suspense</option>
                                <option value="Terror">Terror</option>
                                <option value="Drama">Drama</option>
                                <option value="Fantasia">Fantasia</option>
                                <option value="Mistério">Mistério / Thriller</option>
                                <option value="Biografia">Biografia</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-3">
                            <label for="preco" class="form-label fw-bold text-dark fs-5">Preço (€)</label>
                            <div class="input-group">
                                <input type="number" id="preco" name="preco" step="0.01" min="0"
                                    class="form-control form-control-lg text-dark bg-light" placeholder="0,00" required>
                            </div>
                        </div>
                        <div class="col-12 col-md-3">
                            <label for="estoque" class="form-label fw-bold text-dark fs-5">Estoque Inicial</label>
                            <input type="number" id="estoque" name="estoque" min="0"
                                class="form-control form-control-lg text-dark bg-light" placeholder="0" required>
                        </div>
                        <div class="col-12 mt-4">
                            <label for="imagem" class="form-label fw-bold text-dark fs-5">Capa do Livro (Imagem)</label>
                            <input type="file" id="imagem" name="imagem" class="form-control text-dark bg-light"
                                accept="image/*" required>
                            <div class="form-text text-secondary">Selecione um ficheiro de imagem guardado no seu
                                computador.</div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top border-light">
                        <button type="reset" class="btn btn-light btn-lg px-4 fw-semibold text-dark">Limpar</button>
                        <button type="submit" class="btn btn-dark btn-lg px-4 fw-semibold"><i
                                class="fa-solid fa-plus me-2"></i>Salvar Livro</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection