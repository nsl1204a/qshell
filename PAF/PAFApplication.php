<?php

// *****************************************************************************
// Lenguaje: PHP
// Copyright 2005 Prisacom S.A.
//
// $Id: PAFApplication.php,v 1.20 2009/07/02 10:58:32 jcaride Exp $
//
// *****************************************************************************
require_once 'PEAR.php';
require_once 'Benchmark/Timer.php';
require_once 'PAF/PAFDBDataSource.php';
require_once 'PAF/PAFDBCachedDataSource.php';
require_once 'FPC/FirePHP.class.php';
require_once 'FPC/fb.php';

/**
 * Clase/Objeto para el manejo del runtime de la aplicacion.
 *
 * @author    Virgilio Sanz <vsanz@prisacom.com>
 * @copyright PRISACOM S.A
 * @package PAF
 * @version $Rev$
 */
class PAFApplication
{
// -------------------------- INICIALIZACION ----------------
   /**
    *    M�todo Singleton para obtener el objeto aplicaci�n
    *
    *    @access public
    *    @return PAFApplication
    */
    function &theApp()
    {
        return PAFApplication::_getStaticProperty(__CLASS__, 'theApp');
    }

   /**
    * M�todo que inicializa el sistema leyendo la configuraci�n del fichero
    * pasado por par�metro. Rellenara una serie de globales est�ticas as�:
    *
    * [bloque1] <br>
    * variable1=valor1 <br>
    * variable2=valor2 <br>
    *
    * _getStaticProperty('PAFApplication', 'bloque1') = array( <br>
    *           variable1=>valor1,
    *           variable2 => valor2);
    *
    * La l�gica ser�a usar como bloque la "clase/modulo/namespace" que
    * configura.
    *
    * @access public
    * @param string $confFile Fichero con la configuraci�n general...
    */
    function init($confFile)
    {
        // Inicializo objeto a trav�s del singleton
        $app =& PAFApplication::theApp();
        $app = new PAFApplication();
        $ret = $app->addIniFile(__CLASS__, $confFile);
        if (PEAR::isError($ret)) {return $ret;}

        if (PAFApplication::isDebugging()) {
            // Ponemos la variable que hace que se pinten todas las queries a true.
            global $tracesql;
            $tracesql = 'SI';

            // Iniciamos el timer
            $timer =& PAFApplication::theTimer();
            $timer->start();
        }

				if (isset($_REQUEST['debug']) && ($_REQUEST['debug'] == 'firebug')) {
             PAFApplication::enableFireDebugging();
             return TRUE;
				} else {
             PAFApplication::disableFireDebugging();
				}
        return true;
    }

    /**
     * Funci�n que hace un print_r de una variable.
     */
    function dump($obj, $die = false)
    {
        echo "<pre>\n";
        echo htmlentities(print_r($obj));
        echo "\n</pre>";
        if ($die) die();
    }

