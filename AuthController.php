<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Post;

class AuthController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function register(Request $request)
    {
        try {
            // dd($request->all());
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'nim' => 'required|string|max:10|unique:users',
                'password' => 'required|string|min:8',
            ]);

            $user = User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'nim' => $validatedData['nim'],
                'password' => bcrypt($validatedData['password']),
                'role' => 'user',
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Registration successful. Please login.',
                    'user' => $user
                ], 201);
            }

            // Optionally, log the user in after registration
            Auth::login($user);

            return redirect('/')->with('success', 'Registration successful. Please login.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }
            // For non-AJAX requests, Laravel's default exception handler
            // will redirect back with errors, so we just re-throw.
            throw $e;
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function login(Request $request)
    {
        try {
            $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            ]);

            if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user();

            if ($request->wantsJson()) {
                return response()->json([
                'success' => true,
                'message' => 'Login successful.',
                'user' => $user,
                'redirect_url' => $user->role === 'admin' ? '/admin-home' : ($user->role === 'editor' ? '/editor-home' : '/')
                ]);
            }

            if ($user->role === 'admin') {
                return redirect()->intended('/admin-home')->with('success', 'You are logged in!');
            } elseif ($user->role === 'editor') {
                return redirect()->intended('/editor-home')->with('success', 'You are logged in!');
            }

            return redirect()->intended('/')->with('success', 'You are logged in!');
            }

            if ($request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'The provided credentials do not match our records.',
            ], 401);
            }

            return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
            ])->onlyInput('email');

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
            }
            throw $e;
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        Auth::logout();
        return redirect('/login')->with('success', 'You have been logged out.');
    }
}
