<?php

// *****************************************************************************
// Lenguaje: PHP
// Copyright 2002 Prisacom S.A.
// *****************************************************************************

require_once "PAF/PAFObject.php";
require_once "PAF/PAFConfiguration.php";
/**
  * @const CLASS_PAFSESSION Constante con el identificador único de clase.
  */
define ("CLASS_PAFSESSION", 10);

/**
  * @const PS_DEFAULT_PATH_SESSION Constante con el path por defecto para guardar los ficheros de Sesión.
  */
define ("PS_DEFAULT_PATH_SESSION", "/SESIONES");

/**
  * @const PS_DEFAULT_COOKIE_NAME Constante con el nombre por defecto de la cookie asociada con la sesión.
  */
define ("PS_DEFAULT_COOKIE_NAME", "Prisacom");

/**
  * @const PS_DEFAULT_COOKIE_DOMAIN Constante con el dominio de la cookie.
  */
define ("PS_DEFAULT_COOKIE_DOMAIN", "");

/**
  * @const PS_DEFAULT_COOKIE_PATH Constante con el path dentro del dominio de la cookie.
  */
define ("PS_DEFAULT_COOKIE_PATH", "");

/**
  * @const PS_DEFAULT_COOKIE_LIFETIME Constante con el tiempo de vida por defecto para una cookie.
  *        Esta constante creará cookies que expirarán cuando el cliente cierre su sesión de navegación.
  */
define ("PS_DEFAULT_COOKIE_LIFETIME", 0);

/**
  * @const PS_DEFAULT_COOKIE_TYPE_CACHE Constante con el tipo de caché para la cookie.
  */
define ("PS_DEFAULT_COOKIE_TYPE_CACHE", "nocache");



/**
  * Clase para el manejo de sesiones.
  *
  * @author Sergio Cruz <scruz@prisacom.com>
  * @version $Revision: 1.13 $
  * @access public
  * @package PAF
  */
class PAFSession extends PAFObject
{
    /**
      * Atributo para almacenar el identificador de sesión.
      *
      * @access private
      * @var string
      */
    var $id;

    /**
      * Ruta donde se almacenará el fichero de sesión.
      *
      * @access private
      * @var string
      */
    var $pathSession;

    /**
      * Nombre de la cookie asociada con la sesión actual.
      *
      * @access private
      * @var string
      */
    var $nameCookie;

    /**
      * Dominio de la cookie asociada con la sesión actual.
      *
      * @access private
      * @var string
      */
    var $domainCookie;

    /**
      * Path dentro del dominio anterior donde tiene validez la cookie.
      *
      * @access private
      * @var string
      */
    var $pathCookie;

    /**
      * Tiempo de vida de la cookie dentro del cliente.
      *
      * @access private
      * @var int
      */
    var $cookieLifeTime;

    /**
      * Especifica el tipo de caché que se aplicará en el cliente.
      * Por defecto toma el valor "nocache" que implica que no se realizará ningún tipo
      * de caché en la parte cliente. Otros valores que puede tomar este parámetro son
      * "public" y "private". Ver la ayuda de session_cache_limiter en www.php,net para
      * obtener más información al respecto.
      *
      * @access private
      * @var string
      */
    var $typeCache;

    /**
      * Referencia a la global que mantiene los nombres de cookies de la petición HTTP.
      *
      * @access private
      * @var array
      */
    var $httpCookieVars;

    /**
      * Referencia a la global que mantiene las variables pasadas por POST.
      *
      * @access private
      * @var array
      */
    var $httpPostVars;

    /**
      * Referencia a la global que mantiene las variables pasadas por GET.
      *
      * @access private
      * @var array
      */
    var $httpGetVars;

    /**
      * Referencia a la global que mantiene la IP desde la que se realiza la petición HTTP.
      *
      * @access private
      * @var string
      */
    var $serverAddress;
    
    /**
      * Bandera para cambiar habilitar el almacenamiento de sesion en
	  * subdirectorios
      *
      * @access private
      * @var boolean
      */
    var $useSubdirs = FALSE;

