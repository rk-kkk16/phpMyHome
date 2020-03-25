<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

use Illuminate\Http\Request;

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
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    // added for api_token authentication
    protected function authenticated(Request $request, $user)
    {
        $user->api_token = sha1(date('r') . $user->id . microtime(true));
        $user->save();
    }

    public function logout(Request $request)
    {
        // api_tokenをnullにする
        $user = $request->user();
        $user->api_token = null;
        $user->save();

        $this->guard()->logout();

        $request->session()->flush();
        $request->session()->regenerate();

        return redirect('/');
    }
}
