<?php

// *****************************************************************************
// Lenguaje: PHP
// Copyright 2002 Prisacom S.A.
// ---------
// ChangeLog
// ---------
// $Log: PAFWriter.php,v $
// Revision 1.5  2005/03/04 16:44:34  fjalcaraz
// *** empty log message ***
//
// Revision 1.4  2005/03/04 16:40:05  fjalcaraz
// Importar PAFObject que no lo hacia
//
// Revision 1.3  2003/10/24 15:22:42  vsanz
// Primera versión funcional
//
// Revision 1.2  2003/10/22 16:40:43  vsanz
// Subida de seguridad. Compila pero no está probado.
//
// Revision 1.1  2002/08/13 15:19:55  vsanz
// Primera versi�n
//
//
// *****************************************************************************

require_once "PAF/PAFObject.php";

/**
 * @const CLASS_PAFWRITER Constante con el identificador de clase 
 * para PAFWriter
 */
define ("CLASS_PAFWRITER", 14);

/**
 * Clase base de la jerarquía de objetos PAFWriter.
 * Define la interfaz a implementar para crear objetos que salven datos a 
 * un DataSource.
 *
 * @author Virgilio Sanz <vsanz@prisacom.com>
 * @version $Revision: 1.5 $
 * @package PAF
 */
class PAFWriter extends PAFObject
{
    /**
     * DataSource que se usara para la escritura.
     *
     * @access private
     * @var object Objeto DataSource donde se har� la escritura
     */
    var $ds = null;
    
    /**
     * Constructor de la clase PAFWriter.
     *
     * @access public
     * @param  string $errorClass Nombre de la clase de Error utilizada para el lanzamiento de errores.
     */
    function PAFWriter(&$ds, $errorClass= null)
    {
        $this->PAFObject($errorClass);
        $this->ds =& $ds;
    }
    /**
     * Devuelve el datasource.
     *
     * @access public
     * @return PAFDataSource $ds PAFDataSource a utilizar
     */
    function &getDataSource()
    {
        return $this->ds;
    }
    
    /**
     * Asigna el datasource.
     *
     * @access public
     * @param PAFDataSource $ds PAFDataSource a utilizar
     */
    function setDataSource(&$ds)
    {
        $this->ds =& $ds;
    }
    
    /**
     * Método estático para recuperar el identificador de la clase.
     *
     * @access public
     * @return int Código único de clase.
     */
    function getClassType()
    {
        return CLASS_PAFWRITER;
    }
    
    /**
     * Método estático que retorna el nombre de la clase.
     *
     * @access public
     * @return string Nombre de la clase.
     */
    function getClassName()
    {
        return "PAFWriter";
    }
    
    /**
     * Método de consulta para determinar si una clase es de un tipo determinado.
     * Reimplementado de PAFObject.
     *
     * @access public
     * @param int $tipo Número entero con el C�digo de clase por el que 
     *  queremos preguntar .
     * @return boolean
     */
    function isTypeOf ($tipo)
    {
        return  ( (PAFWriter::getClassType() == $tipo) || PAFObject::isTypeOf($tipo) );
    }
    
    
    /**
     * Método virtual puro ejecutar el salvado de los datos.
     * Este método debe ser sobreescrito en todas las clases que deriven de 
     * ella de forma obligatoria y debe contener las operaciones necesarias 
     * para que el objeto guarde los datos. 
     *
     * @access public
     * @return boolean TRUE si fu� bien, PEAR_Error en caso contrario
     */
    function save()
    {
        echo $this->getClassName() . " es una Clase virtual pura. Debe sobreescribir este m�todo para su utilizaci�n";
        die;
    }
}

?>
