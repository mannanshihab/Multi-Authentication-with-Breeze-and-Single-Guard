<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>


## Laravel Installation

    composer create-project laravel/laravel example-app

## Laravel Breeze
    Laravel Breeze is a minimal, simple implementation of all of Laravel's authentication features, including login, registration, password reset, email verification, and password confirmation. In addition, Breeze includes a simple "profile" page where the user may update their name, email address, and password.

## Installation Breeze

    composer require laravel/breeze --dev

    php artisan breeze:install
 
    php artisan migrate
    npm install
    npm run dev

## Migration Table
    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('email')->unique();
        $table->timestamp('email_verified_at')->nullable();
        $table->string('password');
        $table->enum('role', ['super-admin', 'admin', 'customer'])->default('customer');
        $table->enum('status', ['Active','Inactive'])->default('Active');
        $table->enum('deleted', ['Yes','No'])->default('No');
        $table->rememberToken();
        $table->timestamp('deleted_at')->nullable();
        $table->timestamps();
    });

## Create User Seeder
   php artisan make:seeder UserSeeder

## User Seeder File
   use Illuminate\Support\Facades\DB;

    public function run(): void
    {
        DB::table('users')->insert([
            [
                'name' => 'SuperAdmin',
                'email' => 'superadmin@test.com',
                'password' => bcrypt('password'),
                'role' => 'super-admin',
            ],
            [
                'name' => 'Admin',
                'email' => 'admin@test.com',
                'password' => bcrypt('password'),
                'role' => 'admin',
            ],
            [
                'name' => 'Customer',
                'email' => 'customer@test.com',
                'password' => bcrypt('password'),
                'role' => 'customer',
            ],
        ]);
    }

## Call DatabaseSeeder

    $this->call([UserSeeder::class]);

## Route

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->middleware(['auth', 'role:customer', 'verified'])->name('dashboard');

    Route::middleware(['auth', 'role:super-admin'])->group(function () {
        Route::get('/superAdmin/dashboard', [SuperAdminController::class, 'index'])->name('SuperAdmin');
    });

    Route::middleware(['auth', 'role:admin'])->group(function () {
        Route::get('/admin/dashboard', [AdminController::class, 'index'])->name('admin');
    });

## Navigation Menu

    @if( Auth::user()->role == 'super-admin')
        <x-nav-link :href="route('SuperAdmin')" :active="request()->routeIs('SuperAdmin')">
            {{ __('Dashboard') }}
        </x-nav-link>
        <x-nav-link :href="route('SuperAdmin')" :active="request()->routeIs('Category')">
            {{ __('Category') }}
        </x-nav-link>
        <x-nav-link :href="route('SuperAdmin')" :active="request()->routeIs('Post')">
            {{ __('Post') }}
        </x-nav-link>
    @elseif( Auth::user()->role == 'admin')
        <x-nav-link :href="route('admin')" :active="request()->routeIs('admin')">
            {{ __('Dashboard') }}
        </x-nav-link>
    @else
        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
            {{ __('Dashboard') }}
        </x-nav-link>
    @endif

## Multi-Authentication-with-Breeze-and-Single-Guard
    
    php artisan make:middleware RoleMiddleware

## Kernel Add This Class

    protected $middlewareAliases = [
        'role' => \App\Http\Middleware\RoleMiddleware::class,
    ];    

## RoleMiddleware::class

    public function handle(Request $request, Closure $next, $role): Response
    {
        if($request->User()->role !== $role){
            abort(404);
        }
        return $next($request);
    }

## Authenticated Session Controller

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

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }