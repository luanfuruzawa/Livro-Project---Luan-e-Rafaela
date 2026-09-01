<div class="d-flex flex-column flex-shrink-0 p-3 text-white bg-black shadow-lg position-sticky top-0 vh-100"
    style="width: 260px;">
    <a href="{{ route('home_page') }}"
        class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
        <i class="fa-solid fa-book-open fa-xl me-2 text-white-50"></i>
        <span class="fs-4 fw-bold">LivrosApp</span>
    </a>
    <hr class="border-secondary opacity-25">
    <ul class="nav nav-pills flex-column mb-auto gap-2">
        <li>
            <div class="nav-link text-white bg-secondary bg-opacity-25 d-flex align-items-center py-2 px-3 rounded">
                <i class="fa-solid fa-user-circle fa-lg text-secondary me-3"></i>
                <!-- Exibe o nome do usuário vindo da sessão -->
                <span>{{ session('user')['username'] ?? 'Nome Usuario' }}</span>
            </div>
        </li>
        <li class="nav-item">
            <a href="pesquisar-livro"
                class="nav-link text-white bg-secondary bg-opacity-25 d-flex align-items-center py-2 px-3 rounded">
                <i class="fa-solid fa-magnifying-glass me-3 text-white-50"></i>
                <span>Pesquisar</span>
            </a>
        </li>
        <li>
            <a href="{{ route('carrinho.index') }}"
                class="nav-link text-white bg-secondary bg-opacity-25 d-flex align-items-center py-2 px-3 rounded">
                <i class="fa-solid fa-cart-shopping me-3 text-white-50"></i>
                <span>Carrinho</span>

                @php
                    $userId = session('user')['id'];
                    $totalItens = \App\Models\Carrinho::where('user_id', $userId)
                        ->whereHas('livro')
                        ->sum('quantidade');
                @endphp

                @if($totalItens > 0)
                    <span class="badge bg-danger rounded-pill fs-7">
                        {{ $totalItens }}
                    </span>
                @endif
            </a>
        </li>
    </ul>
    <hr class="border-secondary opacity-25">
    <div>
        <a href="{{ route('logout') }}"
            class="nav-link text-danger bg-danger bg-opacity-10 d-flex align-items-center py-2 px-3 rounded">
            <i class="fa-solid fa-right-from-bracket me-3"></i>
            <span class="fw-bold">Logout</span>
        </a>
    </div>
</div>