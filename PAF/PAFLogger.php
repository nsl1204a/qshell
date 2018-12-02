<?php

require_once 'PAF/PAFObject.php';

define(LOGGER_LEVEL_ALL,    1);
define(LOGGER_LEVEL_TRACE,  2);
define(LOGGER_LEVEL_DEBUG,  4);
define(LOGGER_LEVEL_WARN,   8);
define(LOGGER_LEVEL_ERROR, 16);
define(LOGGER_LEVEL_FATAL, 32);

define(LOGGER_LEVEL_OFF, 2147483647);

/**
 * Clase para el manejo del log.
 * Ejemplo Uso:
 *   
 *   // Ejemplo con mensaje de texto
 *   $log =& new PAFLogger('directorio/fichero.log', LOGGER_LEVEL_DEBUG);
 *   if ($log->is_fatal_enabled()) {
 *       $log->fatal(__FILE__, __LINE__, $msg, $this);
 *   }
 *   
 *   // Ejemplo con objeto PEAR_Error
 *   $obj =& new Objeto();
 *   $res = $obj->metodo();
 *   if (PEAR::isError($res)) {
 *        $log->error(__FILE__, __LINE__, $res, $this);
 *        
 *        // Tratamos de recuperarnos del error
 *        if (!$recuperado)
 *           return $res;
 *   }
 *
 * @author    Virgilio Sanz 
 * @version   $Id: PAFLogger.php,v 1.3 2004/06/16 16:10:21 ljimenez Exp $
 * @package   PAF
 */
class PAFLogger extends PAFObject 
{
    /**
     * @var integer Nivel del log
     */
    var $level;
    
    /**
     * @var string path al fichero de log
     */
    var $filename;

    /**
     * @var float �ltima vez que se escribi� en el log.
     */
    var $lastWrite;
    
    /**
     *    Constructor
     *    
     *    @access public 
     *    @param string $filename Nombre del fichero de log
     *    @param int $level Nivel del log
     */
    function PAFLogger($filename, $level=LOGGER_LEVEL_ALL) 
    {
        $this->set_file_name($filename);
        $this->set_level($level);
    }

    /**
     *    Asigna el nivel de debug con el que funcionar� el objeto
     *    
     *    @access public 
     *    @param int $level Nivel de debug
     */
    function set_level($level) 
    {
        $this->level = $level;
    }

    /**
     *    Devuelve el nivel de debug.
     *    
     *    @access public 
     *    @returns integer nivel de debug
     */
    function get_level() 
    {
        return $this->level;
    }

    /**
     *    Asigna el path completo al fichero de log
     *    
     *    @access public 
     *    @param string $filename Path al fichero de log. 
     */
    function set_file_name($filename) 
    {
        $this->filename = $filename;
    }

    /**
     *    Devuelve el path completo al fichero de log.
     *    
     *    @access public 
     *    @returns string
     */
    function get_file_name() 
    {
        return $this->filename;
    }
   
    /**
     * Devuelve true su el nivel de log es igual o mayor a TRACE.
     * 
     * @access public
     * @returns boolean
     */
    function is_trace_enabled() 
    {
        return (LOGGER_LEVEL_TRACE >= $this->level);
    }
    
    /**
     *    Escribe en el log si el nivel de log.es mayor que INFO
     *    
     *    @access public 
     *    @param string $file Normalmente __FILE__
     *    @param int $line Normalmente __LINE__
     *    @param string $msg Mensaje de error que se quiere guardar.
     */
    function trace($file, $line, $msg, $obj=null)
    {
        if ($this->is_trace_enabled()) {
            return $this->log($file, $line, $msg, $obj, LOGGER_LEVEL_TRACE);
        }
        
        return true;
    }
    
    /**
     * Devuelve true si el nivel de log es igual o mayor a 'DEBUG'.
     * 
     * @access public
     * @returns boolean
     */
    function is_debug_enabled() 
    {
        return (LOGGER_LEVEL_DEBUG >= $this->level);
    }

    /**
     *    Escribe en el log si el nivel de log.es mayor que DEBUG
     *    
     *    @access public 
     *    @param string $file Normalmente __FILE__
     *    @param int $line Normalmente __LINE__
     *    @param string $msg Mensaje de error que se quiere guardar.
     *	 @param Object referencia al objeto donde se produjo el error.
     */
    function debug($file, $line, $msg, $obj=null) 
    {
        if ($this->is_debug_enabled()) {
            return $this->log($file, $line, $msg, $obj, LOGGER_LEVEL_DEBUG);
        }
        
        return true;
    }
    
    /**
     * Devuelve true su el nivel de log es igual o mayor a WARN.
     * 
     * @access public
     * @returns boolean
     */
    function is_warn_enabled() 
    {
        return (LOGGER_LEVEL_WARN >= $this->level);
    }

    /**
     *    Escribe en el log si el nivel de log.es mayor que WARN
     *    
     *    @access public 
     *    @param string $file Normalmente __FILE__
     *    @param int $line Normalmente __LINE__
     *    @param string $msg Mensaje de error que se quiere guardar.
     */
    function warn($file, $line, $msg, $obj=null) 
    {
        if ($this->is_warn_enabled()) {
            return $this->log($file, $line, $msg, $obj, LOGGER_LEVEL_WARN);
        }
        
        return true;
    }

