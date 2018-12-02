<?php
/*
define('LDAP_SERVER', 'prisacom.int');
//define('LDAP_SERVER','10.90.40.144');
define('LDAP_PORT', '389');
define('LDAP_DOMAIN_NAME', 'prisacom');
define('LDAP_BIND_ATTRIB', 'userPrincipalName');
//define('LDAP_BASE_DN', 'OU=Usuarios Sugar,DC=prisacom,DC=int');
define('LDAP_BASE_DN', 'DC=prisacom,DC=int');
//define('LDAP_BASE_DN', 'CN=accesosugar,CN=Listas de Seguridad,CN=Listas PRISACOM,DC=prisacom,DC=int');
define('LDAP_FILTER', '(SamAccountName=');
define('LDAP_SUPERUSER_DN', 'CN=LDapUser,OU=Sistemas,OU=PRISACOM Usuarios Genericos,DC=prisacom,DC=int');
define('LDAP_SUPERUSER_PASS', 'k3alpc-');
define('LDAP_GROUP', 'OU=Usuarios Sugar');*/


define('LDAP_SERVER', 'servicios.elespanol.int');
//define('LDAP_SERVER','10.90.40.144');
define('LDAP_PORT', '389');
//define('LDAP_DOMAIN_NAME', 'prisacom');
//define('LDAP_BIND_ATTRIB', 'userPrincipalName');
//define('LDAP_BASE_DN', 'OU=Usuarios Sugar,DC=prisacom,DC=int');
define('LDAP_BASE_DN', 'dc=elespanol,dc=int');
//define('LDAP_BASE_DN', 'CN=accesosugar,CN=Listas de Seguridad,CN=Listas PRISACOM,DC=prisacom,DC=int');
//define('LDAP_FILTER', '(SamAccountName=');
define('LDAP_SUPERUSER_DN', 'CN=admin');
//define('LDAP_SUPERUSER_PASS', 'k3alpc-');
//define('LDAP_GROUP', 'OU=Usuarios Sugar');

class CRMLoginLDAP
{
    var $usuario;
    var $password;

    function CRMLoginLDAP($usuario, $password)
    {
        $this->usuario  = $usuario;
        $this->password = $password;
    }

    function authenticate()
    {
        //LDAP

        // Conexi�n
        $conn = ldap_connect(LDAP_SERVER, LDAP_PORT);/* or die("No ha sido posible conectarse al servidor")*/
        var_dump($conn);die;
        if (!$conn) {

            return [false, "descError" => "Error de conexi�n"];
        } else {
            @ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);
            @ldap_set_option($conn, LDAP_OPT_REFERRALS, 0); // required for AD

            // Primer bind, con el usuario con el que se puede hacer la b�squeda
            $bind1 = @ldap_bind($conn, LDAP_SUPERUSER_DN, LDAP_SUPERUSER_PASS);
            var_dump($bind1);die;
            if (!$bind1) {
                ldap_close($conn);

                return [false, "descError" => "Acceso denegado, el usuario no est� autorizado"];
            } else {
                //B�squeda
                $res = @ldap_search(
                    $conn,
                    LDAP_BASE_DN,
                    LDAP_FILTER . $this->usuario . ")",
                    [
                        "samaccountname",
                        "mail",
                        "memberof",
                        "department",
                        "displayname",
                        "telephonenumber",
                        "primarygroupid",
                    ]
                );//var_dump("res", $res);
                if (!$res) {
                    ldap_close($conn);

                    return [false, "descError" => "Acceso denegado, el usuario no est� autorizado"];
                    //return array(false, "descError" => "Error en la b�squeda del �rbol de directorios");
                } else {
                    // Recogemos las entradas de la b�squeda
                    $entries = @ldap_get_entries($conn, $res);//var_dump("entries",$entries);exit;
                    if (!$entries || $entries["count"] != 1) {
                        ldap_close($conn);

                        return [false, "descError" => "Acceso denegado, el usuario no est� autorizado"];
                        //return array(false, "descError" => "Error al recuperar resultados de la b�squeda");
                    } else {
                        // Segundo bind, con el usuario devuelto por la b�squeda
                        $dn    = $entries[0]["dn"];//var_dump($dn);
                        $bind2 = @ldap_bind($conn, $dn, $this->password);
                        if (!$bind2) {
                            ldap_close($conn);

                            return [false, "descError" => "Acceso denegado, el usuario no est� autorizado"];
                            //return array(false, "descError" => "El usuario no est� autorizado");
                        }
                    }
                }
            }
        }

        $memberOf = $entries[0]["memberof"];
        $res      = $this->isMemberOfSugar($memberOf);

        ldap_close($conn);

        if ($res) {

            return [true, "descError" => null, "displayName" => $entries[0]["displayname"][0]];
        } else {

            return [false, "descError" => "Acceso denegado, el usuario no est� autorizado"];
        }
        //return array(false, "descError" => "El usuario no peternece al grupo Usuarios Sugar");

    }

    function isMemberOfSugar($listMemberOf)
    {
        $exists = false;

        $sugarGroup = "CN=Usuarios Sugar";

        if ($listMemberOf) {
            foreach ($listMemberOf as $group) {

                $group = explode(",", $group);

                foreach ($group as $value) {

                    if ($value == $sugarGroup) {
                        $exists = true;
                    }
                }
            }
        }

        return $exists;
    }
}