    /**
      * Bandera para cambiar los permisos del fichero de sesiones
      *
      * @access private
      * @var boolean
      */
    var $changePermission;

    /**
      * Constructor
      *
      * @access public
      * @param object $config Objeto PAFConfiguration que debe llevar los siguientes valores
      *        fijados (se refiere a los keys con los que son reconocidas las variables dentro
      *        del objeto PAFConfiguration).
      *        @param string "PATH_SESSION" Path completo donde se almacena la sesión.
      *        @param string "NAME_COOKIE" Nombre de la cookie asociada a la sesión.
      *        @param string "DOMAIN_COOKIE" Dominio de la cookie asociada a la sesión.
      *        @param string "PATH_COOKIE" Path dentro del dominio anterior.
      *        @param int "COOKIE_LIFETIME" Tiempo de vida (en segundos) de la cookie asociada la sesión.
      *        @param string "TYPE_CACHE" Especifica el tipo de caché que se aplicará en el cliente.
      *               Por defecto toma el valor "nocache" que implica que no se realizará ningún tipo
      *               de caché en la parte cliente. Otros valores que puede tomar este parámetro son
      *               "public" y "private". Ver la ayuda de session_cache_limiter en www.php,net para
      *               obtener más información al respecto.
      * @return object Objeto PAFSession inicializado o un error si no se proporcionan los datos de
      *         configuración obligatorios.
      */
    function PAFSession ( $config )
    {
        $this->PAFObject();          // Llamada al constructor de la clase padre.

        if ( !is_object($config) ||  is_null ($config) )
        {
                $this= PEAR::raiseError ("¡¡¡ ERROR !!! (".__FILE__.",". __LINE__.") => No se ha proporcionado nombre de cookie.<br>");
                return $this;
        }

        $this->httpCookieVars= $config->getGlobal("HTTP_COOKIE_VARS");
        $this->httpPostVars= $config->getGlobal("HTTP_POST_VARS");
        $this->httpGetVars= $config->getGlobal("HTTP_GET_VARS");
        $this->serverAddress= $config->getGlobal("SERVER_ADDR");


        // Fija el path donde se guardarán los ficheros de sesión.
        if ( $config->isDefinedVariable("PATH_SESSION_EP3") )
            $this->pathSession= $config->getVariable("PATH_SESSION_EP3");
        else
            $this->pathSession= PS_DEFAULT_PATH_SESSION;

        // Fija el nombre de la cookie.
        if ( $config->isDefinedVariable("NAME_COOKIE_EP3") )
            $this->nameCookie= $config->getVariable("NAME_COOKIE_EP3");
        else
            $this->nameCookie= PS_DEFAULT_COOKIE_NAME;

        // Fija el dominio para la cookie.
        if ( $config->isDefinedVariable("DOMAIN_COOKIE_EP3") )
            $this->domainCookie= $config->getVariable("DOMAIN_COOKIE_EP3");
        else
            $this->domainCookie= PS_DEFAULT_COOKIE_DOMAIN;

        // Fija el Path para la cookie.
        if ( $config->isDefinedVariable("PATH_COOKIE_EP3") )
            $this->pathCookie= $config->getVariable("PATH_COOKIE_EP3");
        else
            $this->pathCookie= PS_DEFAULT_COOKIE_PATH;

        // Fija el tiempo de expiración de la cookie.
        if ( $config->isDefinedVariable("COOKIE_LIFETIME") )
            $this->cookieLifeTime= $config->getVariable("COOKIE_LIFETIME");
        else
            $this->cookieLifeTime= PS_DEFAULT_COOKIE_LIFETIME;

        // Fija el tipo de caché para la cookie.
        if ( $config->isDefinedVariable("TYPE_CACHE") )
            $this->typeCache= $config->getVariable("TYPE_CACHE");
        else
            $this->typeCache= PS_DEFAULT_COOKIE_TYPE_CACHE;
    }

    /**
      * Método estático para recuperar el identificador de la clase.
      *
      * @access public
      * @return int Identificador único de clase.
      */
    function getClassType()
    {
        return CLASS_PAFSESSION;
    }

