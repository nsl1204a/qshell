<?php

// *****************************************************************************
// Lenguaje: PHP
// Copyright 2002 Prisacom S.A.
// ---------
// ChangeLog
// ---------
// $Log: PAFDBWriter.php,v $
// Revision 1.3  2007/07/12 13:29:54  ljimenez
// incluido la clase de logmanager
//
// Revision 1.2  2004/06/16 15:48:53  ljimenez
// eliminados peque�os errores
//
// Revision 1.1  2003/10/22 16:40:43  vsanz
// Subida de seguridad. Compila pero no está probado.
//
//
// *****************************************************************************

require_once "PAF/PAFWriter.php";
require_once "log4php/LoggerManager.php";

/**
* @const CLASS_PAFDBWRITER Constante con el identificador de clase 
* para PAFDBWriter
*/

define ("CLASS_PAFDBWRITER", 18);

/**
* Clase base de la jerarquía de objetos PAFDBWriter.
* Define la interfaz a implementar para crear objetos que salven datos a 
* un DataSource.
*
* @todo Hacer el manejo de transacciones.... no veo muy claro como.
* @author Virgilio Sanz <vsanz@prisacom.com>
* @version $Revision: 1.3 $
* @package PAF
*/
class PAFDBWriter extends PAFWriter
{
    /**
    * Nombre del log a utilizar.
    *
    * @var string
    * @access private
    */
    var $logName = '';
    
    /**
    * Obtiene el último valro insertado en un campo auto_increment o sequence.
    * @var integer
    * @access private
    */
    var $lastInsertedId;
    /**
    * constructor
    *
    * @access public
    * @param object $ds PAFDBDataSource $ds Referencia a la fuente de
    *                            datos (PAFDataSource) para el Recordset.
    * @param object $logger Nombre del "appender" que se va a usar.
    */
    function PAFDBWriter(&$ds, $logName = 'default')
    {
        // Pasó todas las pruebas en la asignación del DS
        $res = PAFDBDataSource::CheckDS($ds);
        if (PEAR::isError($res)) {
            $msg = PAFLogFormat::format(__FILE__,__LINE__,$res->toString());
            if ($log->isErrorEnabled()) {
                $log->error($msg);
            }
            $this = $res;
            return;
        }
        
        // Iniciamos madre
        $res = $this->PAFWriter($ds);
        if (PEAR::isError($res)) {
            $this = $res;
            return;
        }
        
        // Asignamos el log y comprobamos éxito
        $this->setLogName($logName);
        
    }
    
