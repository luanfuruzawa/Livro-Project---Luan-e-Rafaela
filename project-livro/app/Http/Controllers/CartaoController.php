<?php

namespace App\Http\Controllers;

use App\Models\Cartao;
use Illuminate\Http\Request;

class CartaoController extends Controller
{
    public function index()
    {
        $userId = session('user')['id'];
        $cartoes = Cartao::where('user_id', $userId)->latest()->get();

        return view('cartoes.index', compact('cartoes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'titular' => ['required', 'string', 'max:100'],
            'bandeira' => ['required', 'in:Visa,Mastercard,Elo,American Express'],
            'ultimos_quatro' => ['required', 'regex:/^\d{4}$/'],
            'validade_mes' => ['required', 'integer', 'between:1,12'],
            'validade_ano' => [
                'required',
                'integer',
                'digits:4',
                'min:'.now()->year,
                'max:'.now()->addYears(30)->year,
            ],
            'numero' => ['prohibited'],
            'numero_cartao' => ['prohibited'],
            'cvv' => ['prohibited'],
            'codigo_seguranca' => ['prohibited'],
        ]);

        if (
            (int) $validated['validade_ano'] === (int) now()->year
            && (int) $validated['validade_mes'] < (int) now()->month
        ) {
            return back()
                ->withErrors(['validade_mes' => 'O cartão precisa estar dentro da validade.'])
                ->withInput();
        }

        Cartao::create([
            ...$validated,
            'user_id' => session('user')['id'],
        ]);

        return redirect()->route('cartoes.index')->with('success', 'Cartão demonstrativo cadastrado.');
    }

    public function destroy(Cartao $cartao)
    {
        abort_unless($cartao->user_id === session('user')['id'], 403);

        $cartao->delete();

        return redirect()->route('cartoes.index')->with('success', 'Cartão removido.');
    }
}
