<?php

namespace App\Http\Controllers\Auth;

use App;
use App\Http\Controllers\Controller;
use App\Model\AtlasLogistics;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = 'admin/';

    public function __construct()
    {
        App::setLocale('English-en');
        $this->middleware('guest')->except('logout');
    }

    public function showLoginForm()
    {
        return view('auth.login-attex');
    }

    /**
     * Redirect user after authentication
     */
    protected function authenticated(Request $request, $user)
    {


        if ($user->user_type == 'C') {
            Auth::logout();
            session()->flash('error', 'Customer portal has been removed. Please contact Atlas Logistics staff for assistance.');
            return redirect('/admin/login');
        }
        if(AtlasLogistics::get('driver_doc_verification') == 1 && $user->user_type == 'D')
        {
            if ($user->is_verified == '1') 
           {
                return redirect()->intended($this->redirectTo);
           }
           else
           {
               Auth::logout(); 
               session()->flash('error', 'Profile is not Verified. Please, Contact Admin !');
               return redirect('/admin/login');
           }
        }
        else
        {
            return redirect()->intended($this->redirectTo);
        }
       

        
    }
}