    /**
    * Metodo virtual que debe ser sobrescrito. devuelve la query para 
    * este hacer el update o el insert en la base de datos.
    *
    * @access protected
    * @return string query
    */
    function getQuery()
    {
        $msg = PAFLogFormat::format(__FILE__, __LINE__,
        "getQuery es un metodo virtual que debe ser implementado");
        
        // obtenemos el log y escribimos.
        $log =& $this->getLog();
        if ($log->isErrorEnabled()) {
            $log->error($msg);
        }
        
        return PEAR::raiseError($msg);
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
    * Devuelve el nombre del log.
    *
    * @access public
    * @return string logName Nombre del log a utilizar
    */
    function getLogName()
    {
        return $this->logName;
    }
    
    /**
    * Método que encapsula la obtención del log.
    * 
    * @access public
    * @return Logger logger a utilizar dependiente de logName
    */ 
    function &getLog()
    {
        return LoggerManager::getLogger($this->logName);
    }
    
    /**
    * Método estático para recuperar el identificador de la clase.
    *
    * @access public
    * @return int Identificador único de clase.
    */
    function getClassType()
    {
        return CLASS_PAFDBWRITER;
    }
    
    /**
    * Método estático que retorna el nombre de la clase.
    *
    * @access public
    * @return string Nombre de la clase.
    */
    function getClassName()
    {
        return "PAFDBWriter";
    }
    
    /**
    * Método de consulta para determinar si una clase es de un tipo
    * determinado.
    * Reimplementado de PAFObject.
    *
    * @access public
    * @param int $tipo Número entero con el Código de clase por el que
    *  queremos preguntar .
    * @return boolean
    */
    function isTypeOf($tipo)
    {
        return (($tipo == PAFDBWriter::getClassType()) ||
        PAFWriter::isTypeOf($tipo));
    }
    
    /**
    * Método para ejecutar el salvado de los datos.
    * Este es el método que usaran las clases cliente para salvar los datos.
    * @access public
    * @return boolean TRUE si fué bien, PEAR_Error en caso contrario
    */
    function save()
    {
        // Obtenemos el query
        $query = $this->getQuery();
        if (PEAR::isError($query)) {
            if ($log->isErrorEnabled()) {
                $log->error(PAFLogFormat::format(__FILE__, __LINE__,
                $query->getMessage()));    
            }
            return $query;
        }
        
        // Obtenemos el log
        $log =& $this->getLog();
        
        // obetenemos el datasource
        $ds =& $this->getDataSource();
        $ret = PAFDBDataSource::checkDS($ds);
        if (PEAR::isError($ret)) {
            if ($log->isFatalEnabled()) {
                $log->fatal(LogFormat::format(__FILE__, __LINE__, 
                $ret->toString()));
            }
            return $ret;
        }
        
        // Si estamos en nivel debug, calculamos el tiempo ejecutando
        if ($log->isDebugEnabled()) {
            $timeStart = PAFLogFormat::getMicroTime();
        }
        
        // Hacemos una marca
        // $ds->checkpoint();
        
        // Ejecutamos la query
        $result = $ds->runQuery($query);
        
        // Escribimos en el log el error en caso que hubiera.
        if (PEAR::isError($result)) {
            if ($log->isFatalEnabled()) {
                $log->fatal(PAFLogFormat::format(__FILE__, __LINE__,
                $result->toString()));
            }
            
            if ($log->isDebugEnabled()) {
                $log->debug(PAFLogFormat::format(__FILE__, __LINE__,
                $result->getDebugInfo()));
            }
            
            // Intentamos hacer un rollback hasta el anterior checkpoint
            // $ds->rollback();
            
            // Devolvemos el error.
            return $result;
        }
        
        // Hacemos el commit
        // $ds->commit();
        
        // Obtenemos el último registro.
        $this->updateLastInsertedId($result);
        
        // Si estamos en modo debug escribimos en log el tiempo que tardó
        if ($log->isDebugEnabled()) {
            $timeStop = PAFLogFormat::getMicroTime();
            $lapso = number_format($timeStop - $timeStart, 4);
            $log->debug(PAFLogFormat::format(__FILE__, __LINE__,
            "Tiempo haciendo $nQueries update/inserts: $lapso"));
        }
        
        // Todo salió bien!!
        return true;
    }
    
    /**
    * Devuelve el último valor del campo auto_increment en la tabla en la 
    * que se hizo el último insert.
    *
    * @access public
    * @return integer Último valor insertado.
    * @notes Esta función no funciona.....
    */
    function getLastInsertedId()
    {
        return $this->lastInsertedId;
    }
    
    /**
    * Actualiza el valor de $this->lastInsertedId
    *
    * @access private
    * @param mixed $result valor devuelto por PEAR_DB después de un query.
    */
    function updateLastInsertedId(&$result)
    {
        // La versión actual de PEAR, no provee de una forma genérica para 
        // obtener esto, tendríamos que hacerlo específico por base de datos.
        // En MySQL son los campos auto_increment, en pgsql/Oracle se usa un
        // tipo de dato específico que se llaman secuencias y que van
        // independientes de la tabla
        //
        // MySQL: $this->lastID = @mysql_insert_id($this->connection)
        // PostgreSQL:  @pg_getlastoid($this->resultID);
        //
        $ds =& $this->getDataSource();
        $sqltype =& $ds->db->dbsyntax; // $sqltype = $this->ds->db->phptype;
        
        if ('mysql' == $sqltype) {
            $this->lastInsertedId = mysql_insert_id($ds->db->connection);
        } elseif ('pgsql' == $sqltype) {
            $this->lastInsertedId = pg_getlastoid($result);
        } else {
            $msg = PAFLogFormat::format(__FILE__, __LINE__,
            "DataBase not supported");
            if ($log->isErrorEnabled()) {
                $log->error($msg);
            }             
            $this->lastInsertedId = PEAR::raiseError($msg); 
        }
        
    }
}

?>
