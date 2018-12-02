<?php
/**
 * Clase de utilidades para Scripts
 */
require_once 'PAF/PAFApplication.php';
require_once 'PAF/PAFObject.php';
require_once 'PAF/PAFHttpEnv.php'; // Necesaria para PAFApplication
require_once 'phpmailer/class.phpmailer.php';

define('PAFSH_FLAG_CREATE', 1);
define('PAFSH_FLAG_REMOVE', 2);
define('PAFSH_FLAG_TEST', 3);

define('PAFSH_START',    1);
define('PAFSH_INFO',     2);
define('PAFSH_WARN',     4);
define('PAFSH_ERROR',    8);
define('PAFSH_STATS',   16);
define('PAFSH_END',     32);
define('PAFSH_SHUTDWON',64);
define('PAFSH_FLAG_ON',1);
define('PAFSH_FLAG_OFF',0);

define('PAFSH_ENV_PRO', 1);
define('PAFSH_ENV_EDI', 2);
define('PAFSH_ENV_PRE', 3);
define('PAFSH_ENV_DEV', 4);

/**
 * PAFScriptHelper 
 * 
 * @uses PAFOBject
 * @package 
 * @version $Id: $
 * @author $Author: $ 
 */
class PAFScriptHelper extends PAFOBject 
{


    /**
     * console_log_status  indica si la salida por consola esta activa
     * 
     * @var bool
     * @access public
     */
    var $console_log_status;

    /**
     * confData  almacena la configuracion del ini indicada en pMainSection
     * 
     * @var array
     * @access public
     */
    var $confData;

    /**
     * args  Almacena los argumentos de ejecucion del script
     * 
     * @var mixed
     * @access public
     */
    var $args;

    /**
     * label  etiqueta para el log
     * 
     * @var mixed
     * @access public
     */
    var $label;

    /**
     * finalizadoControlado  indica si el fin del script se produce de forma controlada o no, ej: un fatal error
     * 
     * @var bool
     * @access public
     */
    var $finalizadoControlado;

    /**
     * _dont_exit indica si el script debe ejecutar o no la instruccion exit() al llamar a la funcion end()
     * 
     * @var bool
     * @access protected
     */
    var $_dont_exit;

    /**
     * _exit_tried indica si se ha intentado un exit() dentro del script 
     * 
     * @var bool
     * @access protected
     */
    var $_exit_tried;

    /**
     * running_environment indica el entorno donde se ejecuta el script. Se utiliza como prefijo para generar los emails de informes
     * 
     * @var int
     * @access public
     */
    var $running_environment;

    /**
     * remove_flag_on_shutdown indica si hay que eliminar el flag de ejecucion al finalizar el script
     * 
     * @var bool
     * @access public
     */
    var $remove_flag_on_shutdown=true;

    /**
     * levels niveles de logs disponibes
     * 
     * @var array
     * @access public
     */
    var $levels = array(
            'START'   => PAFSH_START,  
            'INFO'    => PAFSH_INFO,   
            'WARN'    => PAFSH_WARN,   
            'ERROR'   => PAFSH_ERROR,  
            'STATS'   => PAFSH_STATS,  
            'END'     => PAFSH_END,    
            'SHUTDOWN'=> PAFSH_SHUTDWON
            );

    /**
     * filter_level filtro con el nivel de log que se va a registrar
     * 
     * @var mixed
     * @access public
     */
    var $filter_level;

    /**
     * buffer_to_file 
     * 
     * @var mixed
     * @access public
     */
    var $buffer_to_file = false;

    var $logger;

    var $_buffer_started;

    var $flagName;

    var $_dir_log;
    

    /**
     * init Metodo Singleton que devuelve una unica instancia de PAFScriptHelper 
     * 
     * @param mixed $pIniFile 
     * @param string $pMainSection 
     * @access public
     * @return PAFScriptHelper
     */
    function &init($pIniFile, $pMainSection='GENERAL')
    {
        static $_utils;

        if(!isset($_utils))
        {
            $_utils = & new PAFScriptHelper($pIniFile, $pMainSection);
        }
        return $_utils;
    }



