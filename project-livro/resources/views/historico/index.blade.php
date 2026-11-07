@extends('layout.main_layout')

@section('content')
    <div class="d-flex w-100">
        @include('componentes.sidebar')
        <main class="flex-grow-1 p-4 min-vh-100 bg-light" style="min-width: 0;">
            <div class="bg-white p-4 rounded-3 shadow-sm mb-4 border">
                <h2 class="fw-bold text-dark mb-1">Histórico</h2>
                <p class="text-muted mb-0">Consulte as ações e as alterações dos registros.</p>
            </div>

            <form method="GET" action="{{ route('historico.index') }}" class="bg-white p-3 rounded-3 border mb-4">
                <div class="row g-3 align-items-end">
                    <div class="col-sm-6 col-lg-3">
                        <label for="area" class="form-label">Área</label>
                        <select id="area" name="area" class="form-select">
                            <option value="">Todas</option>
                            @foreach($areas as $valor => $nome)
                                <option value="{{ $valor }}" @selected(request('area') === $valor)>{{ $nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <label for="acao" class="form-label">Ação</label>
                        <select id="acao" name="acao" class="form-select">
                            <option value="">Todas</option>
                            @foreach($acoes as $valor => $nome)
                                <option value="{{ $valor }}" @selected(request('acao') === $valor)>{{ $nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <label for="usuario" class="form-label">Usuário</label>
                        <select id="usuario" name="usuario" class="form-select">
                            <option value="">Todos</option>
                            @foreach($usuarios as $usuario)
                                <option value="{{ $usuario->id }}" @selected((string) request('usuario') === (string) $usuario->id)>{{ $usuario->username }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <label for="inicio" class="form-label">De</label>
                        <input id="inicio" name="inicio" type="date" value="{{ request('inicio') }}" class="form-control">
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <label for="fim" class="form-label">Até</label>
                        <input id="fim" name="fim" type="date" value="{{ request('fim') }}" class="form-control">
                    </div>
                    <div class="col-sm-6 col-lg-3 d-flex gap-2">
                        <button type="submit" class="btn btn-dark">Filtrar</button>
                        <a href="{{ route('historico.index') }}" class="btn btn-outline-secondary">Limpar</a>
                    </div>
                </div>
                @if($errors->any())
                    <div class="alert alert-danger mt-3 mb-0" role="alert">Verifique os filtros e o intervalo de datas.</div>
                @endif
            </form>

            <div class="bg-white rounded-3 border shadow-sm overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Data</th>
                                <th>Usuário</th>
                                <th>Ação</th>
                                <th>Registro</th>
                                <th class="pe-3">Detalhes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($registros as $registro)
                                @php
                                    $antes = \Illuminate\Support\Arr::only((array) $registro->properties->get('old', []), array_keys($campos));
                                    $depois = \Illuminate\Support\Arr::only((array) $registro->properties->get('attributes', []), array_keys($campos));
                                    $chaves = array_unique(array_merge(array_keys($antes), array_keys($depois)));
                                    $formatar = fn ($valor) => is_scalar($valor) ? (string) $valor : '—';
                                @endphp
                                <tr>
                                    <td class="ps-3 text-nowrap">{{ $registro->created_at?->format('d/m/Y H:i:s') }}</td>
                                    <td>{{ $registro->causer?->username ?? ($registro->causer_id ? 'Usuário #'.$registro->causer_id : 'Sistema') }}</td>
                                    <td>{{ $acoes[$registro->event] ?? 'Ação registrada' }}</td>
                                    <td class="text-nowrap">{{ $areas[$registro->log_name] ?? 'Sistema' }}{{ $registro->subject_id ? ' #'.$registro->subject_id : '' }}</td>
                                    <td class="pe-3">
                                        @if(count($chaves) || $registro->properties->get('request_id'))
                                            <details>
                                                <summary>Ver detalhes</summary>
                                                @if(count($chaves))
                                                    <table class="table table-sm mt-2 mb-1">
                                                        <thead><tr><th>Campo</th><th>Antes</th><th>Depois</th></tr></thead>
                                                        <tbody>
                                                            @foreach($chaves as $chave)
                                                                <tr>
                                                                    <td>{{ $campos[$chave] }}</td>
                                                                    <td class="text-break">{{ $formatar($antes[$chave] ?? null) }}</td>
                                                                    <td class="text-break">{{ $formatar($depois[$chave] ?? null) }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                @endif
                                                @if($registro->properties->get('request_id'))
                                                    <small class="text-muted text-break">Requisição: {{ $registro->properties->get('request_id') }}</small>
                                                @endif
                                            </details>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted p-5">Nenhum registro encontrado.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mt-4">{{ $registros->links() }}</div>
        </main>
    </div>
@endsection
