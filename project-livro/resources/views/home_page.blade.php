@extends('layout.main_layout')

@section('content')
    <div class="d-flex w-100">
        @include('componentes.sidebar')

        <div class="flex-grow-1 p-4 min-vh-100 bg-light">

            <!-- Barra Superior: Título + Botão de Criar (Apenas Admin) -->
            <div class="bg-white p-3 rounded-3 shadow-sm mb-4 border d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="fw-bold text-dark m-0">Catálogo de Livros</h3>
                    <small class="text-muted">Mostrando {{ $livros->count() }}
                        {{ $livros->count() == 1 ? 'livro cadastrado' : 'livros cadastrados' }}</small>
                </div>

                <!-- Botão de Criar Livro (Exclusivo para Admin) -->
                @if(strtolower($nivel_acesso) === 'admin')
                    <a href="{{ route('novo.livro') }}" class="btn btn-dark fw-semibold px-4 py-2 shadow-sm">
                        <i class="fa-solid fa-plus me-2"></i> Novo Livro
                    </a>
                @endif
            </div>

            <!-- Grid de Livros -->
            @if($livros->count() > 0)
                <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
                    @foreach($livros as $livro)
                        <div class="col">
                            <div class="card h-100 border-0 shadow-sm rounded-3 overflow-hidden bg-white">
                                <div class="row g-0 h-100">
                                    <!-- Capa do Livro -->
                                    <div class="col-4 bg-secondary-subtle d-flex align-items-center justify-content-center p-2">
                                        @if($livro->caminho_imagem)
                                            <img src="{{ asset('images/' . $livro->caminho_imagem) }}"
                                                class="img-fluid rounded-2 shadow-sm" alt="{{ $livro->titulo }}"
                                                style="height: 140px; object-fit: cover; width: 100%;">
                                        @else
                                            <i class="fa-solid fa-book fa-3x text-secondary opacity-50"></i>
                                        @endif
                                    </div>

                                    <!-- Detalhes do Livro -->
                                    <div class="col-8 p-3 d-flex flex-column justify-content-between">
                                        <div>
                                            <span class="badge bg-light text-dark border fw-normal mb-2">{{ $livro->genero }}</span>
                                            <h6 class="fw-bold text-dark mb-1 text-truncate" title="{{ $livro->titulo }}">
                                                {{ $livro->titulo }}</h6>
                                            <div class="text-primary fw-bold fs-5 mb-1">
                                                R$ {{ number_format($livro->preco, 2, ',', '.') }}
                                            </div>
                                            <div>
                                                <span
                                                    class="badge {{ $livro->estoque > 0 ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle' }}">
                                                    {{ $livro->estoque > 0 ? 'Em estoque (' . $livro->estoque . ')' : 'Esgotado' }}
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Botão Adicionar ao Carrinho -->
                                        <div class="text-end pt-2 mt-2 border-top">
                                            <form action="{{ route('carrinho.adicionar') }}" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="livro_id" value="{{ $livro->id }}">
                                                <input type="hidden" name="titulo" value="{{ $livro->titulo }}">
                                                <input type="hidden" name="preco" value="{{ $livro->preco }}">
                                                <input type="hidden" name="imagem" value="{{ $livro->caminho_imagem }}">

                                                <button type="submit" class="btn btn-dark btn-sm px-3 py-1 fw-semibold" {{ $livro->estoque <= 0 ? 'disabled' : '' }}>
                                                    <i class="fa-solid fa-cart-plus me-1"></i> Adicionar
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Estado Vazio (Sem Livros) -->
            @else
                <div class="card border-0 shadow-sm rounded-3 text-center p-5 my-5 bg-white">
                    <div class="py-5">
                        <div class="mb-3 text-secondary">
                            <i class="fa-solid fa-folder-open fa-4x opacity-25"></i>
                        </div>
                        <h4 class="fw-bold text-dark mb-1">Nenhum livro cadastrado</h4>
                        <p class="text-muted mb-4">O catálogo está vazio no momento.</p>

                        @if(strtolower($nivel_acesso) === 'admin')
                            <a href="{{ route('novo.livro') }}" class="btn btn-dark px-4 py-2 fw-semibold shadow-sm">
                                <i class="fa-solid fa-plus me-2"></i> Adicionar Primeiro Livro
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection