<?php

return [

    'logging' => env('LDAP_LOGGING', true),

    'connections' => [

        'default' => [

            'auto_connect' => true,

            'connection' => Adldap\Connections\Ldap::class,

            'settings' => [

                'schema' => Adldap\Schemas\ActiveDirectory::class,
                'account_prefix' => env('LDAP_ACCOUNT_PREFIX', ''),
                'account_suffix' => env('LDAP_ACCOUNT_SUFFIX', '@' . env('AD_DOMAIN')),
                'hosts' => [
                    env('AD_HOST'),
                ],

                'port'    => env('LDAP_PORT', 389),
                'base_dn' => env('AD_BASE_DN'),
                'username' => env('AD_USERNAME'),
                'password' => env('AD_PASSWORD'),
                'use_ssl' => filter_var(env('LDAP_SSL', false), FILTER_VALIDATE_BOOLEAN),
                'use_tls' => filter_var(env('LDAP_TLS', false), FILTER_VALIDATE_BOOLEAN),
            ],

        ],
    ],
];
