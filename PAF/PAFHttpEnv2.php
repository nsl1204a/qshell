<?php

// *****************************************************************************
// Lenguaje: PHP
// Copyright 2005 Prisacom S.A.
//
// $Id: PAFHttpEnv.php,v 1.3 2006/11/15 11:08:48 fjalcaraz Exp $
//
// *****************************************************************************

/**
 * Clase para encapsular todo el manejo a las variables gloables y de
 * configuración de apache y php
 * @author Virgilio Sanz <vsanz@prisacom.com>
 * @copyright PRISACOM S.A.
 * @package WS
 * @version $Revision: 1.3 $
 */
class PAFHttpEnv
{
    /**
     * Devuelve true si la petición es un POST
     */
    function isPost()
    {
	    return (count($_POST) > 0);
    }
    
    /**
     * Devuelve true si la petición es un GET
     */
    function isGet()
    {
	    return (count($_POST) == 0);
    }
    
    // Estas funciones están aquí para el día que vuelvan a cambiar el modo
    // de acceder a los GET, POST y demás

    function GET($val) 
    {
        if (!isset($_GET[$val])) return null;
        
        // Limpieza de la variable: Previniendo injection
        return $_GET[$val]; 
    }
    
    function POST($val) 
    {
        if (!isset($_POST[$val])) return null;
        return $_POST[$val];
    }
    
    function SERVER($val) 
    {
        if (!isset($_SERVER[$val])) return null;
        return $_SERVER[$val];
    }
    function REQUEST($val) 
    {
        if (!isset($_REQUEST[$val])) return null;
        return $_REQUEST[$val];
    }

    /**
    * Método que devuelve la url completa desde donde fué llamada esta
    * @returns string
    */
    function getReferer() 
    {
        return PAFHttpEnv::SERVER('HTTP_REFERER');
    }

    /**
     * Devuelve la IP del usuario que está accediendo a la página
     */
    function getClientIp() 
    {
        if ($_SERVER['HTTP_X_FORWARDED_FOR'] != '') {
            $client_ip = (!empty($_SERVER['REMOTE_ADDR'])) ?  $_SERVER['REMOTE_ADDR'] : 
                                                              ((!empty($_ENV['REMOTE_ADDR']) ) ?  $_ENV['REMOTE_ADDR'] : "unknown" );
   
            // los proxys van añadiendo al final de esta cabecera
            // las direcciones ip que van "ocultando". Para localizar la ip real
            // del usuario se comienza a mirar por el principio hasta encontrar
            // una dirección ip que no sea del rango privado. En caso de no
            // encontrarse ninguna se toma como valor el REMOTE_ADDR
            $entries = array ("172.0.0.245", "193.146.228.84", "10.206.192.19", "130.206.192.28", "195.12.230.211");
            $entries = array ("212.80.177.2", "212.23.37.4", "10.12.230.195", "195.12.230.203");
            //$entries = split('[, ]', $_SERVER['HTTP_X_FORWARDED_FOR']);
            
            reset($entries);
            while (list(, $entry) = each($entries)) {
                $entry = trim($entry);
                if ( preg_match("/^([0-9]+.[0-9]+.[0-9]+.[0-9]+)/", $entry, $ip_list) ) {
                    // http://www.faqs.org/rfcs/rfc1918.html
                    $private_ip = array('/^0./',
                                        '/^127.0.0.1/',
                                        '/^192.168..*/',
                                        '/^172..*/',
                                        '/^10..*/');
                    echo $client_ip."<br>";
                    $found_ip = preg_replace($private_ip, $client_ip, $ip_list[1]);
                    echo "Found Ip:".$found_ip."<br>";
                    if ($client_ip != $found_ip) {
                        $client_ip = $found_ip;
                        break;
                    }
                }
            }
        } else {
            $client_ip = (!empty($_SERVER['REMOTE_ADDR'])) ?  $_SERVER['REMOTE_ADDR'] :
                                                             ((!empty($_ENV['REMOTE_ADDR'])) ?  $_ENV['REMOTE_ADDR'] : "unknown" );
        }
   
        return $client_ip;
    }

    /**
     * Devuelve Modified-Since en caso de que venga null, en caso contrario.
     */
    function IfModifiedSince()
    {
#        $last_modified = PAFHttpEnv::SERVER('HTTP_IF_MODIFIED_SINCE');
#        return is_null($last_modified) ? -1 : strtotime($last_modified);
         return strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);
    }

    /**
     * Devuelve la lista de lenguajes que entiende el cliente
     */
    function AcceptLanguage() 
    {
        $lang = PAFHttpEnv::SERVER('HTTP_ACCEPT_LANGUAGE');
        if (is_null($lang)) {
            $lang = 'es_ES';
        }

        return $lang;
    }

    /**
     * Devuelve si el cliente es capaz de recibir compresion con gzip o no
     */
    function AcceptGzip()
    {
        $gzip = PAFHttpEnv::SERVER('HTTP_ACCEPT_ENCODING');
        $busca = strstr($gzip, 'gzip');

        return ($busca == false) ? false : true;
    }
    
    /**
     * Devuelve el nombre del usuario para la autorizacion
     */
    function AuthUser() 
    {
        return PAFHttpEnv::SERVER('PHP_AUTH_USER');
    }

    /**
     * Devuelve la password del usuario enviada por el navegador
     */
    function AuthPassword() 
    {
        return PAFHttpEnv::SERVER('PHP_AUTH_PW');
    }

    /**
     * Devuelve la URL completa del script
     */
     function getFullUrl()
     {
         $full_url = "";
         if (!is_null(PAFHttpEnv::SERVER('HTTPS'))) {
             if (PAFHttpEnv::SERVER('HTTPS') == 'on') {
                $full_url =  'https://';
             } else {
                $full_url = 'http://';
             }
         }
         if (PAFHttpEnv::SERVER('SERVER_PORT') != '80')  {
             $full_url .= PAFHttpEnv::SERVER('HTTP_HOST').':'.PAFHttpEnv::SERVER('SERVER_PORT');
         } else {
             $full_url .=  PAFHttpEnv::SERVER('HTTP_HOST');
         }

         if (!is_null(PAFHttpEnv::SERVER('REQUEST_URI'))) {
             $full_url .= PAFHttpEnv::SERVER('REQUEST_URI');
         } else {
             $full_url = PAFHttpEnv::SERVER('PHP_SELF');
             if(PAFHttpEnv::SERVER('QUERY_STRING') > ' ') {
                 $full_url .=  '?'.PAFHttpEnv::SERVER('QUERY_STRING');
             }
         }

         return $full_url;
     }

    /**
     * Devuelve el fichero físico del script que se ve en el public
     */
     function scriptFile()
     {
        // FIXME: Esto fallaría si hicieramos anteriormente un chroot
        return PAFHttpEnv::SERVER('DOCUMENT_ROOT') . "/" . PAFHttpEnv::SERVER('PHP_SELF');

     }

     /**
      * Devuelve el nombre del usuario bajo el que corre el apache
      */
     function apacheUserName()
     {
        $tmp = posix_getpwuid(posix_geteuid());
        return $tmp['name'];
     }


     function _limpia_vars($value)
     {
	if (get_magic_quotes_gpc())
	{
             if (is_array($value))
                $value = array_map('stripslashes', $value);
             else
	        $value = stripslashes($value);
	}
	return $value;
     }
}

?>
