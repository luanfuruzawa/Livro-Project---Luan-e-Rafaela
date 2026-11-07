<form action="{{ route('carrinho.adicionar') }}" method="POST" class="m-0">
                        @csrf
                        <input type="hidden" name="livro_id" value="{{ $livro->id }}">
                        <input type="hidden" name="quantidade" value="1">

                        <button type="submit"
                            class="btn btn-dark btn-sm d-inline-flex align-items-center px-3 py-2 fw-semibold"
                            {{ $livro->estoque <= 0 ? 'disabled' : '' }}>
                            <i class="fa-solid fa-cart-plus me-2"></i>
                            Adicionar
                        </button>
                    </form>