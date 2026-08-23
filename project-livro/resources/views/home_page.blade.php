@extends('layout.main_layout')

@section('content')
    <div class="d-flex w-100">
        @include('componentes.sidebar')
        <div class="flex-grow-1 p-4 min-vh-100 bg-light">
            <div class="bg-white p-3 rounded-3 shadow-sm mb-4 border d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="fw-bold text-dark m-0">Catálogo de Livros</h3>
                    <small class="text-muted">
                        @if(strtolower($nivel_acesso) === 'admin')
                            Mostrando {{ $livros->count() }}
                            {{ $livros->count() == 1 ? 'livro cadastrado' : 'livros cadastrados' }}
                        @else
                            Confira {{ $livros->count() }}
                            {{ $livros->count() == 1 ? 'livro disponível' : 'livros disponíveis' }} no nosso catálogo
                        @endif
                    </small>
                </div>
                @can('admin')
                    <a href="{{ route('novo_livro') }}" class="btn btn-dark fw-semibold px-4 py-2 shadow-sm">
                        <i class="fa-solid fa-plus me-2"></i> Novo Livro
                    </a>
                @endcan
            </div>
            <hr>
            @if($livros->count() > 0)
                <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
                    @foreach($livros as $livro)
                        @include('componentes.livro', ['livro' => $livro])
                    @endforeach
                </div>
            @else
                <div class="carfd border-0 shadow-sm rounded-3 text-center p-5 my-5 bg-white">
                    <div class="py-5">
                        <div class="mb-3 text-secondary">
                            <i class="fa-solid fa-folder-open fa-4x opacity-25"></i>
                        </div>
                        <h4 class="fw-bold text-dark mb-1">Nenhum livro cadastrado</h4>
                        <p class="text-muted mb-4">O catálogo está vazio no momento.</p>
                        @can('admin')
                            <a href="{{ route('novo_livro') }}" class="btn btn-dark px-4 py-2 fw-semibold shadow-sm">
                                <i class="fa-solid fa-plus me-2"></i> Adicionar Primeiro Livro
                            </a>
                        @endcan
                    </div>
                </div>
            @endif

        </div>
    </div>
@endsection