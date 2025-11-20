<?php
$ldap = ldap_connect("ldap://10.27.240.10");
ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
if (ldap_bind($ldap, "itdewa@jkt.ptdh.co.id", "F0rg0tt3np4st!")) {
    echo "LDAP OK";
} else {
    echo "LDAP FAIL";
}
