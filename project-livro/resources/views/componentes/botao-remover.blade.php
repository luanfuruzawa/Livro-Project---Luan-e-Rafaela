<form action="{{ $rota }}" method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja remover este item?');">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-outline-danger btn-sm d-inline-flex align-items-center px-3 py-2 fw-semibold">
        <i class="fa-solid fa-trash me-2"></i>
        Remover
    </button>
</form>