    /**
     * __construct recoge unicamente variables del ini indicado y lee los parametros del script.
     *
     * @see init()
     * @param mixed $pIniFile ruta completa a un fichero .ini de configuracion
     * @param string $pMainSection  seccion principal a utilizar del fichero de configuracion
     * @access protected
     * @return void
     */
    function __construct($pIniFile, $pMainSection='GENERAL')
    {
        $debug = PAFApplication::getConf('DEBUG');
        if( !$debug )
        {
            die('Falta iniciar PAFApplication. Usar PAFApplication::init(<ini_file>)');
        }
        $this->getScriptArgs();
        PAFApplication::addIniFile('SCRIPT', $pIniFile);
        $this->confData = $this->getIniSection($pMainSection);
        $this->finalizadoControlado = false;
        $this->_dont_exit = false;
        $this->_exit_tried = false;
        $status = (bool)$this->getIniVar('consoleLogStatus');
        $this->consoleLogStatus($status);

        $this->filter_level = PAFSH_START | PAFSH_INFO | PAFSH_WARN | PAFSH_ERROR | PAFSH_STATS | PAFSH_END | PAFSH_SHUTDWON;
        set_error_handler(array(&$this, '_error_handler'));
        $conf = PAFApplication::getConf('DEBUG');
        $this->_dir_log = $conf['dir_log'];
    }

    /**
     * PAFScriptHelper 
     * constructor para PHP4
     * 
     * @see __construct
     */
    function PAFScriptHelper($pIniFile, $pMainSection='GENERAL')
    {
        $this->__construct($pIniFile, $pMainSection);
    }


    /**
     * getScriptArgs 
     * Genera la lista de argumentos del script en ejecucion, tanto para linea de comando como para ejecucion web
     * 
     * @access public
     * @return void
     */
    function getScriptArgs ()
    {

        $this->args = array();
        if( $_SERVER['argv'] )
        {
            $this->args = $this->getCmdArgs($_SERVER['argv']);
        }
        if( $_REQUEST )
        {
            foreach ($_REQUEST as $argKey => $argVal)
            {
                $this->args[strtolower($argKey)] = $argVal;
            }
        }
    }

    /**
     * getCmdArgs 
     * genera un array de parametros y sus valores 
     * esta tambien se puede llamar estaticamente PAFScriptHelper::getCmdArgs()
     * 
     * @param mixed $args 
     * @access public
     * @return array
     */
    function getCmdArgs($args=null)
    {
        $out = array();
        $last_arg = null;
        $args = is_array($args)?$args: $_SERVER['argv'];
        for($i = 1, $il = sizeof($args); $i < $il; $i++)
        {
            if( preg_match("/^--(.+)/", $args[$i], $match) )
            {
                $parts = explode("=", $match[1]);
                $key = preg_replace("/[^a-z0-9-]+/i", "", $parts[0]);
                if(isset($parts[1]))
                {
                    $out[$key] = $parts[1];
                }
                else {
                    $out[$key] = true;
                }
                $last_arg = $key;
            }
            else if( preg_match("/^-([a-z0-9]+)/i", $args[$i], $match) )
            {
                for( $j = 0, $jl = strlen($match[1]); $j < $jl; $j++ )
                {
                    $key = $match[1]{$j};
                    $out[$key] = true;
                }
                $last_arg = $key;
            }
            else if($last_arg !== null)
            {
                $out[$last_arg] = $args[$i];
            }
        }
        return $out;
    }

    /**
     * argExists comprueba si el parametro indicado esta definido en la lista de parametros del script
     * 
     * @param string $pArg 
     * @access public
     * @return bool
     */
    function argExists($pArg)
    {
        $arg = strtolower($pArg);
        if (is_array($this->args) )
        {
            return array_key_exists($arg, $this->args);
        }
        return false;
    }

    /**
     * getArgVal obtiene el valor de un parametro de la lista de parametros del script
     * 
     * @param string $pArg 
     * @access public
     * @return mixed
     */
    function getArgVal($pArg)
    {
        $arg = strtolower($pArg);
        if (is_array($this->args) && array_key_exists($arg, $this->args) )
        {
            return $this->args[$arg];
        }
        return null;
    }

    /**
     * getFirstArgVal obtiene el valor del primero existente en la lista de parametros del script.
     * Esta funcion vale para parametros que se puedan indicar de forma corta o larga. 
     * Ej: -p 1 o --p=1 o --param=1  se puede leer con la funcion $p = $helper->getFirstArgVal('p', 'param') 
     * 
     * @param variable arg list 
     * @access public
     * @return void
     */
    function getFirstArgVal(/* variable arg list */)
    {
        $pArgs = func_get_args();
        foreach($pArgs as $arg)
        {
            $arg = strtolower($arg);
            if (is_array($this->args) && array_key_exists($arg, $this->args) )
            {
                return $this->args[$arg];
            }
        }
        return null;
    }


