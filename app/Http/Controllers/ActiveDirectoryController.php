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

        $throttleKey  = 'ad_login_attempts:' . Str::lower($username) . ':' . $request->ip();
        $maxAttempts  = 5;
        $lockMinutes  = 15;

        $attempts = Cache::get($throttleKey, 0);
        if ($attempts >= $maxAttempts) {
            return response()->json([
                'success' => false,
                'message' => 'Terlalu banyak percobaan login. Coba lagi dalam beberapa menit.',
            ], 429);
        }

        if (!extension_loaded('ldap')) {
            Log::error('LDAP extension missing');
            return response()->json([
                'success' => false,
                'message' => 'Server misconfiguration (LDAP tidak aktif).',
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
                    'message' => 'Invalid credentials',
                ], 401);
            }

            Cache::forget($throttleKey);

            $user = $provider->search()
                ->whereEquals('samaccountname', $username)
                ->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User ditemukan di AD, tapi data tidak tersedia.',
                ], 404);
            }

            $uac = (int) $user->getFirstAttribute('useraccountcontrol');
            $disabled = ($uac & 0x0002) === 0x0002;

            if ($disabled) {
                return response()->json([
                    'success' => false,
                    'message' => 'Account AD Anda disabled. Hubungi administrator.',
                ], 403);
            }

            $attributes = [
                'jdeno'          => $user->getFirstAttribute('employeeid') ?? null,
                'displayName'    => $user->getFirstAttribute('displayname'),
                'mail'           => $user->getFirstAttribute('mail'),
                'memberOf'       => $user->memberof ?: [],
                'organization'   => $user->getFirstAttribute('o'),
                'company'        => $user->getFirstAttribute('company'),
                'departmentName' => $user->getFirstAttribute('department'),
                'title'          => $user->getFirstAttribute('title'),
            ];

            $employeeid = $user->getFirstAttribute('employeeid') ?? null;

            if ($employeeid) {
                $jdedata = DB::connection('CrystalDHDemo')
                    ->table('V_EmpAll')
                    ->where('EmployeeId', $employeeid)
                    ->first();
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'JDE kosong. Hubungi administrator.',
                ], 403);
            }

            $tempUser = new User([
                'id'          => 0,
                'accountName' => $username,
                'name'        => $user->getFirstAttribute('displayname'),
                'email'       => $user->getFirstAttribute('mail'),
                'employeeid'  => $employeeid,
            ]);
            $token = JWTAuth::fromUser($tempUser);

            return response()->json([
                'success'     => true,
                'message'     => 'Login berhasil',
                'token'       => $token,
                'token_type'  => 'bearer',
                'accountName' => $username,
                'attributes'  => $attributes,
                'hrisData'     => $jdedata,
            ]);
        } catch (\Throwable $e) {

            Log::error('LDAP Adldap login error', [
                'username' => $username,
                'error'    => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error, Harap Hubungin admin',
            ], 500);
        }
    }
}
