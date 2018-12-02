<?php
// *****************************************************************************
// Lenguaje: PHP
// Copyright 2002 Prisacom S.A.
// ---------
// ChangeLog
// ---------
// $Log: PAFHtmlOutput.php,v $
// Revision 1.2  2003/10/22 16:40:43  vsanz
// Subida de seguridad. Compila pero no está probado.
//
// Revision 1.1  2003/10/16 17:29:58  vsanz
// Primera subida que compila, todav�a no hay tests
//
//
// *****************************************************************************

require_once 'PAF/PAFOutput.php';
require_once 'PAF/PAFLogFormat.php';
require_once 'log4php/LoggerManager.php';

define('CLASS_HTMLOUTPUT', 17);

/**
* Clase Base para Ouputs que devuelven HTML. 
* Esta clase contiene soporte para logging y separación de parte del Head
* y del Body.
* Para crear un Output de tipo Html hay que heredar de esta clase y
* reimplementar los métodos: getHeadContent, getBodyContent, hasHead, 
* hasBody.
*
* @author Virgilio Sanz <vsanz@prisacom.com> 
* @version $Revision: 1.2 $
* @package PAF
*/
class PAFHtmlOutput extends PAFOutput {
    /**
    * Contiene el nombre del log (appender) a utilizar
    *
    * @var string
    * @acces private
    */
    var $logName;
    
    /**
    * constructor
    *
    * @access public
    * @param $conf PAFConfiguration Configuración del Output.
    */
    function PAFHtmlOutput($logName='default') 
    {
        $this->PAFOutput();
        // Si no nos llega el log nos morimos..
        $this->logName = $logName;
    }
    
    
    /**
    * Asigna el nombre del log.
    *
    * @access public
    * @param string logName Nombre del log a utilizar
    * @return boolean true si va bien PEAR_Error si mal.
    */
    function setLogName($logName)
    {
        $this->logName = $logName;
    }
    
    /**
    * Asigna el nombre del log.
    *
    * @access public
    * @param string logName Nombre del log a utilizar
    * @return boolean true si va bien PEAR_Error si mal.
    */
    function getLogName()
    {
        return $this->logName;
    }
    
    /**
    * Reimplementar este método y devolver true en caso de tener
    * contenido dentro del <head>
    *
    * @access public
    * @return boolean
    */
    function hasHead()
    {
        return false;
    }
    
    /**
    * Reimplementar este método y devolver true en caso de tener
    * contenido dentro del <head>
    *
    * @access public
    * @return boolean
    */
    function hasBody()
    {
        return true;
    }
    
    // INTERFAZ PROTEGIDA para la implementación de las clases hijas,
    // Sólo hay que implementar la que se necesite de estas.
    // La implementación actual es sólo un mecanismo de defensa para
    // producir el menor impacto.
    
    /**
    * Realiza todo lo necesario para devolver la parte que va en el 
    * <head>. Como por ejemplo: javascript, CSS, ...
    *
    * @access protected
    * @return string Contenido para el Head
    */
    function getHeadContent()
    {
        return '';
    }
    
    /**
    * Realiza todo lo necesario para devolver la parte que va en el 
    * <body>.
    *
    * @access protected
    * @return string Contenido para el body
    */
    function getBodyContent()
    {
        return '';
    }
    
    // INTERFAZ PUBLICA
    // ESTAS SON LAS CLASES QUE SE LLAMARÁN DESDE FUERA.    
    /**
    * Esta clase devuelve el contenido para la parte del head y hace
    * todo el control del log. El trabajo de crear el contenido se hace
    * en el método protegido: getHeadContent
    *
    * @access public
    * @return string contenido para el Head
    */
    function getHead()
    {
        $log =& $this->getLog();
        
        if (!$this->hasHead()) {
            $msg = PAFLogFormat::format(__FILE__,__LINE__,"No hay header");
            if ($log->isErrorEnabled()) {
                $log->error($msg);
            }
            return PEAR::raiseError($msg);
        }
        
        // Si estamos en modo debug calculo el tiempo que tarda.
        if ($log->isDebugEnabled()) {
            $timeStart = PAFLogFormat::getMicroTime();
        }
        
        // Hacemos el output realmente
        $ret = $this->getHeadContent();
        if (PEAR::isError($ret)) {
            if ($log->isErrorEnabled()) {
                $log->error(PAFLogFormat::format(__FILE__, __LINE__,
                $ret->getMessage()));
            }
            return $ret;
        }
        
        // Si estamos en modo debug escribimos en log el tiempo que tardó
        if ($log->isDebugEnabled()) {
            $timeStop = PAFLogFormat::getMicroTime();
            $lapso = number_format($timeStop - $timeStart, 4);
            $log->debug(PAFLogFormat::format(__FILE__, __LINE__,
            "Tiempo en getHead ($lapso)"));
        }
        
        return $ret;
    }
    
    /**
    * Esta clase devuelve el contenido para la parte del nody y hace
    * todo el control del log. El trabajo de crear el contenido se hace
    * en el método protegido: getBodyContent
    *
    * @access public
    * @return string contenido para el Body
    */
    function getBody()
    {
        $log =& $this->getLog();
        
        if (!$this->hasBody()) {
            $msg = PAFLogFormat::format(__FILE__,__LINE__,"No hay Body");
            if ($log->isErrorEnabled()) {
                $log->error($msg);
            }
            return PEAR::raiseError($msg);
        }
        // Si estamos en modo debug calculo el tiempo que tarda.
        if ($log->isDebugEnabled()) {
            $timeStart = PAFLogFormat::getMicroTime();
        }
        
        // Obtenemos el output realmente        
        $ret = $this->getHeadContent();
        if ($log->isErrorEnabled()) {
            if (PEAR::isError($ret)) {
                $log->error(PAFLogFormat::format(__FILE__, __LINE__,
                $ret->getMessage()));
            }
            return $ret;
        }
        
        // Si estamos en modo debug escribimos en log el tiempo que tardó
        if ($log->isDebugEnabled()) {
            $timeStop = PAFLogFormat::getMicroTime();
            $lapso = number_format($timeStop - $timeStart, 4);
            $log->debug(PAFLogFormat::format(__FILE__, __LINE__,
            "Tiempo en getBody ($lapso)"));
        }
        
        return $ret;
    }
    /**
    * Método para compatibilidad hacía atrás, normalmente no necesario
    * implementar.
    * @access public
    * @return string contenido del body.
    */
    function getOutput()
    {
        $this->getBody();
    }
    
    /**
    * Método estático para recuperar el identificador de la clase.
    *
    * @access public
    * @return int Identificador único de clase.
    */
    function getClassType()
    {
        return CLASS_PAFHTMLOUTPUT;
    }
    
    /**
    * Método estático que retorna el nombre de la clase.
    *
    * @access public
    * @return string Nombre de la clase.
    */
    function getClassName()
    {
        return "PAFHtmlOutput";
    }
    
    /**
    * Método de consulta para determinar si una clase es de un tipo 
    *  determinado. 
    *  Reimplementado de PAFObject.
    *
    * @access public
    * @param int $tipo Número entero con el Código de clase por el que 
    *  queremos preguntar .
    * @return boolean
    */
    function isTypeOf($tipo)
    {
        return ((PAFHtmlOutput::getClassType() == $tipo) || 
        PAFOutput::isTypeOf($tipo));
    }
    
    /**
    * Método que devuelve el objeto log
    * @access protected
    * @return Logger
    */
    function &getLog()
    {
        return (LoggerManager::getLogger($this->getLogName()));
    }
}

?>