    /**
     * Anyade un fichero .ini de configuracion al array de configuraciones
     * @param string $modulo Modulo que luego referenciaremos en la llamada a getConf
     * @param string $conf_file Fichero que contiene la configuracion
     */
    function addIniFile($modulo, $conf_file)
    {

        $app = PAFApplication::theApp();
		if (!is_object($app)) $app=new PAFApplication();
        $inis =& $app->_getStaticProperty(__CLASS__, 'inis');
        
        if (isset($inis[$conf_file])) {
            $app->debug("$conf_file ya a�adido en conf. para el modulo '".$inis[$conf_file]."'", __FUNCTION__, __CLASS__);
            return false;
        } else {
            $inis[$conf_file] = $modulo;
            /*
            $data = debug_backtrace();
            foreach($data as $entry)
                echo($entry['file'].", linea:".$entry['line']."\n");
            flush();
            */
            $app->debug("A�adiendo conf: $modulo -> $conf_file", __FUNCTION__, __CLASS__);
        }
        // Aqu� parseariamos la configuracion y rellenariamos todas las
        // variables necesarias.
        if (!is_readable($conf_file)) {
		$conf_file = "/DATA/". $conf_file;
		if (!is_readable($conf_file))
            		return $app->raiseError(__FILE__, __LINE__, "No puedo leer $conf_file");
        }

        $conf = parse_ini_file($conf_file, true);
        foreach ($conf as $key => $values) {
            $tmp =& $app->_getStaticProperty($modulo, $key);
			
			// Merge de valores. Se hace as� para no tener problemas con 
			// los $var num�ricos que array_merge resetea

			if ($tmp)	
			{
				foreach ($values as $var=>$value)
					$tmp[$var]=$value;
			}
			else $tmp = $values;
        }

        return true;

    }

// -------------------------- UTILIDADES ----------------
   /**
    *    M�todo f�ctory que obtiene los datasources definidos en el Ini.
    *
    *    @access public
    *    @return PAFDBDataSource.
    */
    function &getDS($name, $connect=true)
    {
        $ds =& PAFApplication::_getStaticProperty(__CLASS__, 'ds_'.$name.'');

        if (is_null($ds)) {
            $conf =& PAFApplication::_getStaticProperty(__CLASS__, 'DB');
            
			if (!isset($conf[$name])) {
                $txt = "$name no existe en la colecci�n de bases de datos.";
                PAFApplication::debug($txt, __FUNCTION__, __CLASS__);
                return PAFApplication::raiseError(__FILE__, __LINE__, $txt);
            }

            $ret = DB::parseDSN($conf[$name]);
            PAFApplication::debug($ret, __FUNCTION__, __CLASS__);
            $ds = &new  PAFDBDataSource($ret['phptype'],
                                       $ret['username'],
                                       $ret['password'],
                                       $ret['hostspec'],
                                       $ret['database']);
            if ($connect) $con = $ds->connect();
            if (PEAR::isError($con)) {
                return PAFApplication::raiseError(__FILE__, __LINE__, $con);
            }
        }

        return $ds;
    }

// -------------------------- UTILIDADES ----------------
   /**
    *    M�todo f�ctory que obtiene los datasources definidos en el Ini.
    *
    *    @access public
    *    @return PAFDBDataSource.
    */
    function &getCachedDS($name)
    {
        $ds =& PAFApplication::_getStaticProperty(__CLASS__, 'cds_'.$name.'');

        if (is_null($ds)) {
            $conf =& PAFApplication::_getStaticProperty(__CLASS__, 'DB');
            if (!isset($conf[$name])) {
                $txt = "$name no existe en la colecci�n de bases de datos.";
                PAFApplication::debug($txt, __FUNCTION__, __CLASS__);
                return PAFApplication::raiseError(__FILE__, __LINE__, $txt);
            }

            $ret = DB::parseDSN($conf[$name]);
            PAFApplication::debug($ret, __FUNCTION__, __CLASS__);
            $ds =& new PAFDBCachedDataSource($ret['phptype'],
                                       $ret['username'],
                                       $ret['password'],
                                       $ret['hostspec'],
                                       $ret['database']);
        }

        return $ds;
    }


    /**
     * Devuelve el path
     */
    function getPath($cual, $modulo=null)
    {
        if (is_null($modulo)) { $modulo = __CLASS__;}

        $conf = PAFApplication::getConf('PATHS', $modulo);
        
        $path = $conf[$cual];
        if (is_null($path)) {
            return PAFApplication::raiseError(__FILE__, __LINE__, "No existe el path $cual para el m�dulo $modulo");
        }	
	if (ereg("DIR_",$path)) {
            eval("\$path=".$path.";");
            return $path;
        }

        $dirs = explode('/', $conf[$cual]);

        if ($dirs[0] == '__PRIVATE__') {
            $dirs[0] = DIR_BASE.'/'.DIR_PRIVATE;
        } else if ($dirs[0] == '__PUBLIC__') {
            $dirs[0] = DIR_BASE.'/'.DIR_PUBLIC;
        } else if ($dirs[0] == '__SESIONES__') {
            $dirs[0] = DIR_SESIONES;
        }

        return (implode('/', $dirs));
    }

