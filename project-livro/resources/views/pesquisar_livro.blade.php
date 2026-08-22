@extends('layout.main_layout')
@section('content')

<div class="d-flex w-100">
    @include('componentes.sidebar')
    <div class="flex-grow-1 p-4 min-vh-100">
        <h2 class="fw-bold text-white mb-4">Pesquisar Livros</h2>
        <div class="card bg-white border-0 shadow-sm p-4 mb-4 rounded-3">
            <form action="#" method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-md-6">
                        <label for="termo_pesquisa" class="form-label fw-bold text-dark fs-5">O que procura?</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-secondary border-end-0">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </span>
                            <input type="text" id="termo_pesquisa" name="q" class="form-control form-control-lg text-dark bg-light border-start-0" placeholder="Digite o título, autor ou palavra-chave...">
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <label for="filtro_categoria" class="form-label fw-bold text-dark fs-5">Filtrar por Categoria</label>
                        <select id="filtro_categoria" name="categoria" class="form-select form-control-lg text-dark bg-light">
                            <option value="" selected disabled>Selecione uma categoria...</option>
                            <option value="ficcao">Ficção Científica</option>
                            <option value="romance">Romance</option>
                            <option value="suspense">Suspense</option>
                            <option value="terror">Terror</option>
                            <option value="drama">Drama</option>
                            <option value="fantasia">Fantasia</option>
                            <option value="misterio">Mistério / Thriller</option>
                            <option value="biografia">Biografia</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-2 d-grid">
                        <button type="submit" class="btn btn-dark btn-lg fw-semibold py-2">
                            Buscar
                        </button>
                    </div>

                </div>
            </form>
        </div>
        <div class="row row-cols-1 row-cols-md-2 g-4">
            <div class="col">
                <div class="card bg-white border-0 shadow-sm p-3 h-100 rounded-3">
                    <div class="row g-3 h-100">
                        <div class="col-4 d-flex align-items-center">
                            <img src="{{ asset('images/annie.png') }}" class="img-fluid rounded-2 shadow-sm"
                                alt="Capa do Livro" style="object-fit: cover; aspect-ratio: 3/4; width: 100%;">
                        </div>
                        <div class="col-8 d-flex flex-column justify-content-between">
                            <div>
                                <span class="badge bg-secondary text-white fw-bold mb-2">Ficção</span>
                                
                                <h4 class="card-title fw-bold text-dark mb-2 text-truncate fs-4">Annie With an E</h4>
                                <div class="d-flex align-items-center gap-3 flex-wrap">
                                    <span class="fs-5 fw-bold text-secondary">15,90 €</span>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle fw-semibold px-3 py-2 fs-6">
                                        Em stock
                                    </span>
                                </div>
                            </div>
                            <div class="text-end mt-3">
                                <button type="button" class="btn btn-dark btn-sm d-inline-flex align-items-center px-3 py-2 fw-semibold">
                                    <i class="fa-solid fa-cart-plus me-2"></i>
                                    Adicionar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

@endsection