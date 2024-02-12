<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        //$request->authenticate();
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
    
        $credentials = $request->only('email', 'password');
    
        if (Auth::attempt($credentials, $request->filled('remember'))) {
            
            if (Auth::user()->deleted_at === null AND Auth::user()->deleted === 'No') {

                $request->session()->regenerate();

                if($request->user()->role === 'super-admin'){
                    
                    return redirect()->route('SuperAdmin');
        
                }elseif($request->user()->role === 'admin'){
                    
                    return redirect()->route('admin');
        
                }else{
                    return redirect()->intended('/dashboard');
                }
            } else {
                Auth::logout();
                return back()->withErrors(['email' => 'Your account has been deleted.']);
            }
        }
    
        return back()->withErrors(['email' => 'The provided credentials do not match our records.']);

    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
