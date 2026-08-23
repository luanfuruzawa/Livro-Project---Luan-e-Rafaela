@extends('layout.main_layout')
@section('content')

<div class="d-flex w-100">
    @include('componentes.sidebar')
    <div class="flex-grow-1 p-4 min-vh-100 bg-secondary-subtle">
        <h2 class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom border-secondary border-opacity-25 text-dark fw-bold">Pesquisar Livros</h2>
        <hr>
        <div class="card bg-white border-0 shadow-sm p-4 mb-4 rounded-3">
            <form method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-md-6">
                        <label for="termo_pesquisa" class="form-label fw-bold text-dark fs-5">O que procura?</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-secondary border-end-0">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </span>
                            <input type="text" id="titulo-livro" name="titulo-livro" class="form-control form-control-lg text-dark bg-light border-start-0" placeholder="Digite o título ou parte dele...">
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <label for="filtro_genero" class="form-label fw-bold text-dark fs-5">Filtrar por Categoria</label>
                        <select id="filtro_genero" name="genero" class="form-select form-control-lg text-dark bg-light">
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
                @forelse ($livros as $livro)
                    <div class="col">
                        <div class="card bg-white border-0 shadow-sm p-3 h-100 rounded-3">
                            <div class="row g-3 h-100">
                                @if ($livro->caminho_imagem)
                                    <div class="col-4 d-flex align-items-center">
                                        <img src="{{ asset('images/' . $livro->caminho_imagem) }}" 
                                             class="img-fluid rounded-2 shadow-sm"
                                             alt="Capa do Livro {{ $livro->titulo }}"
                                             style="object-fit: cover; aspect-ratio: 3/4; width: 100%;">
                                    </div>
                                @endif
                                <div class="{{ $livro->caminho_imagem ? 'col-8' : 'col-12' }} d-flex flex-column justify-content-between">
                                    <div>
                                        <h4 class="card-title fw-bold text-dark mb-2 text-truncate fs-4">
                                            {{ $livro->titulo }}
                                        </h4>
                                        <div class="d-flex align-items-center gap-3 flex-wrap">
                                            <span class="fs-5 fw-bold text-secondary">
                                                R$ {{ number_format($livro->preco, 2, ',', '.') }}
                                            </span>
                                            @if ($livro->estoque > 0)
                                                <span class="badge bg-success-subtle text-success border border-success-subtle fw-semibold px-3 py-2 fs-6">
                                                    Em estoque ({{ $livro->estoque }})
                                                </span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle fw-semibold px-3 py-2 fs-6">
                                                    Esgotado
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-end mt-3 gap-2 d-flex justify-content-end">
                                        <button type="button"
                                            class="btn btn-dark btn-sm d-inline-flex align-items-center px-3 py-2 fw-semibold"
                                            {{ $livro->estoque <= 0 ? 'disabled' : '' }}>
                                            <i class="fa-solid fa-cart-plus me-2"></i>
                                            Adicionar
                                        </button>
                                        <button type="button"
                                            class="btn btn-outline-dark btn-sm d-inline-flex align-items-center px-3 py-2 fw-semibold">
                                            <i class="fa-solid fa-pen-to-square me-2"></i>
                                            Editar
                                        </button><button type="button"
                                            class="btn btn-outline-dark btn-sm d-inline-flex align-items-center px-3 py-2 fw-semibold">
                                            <i class="fa-solid fa-trash me-2"></i>
                                            Remover
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-5">
                        <p class="fs-5 text-muted">Nenhum livro encontrado no catálogo.</p>
                    </div>
                @endforelse
            </div>

        </div>
    </div>
</div>

@endsection