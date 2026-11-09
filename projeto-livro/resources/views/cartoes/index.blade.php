@extends('layout.main_layout')

@section('content')
    <div class="d-flex w-100">
        @include('componentes.sidebar')
        <main class="flex-grow-1 p-4 p-md-5 min-vh-100 bg-light">
            <div class="container-fluid" style="max-width: 1000px;">
                @if(session('success'))
                    <div class="alert alert-success border-0 shadow-sm" role="alert">
                        {{ session('success') }}
                    </div>
                @endif

                <section class="card border-0 shadow-sm rounded-3 bg-white p-4 mb-4">
                    <h2 class="fw-bold text-dark mb-1">Cartões</h2>
                </section>

                <section class="card border-0 shadow-sm rounded-3 bg-white p-4 mb-4">
                    <h3 class="h5 fw-bold mb-3">Cadastrar cartão</h3>
                    <form method="POST" action="{{ route('cartoes.store') }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="titular" class="form-label">Nome do titular</label>
                                <input id="titular" name="titular" type="text" maxlength="100"
                                    value="{{ old('titular') }}" class="form-control @error('titular') is-invalid @enderror"
                                    required autocomplete="off">
                                @error('titular') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="bandeira" class="form-label">Bandeira</label>
                                <select id="bandeira" name="bandeira"
                                    class="form-select @error('bandeira') is-invalid @enderror" required>
                                    <option value="">Selecione</option>
                                    @foreach(['Visa', 'Mastercard', 'Elo', 'American Express'] as $bandeira)
                                        <option value="{{ $bandeira }}" @selected(old('bandeira') === $bandeira)>
                                            {{ $bandeira }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('bandeira') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="ultimos_quatro" class="form-label">Ultimos quatro dígitos</label>
                                <input id="ultimos_quatro" name="ultimos_quatro" type="text" inputmode="numeric"
                                    pattern="[0-9]{4}" maxlength="4" value="{{ old('ultimos_quatro') }}"
                                    class="form-control @error('ultimos_quatro') is-invalid @enderror" required
                                    autocomplete="off">
                                @error('ultimos_quatro') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="validade_mes" class="form-label">Mês de validade</label>
                                <select id="validade_mes" name="validade_mes"
                                    class="form-select @error('validade_mes') is-invalid @enderror" required>
                                    <option value="">Mês</option>
                                    @foreach(range(1, 12) as $mes)
                                        <option value="{{ $mes }}" @selected((string) old('validade_mes') === (string) $mes)>
                                            {{ sprintf('%02d', $mes) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('validade_mes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="validade_ano" class="form-label">Ano de validade</label>
                                <input id="validade_ano" name="validade_ano" type="number"
                                    min="{{ now()->year }}" max="{{ now()->year + 30 }}"
                                    value="{{ old('validade_ano') }}"
                                    class="form-control @error('validade_ano') is-invalid @enderror" required>
                                @error('validade_ano') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <button type="submit" class="btn btn-dark mt-4">
                            <i class="fa-solid fa-plus me-2"></i>Cadastrar cartão
                        </button>
                    </form>
                </section>

                <section class="card border-0 shadow-sm rounded-3 bg-white p-4">
                    <h3 class="h5 fw-bold mb-3">Cartões cadastrados</h3>
                    @forelse($cartoes as $cartao)
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 border rounded-3 p-3 mb-3">
                            <div>
                                <div class="fw-bold">{{ $cartao->bandeira }} •••• {{ $cartao->ultimos_quatro }}</div>
                                <div class="text-muted">{{ $cartao->titular }}</div>
                                <small class="text-muted">
                                    Validade: {{ sprintf('%02d/%04d', $cartao->validade_mes, $cartao->validade_ano) }}
                                </small>
                            </div>
                            <form method="POST" action="{{ route('cartoes.destroy', $cartao) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                    Remover
                                </button>
                            </form>
                        </div>
                    @empty
                        <p class="text-muted mb-0">Nenhum cartão cadastrado.</p>
                    @endforelse
                </section>
            </div>
        </main>
    </div>
@endsection