    /**
     * getIniVar obtiene el valor del parametro en el fichero .ini.
     * 
     * @param string $pVarName 
     * @param string $pSection 
     * @access public
     * @return string
     */
    function getIniVar($pVarName, $pSection=null)
    {
        if ($pSection==null)
        {
            return $this->confData[$pVarName];
        }
        $data = PAFApplication::getConf($pSection, 'SCRIPT');
        return $data[$pVarName];
    }

    /**
     * getIniSection Obtiene todo el array de de valores de una seccion del fichero ini 
     * 
     * @param mixed $pSection 
     * @access public
     * @return void
     */
    function getIniSection($pSection)
    {
        $ret = PAFApplication::getConf($pSection, 'SCRIPT');
        if (PEAR::isError($ret))
        {
            $ret = null;
        }
        if( is_array($ret) && array_key_exists('EXTENDS_SECTION', $ret) )
        {
            $base_section = $ret['EXTENDS_SECTION'];
            unset( $ret['EXTENDS_SECTION'] );
            $ret2 = $this->getIniSection($base_section);
            if(!is_null($ret2))
            {
                $ret = array_merge($ret2, $ret);
            }
        }
        return $ret;
    }

    /**
     * setLabel  asigna una etiqueta para los logs
     * 
     * @param string $pLabel 
     * @access public
     */
    function setLabel($pLabel)
    {
        $this->label = $pLabel;
    }


    /**
     * start  inicia la ejecucion del script, registra la funcion de cierre y guarda descripcion y marca de tiempo de inicio en el log
     * 
     * @param string $pScriptDescription Descripcion del script que se esta iniciando
     * @param string $pLabel  etiqueta descriptiva
     * @access public
     * @return void
     */
    function start($pScriptDescription, $pLabel='')
    {
        register_shutdown_function(array(&$this, 'shutdown'), &$this->finalizadoControlado );

        $label = ($pLabel)? $pLabel : $this->label;
        $this->activityLog('START', sprintf("%s %s. Iniciado: %s", $label, $pScriptDescription, date('Y-m-d H:i:s') ) );

        if ($this->consoleLogStatus() )
        {
            $this->consoleLog('INFO', 'Log de consola activado.');
        }
    }

    /**
     * setDontExit indica que el script no ejecutará exit() en la llamada a end()
     * 
     * @access public
     * @return void
     */
    function setDontExit()
    {
        $this->_dont_exit = true;
    }

    /**
     * exitTried indica si se ha intentado un exit() dentro de la funcion end()
     * 
     * @access public
     * @return void
     */
    function exitTried()
    {
        return $this->_exit_tried;
    }

    /**
     * end finaliza la ejecucion del script. Cierra el log, controla si es un final controlado, en caso contrario envia un email de reporte
     * 
     * @param string $pMessage Texto descriptivo de la finalizacion del script
     * @param string $pLabel etiqueta descriptiva 
     * @param bool $pExit indica  si debe finalizar la ejecucion del script
     * @param bool $sendMail indica si hay que enviar email de reporte
     * @access public
     * @return void
     */
    function end($pMessage='', $pLabel='', $pExit=true, $sendMail=false)
    {
        $label = ($pLabel)? $pLabel : $this->label;

        $this->activityLog('END', sprintf("%s %s - Finalizado: %s\n", $label, $pMessage, date('Y-m-d H:i:s') ) );

        if($sendMail !== false)
        {
            $this->sendEmail($sendMail, $pMessage);
        }

        if ($pExit && !$this->_dont_exit)
        {
            $this->finalizadoControlado = true;
            exit;
        } else {
            $this->finalizadoControlado = true;
            $this->_exit_tried = true;
        }
    }

    /**
     * doExit alias de end() con el paramtro pExit a true. generado para mejor lectura.
     * 
     * @param string $pMessage 
     * @param string $pLabel 
     * @param mixed $sendMail 
     * @access public
     * @return void
     * @see end()
     */
    function doExit($pMessage='', $pLabel='', $sendMail=false)
    {
        // Alias de end() forzando el exit()
        $this->end($pMessage, $pLabel, $pExit=true, $sendMail);
    }

