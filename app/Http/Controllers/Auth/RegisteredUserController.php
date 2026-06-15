<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Cryptocurrency;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register', [
            // Criptos disponibles para declarar el estado inicial
            'cryptocurrencies' => Cryptocurrency::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'symbol']),
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],

            // Estado inicial (flujo del enunciado): saldo USD + tenencias de criptos
            'initial_usd' => ['nullable', 'numeric', 'min:0', 'max:100000000'],
            'holdings' => ['nullable', 'array'],
            'holdings.*.cryptocurrency_id' => ['required', 'integer', 'exists:cryptocurrencies,id'],
            'holdings.*.quantity' => ['required', 'numeric', 'min:0'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // El estado inicial declarado define el portafolio de partida
        $portfolio = $user->portfolio()->create([
            'usd_balance' => $request->input('initial_usd') ?? 100000,
        ]);

        // Crea las tenencias de cripto declaradas (solo las que tengan cantidad > 0)
        foreach ($request->input('holdings', []) as $holding) {
            if ((float) ($holding['quantity'] ?? 0) > 0) {
                $portfolio->assets()->create([
                    'cryptocurrency_id' => $holding['cryptocurrency_id'],
                    'balance' => $holding['quantity'],
                ]);
            }
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
