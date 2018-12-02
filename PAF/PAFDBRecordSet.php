<?php
// *****************************************************************************
// Lenguaje: PHP
// Copyright 2002 Prisacom S.A.
// ---------
// ChangeLog
// ---------
// $Log: PAFDBRecordSet.php,v $
// Revision 1.12  2009/03/17 16:16:08  ljimenez
// eliminado group by del countall
//
// Revision 1.11  2008/10/27 10:52:19  dvillamil
// a�adido reemplazo de tabulaciones en m�todo getCountQuery
//
// Revision 1.10  2007/09/28 09:01:24  ljimenez
// incluido log
//
// Revision 1.9  2007/07/06 12:47:54  ljimenez
// corregido el error del m�todo PAFDBRecordSet
//
// Revision 1.8  2007/07/06 12:33:23  ljimenez
// Corregido el error en el countAll
//
// Revision 1.7  2005/03/14 11:25:40  imanzanas
// El peque�o error de antes, pero sin sentencias de debug
//
// Revision 1.6  2005/03/14 10:45:46  imanzanas
// Solucionado peque�o error en getCountQuery().
//
// Revision 1.5  2003/10/24 15:22:42  vsanz
// Primera versión funcional
//
// Revision 1.4  2003/10/23 14:02:03  vsanz
// Algunos arreglos
//
// Revision 1.3  2003/10/22 16:40:43  vsanz
// Subida de seguridad. Compila pero no está probado.
//
// Revision 1.2  2003/10/16 17:29:58  vsanz
// Primera subida que compila, todavia no hay tests
//
// Revision 1.1  2003/10/10 13:13:05  vsanz
// Primera subida preliminar, ni siquiera compilan
//
// *****************************************************************************
require_once 'PAF/PAFRecordSet.php';
require_once 'PAF/PAFDBDataSource.php';
require_once 'PAF/PAFLogFormat.php';
require_once 'log4php/LoggerManager.php';

define('CLASS_PAFDBRECORDSET', 15);

/**
* clase que encapsula una consulta sobre un origen de datos conteniendo
* funcionalidad de log y benchmark para el tratamiento de errores
*
* Las clases hijas de esta clase sólo necesitan implementar los métodos:
* Setter/Getter de la propia clase, el getQuery (construye la query) y el
* getRD que construye el RecordData específico de este Recordset.
*
* @author Virgilio Sanz <vsanz@prisacom.com>
* @version $Revision: 1.12 $
* @package PAF
*
*/
class PAFDBRecordset extends PAFRecordset
{
    /**
    * Nombre del log a utilizar.
    *
    * @var string
    * @access private
    */
    var $logName;
    
    /**
    * Objeto con un link al resultado de una query.
    *
    * @var DB_Result
    * @see PEAR/DB.php
    */
    var $result = null;
    
    /**
    * constructor
    *
    * @access public
    * @param object $ds PAFDBDataSource $ds Referencia a la fuente de
    *                            datos (PAFDataSource) para el Recordset.
    * @param object $logger Nombre del "appender" que se va a usar.
    */
    function PAFDBRecordset(&$ds, $logName='default')
    {
        // Asignamos el log y comprobamos éxito
        $this->setLogName($logName);
        
        // Comprobamos el datasource que nos llega.
        $ret = PAFDBDataSource::CheckDS($ds);
        if (PEAR::isError($ret)) {
			$log =& $this->getLog();
            if ($log->isErrorEnabled()) {
                $msg = PAFLogFormat::format(__FILE__,__LINE__,
                        $ret->toString());
                $log->error($msg);
            }
            $this = $ret;
            return;
        }
        
        // Llamamos a la madre.
        $ret = $this->PAFRecordSet($ds);        
        if (PEAR::isError($ret)) {
            $this = $ret;
            return;
        }
        
    }
    
