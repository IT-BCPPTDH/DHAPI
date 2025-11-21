<?php

namespace App\Http\Controllers;

use App\Ldap\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use LdapRecord\Container;

class ActiveDirectoryController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        // 1) Validate input
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
            Log::error('LDAP extension missing when attempting AD login', [
                'username' => $username,
            ]);

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
            $connection = Container::getConnection('default');

            $connection->connect();

            $ok = $connection->auth()->attempt($upn, $password);

            if (!$ok) {
                Cache::put($throttleKey, $attempts + 1, now()->addMinutes($lockMinutes));

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials',
                ], 401);
            }

            Cache::forget($throttleKey);

            /** @var \App\Ldap\User|null $user */
            $user = User::whereEquals('samaccountname', $username)->first();

            if (!$user) {
                Log::warning('User authenticated but not found in LDAP query', [
                    'username' => $username,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'User ditemukan di AD, tapi data tidak tersedia.',
                ], 404);
            }

            $uac   = (int)($user->useraccountcontrol[0] ?? 0);
            $disabled = ($uac & 0x0002) === 0x0002;

            if ($disabled) {
                return response()->json([
                    'success' => false,
                    'message' => 'Account AD Anda sedang disabled. Hubungi administrator.',
                ], 403);
            }

            $accountName = $user->samaccountname[0] ?? null;

            $attributes = [
                'displayName'    => $user->displayname[0] ?? null,
                'mail'           => $user->mail[0] ?? null,
                'memberOf'       => $user->memberof ?? [],
                'organization'   => $user->o[0] ?? null,
                'company'        => $user->company[0] ?? null,
                'departmentName' => $user->department[0] ?? null,
                'title'          => $user->title[0] ?? null,
            ];

            return response()->json([
                'success'     => true,
                'message'     => 'Login berhasil',
                'accountName' => $accountName,
                'attributes'  => $attributes,
            ]);
        } catch (\Throwable $e) {
            Log::error('LDAP login error', [
                'username' => $username,
                'error'    => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'LDAP connection error. Silakan coba lagi atau hubungi admin.',
            ], 500);
        }
    }
}
