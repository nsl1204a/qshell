<?php


// *****************************************************************************
// Lenguaje: PHP
// Copyright 2002 Prisacom S.A.
// ---------
// ChangeLog
// ---------
// $Log: PAFRecordSet.php,v $
// Revision 1.16  2003/10/24 15:22:42  vsanz
// Primera versión funcional
//
// Revision 1.15  2003/10/22 16:40:43  vsanz
// Subida de seguridad. Compila pero no está probado.
//
// Revision 1.14  2002/07/12 09:21:15  sergio
// Controlado el define del id de clase para evitar errores de re-declaraci�n.
//
// Revision 1.13  2002/06/19 11:43:46  sergio
// Eliminado m�todo getTotalRecords (innecesario).
//
// Revision 1.12  2002/06/19 11:40:45  sergio
// A�adido interface countAll() para la cuenta total de registros del recordset sin
// l�mites.
//
// Revision 1.11  2002/06/18 17:25:26  sergio
// Modificado el m�todo getCount() por getCountLimit() que es m�s descriptivo.
//
// Revision 1.10  2002/06/18 17:15:58  sergio
// Modificaciones en los atributos y m�todos de control para el paginado de
// Recordsets (from y count).
//
// Revision 1.9  2002/05/31 09:50:19  sergio
// Arreglado el tema de paso por referencia de la DataSource.
//
// Revision 1.8  2002/05/30 17:02:51  sergio
// Eliminado el atributo para guardar los nombres de los campos.
//
// Revision 1.7  2002/05/29 16:43:19  sergio
// A�adidos atributos y m�todos para almacenar los l�mites del Recordset.
//
// Revision 1.6  2002/05/22 09:06:12  sergio
// Eliminados sets en el constructor de datos miembros que ya no existen.
//
// Revision 1.5  2002/05/08 10:01:26  sergio
// Modificaci�n en el constructor para adminitir un par�metro m�s con el nombre
// de la clase de error asociada a ella.
//
// Revision 1.3  2002/04/30 09:46:56  sergio
// Modificaciones en la documentaci�n de la clase.
//
// Revision 1.2  2002/04/29 12:46:45  sergio
// A�adido atributo dataSource y m�todos get/set.
//
// Revision 1.1.1.1  2002/04/22 15:01:42  sergio
// Creaci�n de estructura y primera subida de las clases generales
//
// *****************************************************************************

require_once "PAF/PAFObject.php";
require_once "PAF/PAFRecordData.php";

/**
* @const CLASS_PAFRECORDSET Constante para el identificador �nico de clase.
*/
if (!defined ("CLASS_PAFRECORDSET"))
define ("CLASS_PAFRECORDSET", 3);

/**
* Clase que proporciona el interface a implementar en todos los Recordset.
* Un recordset es una abstracci�n de un filtro sobre una fuente de datos determinada.
* Esto es, representa el conjunto de registros que se recuperan tras una consulta
* con unas condiciones determinadas sobre una fuente de datos cualquiera.
*
* @author Sergio Cruz <scruz@prisacom.com>
* @version $Revision: 1.16 $
* @abstract
* @access public
* @package PAF
*/

class PAFRecordset extends PAFObject
{
    /**
    * Atributo de tipo mixto que almacena la referencia a la fuente de datos actual del Recordset.
    *
    * @access private
    * @var mixed
    */
    var $dataSource= null;
    
    /**
    * Atributo de tipo mixto para mantener los resultados de una consulta a una fuente de datos.
    *
    * @access private
    * @var mixed
    */
    var $result= null;
    
    /**
    * Almacena el valor origen para el caso de Recordsets limitados.
    * Desde d�nde queremos registros en el recordset.
    *
    * @access private
    * @var int
    */
    var $from= null;
    
    /**
    * Almacena el n�mero de registros a recuperar por el Recordset.
    *
    * @access private
    * @var int
    */
    var $count= null;
    
    /**
    * Contador total de registros del Recordset sin aplicar l�mites.
    * Este contador es �til para el caso de las paginaciones de resultados para controlar
    * cu�l es el n�mero total de registros que cumplen el filtro pero sin aplicar sus l�mites
    * ($from, $count).
    *
    * @var int
    */
    var $countAll= null;
    
    /**
    * Constructor.
    *
    * @access public
    * @param object PAFDataSource $ds Referencia a la fuente de datos (PAFDataSource) para el Recordset.
    * @param string $errorClass Nombre de la clase de error asociada a PAFRecordset.
    */
    function PAFRecordset(&$ds, $errorClass= null)
    {
        // Esta l�nea hace que la ejecuci�n del script termine cuando se haga la
        // manipulaci�n de un error lanzado desde ella.
        $this->PAFObject($errorClass);  // LLamada al constructor de la clase padre.
//        $this->setErrorHandling(PEAR_ERROR_DIE); // Esto a lo mejor hay que cambiarlo pq es muy restrictivo.
        $this->setDataSource($ds);
    }
    
    /**
    * M�todo est�tico para recuperar el identificador de la clase.
    *
    * @access public
    * @return int Identificador �nico de clase.
    */
    function getClassType()
    {
        return CLASS_PAFRECORDSET;
    }
    
    /**
    * M�todo est�tico que retorna el nombre de la clase.
    *
    * @access public
    * @return string Nombre de la clase.
    */
    function getClassName()
    {
        return "PAFRecordset";
    }
    