    /**
     * Devuelve true su el nivel de log es igual o mayor a ERROR.
     * 
     * @access public
     * @returns boolean
     */
    function is_error_enabled() 
    {
        return (LOGGER_LEVEL_ERROR >= $this->level);
    }


    /**
     *    Escribe en el log si el nivel de log.
     *    
     *    @access public 
     *    @param string $file Normalmente __FILE__
     *    @param int $line Normalmente __LINE__
     *    @param string $msg Mensaje de error que se quiere guardar.
     */
    function error($file, $line, $msg, $obj=null) 
    {        
        if ($this->is_error_enabled()) {
            return $this->log($file, $line, $msg, $obj, LOGGER_LEVEL_ERROR);
        }
        
        return true;
    }
    
    /**
     * Devuelve true su el nivel de log es igual o mayor a FATAL.
     * 
     * @access public
     * @returns boolean
     */
    function is_fatal_enabled() 
    {
        return (LOGGER_LEVEL_FATAL >= $this->level);
    }

    /**
     *    Escribe en el log si el nivel de log.es mayor que FATAL
     *    
     *    @access public 
     *    @param string $file Normalmente __FILE__
     *    @param int $line Normalmente __LINE__
     *    @param string $msg Mensaje de error que se quiere guardar.
     */
    function fatal($file, $line, $msg, $obj = null) 
    {
        if ($this->is_fatal_enabled()) {
            return $this->log($file, $line, $msg, $obj, LOGGER_LEVEL_FATAL);
        }
        
        return true;
    }

    /**
     *    Forma el mensaje y escribe en el log.
     *    
     *    @access public
     *    @param string $file Normalmente __FILE__
     *    @param int $line Normalmente __LINE__
     *    @param string $msg Mensaje de error que se quiere guardar.
     *    @param object Objeto en el que se produjo el error
     *    @param int $level Nivel de error
     */
    function log($file, $line, $msg, &$obj, $level) 
    {
        global $REMOTE_HOST, $SERVER_NAME, $PHP_SELF, $QUERY_STRING;

	$antes = $this->lastWrite;
        $this->lastWrite = $this->get_time();
        if (PEAR::isError($msg)) {
            $txt = $msg->toString();
        }
        else {
            $txt = $msg;
        }
        $s = sprintf("%s|%.2f|%s|%s|%d|%s|%s|%s|%s|%d|%s\n",
                     date("d/m/Y H:i:s"),
                     $this->lastWrite - $antes,
                     $this->level_to_string($level),
                     basename($file), 
                     $line,
                     (null == $obj) ? ' - ' : get_class($obj),
                     "$PHP_SELF?$QUERY_STRING",
                     $REMOTE_HOST,  // FIXME: Esto hay que cambiarlo para que coja el correcto.
                     $SERVER_NAME, 
                     posix_getpid(),
                     $txt);

        $fp = fopen($this->filename, "a+");
        if (!$fp) {
            return PEAR::raiseError("No puedo escribir en log: ".$this->filename);
        }

        fwrite($fp, $s, strlen($s));
        fclose($fp);

        return true;
    }

    /**
     * Parsea un l�nea de log y devuelve un array con los valores.
     * 
     * @access public
     * @param sring $line Linea a parsear.
     * @returns array Valores que contiene la linea o PEAR_Error en caso de error.
     */
    function parse_line($line) {        
        $valores = explode('|', $line);
        $n_valores = count($valores);
        
        if (11 > $n_valores) {
            return PEAR::raiseError("Error en l�nea: [$line]");
        }
        else if (11 < $n_valores) {
            $msg = implode('|', array_slice($valores, 10));
        }
        else {
            $msg = $valores[10];
        }
        
        return array('time'      => $valores[0],
                     'time_diff' => $valores[1],
                     'level'     => $valores[2],
                     'PEAR'      => $valores[3],
                     'line'      => $valores[4],
                     'class'     => $valores[5],
                     'url'       => $valores[6],
                     'host'      => $valores[7],
                     'server'    => $valores[8],
                     'pid'       => $valores[9],
                     'msg'       => $msg
                     );
    }
    
    /**
     * Devuelve la representaci�n para un nivel de log.
     *
     * @access private
     * @param integer $level
     */
    function level_to_string($level) 
    {
        switch ($level) {
        
        case LOGGER_LEVEL_ALL:
            $ret = 'ALL';
            break;
        
        case LOGGER_LEVEL_DEBUG:
            $ret = 'DEBUG';
            break;
        case LOGGER_LEVEL_TRACE:
            $ret = 'TRACE';
            break;
        case LOGGER_LEVEL_WARN:
            $ret = 'WARN';
            break;
        case LOGGER_LEVEL_ERROR:
            $ret = 'ERROR';
            break;
        case LOGGER_LEVEL_FATAL:
            $ret = 'FATAL';
            break;
        
        case LOGGER_LEVEL_OFF:
            $ret = 'OFF';
            break;
            
        default:
            $ret = 'UNKNOWN';
            break;
        }
    
        return $ret;
    }

    /**
     * Devuelve el tiempo en milisegundo.
     *
     * @access public
     * @return float
     */
    function get_time()
    {
        list($usec, $sec) = explode(' ', microtime());
        return ((float)$usec + (float)$sec);
    }
}

?>
