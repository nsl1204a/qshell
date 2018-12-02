<?php

  // *****************************************************************************
  // Lenguaje: PHP
  // Copyright 2002 Prisacom S.A.
  // ---------
  // ChangeLog
  // ---------
  // $Log: PAFRecordData.php,v $
  // Revision 1.5  2002/07/04 15:51:31  sergio
  // A�adido require de PAFObject que misteriosamente nos hab�amos comido.
  //
  // Revision 1.4  2002/05/07 09:36:16  sergio
  // Modificaciones en la documentaci�n.
  //
  // Revision 1.3  2002/04/30 09:46:49  sergio
  // Modificaciones en la documentaci�n de la clase.
  //
  // Revision 1.2  2002/04/29 16:11:35  sergio
  // Cambios en la documentaci�n.
  //
  // Revision 1.1.1.1  2002/04/22 15:01:42  sergio
  // Creaci�n de estructura y primera subida de las clases generales
  //
  // *****************************************************************************

require_once "PAF/PAFObject.php";

/**
  * @const CLASS_PAFRECORDDATA Constante con el identificador �nico de clase.
  */
define ("CLASS_PAFRECORDDATA", 7);

/**
  * Clase base para contener los datos de un registro obtenido de una fuente
  * de datos v�a Recordset.
  *
  * @author Sergio Cruz <scruz@prisacom.com>
  * @version $Revision: 1.5 $
  * @access public
  * @package PAF
  */
class PAFRecordData extends PAFObject
{
    /**
      * Hash que guarda los datos del registro. Se guardan como "keys" del hash los
      * nombres de los campos.
      *
      * @access private
      */
    var $data= null;

    /**
      * Constructor.
      *
      * @access public
      * @param array $value hash con los datos del registro obetenido v�a Recordset. Las keys de este
      *        hash deben ser los nombres de los campos del registro.
      */
    function PAFRecordData($value)
    {
        $this->PAFObject();
        $this->data= $value;
    }

    /**
      * M�todo est�tico para recuperar el identificador de la clase.
      *
      * @access public
      * @return int Identificador �nico de clase
      */
    function getClassType()
    {
        return CLASS_PAFRECORDDATA;
    }

    /**
      * M�todo est�tico que retorna el nombre de la clase.
      *
      * @access public
      * @return string Nombre de la clase.
      */
    function getClassName()
    {
        return "PAFRecordData";
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
        return ( (PAFRecordData::getClassType() == $tipo) || PAFObject::isTypeOf($tipo) );
    }

    /**
      * Devuelve los datos
      *
      * @access public
      * @return array Hash con los datos del registro.
      */
    function getData()
    {
        return $this->data;
    }

    /**
      * M�todo para realizar Debug de los datos.
      *
      * @access public
      */
    function debugData()
    {
        foreach ($this->data as $value)
            echo $value . "|";
        echo "<br>";
    }
}

?>