    /**
    * Recupera el n�mero de registros m�ximo que recuperar� el recordsest
    * en su ejecuci�n.
    *
    * @access public
    * @return int
    */
    function getCountLimit()
    {
        return $this->count;
    }
    
    /**
    * Recupera el l�mite inferior desde el cual el recordset recuperar� registros.
    *
    * @access public
    * @return int
    */
    function getFromLimit()
    {
        return $this->from;
    }
    
    /**
    * Devuelve el objeto que contiene los resultados de la consulta.
    *
    * @access public
    * @return mixed Resultado del Resultset.
    */
    function getResult()
    {
        return $this->result;
    }
    
    /**
    * Devuelve la fuente de datos a la que se conecta el Recordset.
    *
    * @access public
    * @return object PAFDataSource
    */
    function getDataSource()
    {
        return $this->dataSource;
    }
    
    /**
    * M�todo de consulta para determinar si una clase es de un tipo determinado.
    * Reimplementado de PAFObject.
    *
    * @access public
    * @param int $tipo N�mero entero con el C�digo de clase por el que queremos preguntar .
    * @return boolean
    */
    function isTypeOf ($tipo)
    {
        return  ( (PAFRecordset::getClassType() == $tipo) || PAFObject::isTypeOf($tipo) );
    }
    
    /**
    * Fija la fuente de datos a la que se conecta el Recordset.
    *
    * @access public
    * @param object PAFDataSource $ds Fuente de datos.
    */
    function setDataSource(&$ds)
    {
        // To Do: Controlar que el objeto que se pasa es de tipo PAFDataSource.
        $this->dataSource =& $ds;
    }
    
    /**
    * Fija los l�mites entre los cuales recuperaremos registros dentro del Recordset.
    *
    * @access public
    * @param int $valueFrom L�mite desde el cual queremos recuperar registros del Recordset.
    * @param int $valueCount N�mero m�ximo de registros a recuperar.
    */
    function setLimits($valueFrom, $valueCount)
    {
        $this->from= $valueFrom;
        $this->count= $valueCount;
    }
    
    /**
    * Proporciona el siguiente registro del Recordset.
    * Debe ser reescrita en las clases derivadas de �sta y devolver un objeto de tipo PAFRecordData
    *
    * @access public.
    * @return mixed Un error por ser clase virtual pura.
    */
    function next()
    {
        $error= PEAR::raiseError (
        $this->getClassName() . " es una clase Virtual pura. Debes sobreescribir el m�todo next()<br>" .
        "(Fichero=> " . __FILE__ .", L�nea=> " .  __LINE__ . ")" .
        "<br>");
        return $error;
    }
    
    /**
    * Proporciona el n�mero de registros del Recordset.
    * Debe ser reescrita en las clases derivadas de �sta
    *
    * @access public.
    * @return mixed Un error por ser clase virtual pura.
    */
    function count()
    {
        $error= PEAR::raiseError (
        $this->getClassName() . " es una clase Virtual pura. Debes sobreescribir el m�todo count()<br>" .
        "(Fichero=> " . __FILE__ .", L�nea=> " .  __LINE__ . ")" .
        "<br>");
        
        return $error;
    }
    
    /**
    * Proporciona el n�mero de registros Total de la consulta sin aplicar
    * l�mites.
    * Debe ser reescrita en las clases derivadas de �sta si se desea saber cu�l es
    * n�mero total de resultados que proporciona un recordset cuando a este se le
    * han aplicado l�mites ($from, $count).
    *
    * @access public.
    * @return mixed Un error por ser clase virtual pura.
    */
    function countAll()
    {
        $error= PEAR::raiseError (
        $this->getClassName() . " es una clase Virtual pura. Debes sobreescribir el m�todo countAll()<br>" .
        "(Fichero=> " . __FILE__ .", L�nea=> " .  __LINE__ . ")" .
        "<br>");
        
        return $error;
    }
    
    /**
    * M�todo de ejecuci�n de la consulta sobre la fuente de datos.
    * Debe ser reescrita en las clases derivadas de �sta.
    *
    * @access public.
    * @return mixed Un error por ser clase virtual pura.
    */
    function exec()
    {
        $error= PEAR::raiseError (
        $this->getClassName() . " es una clase Virtual pura. Debes sobreescribir el m�todo exec()<br>" .
        "(Fichero=> " . __FILE__ .", L�nea=> " .  __LINE__ . ")" .
        "<br>");
        
        return $error;
    }
    
    /**
    * M�todo privado para la comprobaci�n de los l�mites de recuperaci�n de
    * registros dentro de un Recordset.
    *
    * @access public
    * @return boolean
    */
    function checkLimits()
    {
        if ($this->isSetFromLimit() && $this->isSetCount())
        return true;
        else
        return false;
    }
    
    /**
    * Comprueba si el l�mite desde el cual queremos recuperar registros de un Recordset
    * se encuentra fijado o no
    *
    * @access private
    * @return boolean
    */
    function isSetFromLimit()
    {
        if ( !is_null ($this->from) )
        return true;
        else
        return false;
    }
    
    /**
    * Comprueba si el l�mite hasta el cual queremos recuperar registros de un Recordset
    * se encuentra fijado o no
    *
    * @access private
    * @return boolean
    */
    function isSetCount()
    {
        if ( !is_null ($this->count) )
        return true;
        else
        return false;
    }
}

?>