    /**
      * Método estático que retorna el nombre de la clase.
      *
      * @access public
      * @return string Nombre de la clase.
      */
    function getClassName()
    {
        return "PAFSession";
    }

    /**
      * Método de consulta para determinar si una clase es de un tipo determinado.
      * Reimplementado de PAFObject.
      *
      * @access public
      * @param int $tipo Número entero con el Código de clase por el que queremos preguntar .
      * @return boolean
      */
    function isTypeOf ($tipo)
    {
        return  ( (PAFSession::getClassType() == $tipo) || PAFObject::isTypeOf($tipo) );
    }

    /**
      * Recupera el valor del identificador de Sesión.
      *
      * @access public
      * @return int
      */
    function getSessionId()
    {
        return $this->id;
    }

    /**
      * Recupera el nombre de la cookie asociada a la sesión actual.
      *
      * @access public
      * @return string
      */
    function getCookieName()
    {
        return $this->nameCookie;
    }

    /**
      * Recupera el dominio de la cookie asociada a la sesión actual.
      *
      * @access public
      * @return string
      */
    function getCookieDomain()
    {
        return $this->domainCookie;
    }

    /**
      * Recupera el path dentro del dominio para la cookie asociada a la sesión actual.
      *
      * @access public
      * @return string
      */
    function getCookiePath()
    {
        return $this->pathCookie;
    }

    /**
      * Recupera el tiempo de expiración de la cookie asociada a la sesión actual.
      *
      * @access public
      * @return int
      */
    function getCookieLifeTime()
    {
        return $this->cookieLifeTime;
    }

    /**
      * Fija el valor de expiración de la cookie a los segundos especificados.
      *
      * @access public
      * @param int @seg Número de segundos que tarda en expirar la cookie de la sesión actual.
      */
    function setCookieLifeTime($seg)
    {
        $this->cookieLifeTime= $seg;
    }

    /**
      * Recupera el tipo de caché aplicada al cliente para la sesión
      *
      * @access public
      * @return string
      */
    function getTypeCache()
    {
        return $this->typeClientCache;
    }

    /**
      * Recupera el tipo de caché aplicada al cliente para la sesión
      *
      * @access public
      * @return string
      */
    function enableSubdirs()
    {
        return $this->useSubdirs=TRUE;
    }

    /**
      * Recupera el Path donde se encuentra el fichero de la sesión actual.
      *
      * @access public
      * @return string
      */
    function getPathSession()
    {
		$path_sess = $this->pathSession;
		if ($this->useSubdirs)
	    	$path_sess .= "/".substr($this->id,0,2);
        return $path_sess;
    }

    /**
      * Método para iniciar una sesión. Si la sesión existe la utiliza y en caso contrario la crea
      * con los parámetros especificados por construcción.
      *
      * @access public
      * @return mixed Identificador único de sesión si se ha conseguido crear o recuperar la sesión
      *         o un error en caso contrario.
      */
    function sessionStart()
    {
        // Para el caso de que el ID de Sesión llegue por Cookie.
        if ( isset ($this->httpCookieVars[$this->getCookieName()]) && !empty ($this->httpCookieVars[$this->getCookieName()]))
        {
            $this->id= $this->httpCookieVars[$this->getCookieName()];
        }
        else
        {
            // Para el caso de que el ID de Sesión llegue por POST
            if ( isset ($this->httpPostVars['sid']) && !empty($this->httpPostVars['sid']) )
            {
                $this->id = $this->httpPostVars['sid'];
            }
            else
            {
                // Para el caso de que el ID de Sesión llegue por GET.
                if (isset ($this->httpGetVars['sid']) && !empty($this->httpGetVars['sid']))
                {
                    $this->id = $this->httpGetVars['sid'];
                }
            }
        }

        // Si no viene el id de Sesión de ninguna de las formas posibles generamos el id
        // de Sesión único.
        if ( empty ( $this->id ) )
        {
            $idSesion= md5( uniqid( rand() ) );
            $nume=explode(".",$this->serverAddress);

            while (list ($key, $val) = each ($nume))
            {
                $aux="";
                for( $i=strlen($val); $i<3; $i++ )
                    $aux.="0";
                $ipAux.=$aux.$val;
            }
            $this->id=$idSesion.$ipAux;

	    $this->changePermission=true;
            //session_set_cookie_params( $this->cookieLifeTime, $this->pathCookie, $this->domainCookie );
        }

        // Configuramos la Sesión.
        session_id ( $this->id );
        session_name( $this->nameCookie );

		
        if( !is_writable($this->pathSession) )
            return PEAR::raiseError ("¡¡¡ ERROR !!! [PAFSession] => Es imposible escribir el fichero de sesión en " . $this->pathSession . "<br>");

		$path_sess = $this->pathSession;
        if ($this->useSubdirs)
		{
		   $path_sess .= "/".substr($this->id,0,2);
		   @mkdir($path_sess, 0755);
		}

        session_save_path( $path_sess );
        session_cache_limiter ( $this->typeCache );
        session_set_cookie_params( $this->cookieLifeTime, $this->pathCookie, $this->domainCookie );
        session_name( $this->nameCookie );


        session_start();
		if ($this->changePermission) {
				$sess_file = $path_sess."/"."sess_".$this->id;
				if (!@chmod($sess_file, octdec(777))) {
					return PEAR::raiseError("¡¡¡ ERROR !!! [PAFSession] => No puedo cambiar permisos de $sess_file <br>" );	
				}
		}
        return $this->id;
    }