    /**
     * sendEmail envia un mail con el reporte de la ejecucion y la ruta completa del fichero de log
     * 
     * @param string $iniVar variable del fichero ini que indica la seccion a utilizar para enviar el mail
     * @param string $pMessage Mensaje a enviar en el correo
     * @param string $pPrefix Prefijo para el asunto del correo
     * @param bool $pLog indica si se debe enviar la ruta completa al fichero de log
     * @access public
     * @return void
     */
    function sendEmail($iniVar, $pMessage, $pPrefix='', $pLog=true) 
    {
        if( !is_string($iniVar))
        {
            $paramsMail = $this->getIniSection($this->getIniVar('DEFAULT_MAIL'));
        }else{
            $paramsMail = $this->getIniSection($this->getIniVar($iniVar));
        }
        switch($this->running_environment )
        {
            case PAFSH_ENV_PRO:
                $env = '[PRO] ';
                break;
            case PAFSH_ENV_PRE:
                $env = '[PRE] ';
                break;
            case PAFSH_ENV_EDI:
                $env = '[EDI] ';
                break;
            default:
                $env = '[DEV] ';

        }
        $subject = sprintf("%s%s %s", $env,  $pPrefix,  $paramsMail['SUBJECT']);
        $msg = $pMessage;
        if ($pLog)
        {
            $msg .= "\n\nLog: ". $this->getLogFullPath();
        }
        $msg .= "\nFecha: ". date('Y/m/d H:i:s');


        $this->_sendEmail( $paramsMail['TO'], $subject, $msg,$paramsMail['FROM'], $paramsMail['FROM_NAME']);
    }


    /**
     * _sendEmail 
     * Metodo que reporta un mail con los sucesos durante la ejecucion de algun script
     * para los correos contenidos en la lista de correos
     * 
     * @param string $to direccion remitente
     * @param string $subject titulo del mensaje
     * @param string $body cuerpo del mensaje
     * @param string $from email del remitente
     * @param string $fromName  nombre del remitente
     * @access protected
     * @return void
     */
    function _sendEmail($to, $subject = null, $body = null, $from = 'nobody@prisadigital.com',$fromName = 'Scripts') 
    {
        $use_sendmail = $this->getIniVar('USE_SENDMAIL');
        if( !$use_sendmail ) 
        {
            $this->consoleLog('WARN', "Email no enviado. Mensaje: {$body}");
            return;
        }
        $mail = new PHPMailer();
        $mail->From = $from;
        $mail->FromName = $fromName;
        $mail->IsHTML(true);

        $to = explode(';', $to);
        for($x=0;$x<count($to);$x++)
        {
            $mail->AddAddress($to[$x]);
        }

        $mail->Subject = $subject ? $subject : 'Pruebas';
        $mail->Body = nl2br(preg_replace( "/\n/", "\r\n",  $body));
        $result = $mail->Send();

        //Si no se ha mandado el mensaje se haran 4 intentos de remitirlo
        $intentos = 1;
        while((!$result) && ($intentos<5))
        {
            sleep(5);
            $result = $mail->Send();
            $intentos =+1;
        }

        if(!$result)
        {
            $this->consoleLog('ERROR', "Problemas enviando correo electrónico a {$valor}");
            $this->consoleLog($mail->ErrorInfo);
        }else{
            $this->consoleLog('INFO', "Mensaje enviado correctamente.");
        }
    }

    /**
     * getLogFullPath obtiene/genera la ruta completa al fichero de log
     * NOTA: obtenido de PAFApplication::log ya que no guarda la ruta y el fichero en propiedades
     * 
     * @access public
     * @return void
     */
    function getLogFullPath() 
    {
        if( !isset($this->logFullPath ) )
        {
            $log = $this->getIniVar('DIR_LOG_NAME');


            $username = PAFHttpEnv::apacheUserName();

            $this->logFullPath = sprintf("%s/%s/%s_%s_%s.log", $this->_dir_log, $log, date('Ymd'), $log, $username);
        }
        return $this->logFullPath;
    }

    /**
     * consoleLogStatus indica si el log se mostrará por pantalla
     * 
     * @param bool $pConsoleLogStatus 
     * @access public
     * @return void
     */
    function consoleLogStatus($pConsoleLogStatus = null) 
    {
        if ($pConsoleLogStatus != null)
        {
            $this->console_log_status = $pConsoleLogStatus;
        }
        return $this->console_log_status;
    }


