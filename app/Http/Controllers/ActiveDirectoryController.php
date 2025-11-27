<?php

namespace App\Http\Controllers;

use Adldap\Laravel\Facades\Adldap;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;


class ActiveDirectoryController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'username' => 'required|string|max:128',
            'password' => 'required|string|min:1|max:256',
        ]);

        $username = trim($data['username']);
        $password = $data['password'];

        $throttleKey = 'ad_login_attempts:' . Str::lower($username) . ':' . $request->ip();
        $maxAttempts = 5;
        $lockMinutes = 5;

        $attempts = Cache::get($throttleKey, 0);
        if ($attempts >= $maxAttempts) {
            return response()->json([
                'success' => false,
                'message' => "Terlalu banyak percobaan login. Coba lagi dalam $lockMinutes menit.",
            ], 429);
        }

        if (!extension_loaded('ldap')) {
            Log::error('LDAP extension missing');
            return response()->json([
                'success' => false,
                'message' => 'Server error: LDAP tidak aktif.',
            ], 500);
        }

        if (!preg_match('/^[a-zA-Z0-9._-]+$/', $username)) {
            return response()->json([
                'success' => false,
                'message' => 'Format username tidak valid.',
            ], 422);
        }

        $domain = env('AD_DOMAIN', 'jkt.ptdh.co.id');
        $upn    = $username . '@' . $domain;

        try {
            $provider = Adldap::getProvider('default');

            if (!$provider->auth()->attempt($username, $password, true)) {

                Cache::put($throttleKey, $attempts + 1, now()->addMinutes($lockMinutes));

                return response()->json([
                    'success' => false,
                    'message' => 'Username atau password salah',
                ], 401);
            }

            Cache::forget($throttleKey);

            $user = $provider->search()
                ->whereEquals('samaccountname', $username)
                ->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User ditemukan di AD, namun data tidak ada.',
                ], 404);
            }

            $uac = (int) $user->getFirstAttribute('useraccountcontrol');
            $isDisabled = ($uac & 0x0002) === 0x0002;

            if ($isDisabled) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun AD Anda dinonaktifkan.',
                ], 403);
            }

            $attributes = [
                'jdeno'          => $user->getFirstAttribute('employeeid'),
                'displayName'    => $user->getFirstAttribute('displayname'),
                'mail'           => $user->getFirstAttribute('mail'),
                'memberOf'       => $user->memberof ?: [],
                'company'        => $user->getFirstAttribute('company'),
                'departmentName' => $user->getFirstAttribute('department'),
                'title'          => $user->getFirstAttribute('title'),
            ];

            $empId = $user->getFirstAttribute('employeeid');

            if (!$empId) {
                return response()->json([
                    'success' => false,
                    'message' => 'EmployeeId kosong di AD.',
                ], 403);
            }

            $jdedata = DB::connection('CrystalDHDemo')
                ->table('V_EmpAll')
                ->where('EmployeeId', $empId)
                ->first();

            $tempUser = new User([
                'id'          => 0,
                'accountName' => $username,
                'name'        => $user->getFirstAttribute('displayname'),
                'email'       => $user->getFirstAttribute('mail'),
                'employeeid'  => $empId,
            ]);

            $token = JWTAuth::fromUser($tempUser);

            return response()->json([
                'success'     => true,
                'message'     => 'Login berhasil',
                'token'       => $token,
                'token_type'  => 'bearer',
                'accountName' => $username,
                'attributes'  => $attributes,
                'hrisData'    => $jdedata,
            ]);
        } catch (\Throwable $e) {

            Log::error('LDAP Login Error', [
                'username' => $username,    
                'error'    => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server. Hubungi administrator.',
            ], 500);
        }
    }
}
