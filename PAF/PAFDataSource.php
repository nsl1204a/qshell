<?php

  // ****************************************************************************
  // Lenguaje: PHP
  // Copyright 2002 Prisacom S.A.
  // ---------
  // ChangeLog
  // ---------
  // $Log: PAFDataSource.php,v $
  // Revision 1.5  2002/08/01 15:15:16  sergio
  // Añadido paquete PAF a la documentación.
  //
  // Revision 1.4  2002/07/29 09:26:55  gustavo
  // Eliminamos el espacio final del fichero.
  //
  // Revision 1.3  2002/05/08 10:01:47  sergio
  // Modificación en el constructor para adminitir un parámetro más con el nombre
  // de la clase de error asociada a ella.
  //
  // Revision 1.2  2002/04/30 09:46:34  sergio
  // Modificaciones en la documentación de la clase.
  //
  // Revision 1.1.1.1  2002/04/22 15:01:42  sergio
  // Creación de estructura y primera subida de las clases generales
  //
  // *****************************************************************************

require_once "PAF/PAFObject.php";

/**
  * @const CLASS_PAFDATASOURCE Constante para el identificador único de clase.
  */

define ("CLASS_PAFDATASOURCE", 2);

/**
  * Clase  base de la jerarquía de clases que representa la abstracción
  * de acceso a fuentes de datos del Framework PAF. Define el interface público
  * básico que deberán implementar todas las clases que representen fuentes de
  * datos. Dicho interface coniste de los siguientes métodos:
  *
  * - connect().
  * - disconnect().
  *
  * Todas las clases que deriven de esta deberán sobreescribir estos métodos de
  * interface para actuar correctamente dependiendo del modo de conexión que utilicen.
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
      * Estado de la conexión. TRUE si está conectado o FALSE en caso contrario. Debe ser actualizado dinámicamente en las implementaciones de los métodos connect y disconnect.
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
      * Método estático para recuperar el identificador de la clase.
      *
      * @access public
      * @return int Identificador único de clase
      */
    function getClassType()
    {
        return CLASS_PAFDATASOURCE;
    }

    /**
      * Método estático que Retorna el nombre de la clase.
      *
      * @access public
      * @return string Nombre de la clase.
      */
    function getClassName()
    {
        return "PAFDataSource";
    }

    /**
      * Método de consulta para determinar si una clase es de un tipo determinado.
      * Reimplementado de PAFObject.
      *
      * @access public
      * @param int $tipo Número entero con el Código de clase por el que queremos preguntar .
      * @return boolean TRUE si el objeto this es del tipo especifocado por parámetro o FALSE
      *          en caso contrario.
      */
    function isTypeOf ($tipo)
    {
        return ( (PAFDataSource::getClassType() == $tipo) || PAFObject::isTypeOf($tipo) );
    }

    /**
      * Devuelve el estado de la conexión
      *
      * @access public
      * @return boolean TRUE si se encuentra abierta la conexión con la fuente de datos
      *         y FALSE en caso contrario.
      */
    function getConnectionStatus()
    {
        return $this->connectionStatus;
    }

    /**
      * Fija el valor del estado de conexión a true o false dependiendo del valor del parámetro
      * pasado.
      *
      * @access public
      * @param boolean $value Indica el estado de la conexión (TRUE=> conectado; FALSE=> desconectado).
      *
      */
    function setConnectionStatus($value)
    {
        $this->connectionStatus= $value;
    }

    /**
      * Método de conexión a la fuente de datos.
      * Se debe implementar por todas las clases derivadas de esta ya que cada
      * fuente de datos realiza la conexión de una manera distinta.
      *
      * @access public
      * @abstract
      */
    function connect()
    {
        echo $this->getClassName() . " es una Clase virtual pura. Debe sobreescribir este método para su utilización";
        die;   // o bien un return false (depende de lo makarras que nos pongamos).
    }

    /**
      * Método de desconexión a la fuente de datos.
      * Se debe sobreescribir por todas las clases derivadas de esta ya que cada
      * fuente de datos realiza la desconexión de forma distinta.
      *
      * @access public
      * @abstract
      */
    function disconnect()
    {
        echo $this->getClassName() . " es una Clase virtual pura. Debe sobreescribir este método para su utilización";
        die;   // o bien un return false (depende de lo makarras que nos pongamos).
    }

    /**
      * Determina si la fuente de datos se encuentra conectada.
      * El estado de esta conexión debe ser actualizada en las diferentes
      * implementaciones que se hagan de los métodos connect() y disconnect().
      * Este método no debe sobreescribirse en las distintas especilizaciones
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