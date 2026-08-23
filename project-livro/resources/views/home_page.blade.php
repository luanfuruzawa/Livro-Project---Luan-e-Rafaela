@extends('layout.main_layout')
@section('content')
    <div class="d-flex w-100 min-vh-100 bg-secondary-subtle">
        @include('componentes.sidebar')
        <div class="flex-grow-1 p-4">
            <div
                class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom border-secondary border-opacity-25">
                <h2 class="fw-bold text-dark mb-0">Catálogo de Livros</h2>
            </div>
            <hr>
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
                                        <!-- Para Admin -->

                                        @if($nivel_acesso == 'Admin')
                                        <button type="button"
                                            class="btn btn-outline-dark btn-sm d-inline-flex align-items-center px-3 py-2 fw-semibold">
                                            <i class="fa-solid fa-pen-to-square me-2"></i>
                                            Editar
                                        </button><button type="button"
                                            class="btn btn-outline-dark btn-sm d-inline-flex align-items-center px-3 py-2 fw-semibold">
                                            <i class="fa-solid fa-trash me-2"></i>
                                            Remover
                                        </button>
                                        @endif
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
@endsection