    /**
      * Cierra y guarda los datos de la sesión actualmente activa.
      *
      * @access public
      */
    function sessionClose()
    {
        session_write_close();
    }

    /**
      * Método para registrar una variable dentro de la sesión.
      * Es posible registrar variables simples, arrays u objetos.
      *
      * @access public
      * @param string $value Nombre de la variable, array u objeto que
      *        queremos registrar en la sesión.
      * @return mixed true si la variable, array u objeto se ha registrado correctamente dentro de la
      *         sesión o un error en caso contrario.
      */
    function sessionRegister ($value)
    {
        if ( session_register($value) )
             return true;
        else
            return PEAR::raiseError("¡¡¡ ERROR !!! [PAFSession] => La variable $value no se ha podido registrar corretamente en la sesion $this->id (" . $this->getSessionId() . ")");
    }

    /**
      * Elimina la variable indicada de la sesión. También elimina la variable global asociada a
      * lo mismo.
      *
      * @access public
      * @param string $value Nombre de la variable, array u objeto que
      *        queremos registrar en la sesión.
      * @return mixed true si la variable, array u objeto se ha registrado correctamente dentro de la
      *         sesión o un error en caso contrario.
      */
    function sessionUnregister ($value)
    {
        if ( session_unregister($value) )
        {
            unset ($value);
            return true;
        }
        else
            return PEAR::raiseError("¡¡¡ ERROR !!! [PAFSession] => La variable $value no se ha podido des-registrar corretamente de la sesion $this->id (" . $this->getSessionId() . ")");
    }

    /**
      * Elimina todos los datos que se encuentren registrados en la sesión actual.
      * Las variables que se encuentren registradas en ese momento siguen estando registradas.
      * @access public
      */
    function sessionClear()
    {
        session_unset();
    }

    /**
      * Elimina la sesión completamente. Esto es, Elimina los datos de sesión y hace un "unset" de las
      * variables globales utilizadas. También elimina el fichero físico donde se mantienen los datos
      * de la sesión actual.
      *
      * @access public
      * @return mixed true si se ha conseguido eliminar completamente la sesión o un error
      *         en caso contrario.
      */
    function sessionDestroy()
    {
        $this->sessionClear();
        if ( session_destroy() )
            return true;
        else
            return PEAR::raiseError ("¡¡¡ ERROR !!! [PAFSession] => La sesión " . $this->getSessionId() . " no ha podido eliminarse.");
    }

    /**
      * Método para comprobar si una determinada variable, array u objeto se encuentra registrado
      * en la sesión actual.
      *
      * @acces public.
      * @return boolean
      */
    function isRegistered ($value)
    {
        return session_is_registered($value);
    }

}

?>
