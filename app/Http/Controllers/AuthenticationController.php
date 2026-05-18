<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Mail\MailSender;
use App\Models\Token;
use Illuminate\Support\Facades\Mail;

class AuthenticationController extends Controller
{
    public function index_login()
    {
        $data['title'] = "Login";

        return view('auth.templates.header', $data)
            . view('auth.login', $data)
            . view('auth.templates.footer');
    }

    public function login_function(Request $req)
{
    $validator = Validator::make($req->all(), [
        'email-input' => 'required',
        'password-input' => 'required',
    ], [
        'required' => 'Please fill out all form',
    ]);

    if ($validator->fails()) {
        return redirect()->back()->with('resp_msg', $validator);
    }

    $auth = DB::table('md_auth as ma')
        ->leftJoin(
            'md_auth_role as mr',
            'ma.ROLE_ID',
            '=',
            'mr.ROLE_ID'
        )
        ->where('ma.EMAIL', $req->input('email-input'))
        ->where(
            'ma.PASSWORD',
            hash('sha256', md5($req->input('password-input')))
        )
        ->where('ma.IS_ACTIVE', '!=', 0)
        ->first();

    Session::flush();

    if (!empty($auth)) {

        $user_data = collect([
            'ID_USER' => $auth->USER_CODE,
            'EMAIL' => $auth->EMAIL,
            'ROLE' => $auth->ROLE,
            'ID_ROLE' => $auth->ROLE_ID,
        ]);

        Session::push('user', $user_data);

        switch ($auth->ROLE) {

            case "ADMIN":

                Session::put('usersession', "ADMIN");
                return redirect('/dashboard-admin');

            case "FRONTDESK":

                Session::put('usersession', "FRONTDESK");
                return redirect('/dashboard-frontdesk');

            case "PATIENT":

                Session::put('usersession', "PATIENT");
                return redirect('/dashboard-pasien');

            default:

                return redirect('/');
        }

    } else {

        return redirect('/login')
            ->with('resp_msg', "Your account not found.");
    }
}

    public function index_register()
    {
        $data['title'] = "Register Let's Help 2gether";

        return view('auth.templates.header', $data)
            . view('auth.register', $data)
            . view('auth.templates.footer');
    }

    public function register_function(Request $req)
{
    $validator = Validator::make($req->all(), [
        'email-input' => 'required|email',
        'password-input' => 'required',
    ], [
        'required' => 'Please fill out all form',
    ]);

    if ($validator->fails()) {
        return redirect()->back()->with('resp_msg', $validator);
    }

    $auth = DB::table('md_auth')
        ->where('EMAIL', $req->input('email-input'))
        ->first();

    if (!empty($auth)) {

        return redirect('/register')
            ->with('resp_msg', "Email already registered");

    } else {

        $user_data = [

            'USER_CODE' => $this->GenerateUniqID(
                'FDT',
                $req->input('email-input')
            ),

            'EMAIL' => $req->input('email-input'),

            'PASSWORD' => hash(
                'sha256',
                md5($req->input('password-input'))
            ),

            // Default FRONTDESK
            'ROLE_ID' => 2,

            'IS_ACTIVE' => 1,
        ];

        try {

            DB::table('md_auth')->insert($user_data);

            return redirect('/register')
                ->with('succ_msg', "Successfully registered");

        } catch (HttpException $e) {

            return $e;
        }
    }
}

    public function logout()
    {
        Session::flush();
        return redirect('login');
    }

    public function forgotPassword()
    {
        $data['title'] = "Lupa Password";

        return
            view('forgotPassword', $data);
    }

    public function GenerateUniqID($first, $var)
    {
        $string = preg_replace('/[^a-z]/i', '', $var);
        $vocal = array("a", "e", "i", "o", "u", "A", "E", "I", "O", "U", " ");
        $scrap = str_replace($vocal, "", $string);
        $begin = substr($scrap, 0, 4);
        $uniqid = strtoupper($begin);
        $microtime = microtime(true);
        $microtime = str_replace('.', '', $microtime);
        $hashed_short_microtime = md5($microtime);
        $short_hashed_microtime = substr($hashed_short_microtime, 0, 3);
        return $first . "_" . $uniqid . $short_hashed_microtime;
    }
}
