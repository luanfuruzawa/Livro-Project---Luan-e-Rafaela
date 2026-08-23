@extends('layout.main_layout')
@section('content')

    <div class="d-flex w-100">
        @include('componentes.sidebar')
        <div class="flex-grow-1 p-4 min-vh-100 bg-secondary-subtle">
            <h2
                class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom border-secondary border-opacity-25 text-dark fw-bold">
                Pesquisar Livros</h2>
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
                                <input type="text" id="titulo-livro" name="titulo-livro"
                                    class="form-control form-control-lg text-dark bg-light border-start-0"
                                    placeholder="Digite o título ou parte dele..."
                                    value="{{ $titulo ?? request('titulo-livro') }}">
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="filtro_genero" class="form-label fw-bold text-dark fs-5">Filtrar por
                                Categoria</label>
                            <select id="filtro_genero" name="genero" class="form-select form-control-lg text-dark bg-light">
                                <option value="" {{ empty($genero ?? request('genero')) ? 'selected' : '' }}>Todas as
                                    categorias</option>
                                <option value="Ficcao" @selected(($genero ?? request('genero')) == 'Ficcao')>Ficção Científica
                                </option>
                                <option value="Romance" @selected(($genero ?? request('genero')) == 'Romance')>Romance
                                </option>
                                <option value="Suspense" @selected(($genero ?? request('genero')) == 'Suspense')>Suspense
                                </option>
                                <option value="Terror" @selected(($genero ?? request('genero')) == 'Terror')>Terror</option>
                                <option value="Drama" @selected(($genero ?? request('genero')) == 'Drama')>Drama</option>
                                <option value="Fantasia" @selected(($genero ?? request('genero')) == 'Fantasia')>Fantasia
                                </option>
                                <option value="Misterio" @selected(($genero ?? request('genero')) == 'Misterio')>Mistério /
                                    Thriller</option>
                                <option value="Biografia" @selected(($genero ?? request('genero')) == 'Biografia')>Biografia
                                </option>
                                <option value="Aventura" @selected(($genero ?? request('genero')) == 'Aventura')>Aventura
                                </option>
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
                    @include('componentes.livro', ['livro' => $livro])
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