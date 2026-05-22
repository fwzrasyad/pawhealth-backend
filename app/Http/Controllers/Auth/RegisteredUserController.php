<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
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
        return Inertia::render('Auth/Register');
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
        ]);

        $auth = app('firebase.auth');
        try {
            $firebaseUser = $auth->createUser([
                'email'         => $request->email,
                'password'      => $request->password,
                'displayName'   => $request->name,
            ]);
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'email' => 'Failed to create Firebase account: ' . $e->getMessage(),
            ]);
        }

        // Create the pending clinic first
        $clinic = \App\Models\Clinic::create([
            'name' => $request->name . "'s Clinic", // Fallback name
            'status' => 'pending',
        ]);

        $user = User::create([
            'user_id' => $firebaseUser->uid,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'manager',
            'clinic_id' => $clinic->clinic_id,
        ]);

        event(new Registered($user));

        // Do not use Auth::login($user) because Manager portal relies on Firebase Auth.
        // Simply redirect them to the manager login screen.
        return redirect('/manager/login')->with('status', 'Registration successful. Please log in.');
    }
}