   /**
    *    M�todo Singleton para obtener el objeto con la configuraci�n
    *
    *    @access public
    *    @return PAFApplication
    */
    function &getConf($bloque, $modulo=null)
    {
        if (is_null($modulo)) { $modulo = __CLASS__;}
        return PAFApplication::_getStaticProperty($modulo, $bloque);
    }

    /**
      * M�todo Singleton para obtener objetos de la configuaci�n. Modulos enteros
      *
      * @access public
      * @return PAFApplication
      */
    function &getAllConf($modulo){
        return PAFApplication::_getStaticProperty($modulo);
    }

   /**
    * Devuelve la configuraci�n en el m�todo "antiguo" para un medio en particular. 
    * Definido en configuration.inc del medio.
    * ej: $confElPais =& PAFApplication::theConf('configElPais");
    */
    function &theConfig($s_conf='configCuatro')
    {
        global $$s_conf;
        return $$s_conf;
    }
    

// -------------------------- ERRORES/DEBUG ----------------
   /**
    *    M�todo para lanzar (o relanzar) un error pero formateandolo para
    * tener el lugar donde se produzco el error.
    *
    *    @access public
    *    @param $error PEAR_Error O string con el error.
    *    @param $file Fichero donde se origin� el error (__FILE__)
    *    @param $line Liena del fichero donde se origin� el error (__LINE__)
    *    @return PEAR_Error
    */
    function raiseError($file, $line, $error)
    {
        $msg = PAFApplication::_mixedToText($error);
        $txt = sprintf("%s (%d): %s", $file, $line, $msg);
        PAFApplication::debug($txt, "ERROR");
        return PEAR::raiseError($txt);
    }

   /**
    *    M�todo que devuelve el array de js a a�adir en la PAGE
    *
    *    @access public
    *		 @return array de scripts
    */
    function &getJS()
    {
			$_JS =& PAFApplication::_getStaticProperty(__CLASS__, '_JS');
			return $_JS;
	}

   /**
    *    M�todo que devuelve el array de CSS a a�adir en la PAGE
    *
    *    @access public
    *        @return array de ficheros CSS
    */
    function &getCss()
    {
            $_Css =& PAFApplication::_getStaticProperty(__CLASS__, '_Css');
            return $_Css;
    }


   /**
    * M�todo que devuelve true si estamos trabajando desde una URL interna
    * @returns boolean
    */
    function &isInternalServer()
    {
		if($_SERVER['REMOTE_ADDR'] && substr($_SERVER['REMOTE_ADDR'],0,3)=='10.'){
            return true;
        }
        else{
            return false;
        }
    }

    /**
     * Metodo que configura la PAFApplication para enviar mensajes al FirePHP
     *
     */
    function enableFireDebugging() {
        $_firebugEnabled =& PAFApplication::_getStaticProperty(__CLASS__, '_firebugEnabled');
        $_firebugEnabled = TRUE;
        $firephp =& FirePHP::getInstance(true);
    }

    function disableFireDebugging() {
		$_firebugEnabled =& PAFApplication::_getStaticProperty(__CLASS__, '_firebugEnabled');
		$_firebugEnabled = FALSE;
    }
    
