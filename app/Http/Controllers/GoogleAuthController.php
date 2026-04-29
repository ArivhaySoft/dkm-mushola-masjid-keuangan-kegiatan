<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // Check if this is the very first user (no users exist)
            $isFirstUser = User::count() === 0;

            $user = User::updateOrCreate(
                ['email' => $googleUser->getEmail()],
                [
                    'name'      => $googleUser->getName(),
                    'avatar'    => $googleUser->getAvatar(),
                    'google_id' => $googleUser->getId(),
                    'password'  => null,
                ]
            );

            if ($isFirstUser) {
                // First user needs admin secret verification
                session(['pending_admin_user_id' => $user->id]);
                return redirect()->route('admin-setup');
            }

            // Assign default role if no roles
            if ($user->roles()->count() === 0) {
                $viewerRole = Role::where('name', 'viewer')->first();
                if ($viewerRole) {
                    $user->roles()->attach($viewerRole);
                }
            }

            Auth::login($user, true);

            return redirect()->route('dashboard');
        } catch (\Exception $e) {
            return redirect()->route('login')
                ->with('error', 'Gagal login dengan Google: ' . $e->getMessage());
        }
    }

    public function adminSetup()
    {
        if (!session('pending_admin_user_id')) {
            return redirect()->route('login');
        }
        return view('auth.admin-setup');
    }

    public function adminSetupStore(Request $request)
    {
        $request->validate([
            'admin_secret' => 'required|string',
        ]);

        $userId = session('pending_admin_user_id');
        if (!$userId) {
            return redirect()->route('login')->with('error', 'Sesi tidak valid.');
        }

        $secret = config('app.admin_secret');
        if (!$secret || $request->admin_secret !== $secret) {
            return back()->with('error', 'Password administrator tidak valid.');
        }

        $user = User::findOrFail($userId);

        // Assign admin role
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole && !$user->roles()->where('role_id', $adminRole->id)->exists()) {
            $user->roles()->attach($adminRole);
        }

        session()->forget('pending_admin_user_id');
        Auth::login($user, true);

        return redirect()->route('dashboard');
    }

    public function loginEmail(\Illuminate\Http\Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors(['email' => 'Email atau password salah.'])->onlyInput('email');
    }

    public function logout()
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('home');
    }
}
