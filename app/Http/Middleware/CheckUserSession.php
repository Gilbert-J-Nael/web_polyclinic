<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $level): Response
    {
        // return $next($request);
        if (!$request->session()->exists('user')) {
            return redirect('/login')->with('resp_msg', 'Session Anda Telah Habis, Silahkan Login Kembali');
        } else {
            if (session('user')[0]['ROLE'] == $level) {
                return $next($request);
            } else {
                return redirect('/login')->with('resp_msg', "Anda Tidak Dapat Mengakses Halaman Ini dengan Role Ini");
            }
        }
    }
}