    /**
    * Metodo virtual que debe ser sobrescrito. devuelve la query para este
    * recordSet
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
    * Metodo virtual que debe ser sobrescrito. devuelve un RecordData
    * para este registro dado por $row
    *
    * @access protected
    * @param array $row Contiene los campos que devuelve la query
    * @return PAFBDBRecordData En realidad deberia devolver el tipo
    *  RecordData de la hija de PAFDBRecordSet, ya que ella es la que
    *  sabe que PAFDBRecordData ha de devolver.
    */
    function &getRD($row)
    {
        $msg = PAFLogFormat::format(__FILE__, __LINE__,
            "getRD es un metodo virtual que debe ser implementado");
        
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
    * Asigna el DataSource. Sobreescribimos el existente en PAFRecordSet 
    * para poder comprobar el ds que nos llega.
    *
    * @access public
    * @param PAFDBDataSource $ds PAFDBDataSource a utilizar
    * @return boolean true si va bien PEAR_Error si mal.
    */
    function setDataSource(&$ds)
    {
        // obtenemos el log
        $log =& $this->getLog();
        
        // Comprobamos que el datasource sea un objeto de este tipo
        $res = PAFDBDataSource::CheckDS($ds);
        if (PEAR::isError($res)) {
            if ($log->isErrorEnabled()) {
                $msg = PAFLogFormat::format(__FILE__,__LINE__,
                    $res->toString());
                $log->error($msg);
            }
            return $res;
        }
        
        $this->dataSource =& $ds;

        return true;
    }
    
    /**
    * Método de ejecución de la consulta sobre la fuente de datos.
    *
    * @access public.
    * @return mixed true si todo va bien, PEAR_Error en caso contrario.
    */
    function exec()
    {
		$log =& $this->getLog();

        // Obtenemos el query
        $query = $this->getQuery();
        if (PEAR::isError($query)) {
            if ($log->isErrorEnabled()) {
                $log->error(PAFLogFormat::format(__FILE__, 
                                                 __LINE__,
                                                 $query->getMessage()));    
            }
            return $query;
        }
        
        // hacemos el exec.
        $this->result =& $this->realExec($query, true);
        if (PEAR::isError($this->result)) {
            if ($log->isErrorEnabled()) {
                $log->error(PAFLogFormat::format(__FILE__, 
                                                 __LINE__,
                                                 $this->result->getMessage()));
            }
            
            return $this->result;
        }
        return true;
    }
    
    /**
    * Devuelve el siguiente registro del Resultset.
    *
    * @access public
    * @return Objeto PAFRecordData estándar para el tratamiento de los datos.
    */
    function &next()
    {
        $res = $this->checkResult();
        if (PEAR::isError($res)) {
            $log =& $this->getLog();
            if ($log->isFatalEnabled()) {
                $log->fatal(PAFLogFormat::format(__FILE__,
                                                 __LINE__,
                                                 $res->toString()));
            }
            return $res;
        }
        
        $row = $this->result->fetchRow(DB_FETCHMODE_ASSOC);
        return (is_null($row) ? false : $this->getRD($row));
    }
    
    /**
    * Devuelve el número de registros del Recordset, teniendo en cuenta los
    * límites de la query.
    *
    * @access public
    * @return int con el número de registros del Recordset.
    */
    function count()
    {
        $res = $this->checkResult();
        if (PEAR::isError($res)) {
            $log =& $this->getLog();
            if ($log->isFatalEnabled()) {
                $log->fatal(PAFLogFormat::format(__FILE__, __LINE__, 
                $res->toString()));
            }
            return $res;
        }
        
        return $this->result->numRows();
    }
    
    /**
    * Proporciona el número de registros Total de la consulta sin aplicar
    * límites.
    *
    * @access public.
    * @return mixed Un error por ser clase virtual pura.
    */
    function countAll()
    {
        // Obtenemos el query y lo ejecutamos
        $query = $this->getCountQuery();
        if (PEAR::isError($query)) {
            return $query;
        }
        
        $result =& $this->realExec($query, false);
        if (PEAR::isError($result)) {
            return $result;
        }
        
        // Obtenemos el resultado.
        $row = $result->fetchRow();
        if (PEAR::isError($row)) {
            $log =& $this->getLog();
            if ($log->isFatalEnabled()) {
                $log->fatal(PAFLogFormat::format(__FILE__, __LINE__,
                $row->toString()));
            }
            return $row;
        }
        
        // Parece que todo salió bien.
        return intval($row[0]);
    }
    
    
    /**
    * Método estático para recuperar el identificador de la clase.
    *
    * @access public
    * @return int Identificador �nico de clase.
    */
    function getClassType()
    {
        return CLASS_PAFDBRECORDSET;
    }
    
    /**
    * Método estático que retorna el nombre de la clase.
    *
    * @access public
    * @return string Nombre de la clase.
    */
    function getClassName()
    {
        return "PAFDBRecordSet";
    }
    
    /**
    * Método de consulta para determinar si una clase es de un tipo
    * determinado.
    *
    * Reimplementado de PAFObject.
    *
    * @access public
    * @param int $tipo Número entero con el Código de clase por el que
    *  queremos preguntar .
    * @return boolean
    */
    function isTypeOf($tipo)
    {
        return ((PAFDBRecordSet::getClassType() == $tipo) ||
        PAFRecordSet::isTypeOf($tipo));
    }
    
    
    /**
    * Hace un regexp del tipo select .* from (.*) where (.*)
    * select count(1) from [TABLES] [CLAUSULA WHERE]
    *
    * @access protected
    * @return integer n�mero de registros que devolver� la query.
    */
    function getCountQuery()
    {
		$query = $this->getQuery();
		//Sustituimos tabulaciones, y saltos de l�nea por espacios en blanco
		//para que funcione la expresi�n regular al buscar
        $query = str_replace("\n"," ",$query);
        $query = str_replace("\r"," ",$query);
        $query = str_replace("\t"," ",$query);
        PAFApplication::debug('Query Real: ' . $query, __FUNCTION__, __CLASS__);
        if (PEAR::isError($query)) {
            return PAFApplication::raiseError(__FILE__, __LINE__, $query);
        }

        // 1 -> tiene las tablas
        // 2 -> where + clausula where
        // 3 -> Clausula where
        // El ? es 0 o 1
        $regs = array();

        if (preg_match("/(select (.*?) from (.*?))(group by(.*))?(limit(.*))?$/i",$query,$regs)) {
            $fromPart = $regs[3];
        } else {
            $msg = "'$query' no es un sql select";
            PAFApplication::debug($msg, __FUNCTION__);
            return PAFApplication::raiseError(__FILE__, __LINE__, $msg);
        }
        return "select count(1) from $fromPart";
    }
    
    /**
    * Comprueba que this->result esté ok
    *
    * @access protected
    * @returns true si bien PEAR_Error si mal
    */
    function checkResult()
    {
        if (PEAR::isError($this->result)) {
            return $this->result;
        }
        
        if (is_null($this->result)) {
            return PEAR::raiseError('result es null');
        }
        
        return true;
    }
    
    /**
    * Ejecución de la consulta sobre la Fuente de datos, devuelve el 
    * resultado de tipo PEAR/DB_Result o PEAR_Error.
    *
    * @param boolean $checkLimits hace que se chequeen o no los límites de 
    * la query
    *
    * @param string $query Query que queremos ejecutar
    *
    * @access private
    *
    * @return PEAR/DB_Result con el resultado si todo fué bien o PEAR_Error
    * en caso contrario.
    */
    function &realExec($query, $checkLimits = true)
    {
        // Obtenemos el log
        $log =& $this->getLog();
        
        // obetenemos el datasource
        $ds =& $this->getDataSource();
        $ret = PAFDBDataSource::checkDS($ds);
        if (PEAR::isError($ret)) {
            if ($log->isFatalEnabled()) {
                $log->fatal(LogFormat::format(__FILE__, 
                                              __LINE__, 
                                              $ret->toString()));
            }
            return $ret;
        }
        
        // Si estamos en nivel debug, ponemos el tiempo de la query
        if ($log->isDebugEnabled()) {
            $timeStart = PAFLogFormat::getMicroTime();
        }
        
        // Fija los límites si los hubiera.
        if ($checkLimits && $this->checkLimits()) {
            $result = $ds->runQuery($query, 
                                    $this->getFromLimit(), 
                                    $this->getCountLimit());
        } else {
            $result= $ds->runQuery($query);
        }
        
        // Si estamos en modo debug escribimos en log el tiempo que tardó
        if ($log->isDebugEnabled()) {
            $timeStop = PAFLogFormat::getMicroTime();
            $lapso = number_format($timeStop - $timeStart, 4);
            $log->debug(PAFLogFormat::format(
                __FILE__, __LINE__, "Tiempo en QUERY ($lapso) '$query'"));
        }
        
        // Escribimos en el log el error en caso que hubiera.
        if (PEAR::isError($result)) {
            if ($log->isFatalEnabled()) {
                $log->fatal(PAFLogFormat::format(__FILE__, 
                                                 __LINE__,
                                                 $result->toString()));
            }
            
            if ($log->isDebugEnabled()) {
                $log->debug(PAFLogFormat::format(__FILE__, 
                                                 __LINE__,
                                                 $result->getDebugInfo()));
            }
            return $result;
        }
        
        // Todo salió bien!! devolvemos el resultado
        return $result;
    }
}

?>