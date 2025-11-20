<?php

namespace App\Http\Controllers;

use App\Ldap\User;
use Illuminate\Http\JsonResponse;
use LdapRecord\Container;
use Illuminate\Http\Request;

class ActiveDirectoryController extends Controller
{
    public function getUsers(): JsonResponse
    {
        $users = User::all()->map(function (User $u) {
            return [
                'distinguishedName' => $u->getDn(),
                'accountName'       => $u->getFirstAttribute('samaccountname'),
                'displayName'       => $u->getFirstAttribute('displayname'),
                'mail'              => $u->getFirstAttribute('mail'),
                'memberOf'          => $u->getAttribute('memberof') ?? [],
                'organization'      => $u->getFirstAttribute('o'),
                'company'           => $u->getFirstAttribute('company'),
                'departmentName'    => $u->getFirstAttribute('department'),
                'title'             => $u->getFirstAttribute('title'),
            ];
        });

        return response()->json($users);
    }

    public function getUser(string $accountName): JsonResponse
    {
        $user = User::whereEquals('samaccountname', $accountName)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json([
            'distinguishedName' => $user->getDn(),
            'accountName'       => $user->getFirstAttribute('samaccountname'),
            'displayName'       => $user->getFirstAttribute('displayname'),
            'mail'              => $user->getFirstAttribute('mail'),
            'memberOf'          => $user->getAttribute('memberof') ?? [],
            'organization'      => $user->getFirstAttribute('o'),
            'company'           => $user->getFirstAttribute('company'),
            'departmentName'    => $user->getFirstAttribute('department'),
            'title'             => $user->getFirstAttribute('title'),
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        $connection = Container::getConnection('default');
        $upn = $request->username . '@jkt.ptdh.co.id';

        try {
            if ($connection->auth()->attempt($upn, $request->password)) {
                $user = User::whereEquals('samaccountname', $request->username)->first();

                if (!$user) {
                    return response()->json([
                        'success' => false,
                        'message' => 'User ditemukan di AD, tapi data tidak tersedia.'
                    ], 404);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Login berhasil',
                    'data' => [
                        'distinguishedName' => $user->getDn(),
                        'accountName'       => $user->samaccountname[0] ?? null,
                        'displayName'       => $user->displayname[0] ?? null,
                        'mail'              => $user->mail[0] ?? null,
                        'memberOf'          => $user->memberof ?? [],
                        'company'           => $user->company[0] ?? null,
                        'departmentName'    => $user->department[0] ?? null,
                        'title'             => $user->title[0] ?? null,
                    ],
                ]);
            }
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'LDAP connection error: ' . $e->getMessage(),
            ], 500);
        }
    }
}