    /**
     * Metodo que comprueba si estamos en modo debug y la password de debug es correcta.
     * @returns boolean
     */
    function &isFirebugDebugging()
    {
		$_firebugEnabled =& PAFApplication::_getStaticProperty(__CLASS__, '_firebugEnabled');
        return $_firebugEnabled;
    }
	/**
     *
     * M�todo que a�ade un mensaje a la cola de mensajes para enviar
     * a la consola de Firebug
     * @access public
     * @param $msg Mensaje o objeto a depurar
     * @param $loglevel Nivel de error
     * 
     */
    function fireDebug($mix, $loglevel='LOG') {
        if ((TRUE === PAFApplication::isFirebugDebugging()) && (TRUE === PAFApplication::fireNetworkAllowed())) {
            $firephp =& FirePHP::getInstance();        

            $txt = '';
            if (is_string($mix)) {
                $txt = $mix;
            } elseif (method_exists($mix, 'getMessage')) {
                $txt = $mix->getMessage();
            } elseif (method_exists($mix, 'toString')) {
                $txt = $mix->toString();
            } elseif (method_exists($mix, 'get')) {
                $txt = $mix->get();
            } else {
                $txt = $mix;
            }
        
            switch($logLevel) {
                case 'LOG':
                    $firephp->log($txt);
                    break;
                case 'WARN':
                    $firephp->warn($txt);
                    break;
                case 'INFO':
                    $firephp->info($txt);
                    break;
                case 'ERROR':
                default:
                    $firephp->error($txt);
                    break;
            }
            return true;
        }
    }


    /**
     * Metodo que comprueba si estamos en la subred de desarrollo/sistemas
     * para que no salgan cabeceras a produccion
     *
     */
    
    function fireNetworkAllowed() {
        $apacheHeaders = apache_request_headers();
        if (array_key_exists('X-Forwarded-For',$apacheHeaders)) {
            $remoteIP = $apacheHeaders['X-Forwarded-For'];
        } else {
            $remoteIP = $_SERVER['REMOTE_ADDR'];
        }

        if ('10.' === substr($remoteIP,0,3)) {
            return TRUE;
        } else {
            return FALSE;
        }
        
    }

    /**
     *
     * M�todo que envia las cabeceras de FirePHP
     * 
     */
    function fireDebugSend() {
        return true;
    }

    /**
     * Env�a un error a la consola de Firebug
     */
    function fireError($msg) {
        PAFApplication::fireDebug($msg,'ERROR');
    }
    
    /**
     * Env�a un warning a la consola de Firebug
     */		
    function fireWarning($msg) {
        PAFApplication::fireDebug($msg,'WARN');
    }
    
    /**
     * Env�a un mensaje de Log a la consola de Firebug
     */
    function fireLog($msg) {
        PAFApplication::fireDebug($msg,'LOG');
    }
    
    /**
     * Env�a un mensaje informativo a la consola de Firebug
     */
    function fireInfo($msg) {
        PAFApplication::fireDebug($msg,'INFO');
    }

    /**
     * Env�a una traza
     */
    function fireTrace() {
        if ((TRUE === PAFApplication::isFirebugDebugging()) && (TRUE === PAFApplication::fireNetworkAllowed())) {
            $firephp =& FirePHP::getInstance();
            $firephp->trace('Traza');
        }
    }
		
   /**
    *    M�todo que devuelve la cadena de c�digo JS apra a�adir a la PAGE
    *
    *    @access public
    *    @return string $jsDeclaration cadena con c�digo js 
    */
    function &getJSDeclaration()
    {
			$_JSDeclaration =& PAFApplication::_getStaticProperty(__CLASS__, '_JSDeclaration');
        		PAFApplication::debug($_JSDeclaration, __FUNCTION__, __CLASS__);
			return $_JSDeclaration;
		}
		
   /**
    *    M�todo que almacena de manera est�tica los scripts de la p�gina
    *
    *    @access public
    *
    *    @param array $jsScripts array de scripts js para a�adir en la Page
    */
    function addJS($jsScripts)
    {
		if (!$jsScripts) return;
    	$_JS =& PAFApplication::_getStaticProperty(__CLASS__, '_JS');
		if (is_array($jsScripts)){
			 foreach (array_keys($jsScripts) as $js)
       			$_JS[$js] = true;
		}	else {
				$_JS[$jsScripts] = true;
		}
        PAFApplication::debug($_JS, __FUNCTION__, __CLASS__);
	}

