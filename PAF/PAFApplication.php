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
    *    Método Singleton para obtener el objeto aplicación
    *
    *    @access public
    *    @return PAFApplication
    */
    function &theApp()
    {
        return PAFApplication::_getStaticProperty(__CLASS__, 'theApp');
    }

   /**
    * Método que inicializa el sistema leyendo la configuración del fichero
    * pasado por parámetro. Rellenara una serie de globales estáticas así:
    *
    * [bloque1] <br>
    * variable1=valor1 <br>
    * variable2=valor2 <br>
    *
    * _getStaticProperty('PAFApplication', 'bloque1') = array( <br>
    *           variable1=>valor1,
    *           variable2 => valor2);
    *
    * La lógica sería usar como bloque la "clase/modulo/namespace" que
    * configura.
    *
    * @access public
    * @param string $confFile Fichero con la configuración general...
    */
    function init($confFile)
    {
        // Inicializo objeto a través del singleton
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
     * Función que hace un print_r de una variable.
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
            $app->debug("$conf_file ya añadido en conf. para el modulo '".$inis[$conf_file]."'", __FUNCTION__, __CLASS__);
            return false;
        } else {
            $inis[$conf_file] = $modulo;
            /*
            $data = debug_backtrace();
            foreach($data as $entry)
                echo($entry['file'].", linea:".$entry['line']."\n");
            flush();
            */
            $app->debug("Añadiendo conf: $modulo -> $conf_file", __FUNCTION__, __CLASS__);
        }
        // Aquí parseariamos la configuracion y rellenariamos todas las
        // variables necesarias.
        if (!is_readable($conf_file)) {
		$conf_file = "/DATA/". $conf_file;
		if (!is_readable($conf_file))
            		return $app->raiseError(__FILE__, __LINE__, "No puedo leer $conf_file");
        }

        $conf = parse_ini_file($conf_file, true);
        foreach ($conf as $key => $values) {
            $tmp =& $app->_getStaticProperty($modulo, $key);
			
			// Merge de valores. Se hace así para no tener problemas con 
			// los $var numéricos que array_merge resetea

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
    *    Método fáctory que obtiene los datasources definidos en el Ini.
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
                $txt = "$name no existe en la colección de bases de datos.";
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
    *    Método fáctory que obtiene los datasources definidos en el Ini.
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
                $txt = "$name no existe en la colección de bases de datos.";
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
            return PAFApplication::raiseError(__FILE__, __LINE__, "No existe el path $cual para el módulo $modulo");
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
    *    Método Singleton para obtener el objeto con la configuración
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
      * Método Singleton para obtener objetos de la configuación. Modulos enteros
      *
      * @access public
      * @return PAFApplication
      */
    function &getAllConf($modulo){
        return PAFApplication::_getStaticProperty($modulo);
    }

   /**
    * Devuelve la configuración en el método "antiguo" para un medio en particular. 
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
    *    Método para lanzar (o relanzar) un error pero formateandolo para
    * tener el lugar donde se produzco el error.
    *
    *    @access public
    *    @param $error PEAR_Error O string con el error.
    *    @param $file Fichero donde se originó el error (__FILE__)
    *    @param $line Liena del fichero donde se originó el error (__LINE__)
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
    *    Método que devuelve el array de js a añadir en la PAGE
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
    *    Método que devuelve el array de CSS a añadir en la PAGE
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
    * Método que devuelve true si estamos trabajando desde una URL interna
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
     * Método que añade un mensaje a la cola de mensajes para enviar
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
     * Método que envia las cabeceras de FirePHP
     * 
     */
    function fireDebugSend() {
        return true;
    }

    /**
     * Envía un error a la consola de Firebug
     */
    function fireError($msg) {
        PAFApplication::fireDebug($msg,'ERROR');
    }
    
    /**
     * Envía un warning a la consola de Firebug
     */		
    function fireWarning($msg) {
        PAFApplication::fireDebug($msg,'WARN');
    }
    
    /**
     * Envía un mensaje de Log a la consola de Firebug
     */
    function fireLog($msg) {
        PAFApplication::fireDebug($msg,'LOG');
    }
    
    /**
     * Envía un mensaje informativo a la consola de Firebug
     */
    function fireInfo($msg) {
        PAFApplication::fireDebug($msg,'INFO');
    }

    /**
     * Envía una traza
     */
    function fireTrace() {
        if ((TRUE === PAFApplication::isFirebugDebugging()) && (TRUE === PAFApplication::fireNetworkAllowed())) {
            $firephp =& FirePHP::getInstance();
            $firephp->trace('Traza');
        }
    }
		
   /**
    *    Método que devuelve la cadena de código JS apra añadir a la PAGE
    *
    *    @access public
    *    @return string $jsDeclaration cadena con código js 
    */
    function &getJSDeclaration()
    {
			$_JSDeclaration =& PAFApplication::_getStaticProperty(__CLASS__, '_JSDeclaration');
        		PAFApplication::debug($_JSDeclaration, __FUNCTION__, __CLASS__);
			return $_JSDeclaration;
		}
		
   /**
    *    Método que almacena de manera estática los scripts de la página
    *
    *    @access public
    *
    *    @param array $jsScripts array de scripts js para añadir en la Page
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
    *    Método que almacena de manera estática los archivos css de la página
    *
    *    @access public
    *
    *    @param array $cssFiles array de ficheros css para añadir en la Page
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
    *    Método que almacena de manera estática js en linea (Delcaration)
		*		 de la página
    *
    *    @access public
    *
    *    @param string $jsDeclaration cadena con código js para añadir a la Page
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
    *    Método que pinta en pantalla dependiendo de si tenemos activo o no
    * el sistema de debug.
    *
    *    @access public
    *
    *    @param string $msg Mensaje que queremos imprimir (también funciona con un PEAR_Error)
    *    @param string $lugar Lugar donde estámos debugando (__FUNCTION__)
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
    *    Marca el tiempo para esta ejecución.
    *
    *    @access public
    *    @param string $lugar Lugar donde estamos, normalmente: __FUNCTION__
    *    @param string $marca Nombre que queremos dar a este punto en la ejecución
    */
    function trace($lugar, $marca)
    {
        if (PAFApplication::isDebugging()) {
            $timer =& PAFApplication::theTimer();
            $timer->setMarker($lugar.'::'.$marca);
        }
    }

   /**
    *    Pinta los tiempos de ejecución
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
     *  Actualmente sólo tiene un nivel, así que cualquier valor no cero activa el debug.
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
     * (Esto no debería estar en esta clase)
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
     * Convierte cualquier objeto a su representación textual
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
     *    MÃ©todo robado de clase PEAR (La que tenemos en prisacom no tiene este método)
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
