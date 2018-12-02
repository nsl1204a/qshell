<?php

// ****************************************************************************
// Lenguaje: PHP
// Copyright 2002 Prisacom S.A.
// ****************************************************************************

    require_once "PAF/PAFObject.php";    
    require_once "PAF/PAFSocket.php";
    require_once "PAF/PAFHttpClient.php";
    require_once "PAF/PAFLog.php";

    /**
    * @const CLAVE_IN Constante para identificar la clave que pasamos al dominio
    */

    define ("CLAVE_IN", "Tr0T@M");

    /**
    * @const PIR_TIMEOUT Constante para timeout de transmisión
    *        del socket (expresado en segundos).
    */
    define ("PIR_TIMEOUT", 45);

    /**
    * @const PIR_TIME_INTERVAL Minutos del intervalo para parámetro adicional de la petición
    *        a la caché de doble nivel. Todas las peticiones pertenecen a una hora. Ese intervalo es el
    *        que indica esta constante (10:00 => 1002, 10:03=> 1004, etc.).
    */
    define ("PIR_TIME_INTERVAL", 2);

    /**
    * @const PIR_RETRIES Número de reintentos que se realizan al dominio interno si se producen errores
    *        en la recuperación de la página.
    */
    define ("PIR_RETRIES", 3);

    /**
    * @const MIN_CHARS_PAGE Número mínimo de caracteres que debe tener una página devuelta para
    *        ser considerada como una página "buena".
    */
    define ("MIN_CHARS_PAGE", 600);
    
    // TODO: Documentación de las constantes para el manejo de errores.
    // Estas constantes definen las posibles condiciones de error que nos podemos encontrar
    // a la hora de recuperar el contenido de una petición interna.
    define ("PIR_ERROR_PEAR_NUMBER", -1000);
    define ("PIR_ERROR_NULLPAGE_NUMBER", -1001);
    define ("PIR_ERROR_INCOMPLETEPAGE_NUMBER", -1002);
    define ("PIR_ERROR_EMPTYPAGE_NUMBER", -1003);

    define ("PIR_ERROR_PEAR_DESC", "Se ha producido un error interno.");
    define ("PIR_ERROR_NULLPAGE_DESC", "Se ha recuperado una página nula.");
    define ("PIR_ERROR_INCOMPLETEPAGE_DESC", "Se ha recuperado una página incompleta");
    define ("PIR_ERROR_EMPTYPAGE_DESC", "Se ha recuperado una página vacía");
    
    
    /**
    * Encapsula la petíción de paginas a un dominio.
    *
    * Contiene un sistema de log de acciones apoyado en la clase PAFLog que debe ser incializado
    * explicitamente por el usuario si se quiere utilizar por medio del método initLog. Si no se inicializa
    * no se guardará traza de nada. Por defecto el modo de distribución de este log es PL_BALANCEMODE_YEAR_MONTH_DATE_HOUR
    * lo cual indica que se distribuiran por Año/mes/dia un fichero por hora.
    *
    * El formato de este log es el siguiente:
    * <fecha> |<hora> |<ip_cliente> |<ip_servidor> |<mensaje>
    *
    * Donde: <host> indica el nombre del host que esté realizando la petición interna.
    *
    * @author Alfonso Gomáriz <agomariz@prisacom.com>, Sergio Cruz <scruz@prisacom.com>
    * @version $Revision: 1.16 $
    * @access public
    * @package PAF
    */
    class PAFInternalRequest extends PAFObject
    {
        /**
        * Socket de comunicaciones con el dominio interno.
        *
        * @access private
        * @var objeto PAFSocket.
        */
        var $sock= null;

        /**
        * Cliente HTTP que realiza la petición de la página.
        *
        * @access private
        * @var object PAFHttpClient
        */
        var $httpClient= null;
       
        /**
        * Objeto PAFLog para el log de debug.
        *
        * @access private
        * @var object
        */
        var $log= null;
        
        // Parámetros por defecto para el Log de peticiones internas.
        var $logName= "PAFInternalRequest";
        var $logExt= "data";
        var $logBaseDir="/LOGS/sergio";
        var $logBalancingMode= PL_BALANCEMODE_YEAR_MONTH_DATE_HOUR;
        var $logFieldDef= array(11,9,25,25,500);
        var $logFieldSeparator= "|";
        
        /**
        * Contiene el número mínimo de caracteres que debe tener una página pedida al dominio interno
        * para ser aceptada como "buena.
        *
        * @access private
        * @var int
        */
        var $minimumBytes= MIN_CHARS_PAGE; 
        
        /**
        * Constructor
        * @access public
        *
        * @param string $host Dominio al que conectamos
        * @param int $port Puerto al que conectamos
        * @return mixed Objeto PAFInternalRequest inicializado o un PEAR_Error en caso de que
        *         no se proporcione el host correspondiente al dominio interno.
        */
        function PAFInternalRequest($host, $port=80)
        {            
            // Lanza error si no se proporciona un host (parámetro obligatorio).
            if ( empty($host) )
            {
                $this= PEAR::raiseError("ERROR [PAFInternalRequest]=> No se ha proporcionado un HOST correcto.");
                return $this;
            }

            $this->PAFObject();
            $this->sock= new PAFSocket($host, $port);
            $this->sock->setPeticionTimeout(PIR_TIMEOUT);
            $this->httpClient= new PAFHttpClient($this->sock);
        }
        
        // TODO: Incorporar métodos get/set para poder cambiar los parámetros por defecto del log.
        
        /**
        * Devuelve el número de bytes mínimo que debe tener una página devuelta por el dominio interno 
        * para ser considerada como "buena".
        *
        * @return int
        * @access public
        */
        function getMinimumBytes()
        { return $this->minimumBytes; }
        
        /**
        * Establece el número de bytes mínimo que debe tener una página devuelta por el dominio interno 
        * para ser considerada como "buena".
        *
        * @param int $value Número de bytes mínimo.
        * @return mixed true si el valor proporcionado por parámetro es numérico o un PEAR_Error en caso contrario.
        */
        function setMinimumBytes($value)
        {
            if ( empty($value) || !is_int($value) )
            {
                $msg="¡ERROR! [PAFInternalRequest::setMinimumBytes]=> El parámetro proporcionado al método es vacío o no es un número entero.";
                return PEAR::raiseError($msg);
            }
            
            $this->minimumBytes= $value;
            return true;
        }
        
        /**
        * Metodo de petición de página al dominio interno.
        *
        * @access public
        * @param string $page Página que solicitamos
        * @return el parseo de la página o un objeto PEAR_Error en caso de error.
        */
        function getPage ($page, $host=null, $otherHeaders="")
        {
            $result=null;           // Resultado de la operación GET.
            $resultContent=null;    // Contenido obtenido de la operación GET.
            $aux=1;                 // Contador de reintentos en caso de error en la operación GET.
            $parameters= null;  // Lista de parámetros que trae originalmente la URL solicitada.

            // Extrae el nombre del script o página solicitada.
            $explodePage= explode ("?", $page);
            $page= trim($explodePage[0]);

            // Es OBLIGATORIO pasar este parámetro porque si no nos metemos en un bucle sin salida. 
            // También es importante introducirlo al principio ya que si viene por URL un parámetro 
            // claveIn podemos entrar en un bucle sin salida.
            //
            // NOTA: Probar a hacer esto con funciones de cadena a ver si pesa menos y ganamos algo
            //       en rendimiento.
            $parameters['claveIn']= CLAVE_IN;
         
            // Extrae los parámetros originales de la URL.
            if ( count($explodePage) == 2 && !empty($explodePage[1]) )
            {
                $paramsExplode= explode("&",$explodePage[1]);
                $numElem= count ($paramsExplode);
                for ($i= 0; $i<$numElem; $i++)
                {
                    $paramExplodeEq = explode("=", $paramsExplode[$i]);
                    // Elimina cualquier parámetro que venga en la URL original que se llame
                    // como el necesario para la clave de petición al dominio interno.
                    if ( strcmp($paramExplodeEq[0],"claveIn") != 0 )
                    { $parameters[$paramExplodeEq[0]]= $paramExplodeEq[1]; }    
                }                 
            }

            $socket=& $this->httpClient->getSocket();
	    $retries = 0;

	    do {
            	// Solicitamos la página por primera vez (con sus parámetros originales). El propósito
            	// de esto es intentar recuperar la página que ya hubiera en la caché. De este modo
            	// preservamos el tiempo de caché original que imponga la página.
            	$result= $this->httpClient->GET($page, $parameters, $host, $otherHeaders);                         

            	$response304=false; 
            	if ( !PEAR::isError($result) )
            	{
                	//$this->logMessage("Petición al dominio interno correcto.");
                	$result= $this->httpClient->getContent(); 
				
				$allHeaders=$this->getAllHeaders();
				if (ereg(".304.",$allHeaders[0])){
					$response304=true;
				}
            	}

		$error = (!$response304 && (PEAR::isError($result) || is_null($result) || strlen(trim($result)) == 0 || (strlen ($result)<= $this->minimumBytes )));

                // Se pide la página correspondiente al intervalo de tiempo que corresponda a la hora
                // en la que nos encontramos. Esta petición se realiza tres veces por si acaso.

                $time= strftime("%H:%M");                   // Recoge la hora actual.
                $timeGap= $this->CalculateTimeGap($time);   // Calcula a qué intervalo corresponde.
                $parameters["__retries"] = $retries;
		$parameters["__time"] = $timeGap;

		$retries++;


	    } while ($error && $retries < PIR_RETRIES);

            if ( $retries == PIR_RETRIES )
                { $this->logMessage($result); }

            $this->httpClient->socket->close();
            return $result;
        }        
        
        /**
        * Metodo de inicialización del dato miembro allHeaders mediante
		* la consulta de todos los headers de la página pedida.
        *
        * @access public
        * @return todos los headers
        */
        function getAllHeaders ()
        {
			if (is_array($this->allHeaders)) return $this->allHeaders;

            $header_str=null;           // Resultado de la operación GET.
			$header_str= $this->httpClient->getHeaders();
			$this->allHeaders=explode("\r\n", $header_str);
			return $this->allHeaders;
		}
        /**
        * Metodo de consulta de los headers de la página pedida
        *
        * @access public
        * @param string $type tipo de header pedido
        * @return el valor de ese header
        */
        function getHeader ($type)
        {
            $header_str=null;           // Resultado de la operación GET.
			$header_str= $this->httpClient->getHeaders();
			ereg("$type: ([^\r\n]*)", $header_str, $result);
			return $result[1];
		}
        /**
        * Método para la inicialización del log de la clase.
        * Proporciona valores por defecto para todos los parámetros con el fin de facilitar su utilización.
        * Es posible modificar los valores por defecto que controlarán el comportamiento del log una vez que
        * se ha llamado a este método de incialización haciendo uso de los métodos propios de la clase
        * PAFLog.
        *
        * @param string $logFileName Nombre para el fichero de log.
        * @param string $logBaseDir Directorio base de ubicación del fichero de log.
        * @param string $logFileExt Extensión para el fichero de log.
        * @param int $balanceMode Modo de balanceo/distribución para el log.
        * @param array $logFieldLenDef Array de integers con las longitudes asociadas a los campos de cada
        *        línea del log.
        *
        * @return mixed True si se ha conseguido inicializar el sistema de log de la clase
        *         o un PEAR_Error en caso contrario.
        * @access public
        */
        function initLog()
        {
            // Creación del objeto Log a partir de los valores por defecto 
            $this->log= new PAFLog (
                                    $this->logName,
                                    $this->logBaseDir,
                                    $this->logFieldDef,
                                    $this->logExt,
                                    $this->logBalancingMode
                                   );
                                   
            // Comprobación de inicialización correcta del sistema de logs.
            if ( PEAR::isError($this->log) )
            {
                $msg= "¡ERROR! [PAFInternalRequest::initLog]=> Se ha producido un error al intentar inicializar el sistema de log. ";
                $msg.= $this->log->getMessage();
                $this->log= null;
                return PEAR::raiseError($msg);
            }
            
            // Fija el separador de campo utilizado para los registros del log.
            $this->log->setFieldSeparator($this->logFieldSeparator);
            
            return true;
        }
        
        /**
        * Recibe la hora actual en formato hh:mm y calcula en que intervalo dentro de la hora
        * se encuentra teniendo en cuenta la constante PIR_TIME_INTERVAL.
        *
        * @access private
        * @return string
        */
        function CalculateTimeGap($time)
        {
            $ex= explode (":", $time); // $ex[0]=> hora, $ex[1]=> minutos.

            // Comprueba que la hora viene en el formato adecuado.
            // To-Do: A ver qué devolvemos en este punto.
            if (count($ex) < 2)
                return "Error";
            if ( strlen($ex[0]) != 2 || strlen($ex[1]) != 2)
                return "Error";

            $res= (integer)($ex[1] / PIR_TIME_INTERVAL);
            $ex[1]= ($res*PIR_TIME_INTERVAL) + PIR_TIME_INTERVAL;
            if ($ex[1] >= 60)
            {
                $ex[1]= "00";
                $ex[0]+=1;
            }

            // Formatea la cadena de los minutos para que tenga 2 caracteres.
            $ex[0]= sprintf("%02d", $ex[0]);
            $ex[1]= sprintf("%02d", $ex[1]);

            return ($ex[0] . $ex[1]);
        }
        
        /**
        * Se encarga de distinguir el error que se ha producido y dejar traza en el log de errores. 
        * El formato del log es el siguiente.
        * <dd-mm-yyyy> |<hh:mm.ss> |<ip_cliente> |<ip_servidor> |<mensaje>
        *
        * @param object $result Objeto PEAR_Error con el resultado de la petición al dominio interno.
        * @result mixed PEAR_Error en caso de que el sistema de logs no se encuentre inicializado o bien
        *         se produzca algún error durante la escritura del registro en el fichero. True en caso de que
        *         todo vaya bien.
        * @access protected
        */
        function logMessage($result)
        {
            // Comprueba si el sistema de log se encuentra inicializado.
            if ( is_null($this->log) )
            {
                $msg="¡ERROR! [PAFInternalRequest::logMessage]=> El sistema de logs no se encuentra incializado utilice PAFInternalRequest:initLog() desde su programa para inicializarlo.";
                return PEAR::raiseError($msg);
            }
            
            $result= $this->manageError($result);
            
            // Inicialización de los campos del log de errores.
            $errorDate= strftime("%d-%m-%Y");
            $errorTime= strftime("%H:%M.%S");
            $errorClient= PAFLog::getRealIP();
            $errorHost= getenv("HTTP_HOST");
            $errorMessage="";
            $errorLine="";
                                    
            $errorLine= $errorDate . 
                        $this->logFieldSeparator .
                        $errorTime .
                        $this->logFieldSeparator .
                        $errorClient .
                        $this->logFieldSeparator .
                        $errorHost . 
                        $this->logFieldSeparator .
                        $result->getMessage() . " (URL=> " . getenv("REQUEST_URI") . ")";
                        
            $resultOp= $this->log->writeLog($errorLine);
            
            return $resultOp;
        }
        
        /**
        * Método protegido que se encarga del manejo de las posibles condiciones de error que se pueden
        * producir en la petición de una página al dominio interno. Las condiciones de error contempladas
        * hasta la fecha son:
        *
        * 1.- Se produce un error interno (fallo de conexión, etc). En este caso $result es de tipo PEAR_Error.
        * 2.- Se recoge una página vacía. Hay que generar un PEAR_Error específico.
        * 3.- Se recoge una página cuya longitud mínima en bytes es menor de lo deseado. Este límite viene fijado
        *     por el atributo "minimumBytes" y puede ser alterado por medio del método setMinimumBytes.
        * 
        * @return object PEAR_Error con la descripción, código y nivel del error producido.
        * @access protected
        */
        function manageError($result)
        {
           // Si viene un Error interno.
           if ( PEAR::isError($result) )    
           { 
               $errorCode= PIR_ERROR_PEAR_NUMBER;
               $errorMessage= PIR_ERROR_PEAR_DESC . "=>" . $result->getMessage();
               $errorLevel= E_USER_ERROR;
           }
           // Si la página recuperada en null.
           elseif ( is_null($result) || empty($result) )
           {
               $errorCode= PIR_ERROR_NULLPAGE_NUMBER;
               $errorMessage= PIR_ERROR_NULLPAGE_DESC;
               $errorLevel= E_USER_ERROR;
           }
           // Si la página no puede ser considerada válida por el número de bytes que la componen.
           elseif ( strlen ($result)<= $this->minimumBytes )
           {
               $errorCode= PIR_ERROR_INCOMPLETEPAGE_NUMBER;
               $errorMessage= PIR_ERROR_INCOMPLETEPAGE_DESC;
               $errorLevel= E_USER_ERROR;
               
           }
           // Si la página viene completamente vacía.
           elseif ( strlen(trim($result)) == 0 )
           {
               $errorCode= PIR_ERROR_EMPTYPAGE_NUMBER;
               $errorMessage= PIR_ERROR_EMPTYPAGE_DESC;
               $errorLevel= E_USER_WARNING;
           }
           // En cualquier otro caso consideramos que lo que se desea es escribir un mensaje en el log.
           else
           {
               $errorCode= 0;
               $errorMessage= $result;
               $errorLevel= E_USER_NOTICE;
           }
           
           $err=& PEAR::raiseError(
                                    $errorMessage,
                                    $errorCode,
                                    null,
                                    $errorLevel
                                  );
           return $err;
        }
    }
?>
