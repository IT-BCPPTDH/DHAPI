<?php
$ldap_host = "ldap://10.27.240.10";   // atau ldaps:// jika pakai SSL
$ldap_port = 389;
$username  = "itdewa@jkt.ptdh.co.id";
$password  = "F0rg0tt3np4st!";

echo "Testing LDAP...\n";

$ldap = ldap_connect($ldap_host, $ldap_port);
ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);

if (!$ldap) {
    die("Cannot connect\n");
}

echo "Connected. Now binding...\n";

if (@ldap_bind($ldap, $username, $password)) {
    echo "LDAP OK\n";
} else {
    echo "LDAP FAIL: " . ldap_error($ldap) . "\n";
}
