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
                    @include('componentes.livro', ['livro' => $livro])
                @empty
                    <div class="col-12 text-center py-5">
                        <p class="fs-5 text-muted">Nenhum livro encontrado no catálogo.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection