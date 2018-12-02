<?php

  // ****************************************************************************
  // Lenguaje: PHP
  // Copyright 2002 Prisacom S.A.
  // ---------
  // ChangeLog
  // ---------
  // $Log: PAFDataSource.php,v $
  // Revision 1.5  2002/08/01 15:15:16  sergio
  // A�adido paquete PAF a la documentaci�n.
  //
  // Revision 1.4  2002/07/29 09:26:55  gustavo
  // Eliminamos el espacio final del fichero.
  //
  // Revision 1.3  2002/05/08 10:01:47  sergio
  // Modificaci�n en el constructor para adminitir un par�metro m�s con el nombre
  // de la clase de error asociada a ella.
  //
  // Revision 1.2  2002/04/30 09:46:34  sergio
  // Modificaciones en la documentaci�n de la clase.
  //
  // Revision 1.1.1.1  2002/04/22 15:01:42  sergio
  // Creaci�n de estructura y primera subida de las clases generales
  //
  // *****************************************************************************

require_once "PAF/PAFObject.php";

/**
  * @const CLASS_PAFDATASOURCE Constante para el identificador �nico de clase.
  */

define ("CLASS_PAFDATASOURCE", 2);

/**
  * Clase  base de la jerarqu�a de clases que representa la abstracci�n
  * de acceso a fuentes de datos del Framework PAF. Define el interface p�blico
  * b�sico que deber�n implementar todas las clases que representen fuentes de
  * datos. Dicho interface coniste de los siguientes m�todos:
  *
  * - connect().
  * - disconnect().
  *
  * Todas las clases que deriven de esta deber�n sobreescribir estos m�todos de
  * interface para actuar correctamente dependiendo del modo de conexi�n que utilicen.
  *
  * @author Sergio Cruz <scruz@prisacom.com>
  * @version $Revision: 1.5 $
  * @abstract
  * @access public
  * @package PAF
  */
class PAFDataSource extends PAFObject
{
    /**
      * Estado de la conexi�n. TRUE si est� conectado o FALSE en caso contrario. Debe ser actualizado din�micamente en las implementaciones de los m�todos connect y disconnect.
      *
      * @access private
      * @var boolean
      */
    var $connectionStatus;

    /**
      * Constructor.
      * @access public
      * @param string $errorClass Nombre de la clase de error asociada a PAFDataSource.
      */
    function PAFDataSource($errorClass= null)
    {
        $this->PAFObject($errorClass);
        $this->connectionStatus= false;
    }

    /**
      * M�todo est�tico para recuperar el identificador de la clase.
      *
      * @access public
      * @return int Identificador �nico de clase
      */
    function getClassType()
    {
        return CLASS_PAFDATASOURCE;
    }

    /**
      * M�todo est�tico que Retorna el nombre de la clase.
      *
      * @access public
      * @return string Nombre de la clase.
      */
    function getClassName()
    {
        return "PAFDataSource";
    }

    /**
      * M�todo de consulta para determinar si una clase es de un tipo determinado.
      * Reimplementado de PAFObject.
      *
      * @access public
      * @param int $tipo N�mero entero con el C�digo de clase por el que queremos preguntar .
      * @return boolean TRUE si el objeto this es del tipo especifocado por par�metro o FALSE
      *          en caso contrario.
      */
    function isTypeOf ($tipo)
    {
        return ( (PAFDataSource::getClassType() == $tipo) || PAFObject::isTypeOf($tipo) );
    }

    /**
      * Devuelve el estado de la conexi�n
      *
      * @access public
      * @return boolean TRUE si se encuentra abierta la conexi�n con la fuente de datos
      *         y FALSE en caso contrario.
      */
    function getConnectionStatus()
    {
        return $this->connectionStatus;
    }

    /**
      * Fija el valor del estado de conexi�n a true o false dependiendo del valor del par�metro
      * pasado.
      *
      * @access public
      * @param boolean $value Indica el estado de la conexi�n (TRUE=> conectado; FALSE=> desconectado).
      *
      */
    function setConnectionStatus($value)
    {
        $this->connectionStatus= $value;
    }

    /**
      * M�todo de conexi�n a la fuente de datos.
      * Se debe implementar por todas las clases derivadas de esta ya que cada
      * fuente de datos realiza la conexi�n de una manera distinta.
      *
      * @access public
      * @abstract
      */
    function connect()
    {
        echo $this->getClassName() . " es una Clase virtual pura. Debe sobreescribir este m�todo para su utilizaci�n";
        die;   // o bien un return false (depende de lo makarras que nos pongamos).
    }

    /**
      * M�todo de desconexi�n a la fuente de datos.
      * Se debe sobreescribir por todas las clases derivadas de esta ya que cada
      * fuente de datos realiza la desconexi�n de forma distinta.
      *
      * @access public
      * @abstract
      */
    function disconnect()
    {
        echo $this->getClassName() . " es una Clase virtual pura. Debe sobreescribir este m�todo para su utilizaci�n";
        die;   // o bien un return false (depende de lo makarras que nos pongamos).
    }

    /**
      * Determina si la fuente de datos se encuentra conectada.
      * El estado de esta conexi�n debe ser actualizada en las diferentes
      * implementaciones que se hagan de los m�todos connect() y disconnect().
      * Este m�todo no debe sobreescribirse en las distintas especilizaciones
      * que se hagan de esta clase.
      *
      * @access public
      * @return boolean TRUE si la fuente de datos se encuentra conectada o FALSE en caso contrario.
      */
    function isConnected()
    {
        if ($this->getConnectionStatus())
            return true;
        else
            return false;
    }
}
?>