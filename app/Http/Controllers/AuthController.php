<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function proseslogin(Request $request)
    {
        // $pass = 123;
        // echo Hash::make($pass);

        // dd($request->email, $request->password);
        // Mencoba login dengan guard 'karyawan'
        if (Auth::guard('karyawan')->attempt(['email' => $request->email, 'password' => $request->password])) {
            
            return redirect()->intended('/dashboard'); // Redirect ke dashboard jika login berhasil
        } else {
            // Redirect kembali ke halaman login dengan pesan kesalahan
            return redirect('/')->with(['warning'=>'Email/Password Salah']);
        }
    }

    public function proseslogout()
    {
        if (Auth::guard('karyawan')->check()) {
            Auth::guard('karyawan')->logout();
            return redirect('/')->with('message', 'Logout berhasil');
        }

        return redirect('/')->with('message', 'Tidak ada sesi login ditemukan');
    }
}
