@extends('layout.main_layout')
@section('content')
    <div class="d-flex w-100">
        @include('componentes.sidebar')
        <div class="flex-grow-1 p-4 min-vh-100">

            <!-- Barra Superior -->
            <div
                class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom border-secondary border-opacity-25">
                <h2 class="fw-bold text-white mb-0">Catálogo de Livros</h2>
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
                                    <h4 class="card-title fw-bold text-dark mb-2 text-truncate fs-4">Annie With an E</h4>
                                    <div class="d-flex align-items-center gap-3 flex-wrap">
                                        <span class="fs-5 fw-bold text-secondary">15,90 R$</span>
                                        <span
                                            class="badge bg-success-subtle text-success border border-success-subtle fw-semibold px-3 py-2 fs-6">
                                            Em stock</span>
                                    </div>
                                </div>
                                <div class="text-end mt-3">
                                    <button type="button"
                                        class="btn btn-dark btn-sm d-inline-flex align-items-center px-3 py-2 fw-semibold">
                                        <i class="fa-solid fa-cart-plus me-2"></i>
                                        Adicionar
                                    </button>
                                    
                                    <button type="button"
                                        class="btn btn-dark btn-sm d-inline-flex align-items-center px-3 py-2 fw-semibold">
                                        <i class="fa-solid fa-pen-to-square me-2"></i>
                                        Editar
                                    </button>
                                   
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
<<<<<<< HEAD
                <!-- <div class="col">
                            <div class="card bg-white border-0 shadow-sm p-3 h-100 rounded-3">
                                <div class="row g-3 h-100">
                                    <div class="col-4 d-flex align-items-center">
                                        <img src="{{ asset('images/odette.png') }}" class="img-fluid rounded-2 shadow-sm"
                                            alt="Capa do Livro" style="object-fit: cover; aspect-ratio: 3/4; width: 100%;">
                                    </div>
                                    <div class="col-8 d-flex flex-column justify-content-between">
                                        <div>
                                            <h4 class="card-title fw-bold text-dark mb-2 text-truncate fs-4">Odette</h4>
                                            <div class="d-flex align-items-center gap-3 flex-wrap">
                                                <span class="fs-5 fw-bold text-secondary">19,90 €</span>
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle fw-semibold px-3 py-2 fs-6">
                                                    Sold Out
                                                </span>
                                            </div>
                                        </div>
                                        <div class="text-end mt-3">
                                            <button type="button" class="btn btn-dark btn-sm d-inline-flex align-items-center px-3 py-2 fw-semibold"><i class="fa-solid fa-cart-plus me-2"></i>Adicionar</button>
                                        </div>
                                    </div>
                                </div>
                            </div> 
                </div> -->
=======
>>>>>>> classe-livro
            </div>
        </div>
    </div>
@endsection