   /**
    *    M�todo que almacena de manera est�tica los archivos css de la p�gina
    *
    *    @access public
    *
    *    @param array $cssFiles array de ficheros css para a�adir en la Page
    */
    function addCss($cssFiles)
    {
        if (!$cssFiles) return;
        $_Css =& PAFApplication::_getStaticProperty(__CLASS__, '_Css');
        if (is_array($cssFiles)){
             foreach (array_keys($cssFiles) as $css)
                $_Css[$css] = true;
        }   else {
                $_Css[$cssFiles] = true;
        }
        PAFApplication::debug($_Css, __FUNCTION__, __CLASS__);
    }

		
   /**
    *    M�todo que almacena de manera est�tica js en linea (Delcaration)
		*		 de la p�gina
    *
    *    @access public
    *
    *    @param string $jsDeclaration cadena con c�digo js para a�adir a la Page
    */
    function addJSDeclaration($jsDeclaration)
    {
		if (is_array($jsDeclaration))
			var_dump(debug_backtrace());
		$_JSDeclaration =& PAFApplication::_getStaticProperty(__CLASS__, '_JSDeclaration');
		$_JSDeclaration .= $jsDeclaration;
       	PAFApplication::debug($_JSDeclaration, __FUNCTION__, __CLASS__);
	}
		
   /**
    *    M�todo que pinta en pantalla dependiendo de si tenemos activo o no
    * el sistema de debug.
    *
    *    @access public
    *
    *    @param string $msg Mensaje que queremos imprimir (tambi�n funciona con un PEAR_Error)
    *    @param string $lugar Lugar donde est�mos debugando (__FUNCTION__)
    *    @param integer $sublugar Lugar dentro del lugar donde pintamos (__CLASS__)
    */
    function debug($msg, $lugar, $sublugar = "")
    {
        if (PAFApplication::isDebugging()) {
            $msg  = PAFApplication::_mixedToText($msg);
            printf("<b>%s.%s</b>: <br><pre>\n%s\n</pre><br>\n", $sublugar, $lugar, $msg);
        }
    }
    /**
     * Escribe en el log de aplicacion solo en caso de error grave
     * @param $txt string contenido del error, puede ser un string o un pear_error
     */
    function log($msg, $log='errores')
    {
        $conf = PAFApplication::getConf('DEBUG');
        $dir_log = $conf['dir_log'];
        $username = PAFHttpEnv::apacheUserName();
        $log_file = sprintf("%s/%s/%s_%s_%s.log", $dir_log, $log, date('Ymd'), $log, $username);

        // Creamos el directorio si no existe.
        PAFApplication::mkdirhier(dirname($log_file));

        PAFApplication::debug($msg, __FUNCTION__, __CLASS__);
        $txt = PAFApplication::_transformToLog($msg);
        $fp = @fopen($log_file, 'a+');
        if (!$fp) {
            PAFApplication::debug("No puedo abrir el fichero de log: $log_file", __FUNCTION__, __CLASS__);
        }
        fwrite($fp, $txt."\n");
        fclose($fp);
    }

   /**
    *    Marca el tiempo para esta ejecuci�n.
    *
    *    @access public
    *    @param string $lugar Lugar donde estamos, normalmente: __FUNCTION__
    *    @param string $marca Nombre que queremos dar a este punto en la ejecuci�n
    */
    function trace($lugar, $marca)
    {
        if (PAFApplication::isDebugging()) {
            $timer =& PAFApplication::theTimer();
            $timer->setMarker($lugar.'::'.$marca);
        }
    }

