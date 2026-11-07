@extends('layout.main_layout')

@section('content')
    <div class="d-flex w-100">
        @include('componentes.sidebar')
        <div class="flex-grow-1 p-4 p-md-5 min-vh-100 bg-light">
            <div class="container-fluid" style="max-width: 1000px;">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                        <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                <div class="card border-0 shadow-sm rounded-3 bg-white p-4 mb-4">
                    <div>
                        <h2 class="fw-bold text-dark m-0">
                            @include('componentes.botao-voltar')
                            Meus Pedidos
                        </h2>
                        <small class="text-muted">Consulte o histórico de todas as suas compras</small>
                    </div>
                </div>
                @if($pedidos->count() > 0)
                    @foreach($pedidos as $pedido)
                        <div class="card border-0 shadow-sm rounded-3 bg-white mb-4 overflow-hidden">
                            <!-- Topo do Card do Pedido -->
                            <div class="card-header bg-dark text-white p-3 border-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div>
                                    <span class="fw-bold me-3">Pedido #{{ $pedido->id }}</span>
                                    <small class="text-white-50">Realizado em: {{ $pedido->created_at ? $pedido->created_at->format('d/m/Y H:i') : 'N/A' }}</small>
                                </div>
                                <div class="fs-5 fw-bold text-warning">
                                    Total: R$ {{ number_format($pedido->valor_total, 2, ',', '.') }}
                                </div>
                            </div>

                            <!-- Corpo do Card / Itens do Pedido -->
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="ps-4">Livro</th>
                                                <th class="text-center">Qtd</th>
                                                <th class="text-end pe-4">Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($pedido->itens as $item)
                                                <tr>
                                                    <td class="ps-4">
                                                        <div class="d-flex align-items-center gap-3">
                                                            @if(!empty($item->livro->caminho_imagem))
                                                                <img src="{{ asset('images/' . $item->livro->caminho_imagem) }}"
                                                                    class="rounded-2 shadow-sm"
                                                                    style="width: 35px; height: 48px; object-fit: cover;">
                                                            @else
                                                                <div class="bg-secondary-subtle rounded-2 d-flex align-items-center justify-content-center"
                                                                    style="width: 35px; height: 48px;">
                                                                    <i class="fa-solid fa-book text-muted" style="font-size: 0.8rem;"></i>
                                                                </div>
                                                            @endif
                                                            <div>
                                                                <h6 class="fw-bold text-dark mb-0 text-truncate" style="max-width: 300px;">
                                                                    {{ $item->livro->titulo ?? 'Livro indisponível' }}
                                                                </h6>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="text-center fw-semibold text-muted">
                                                        {{ $item->quantidade }}x
                                                    </td>
                                                    <td class="text-end pe-4 fw-semibold text-dark">
                                                        R$ {{ number_format($item->valor_total, 2, ',', '.') }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <!-- Caso não tenha pedidos -->
                    <div class="card border-0 shadow-sm rounded-3 text-center p-5 bg-white">
                        <div class="py-5">
                            <div class="mb-3 text-secondary">
                                <i class="fa-solid fa-box-open fa-4x opacity-25"></i>
                            </div>
                            <h4 class="fw-bold text-dark mb-1">Ainda não fez nenhuma compra</h4>
                            <p class="text-muted mb-4">Os seus pedidos finalizados aparecerão listados aqui.</p>
                            <a href="{{ route('home_page') }}" class="btn btn-dark px-4 py-2 fw-semibold shadow-sm">
                                Voltar à Loja
                            </a>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>
@endsection