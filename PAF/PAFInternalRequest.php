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
    * @const PIR_TIMEOUT Constante para timeout de transmisi�n
    *        del socket (expresado en segundos).
    */
    define ("PIR_TIMEOUT", 45);

    /**
    * @const PIR_TIME_INTERVAL Minutos del intervalo para par�metro adicional de la petici�n
    *        a la cach� de doble nivel. Todas las peticiones pertenecen a una hora. Ese intervalo es el
    *        que indica esta constante (10:00 => 1002, 10:03=> 1004, etc.).
    */
    define ("PIR_TIME_INTERVAL", 2);

    /**
    * @const PIR_RETRIES N�mero de reintentos que se realizan al dominio interno si se producen errores
    *        en la recuperaci�n de la p�gina.
    */
    define ("PIR_RETRIES", 3);

    /**
    * @const MIN_CHARS_PAGE N�mero m�nimo de caracteres que debe tener una p�gina devuelta para
    *        ser considerada como una p�gina "buena".
    */
    define ("MIN_CHARS_PAGE", 600);
    
    // TODO: Documentaci�n de las constantes para el manejo de errores.
    // Estas constantes definen las posibles condiciones de error que nos podemos encontrar
    // a la hora de recuperar el contenido de una petici�n interna.
    define ("PIR_ERROR_PEAR_NUMBER", -1000);
    define ("PIR_ERROR_NULLPAGE_NUMBER", -1001);
    define ("PIR_ERROR_INCOMPLETEPAGE_NUMBER", -1002);
    define ("PIR_ERROR_EMPTYPAGE_NUMBER", -1003);

    define ("PIR_ERROR_PEAR_DESC", "Se ha producido un error interno.");
    define ("PIR_ERROR_NULLPAGE_DESC", "Se ha recuperado una p�gina nula.");
    define ("PIR_ERROR_INCOMPLETEPAGE_DESC", "Se ha recuperado una p�gina incompleta");
    define ("PIR_ERROR_EMPTYPAGE_DESC", "Se ha recuperado una p�gina vac�a");
    
    
    /**
    * Encapsula la pet�ci�n de paginas a un dominio.
    *
    * Contiene un sistema de log de acciones apoyado en la clase PAFLog que debe ser incializado
    * explicitamente por el usuario si se quiere utilizar por medio del m�todo initLog. Si no se inicializa
    * no se guardar� traza de nada. Por defecto el modo de distribuci�n de este log es PL_BALANCEMODE_YEAR_MONTH_DATE_HOUR
    * lo cual indica que se distribuiran por A�o/mes/dia un fichero por hora.
    *
    * El formato de este log es el siguiente:
    * <fecha> |<hora> |<ip_cliente> |<ip_servidor> |<mensaje>
    *
    * Donde: <host> indica el nombre del host que est� realizando la petici�n interna.
    *
    * @author Alfonso Gom�riz <agomariz@prisacom.com>, Sergio Cruz <scruz@prisacom.com>
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
        * Cliente HTTP que realiza la petici�n de la p�gina.
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
        
        // Par�metros por defecto para el Log de peticiones internas.
        var $logName= "PAFInternalRequest";
        var $logExt= "data";
        var $logBaseDir="/LOGS/sergio";
        var $logBalancingMode= PL_BALANCEMODE_YEAR_MONTH_DATE_HOUR;
        var $logFieldDef= array(11,9,25,25,500);
        var $logFieldSeparator= "|";
        
        /**
        * Contiene el n�mero m�nimo de caracteres que debe tener una p�gina pedida al dominio interno
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
            // Lanza error si no se proporciona un host (par�metro obligatorio).
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
        
        // TODO: Incorporar m�todos get/set para poder cambiar los par�metros por defecto del log.
        
        /**
        * Devuelve el n�mero de bytes m�nimo que debe tener una p�gina devuelta por el dominio interno 
        * para ser considerada como "buena".
        *
        * @return int
        * @access public
        */
        function getMinimumBytes()
        { return $this->minimumBytes; }
        
        /**
        * Establece el n�mero de bytes m�nimo que debe tener una p�gina devuelta por el dominio interno 
        * para ser considerada como "buena".
        *
        * @param int $value N�mero de bytes m�nimo.
        * @return mixed true si el valor proporcionado por par�metro es num�rico o un PEAR_Error en caso contrario.
        */
        function setMinimumBytes($value)
        {
            if ( empty($value) || !is_int($value) )
            {
                $msg="�ERROR! [PAFInternalRequest::setMinimumBytes]=> El par�metro proporcionado al m�todo es vac�o o no es un n�mero entero.";
                return PEAR::raiseError($msg);
            }
            
            $this->minimumBytes= $value;
            return true;
        }
        
        /**
        * Metodo de petici�n de p�gina al dominio interno.
        *
        * @access public
        * @param string $page P�gina que solicitamos
        * @return el parseo de la p�gina o un objeto PEAR_Error en caso de error.
        */
        function getPage ($page, $host=null, $otherHeaders="")
        {
            $result=null;           // Resultado de la operaci�n GET.
            $resultContent=null;    // Contenido obtenido de la operaci�n GET.
            $aux=1;                 // Contador de reintentos en caso de error en la operaci�n GET.
            $parameters= null;  // Lista de par�metros que trae originalmente la URL solicitada.

            // Extrae el nombre del script o p�gina solicitada.
            $explodePage= explode ("?", $page);
            $page= trim($explodePage[0]);

            // Es OBLIGATORIO pasar este par�metro porque si no nos metemos en un bucle sin salida. 
            // Tambi�n es importante introducirlo al principio ya que si viene por URL un par�metro 
            // claveIn podemos entrar en un bucle sin salida.
            //
            // NOTA: Probar a hacer esto con funciones de cadena a ver si pesa menos y ganamos algo
            //       en rendimiento.
            $parameters['claveIn']= CLAVE_IN;
         
            // Extrae los par�metros originales de la URL.
            if ( count($explodePage) == 2 && !empty($explodePage[1]) )
            {
                $paramsExplode= explode("&",$explodePage[1]);
                $numElem= count ($paramsExplode);
                for ($i= 0; $i<$numElem; $i++)
                {
                    $paramExplodeEq = explode("=", $paramsExplode[$i]);
                    // Elimina cualquier par�metro que venga en la URL original que se llame
                    // como el necesario para la clave de petici�n al dominio interno.
                    if ( strcmp($paramExplodeEq[0],"claveIn") != 0 )
                    { $parameters[$paramExplodeEq[0]]= $paramExplodeEq[1]; }    
                }                 
            }

            $socket=& $this->httpClient->getSocket();
	    $retries = 0;

	    do {
            	// Solicitamos la p�gina por primera vez (con sus par�metros originales). El prop�sito
            	// de esto es intentar recuperar la p�gina que ya hubiera en la cach�. De este modo
            	// preservamos el tiempo de cach� original que imponga la p�gina.
            	$result= $this->httpClient->GET($page, $parameters, $host, $otherHeaders);                         

            	$response304=false; 
            	if ( !PEAR::isError($result) )
            	{
                	//$this->logMessage("Petici�n al dominio interno correcto.");
                	$result= $this->httpClient->getContent(); 
				
				$allHeaders=$this->getAllHeaders();
				if (ereg(".304.",$allHeaders[0])){
					$response304=true;
				}
            	}

		$error = (!$response304 && (PEAR::isError($result) || is_null($result) || strlen(trim($result)) == 0 || (strlen ($result)<= $this->minimumBytes )));

                // Se pide la p�gina correspondiente al intervalo de tiempo que corresponda a la hora
                // en la que nos encontramos. Esta petici�n se realiza tres veces por si acaso.

                $time= strftime("%H:%M");                   // Recoge la hora actual.
                $timeGap= $this->CalculateTimeGap($time);   // Calcula a qu� intervalo corresponde.
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
        * Metodo de inicializaci�n del dato miembro allHeaders mediante
		* la consulta de todos los headers de la p�gina pedida.
        *
        * @access public
        * @return todos los headers
        */
        function getAllHeaders ()
        {
			if (is_array($this->allHeaders)) return $this->allHeaders;

            $header_str=null;           // Resultado de la operaci�n GET.
			$header_str= $this->httpClient->getHeaders();
			$this->allHeaders=explode("\r\n", $header_str);
			return $this->allHeaders;
		}
        /**
        * Metodo de consulta de los headers de la p�gina pedida
        *
        * @access public
        * @param string $type tipo de header pedido
        * @return el valor de ese header
        */
        function getHeader ($type)
        {
            $header_str=null;           // Resultado de la operaci�n GET.
			$header_str= $this->httpClient->getHeaders();
			ereg("$type: ([^\r\n]*)", $header_str, $result);
			return $result[1];
		}
        /**
        * M�todo para la inicializaci�n del log de la clase.
        * Proporciona valores por defecto para todos los par�metros con el fin de facilitar su utilizaci�n.
        * Es posible modificar los valores por defecto que controlar�n el comportamiento del log una vez que
        * se ha llamado a este m�todo de incializaci�n haciendo uso de los m�todos propios de la clase
        * PAFLog.
        *
        * @param string $logFileName Nombre para el fichero de log.
        * @param string $logBaseDir Directorio base de ubicaci�n del fichero de log.
        * @param string $logFileExt Extensi�n para el fichero de log.
        * @param int $balanceMode Modo de balanceo/distribuci�n para el log.
        * @param array $logFieldLenDef Array de integers con las longitudes asociadas a los campos de cada
        *        l�nea del log.
        *
        * @return mixed True si se ha conseguido inicializar el sistema de log de la clase
        *         o un PEAR_Error en caso contrario.
        * @access public
        */
        function initLog()
        {
            // Creaci�n del objeto Log a partir de los valores por defecto 
            $this->log= new PAFLog (
                                    $this->logName,
                                    $this->logBaseDir,
                                    $this->logFieldDef,
                                    $this->logExt,
                                    $this->logBalancingMode
                                   );
                                   
            // Comprobaci�n de inicializaci�n correcta del sistema de logs.
            if ( PEAR::isError($this->log) )
            {
                $msg= "�ERROR! [PAFInternalRequest::initLog]=> Se ha producido un error al intentar inicializar el sistema de log. ";
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
            // To-Do: A ver qu� devolvemos en este punto.
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
        * @param object $result Objeto PEAR_Error con el resultado de la petici�n al dominio interno.
        * @result mixed PEAR_Error en caso de que el sistema de logs no se encuentre inicializado o bien
        *         se produzca alg�n error durante la escritura del registro en el fichero. True en caso de que
        *         todo vaya bien.
        * @access protected
        */
        function logMessage($result)
        {
            // Comprueba si el sistema de log se encuentra inicializado.
            if ( is_null($this->log) )
            {
                $msg="�ERROR! [PAFInternalRequest::logMessage]=> El sistema de logs no se encuentra incializado utilice PAFInternalRequest:initLog() desde su programa para inicializarlo.";
                return PEAR::raiseError($msg);
            }
            
            $result= $this->manageError($result);
            
            // Inicializaci�n de los campos del log de errores.
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
        * M�todo protegido que se encarga del manejo de las posibles condiciones de error que se pueden
        * producir en la petici�n de una p�gina al dominio interno. Las condiciones de error contempladas
        * hasta la fecha son:
        *
        * 1.- Se produce un error interno (fallo de conexi�n, etc). En este caso $result es de tipo PEAR_Error.
        * 2.- Se recoge una p�gina vac�a. Hay que generar un PEAR_Error espec�fico.
        * 3.- Se recoge una p�gina cuya longitud m�nima en bytes es menor de lo deseado. Este l�mite viene fijado
        *     por el atributo "minimumBytes" y puede ser alterado por medio del m�todo setMinimumBytes.
        * 
        * @return object PEAR_Error con la descripci�n, c�digo y nivel del error producido.
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
           // Si la p�gina recuperada en null.
           elseif ( is_null($result) || empty($result) )
           {
               $errorCode= PIR_ERROR_NULLPAGE_NUMBER;
               $errorMessage= PIR_ERROR_NULLPAGE_DESC;
               $errorLevel= E_USER_ERROR;
           }
           // Si la p�gina no puede ser considerada v�lida por el n�mero de bytes que la componen.
           elseif ( strlen ($result)<= $this->minimumBytes )
           {
               $errorCode= PIR_ERROR_INCOMPLETEPAGE_NUMBER;
               $errorMessage= PIR_ERROR_INCOMPLETEPAGE_DESC;
               $errorLevel= E_USER_ERROR;
               
           }
           // Si la p�gina viene completamente vac�a.
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
