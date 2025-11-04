<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use App\Services\Admin\ContractExpirationService;
use Illuminate\Support\Facades\Cache;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/admin';
    
    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(\Illuminate\Http\Request $request, $user)
    {
        // Check if this is first login of the day and send expired contracts notification
        $this->checkExpiredContractsOnFirstLogin();
        
        return redirect()->route('admin.dashboard');
    }

    /**
     * Check and send expired contracts notification on first login of the day
     *
     * @return void
     */
    protected function checkExpiredContractsOnFirstLogin(): void
    {
        $today = now()->format('Y-m-d');
        $cacheKey = "first_login_today_{$today}";
        
        // Check if already checked today using cache
        if (Cache::has($cacheKey)) {
            return;
        }
        
        try {
            $contractExpirationService = app(ContractExpirationService::class);
            $result = $contractExpirationService->checkAndSendExpiredContracts();
            
            // Mark as checked today (cache for 24 hours)
            Cache::put($cacheKey, true, now()->endOfDay());
            
            // If failed, allow retry on next login
            if (!$result['success'] && isset($result['retry_count']) && $result['retry_count'] < 2) {
                // Don't cache if failed, allow retry on next login
                Cache::forget($cacheKey);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to check expired contracts on login', [
                'error' => $e->getMessage(),
            ]);
            // Don't cache on error, allow retry
            Cache::forget($cacheKey);
        }
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

}