    /**
     * consoleLog  log de actividad solo visible en ejecucion por consola 
     * 
     * @param mixed $level nivel de log del mensaje
     * @param mixed $pMessage mensaje a registrar
     * @access public
     * @return void
     */
    function consoleLog($level, $pMessage)
    {
        if ($this->console_log_status)
        {
            if( is_array( $pMessage ) )
            {
                $pMessage = implode(', ', $pMessage);
            }
            echo "\n[{$level}] {$pMessage}";
            $this->_flush();
        }
    }


    /**
     * activityLog 
     * Grabamos el log de actividad en fichero
     * 
     * @param mixed $level nivel de log del mensaje
     * @param mixed $pMessage  mensaje a registrar
     * @access public
     * @return void
     */
    function activityLog($level, $pMessage)
    {
        $this->log($level, $pMessage);
        $this->consoleLog($level, $pMessage);
    }

    /**
     * logStats   log en una línea resumida del array con datos estadisticos de ejecucion.
     * 
     * @param mixed $pLogData 
     * @param string $pLabel 
     * @access public
     * @return void
     */
    function logStats($pLogData, $pLabel='Resumen:')
    {
        if (!is_array($pLogData))
        {
            $this->activityLog('STATS', $pLogData);
        } else {
            $message = "$pLabel ";
            foreach ($pLogData as $counter => $value)
            {
                $message .= sprintf('[%s: %s] ', $counter, $value);
            }
            $this->activityLog('STATS', $message);
        }
    }

    /**
     * setEnv indica el entorno donde se ejecutará el script
     * 
     * @param mixed $env 
     * @access public
     * @return void
     */
    function setEnv($env)
    {
        $this->running_environment = $env;
    }


    /** ------- GESTION DE ERRORES ---- **/
    /**
     * processError 
     * Recoje un PEAR error y lo registra en el log. Genera un backtrace comprimido.
     * 
     * @param mixed $pError 
     * @param string $pLabel 
     * @access public
     * @return void
     */
    function processError($pError, $pLabel='')
    {
        $label = ($pLabel)? $pLabel : $this->label;
        if( PEAR::isError($pError) ) 
        {
            $msg = "PEAR_Error: ".$pError->toString();
            $msg.= "\nBACKTRACE:\n";
            $msg.= "\n----------------------------------------\n";
            array_walk(debug_backtrace(),create_function('$a,$b,$c','$c.= "{$a[\'function\']}()(".basename($a[\'file\']).":{$a[\'line\']}) | ";'), &$msg);
            $msg.= "\n----------------------------------------\n";
            $this->activityLog('ERROR', $msg);
        }
    }


    /**
     * log gestion de logs segun nivel de log.  Si existe un objeto logger lo utliza, en caso contrario almacena en fichero segun 
     * el nivel de log configurado en filter_level
     * 
     * @access public
     * @return void
     */
    function log(/* variable argument list */) 
    {
        if( is_object( $this->logger ) && method_exists($this->logger, 'log') )
        {
            $args = func_get_args();
            call_user_func_array(array($this->logger, "log"), $args);
        }
        else 
        {
            $level = func_get_arg(0);
            $msg = func_get_arg(1);
            if( is_string($level) && array_key_exists($level, $this->levels)  )
            {
                $level_flag = $this->levels[$level];
            }
            else 
            {
                $level_flag = $level;
            }

            if ( !is_int($level_flag) || ($this->filter_level & $level_flag) === $level_flag )
            {
                $log_file = $this->getLogFullPath();

                // Creamos el directorio si no existe.
                PAFApplication::mkdirhier(dirname($log_file));

                if( $level ) 
                {
                    $msg = sprintf("[%s] %s\n", $level, $msg);
                }
                else
                {
                    $msg = sprintf("%s\n", $msg);
                }
                $fp = @fopen($log_file, 'a+');
                fwrite($fp, $msg);
                fclose($fp);
            }
        }
    }

