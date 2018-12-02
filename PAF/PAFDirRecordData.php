<?php
require_once("PAF/PAFRecordData.php");

/**
 * PAFDirRecordData 
 * 
 * @uses PAFRecordData
 * @package 
 * @version $Id: $
 * @author $Author: $ 
 */
class PAFDirRecordData extends PAFRecordData 
{

    /**
     * __construct inicia el objeto con los datos de un fichero
     * 
     * @param mixed $value array obtenido con PAFDirRecordset
     * @access protected
     * @return void
     */
    function __construct (&$value)
    {
        parent::PAFRecordData($value);
    }

    /**
     * PAFDirRecordData  constructor para PHP4
     * 
     * @see __construct()
     */
    function PAFDirRecordData(&$values)
    {
        $this->__construct($values);
    }

    /**
     * getFilename obtiene el nombre del fichero 
     * 
     * @access public
     * @return void
     */
    function getFilename()
    {
        return $this->data['PEAR'];
    }

    /**
     * getStat obtiene datos detallado del fichero
     * 
     * @access public
     * @return array
     */
    function getStat()
    {
        return @stat($this->data['PEAR']);
    }

    /**
     * getContent obtiene el contenido completo del fichero 
     * 
     * @access public
     * @return mixed

     */
    function getContent()
    {
        return file_get_contents($this->data['PEAR']);
    }

    /**
     * putContent agrega contenido de $pContent al fichero
     * 
     * @param mixed $pContent 
     * @access public
     * @return void
     */
    function putContent($pContent)
    {
        return file_put_contents($this->data['PEAR'], $pContent);
    }

    /**
     * getSize obtiene el tama�o en bytes del fichero 
     * 
     * @access public
     * @return void
     */
    function getSize()
    {
        return filesize($this->data['PEAR']);
    }

    /**
     * getDirname obtiene el path del fichero
     * 
     * @access public
     * @return void
     */
    function getDirname()
    {
        return dirname($this->data['PEAR']);
    }

    /**
     * getBasename obtiene el nombre del fichero
     * 
     * @access public
     * @return void
     */
    function getBasename()
    {
        return basename($this->data['PEAR']);
    }

    /**
     * getExtension  obtiene la extension del fichero
     * 
     * @access public
     * @return void
     */
    function getExtension()
    {
        return pathinfo($this->data['PEAR'], PATHINFO_EXTENSION);
    }

}

