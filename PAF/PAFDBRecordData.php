<?php
// *****************************************************************************
// Lenguaje: PHP
// Copyright 2003 Prisacom S.A.
// ---------
// ChangeLog
// ---------
// $Log: PAFDBRecordData.php,v $
// Revision 1.4  2003/10/23 14:02:03  vsanz
// Algunos arreglos
//
// Revision 1.3  2003/10/22 16:40:43  vsanz
// Subida de seguridad. Compila pero no está probado.
//
// Revision 1.2  2003/10/16 17:29:58  vsanz
// Primera subida que compila, todav�a no hay tests
//
// Revision 1.1  2003/10/10 13:13:05  vsanz
// Primera subida preliminar, ni siquiera compilan
//
//
// *****************************************************************************

//require_once 'log4php/LoggerManager.php';
require_once 'PAF/PAFRecordData.php';
require_once 'PAF/PAFDBDataSource.php';
//require_once 'PAF/PAFLogFormat.php';

define ("CLASS_PAFDBRECORDDATA", 16);

/**
* clase que encapsula un registro para el resultado de una query.
* Cada PAFDBRecordSet tiene que tener un PAFDBRecordData asociado que
* que encapsule el acceso a los datos. Sólo hay que implementar los
* setter y getter, así como las reglas de negocio asociadas al recorddata.
* También hay que implementar los métodos getUpdateSQL y getInsertSQL, para
* poder hacer updates e inserts a través del método updateDb()
*
* @author Virgilio Sanz <vsanz@prisacom.com>
* @ver $Revision: 1.4 $
* @package PAF
*
*/
class PAFDBRecordData extends PAFRecordData
{
    /**
    * Referencia al objeto PAFDataSource
    *
    * @var PAFDataSource
    * @access private
    */
    var $ds = null;
    
    /**
    * Nombre del log a utilizar.
    *
    * @var string
    * @access private
    */
    var $logName = '';
    
    /**
    * constructor
    *
    * @access public
    * @param object $ds PAFDBDataSource $ds Referencia a la fuente de
    *                            datos (PAFDataSource) para el Recordset.
    * @param object $logger Nombre del "appender" que se va a usar.
    */
    function PAFDBRecordData($row, &$ds, $logName = 'default')
    {
        // Iniciamos madre
        $this->PAFRecordData($row);
        
        // Asignamos el log y comprobamos éxito
        $this->setLogName($logName);
        
        // Pasó todas las pruebas en la asignación del DS
        $ret = $this->setDataSource($ds);
        if (PEAR::isError($ret)) {
            $this = $ret;
            return;
        }
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
        //return LoggerManager::getLogger($this->logName);
    }
    
    
    /**
    * Devuelve el datasource.
    *
    * @access public
    * @return PAFDBDataSource $ds PAFDBDataSource a utilizar
    */
    function &getDataSource()
    {
        return $this->ds;
    }
    
    /**
    * Asigna el datasource.
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
            //$msg = PAFLogFormat::format(__FILE__,__LINE__,$res->toString());
            //if ($log->isErrorEnabled()) {
            //    $log->error($msg);
            //}
            return $res;
        }
        
        $this->ds =& $ds;
        return true;
    }
    
    /**
    * Método estático para recuperar el identificador de la clase.
    *
    * @access public
    * @return int Identificador único de clase.
    */
    function getClassType()
    {
        return CLASS_PAFDBRECORDDATA;
    }
    
    /**
    * Método estático que retorna el nombre de la clase.
    *
    * @access public
    * @return string Nombre de la clase.
    */
    function getClassName()
    {
        return "PAFDBRecordData";
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
        return (($tipo == PAFDBRecordData::getClassType()) ||
        PAFRecordData::isTypeOf($tipo));
    }
}

?>
