<?php

namespace App\Http\Controllers\Web\Auth;

use App\Facades\MessageResponseJson;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    protected $user;
    public function __construct()
    {
        $this->user = new User();
    }
    public function index()
    {
        return view('admin.pages.login.index');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login'    => 'required|min:3|max:50',
            'password' => 'required|min:6|max:50',
        ]);

        DB::beginTransaction();

        try {
            $loginField = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

            $user = $this->user->where($loginField, $request->login)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return MessageResponseJson::unauthorized('Email/Username atau password salah.');
            }

            if ($user->email_verified_at === null) {
                return MessageResponseJson::unauthorized('Akun anda belum diverifikasi. Silahkan hubungi admin.');
            }

            if (!$user->hasAnyRole(['Super Admin', 'Admin'])) {
                return MessageResponseJson::forbidden('Anda tidak memiliki akses ke halaman ini.');
            }

            if (Auth::attempt([$loginField => $request->login, 'password' => $request->password])) {
                $request->session()->regenerate();
                DB::commit();

                return MessageResponseJson::success('Login berhasil.', [
                    'redirect' => url('/~admin-panel')
                ]);
            }

            DB::rollBack();
            return MessageResponseJson::unauthorized('Email/Username atau password salah.');
        } catch (\Exception $e) {
            DB::rollBack();
            return MessageResponseJson::serverError('Terjadi kesalahan. ' . $e->getMessage());
        }
    }
}
