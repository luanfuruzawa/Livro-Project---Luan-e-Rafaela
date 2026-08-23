@extends('layout.main_layout')

@section('content')
    <div class="d-flex w-100">
        @include('componentes.sidebar')

        <div class="flex-grow-1 p-4 p-md-5 min-vh-100 bg-light">
            <div class="container-fluid" style="max-width: 1000px;">

                <!-- Cabeçalho -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="fw-bold text-dark m-0">Meu Carrinho</h2>
                        <small class="text-muted">Confira os itens selecionados antes de finalizar</small>
                    </div>

                    @if(count($carrinho) > 0)
                        <form action="{{ route('carrinho.limpar') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger btn-sm px-3 fw-semibold">
                                <i class="fa-solid fa-trash me-1"></i> Esvaziar
                            </button>
                        </form>
                    @endif
                </div>

                <!-- Listagem do Carrinho -->
                @if(count($carrinho) > 0)
                    <div class="row g-4">
                        <!-- Tabela de Produtos -->
                        <div class="col-lg-8">
                            <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Item</th>
                                                <th class="text-center">Preço</th>
                                                <th class="text-center">Qtd</th>
                                                <th class="text-end">Remover</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $totalGeral = 0; @endphp
                                            @foreach($carrinho as $id => $item)
                                                @php 
                                                                                            $subtotal = $item['preco'] * $item['quantidade'];
                                                    $totalGeral += $subtotal;
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center gap-3">
                                                            @if($item['imagem'])
                                                                <img src="{{ asset('images/' . $item['imagem']) }}"
                                                                    class="rounded-2 shadow-sm"
                                                                    style="width: 45px; height: 60px; object-fit: cover;">
                                                            @else
                                                                <div class="bg-secondary-subtle rounded-2 d-flex align-items-center justify-content-center"
                                                                    style="width: 45px; height: 60px;">
                                                                    <i class="fa-solid fa-book text-muted"></i>
                                                                </div>
                                                            @endif
                                                            <div>
                                                                <h6 class="fw-bold text-dark mb-0 text-truncate"
                                                                    style="max-width: 180px;">{{ $item['titulo'] }}</h6>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="text-center fw-semibold text-dark">
                                                        R$ {{ number_format($item['preco'], 2, ',', '.') }}
                                                    </td>
                                                    <td class="text-center">
                                                        <form action="{{ route('carrinho.atualizar') }}" method="POST"
                                                            class="d-inline-flex">
                                                            @csrf
                                                            <input type="hidden" name="livro_id" value="{{ $id }}">
                                                            <input type="number" name="quantidade" value="{{ $item['quantidade'] }}"
                                                                min="1" class="form-control form-control-sm text-center fw-bold"
                                                                style="width: 60px;" onchange="this.form.submit()">
                                                        </form>
                                                    </td>
                                                    <td class="text-end">
                                                        <form action="{{ route('carrinho.remover') }}" method="POST"
                                                            class="d-inline">
                                                            @csrf
                                                            <input type="hidden" name="livro_id" value="{{ $id }}">
                                                            <button type="submit" class="btn btn-light text-danger btn-sm border-0">
                                                                <i class="fa-solid fa-xmark fs-5"></i>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Resumo Financeiro -->
                        <div class="col-lg-4">
                            <div class="card border-0 shadow-sm rounded-3 bg-white p-4">
                                <h5 class="fw-bold text-dark mb-3">Resumo da Compra</h5>

                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Subtotal:</span>
                                    <span class="fw-bold text-dark">R$ {{ number_format($totalGeral, 2, ',', '.') }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-3">
                                    <span class="text-muted">Frete:</span>
                                    <span class="text-success fw-bold">Grátis</span>
                                </div>

                                <hr>

                                <div class="d-flex justify-content-between mb-4">
                                    <span class="fs-5 fw-bold text-dark">Total:</span>
                                    <span class="fs-4 fw-bold text-primary">R$
                                        {{ number_format($totalGeral, 2, ',', '.') }}</span>
                                </div>

                                <button class="btn btn-dark btn-lg w-100 fw-semibold shadow-sm mb-2">
                                    Finalizar Pedido
                                </button>
                                <a href="{{ route('home_page') }}" class="btn btn-outline-secondary w-100 fw-semibold">
                                    Continuar Comprando
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Carrinho Vazio -->
                @else
                    <div class="card border-0 shadow-sm rounded-3 text-center p-5 bg-white">
                        <div class="py-5">
                            <div class="mb-3 text-secondary">
                                <i class="fa-solid fa-cart-shopping fa-4x opacity-25"></i>
                            </div>
                            <h4 class="fw-bold text-dark mb-1">Seu carrinho está vazio</h4>
                            <p class="text-muted mb-4">Adicione livros do catálogo para visualizar suas escolhas aqui.</p>
                            <a href="{{ route('home_page') }}" class="btn btn-dark px-4 py-2 fw-semibold shadow-sm">
                                <i class="fa-solid fa-arrow-left me-2"></i> Ir para o Catálogo
                            </a>
                        </div>
                    </div>
                @endif
                s
            </div>
        </div>
    </div>
@endsection