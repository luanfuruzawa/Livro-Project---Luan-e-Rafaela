<div class="col">
    <div class="card bg-white border-0 shadow-sm p-3 h-100 rounded-3">
        <div class="row g-3 h-100">
            @if ($livro->caminho_imagem)
                <div class="col-4 d-flex align-items-center">
                    <img src="{{ asset('images/' . $livro->caminho_imagem) }}" class="img-fluid rounded-2 shadow-sm"
                        alt="Capa do Livro {{ $livro->titulo }}" style="object-fit: cover; aspect-ratio: 3/4; width: 100%;">
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

                <div class="text-end mt-3 gap-2 d-flex justify-content-end align-items-center">
                        @include('componentes.botao-adicionar-carrinho')
                    @can('admin')
                        @include('componentes.botao-editar', ['livro' => $livro])
                        @include('componentes.botao-remover', ['rota' => route('livros.destroy', $livro->id)])
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>