   /**
    *    Pinta los tiempos de ejecuci�n
    *
    *    @access public
    */
    function printTimer()
    {
        if (PAFApplication::isDebugging()) {
            $timer =& PAFApplication::theTimer();
            if (is_null($timer)) {
                $timer = PAFApplication::theTimer();
            }
            $timer->stop();
            $timer->display();
        }
    }

    /**
     * Metodo que comprueba si estamos en modo debug y la password de debug es correcta.
     * @returns boolean
     */
    function isDebugging()
    {
        if (!isset($_REQUEST['debug'])) return false;
        if ( substr($_SERVER['REMOTE_ADDR'],0,3)!='10.') return false;
        $conf =& PAFApplication::getConf('DEBUG');
        $clave = $conf['password'];
        return (($_REQUEST['debug'] == $clave) && !is_null($clave));
    }


    /**
     *  Configura un nivel de debug. 0 para no debug
     *  Actualmente s�lo tiene un nivel, as� que cualquier valor no cero activa el debug.
     *  @access public
     */
    function setDebugLevel($level)
    {
        $debugLevel =& PAFApplication::_getStaticProperty(__CLASS__, 'debugLevel');
        $debugLevel = $level;
       
        global $tracesql;
        if ($debugLevel) {
            // Ponemos la variable que hace que se pinten todas las queries a true.
            $tracesql = 'SI';
        } else {
            $tracesql = 'NO';

        }
    }


   /**
    *    Obtiene el Timer.
    *
    *    @access public
    *    @return Benchmark_Timer
    */
    function &theTimer()
    {
        $timer =& PAFApplication::_getStaticProperty(__CLASS__, 'timer');
        if (is_null($timer)) {
            $timer = new Benchmark_Timer();
        }
        return $timer;
    }

    /**
     * Crea una jerarquia de directorios completa
     * (Esto no deber�a estar en esta clase)
     */
    function mkdirhier($path, $mask= 0777)
    {
       if (!file_exists($path)) {
           PAFApplication::mkdirhier(dirname($path));
           return mkdir($path, $mask);
       }
    }

// ------------------------------ METODOS PRIVADOS ------------------------
    /**
     * Convierte cualquier objeto a su representaci�n textual
     * @var mixed $mix Objeto a "serializar"
     * @return string
     */
    function _mixedToText($mix)
    {

        $txt = '';
        if (is_string($mix)) {
            $txt = $mix;
        } elseif (method_exists($mix, 'getMessage')) {
            $txt = $mix->getMessage();
        } elseif (method_exists($mix, 'toString')) {
            $txt = $mix->toString();
        } elseif (method_exists($mix, 'get')) {
            $txt = $mix->get();
        } else {
            $txt = print_r($mix, true);
        }

        return $txt;
    }


    /**
     * Formatea el mensaje para sacarlo en el log.
     */
    function _transformToLog($msg)
    {
        // hora, txt, url, referrer
        return sprintf("%s, %s, %s, %s",
                       date('H:i:s'),
                       PAFApplication::_mixedToText($msg),
                       PAFHttpEnv::getFullUrl(),
                       PAFHttpEnv::getReferer());
    }

    /**
     *    Método robado de clase PEAR (La que tenemos en prisacom no tiene este m�todo)
     *    -----
     *
     * If you have a class that's mostly/entirely static, and you need static
     * properties, you can use this method to simulate them. Eg. in your method(s)
     * do this: $myVar = &PEAR::_getStaticProperty('myclass', 'myVar');
     * You MUST use a reference, or they will not persist!
     *
     * @access public
     * @param  string $class  The calling classname, to prevent clashes
     * @param  string $var    The variable to retrieve.
     * @return mixed A reference to the variable. If not set it will be
     *                 auto initialised to NULL.
     */
    function &_getStaticProperty($class, $var=null)
    {
       
		
		static $properties;
		if($var){
			return $properties[$class][$var];
		}
		else{
			return $properties[$class];
		}
    }

}

?>
