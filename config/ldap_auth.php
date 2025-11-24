<?php

return [

    'connection' => 'default',

    'provider' => Adldap\Laravel\Auth\DatabaseUserProvider::class,

    'model' => App\Models\User::class,

    'rules' => [
        Adldap\Laravel\Validation\Rules\DenyTrashed::class,
    ],

    'scopes' => [
        //
    ],

    'identifiers' => [

        'ldap' => [
            'locate_users_by' => 'samaccountname',
            'bind_users_by'   => 'userprincipalname',
        ],

        'database' => [
            'guid_column' => 'objectguid',
            'username_column' => 'username',
        ],
    ],

    'passwords' => [
        'sync' => false,
        'column' => 'password',
    ],

    'logging' => env('LDAP_LOGGING', true),
];