    /**
     * setFilterLevel indica el nivel de logs que se deben registrar. Utiliza flags binarios. 
     * Ej para activar un flag:     setFilterlevel(PAFSH_START, PAFSH_FLAG_ON) 
     * Ej para desactivar un flag:  setFilterlevel(PAFSH_START, PAFSH_FLAG_OFF) 
     * 
     * @param mixed $flag nivel de log a activar/desactivar. ver $filter_level
     * @param mixed $set accion a realizar PAFSH_FLAG_ON|PAFSH_FLAG_OFF
     * @access public
     * @return void
     */
    function setFilterLevel($flag, $set=PAFSH_FLAG_ON)
    {
        if (($set == PAFSH_FLAG_ON))  $this->filter_level = ($this->filter_level  | $flag);
        if (($set == PAFSH_FLAG_OFF)) $this->filter_level = ($this->filter_level  & ~$flag);
    }

    /**
     * setLogger agrega un objeto externo de log. debe tener al menos una funcion log()
     * 
     * @param mixed $logger Objeto donde registrar el log
     * @access public
     * @return void
     */
    function setLogger(&$logger)
    {
        $this->logger =& $logger;
    }


    /** ------- GESTION DE ERRORES ---- **/

    /** ------- GESTION DE FLAGS ---- **/
    /**
     * flag realiza todas las acciones sobre el flag indicado
     * si no se indica un nombre se utiliza el nombre por defecto configurado 
     * 
     * @param mixed $flagName nombre del flag a tratar
     * @param mixed $action accion a realizar sobre el flag. crear/borrar/comprobar
     * @access public
     * @return mixed 
     */
    function flag($flagName, $action=PAFSH_FLAG_TEST) 
    {
        if( is_null( $flagName ) ) 
        {
            $flagName = $this->getFlagName();
        }

        $flag = $this->getFlagFilename($flagName);

        switch( $action )
        {
            case PAFSH_FLAG_CREATE: 
                {
                    if( $this->flag($flagName, PAFSH_FLAG_TEST) )
                    {
                        $res = false;
                    } else {
                        $res = touch($flag);
                    }
                } break;
            case PAFSH_FLAG_REMOVE: 
                {
                    if( $this->flag($flagName, PAFSH_FLAG_TEST) )
                    {
                        $res = unlink($flag);
                    } else {
                        $res = false;
                    }
                } break;
            default: 
                {
                // PAFSH_FLAG_TEST
                    $res = 0;
                    if (file_exists($flag))
                    {
                        $res = time() - filemtime($flag);
                        // nos aseguramos que al menos reportamos 1
                        if  ($res <= 0)
                        {
                            $res  = 1;
                        }
                    }
                }
        }
        return $res;
    }

    /**
     * getFlagFilename  obtiene la ruta completa al fichero de flag. si el directorio indicado no existe intenta generarlo
     * 
     * @param mixed $flagName nombre del flag a buscar
     * @access public
     * @return string 
     */
    function getFlagFilename($flagName)
    {
        $path = $this->getIniVar('FLAG_PATH');
        $flag = sprintf("%s/%s.flg", $path, $flagName);
        PAFApplication::mkdirhier(dirname($flag));
        return $flag;
    }

    /**
     * expiredFlag comprueba si el flag indicado esta expirado segun el tiempo indicado o la variable MAX_FLAG_AGE_IN_SECONDS
     * indicada en el fichero ini.
     * Si el flag esta caducado lo elimina
     * 
     * @param mixed $flagName Nombre del flag a buscar
     * @param mixed $expiration_time tiempo de expiracion a comprobar
     * @access public
     * @return void
     */
    function expiredFlag($flagName=null, $expiration_time=null)
    {
        if( !$expiration_time ) 
        {
            $expiration_time = $this->getIniVar('MAX_FLAG_AGE_IN_SECONDS');
        }
        $flagAge = $this->testFlag($flagName);
        if( $flagAge && $flagAge > $expiration_time )
        {
            $this->removeFlag($flagName);
            return true;
        }
        else 
        {
            return false;
        }
    }

    /**
     * testFlag comprueba si el flag existe
     * 
     * @param string $flagName nombre del flag a comprobar
     * @access public
     * @return bool
     * @see flag()
     */
    function testFlag($flagName=null) 
    {
        return $this->flag($flagName, PAFSH_FLAG_TEST);
    }

    /**
     * flagExists 
     * alias de testFlag() para mejor lectura de script.
     * 
     * @param mixed $flagName 
     * @access public
     * @return bool
     * @see testFlag()
     */
    function flagExists($flagName=null)
    {
        return $this->flag($flagName, PAFSH_FLAG_TEST);
    }


