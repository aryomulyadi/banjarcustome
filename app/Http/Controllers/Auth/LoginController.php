<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->to($this->homeFor(Auth::user()));
        }

        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'Email atau password salah.',
            ]);
        }

        if (! Auth::user()->isStaff()) {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => 'Email atau password salah.',
            ]);
        }

        Auth::user()->forceFill(['last_login_at' => now()])->saveQuietly();

        $request->session()->regenerate();

        return redirect()->to($this->redirectTo($request));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    protected function homeFor($user): string
    {
        if ($user?->isAdmin()) {
            return route('admin.dashboard');
        }

        if ($user?->isProduksi()) {
            return route('admin.produksi');
        }

        return route('home');
    }

    protected function redirectTo(Request $request): string
    {
        $intended = $request->session()->pull('url.intended');
        $path = $intended ? (string) parse_url($intended, PHP_URL_PATH) : null;
        $user = Auth::user();

        if ($path !== null && str_starts_with($path, '/admin')) {
            if ($user?->isProduksi() && ! $this->isProduksiPath($path)) {
                return route('admin.produksi');
            }

            return $intended;
        }

        return $user?->isProduksi() ? route('admin.produksi') : route('admin.dashboard');
    }

    /**
     * Rute /admin yang boleh dituju akun produksi setelah login.
     * Selain itu (mis. /admin atau /admin/pesanan milik admin) diarahkan
     * ke antrian agar tidak berujung 403.
     */
    protected function isProduksiPath(string $path): bool
    {
        if (preg_match('#^/admin/(produksi|profil)(/|$)#', $path) === 1) {
            return true;
        }

        return preg_match('#^/admin/pesanan/\d+#', $path) === 1;
    }
}
