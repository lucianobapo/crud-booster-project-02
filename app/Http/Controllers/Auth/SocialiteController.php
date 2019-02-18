<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use crocodicstudio\crudbooster\helpers\CRUDBooster;
use HttpRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;
use GuzzleHttp\Client;


class SocialiteController extends Controller
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

//    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
//        $this->middleware('guest')->except('logout');
    }

    /**
     * Redirect the user to the Google authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToProvider($provider)
    {
        if(!array_key_exists($provider, config('services'))){
            $module = CRUDBooster::getCurrentModule();
            CRUDBooster::insertLog(trans('Provider Error', ['module' => $module->name]));
            CRUDBooster::redirect(CRUDBooster::adminPath(), trans('crudbooster.denied_access'));
        }

        return Socialite::driver($provider)->with(['provider' => $provider])->redirect();

    }

    /**
     * Obtain the user information from Google.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleProviderCallback($provider)
    {

        $module = CRUDBooster::getCurrentModule();

        if(!array_key_exists($provider, config('services'))){
            CRUDBooster::insertLog('Provider Not Configured: '.$provider, ['module' => $module->name]);
            CRUDBooster::redirect(CRUDBooster::adminPath(), trans('crudbooster.denied_access'));
        }

        try {
            $user = Socialite::driver($provider)->user();
        }
        catch (\Exception $e) {
            $module = CRUDBooster::getCurrentModule();
            CRUDBooster::insertLog('Provider Error: '.$e->getMessage(), ['module' => $module->name]);
            CRUDBooster::redirect(CRUDBooster::adminPath(), trans('crudbooster.denied_access'));
        }

        // check if they're an existing user
        $existingUser = DB::table(config('crudbooster.USER_TABLE'))
            ->where("email", $user->email)
            ->first();

        if($existingUser){
            // log them in
            $this->userLogin($existingUser);
        }
        else {
            // create a new user
            $this->createNewUser($user);
        }

        return redirect(CRUDBooster::adminPath());
    }

    private function userLogin($user)
    {
        $priv = DB::table("cms_privileges")->where("id", $user->id_cms_privileges)->first();

        $roles = DB::table('cms_privileges_roles')->where('id_cms_privileges', $user->id_cms_privileges)
            ->join('cms_moduls', 'cms_moduls.id', '=', 'id_cms_moduls')
            ->select('cms_moduls.name', 'cms_moduls.path', 'is_visible', 'is_create', 'is_read', 'is_edit', 'is_delete')->get();

        $photo = ($user->photo) ? asset($user->photo) : asset('vendor/crudbooster/avatar.jpg');

        Session::put('admin_id', $user->id);
        Session::put('admin_is_superadmin', $priv->is_superadmin);
        Session::put('admin_name', $user->name);
        Session::put('admin_photo', $photo);
        Session::put('admin_privileges_roles', $roles);
        Session::put("admin_privileges", $user->id_cms_privileges);
        Session::put('admin_privileges_name', $priv->name);
        Session::put('admin_lock', 0);
        Session::put('theme_color', $priv->theme_color);
        Session::put("appname", CRUDBooster::getSetting('appname'));

        CRUDBooster::insertLog(trans("crudbooster.log_login", ['email' => $user->email, 'ip' => Request::server('REMOTE_ADDR')]));

        $cb_hook_session = new \App\Http\Controllers\CBHook;
        $cb_hook_session->afterLogin();
    }

    private function createNewUser($user)
    {
        $client = new Client();

        $user_agent = Request::header('User-Agent');
        $token = md5(env('API_SECRET_KEY') . time(). $user_agent);


        $response = $client->post(url('api/add_user'),
            [
                'form_params'=>[
                    'name'=>$user->name,
                    'email'=>$user->email,
                ],

                'headers' => [
                    'X-Authorization-Token' => $token,
                    'X-Authorization-Time' => time(),
                    'User-Agent' => $user_agent,
                ]
            ]
        );

        $contents = (string) $response->getBody();
        $response_data = json_decode($contents);

        if ($response_data->api_message=='success') {

            // check if they're an existing user
            $existingUser = DB::table(config('crudbooster.USER_TABLE'))
                ->where("id", $response_data->id)
                ->first();
            if($existingUser){
                // log them in
                $this->userLogin($existingUser);
            } else $this->errorLogin($response_data->id);

        }else $this->errorLogin($contents);

    }

    private function errorLogin($message)
    {
        $module = CRUDBooster::getCurrentModule();

        CRUDBooster::insertLog('Login Error: '.$message, ['module' => $module->name]);
        CRUDBooster::redirect(CRUDBooster::adminPath(), trans('crudbooster.denied_access'));
    }
}