    /**
     * createFlag hace un touch() del fichero de flag con el nombre indicado. 
     * 
     * @param string $flagName nombre del flag a crear
     * @access public
     * @return bool
     */
    function createFlag($flagName=null) 
    {
        return $this->flag($flagName, PAFSH_FLAG_CREATE);
    }

    /**
     * removeFlag elimina el fichero de flag indicado
     * 
     * @param mixed $flagName nombre del flag a borrar
     * @access public
     * @return bool
     */
    function removeFlag($flagName=null) 
    {
        return $this->flag($flagName, PAFSH_FLAG_REMOVE);
    }

    /**
     * getFlagName obtiene el nombre del flag por defecto del script
     * 
     * @access public
     * @return string
     */
    function getFlagName()
    {
        return $this->flagName;
    }

    /**
     * setFlagName indica el nombre por defecto para la gestion de flags
     * 
     * @param string $name  nombre del flag
     * @access public
     * @return void
     */
    function setFlagName($name)
    {
        $this->flagName = $name;
    }

    /** ------- GESTION DE FLAGS ---- **/


    /** ------- FLUSH - START ---- **/
    /**
     * _flush  limpia el buffer en la ejecucion por consola. 
     * 
     * @access protected
     * @return void
     */
    function _flush()
    {
        // check that buffer is actually set before flushing
        if (ob_get_length())
        {
            @ob_flush();
            @flush();
            @ob_end_flush();
            $this->_buffer_started = false;
        }
        $this->_ob_start();
    }

    function sendBufferToLog()
    {
        $this->buffer_to_file = true;
        $this->_ob_start();
    }
    function _ob_start()
    {

        if( $this->_buffer_started ) return;

        if($this->buffer_to_file)
        {
            @ob_start(array($this, '_ob_file_callback'));
        }
        else
        {
            @ob_start();
        }

        $this->_buffer_started = true;

    }
    /** ------- FLUSH - END ---- **/

    /**
     * shutdown se ejecuta siempre luego de finalizar el script, ya sea de forma controlada o por un error.
     * recibe el estado de la variable finalizadoControlado. Si el script no ha registrado el finalizadoControlado a TRUE, 
     * intenta enviar un email con el reporte de ejecucion
     * 
     * @param mixed $finalizadoControlado estado de ejecucion del script
     * @access public
     * @return void
     */
    function shutdown($finalizadoControlado)
    {
        $this->removeFlagOnShutdown(); 
        if( !$finalizadoControlado ) 
        {
            $msg = "Ejecucion abortada de forma no controlada.";

            $this->activityLog('SHUTDOWN',  $msg );
            $this->sendEmail( null, $msg, "SHUTDOWN" );
        }
    }

    /**
     * setRemoveFlagOnShutdown indica si el flag de ejecucion debe ser eliminado automaticamente al finalizar el script
     * 
     * @param bool $v 
     * @access public
     * @return void
     */
    function setRemoveFlagOnShutdown($v)
    {
        $this->remove_flag_on_shutdown = $v;
    }

    /**
     * removeFlagOnShutdown elimina el flag por defecto del script una vez que la ejecucion ha finalizado
     * 
     * @access public
     * @return void
     */
    function removeFlagOnShutdown() 
    {
        $flagName = $this->getFlagName();
        if( $this->remove_flag_on_shutdown && $this->flagExists($flagName) ) 
        {
            $this->removeFlag($flagName);
        }
    }

    function _ob_file_callback($buffer)
    {
        //$obj =& PAFScriptHelper::init(null); // recuperamos la instancia
        $obj =& $this;
        $msg = trim($buffer);
        if( strlen($msg) )
            $obj->log('', $msg);
    }

    function _error_handler($errno, $errstr, $errfile, $errline)
    {
        switch ($errno) {
            case E_NOTICE:
            case E_USER_NOTICE:
                return;
                $errors = "Notice";
                break;
            case E_WARNING:
            case E_USER_WARNING:
                $errors = "Warning";
                break;
            case E_ERROR:
            case E_USER_ERROR:
                $errors = "Fatal Error";
                break;
            default:
                $errors = "Unknown";
                break;
        }
        $msg = sprintf( '[err:%s - %s][%s:%s]',$errno, $errstr, $errfile, $errline);
        $this->log('PHP-'.$errors, $msg);
    }

}

