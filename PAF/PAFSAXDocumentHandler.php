<?php

  // *****************************************************************************
  // Lenguaje: PHP
  // Copyright 2002 Prisacom S.A.
  // ---------
  // ChangeLog
  // ---------
  // $Log: PAFSAXDocumentHandler.php,v $
  // Revision 1.3  2002/09/10 15:57:19  scruz
  // Eliminado echo en el método "endElement".
  //
  // Revision 1.2  2002/09/10 15:54:18  scruz
  // Añadido atributo currentTag así como los métodos get/set asociados.
  //
  // Revision 1.1  2002/05/23 08:28:13  sergio
  // Clase SAX Parser para XML.
  //
  // *****************************************************************************

require_once "PAF/PAFObject.php";

/**
  * @const CLASS_PAFSAXDocumentHandler Constante para el identificador único de clase.
  */

define ("CLASS_PAFSAXDOCUMENTHANDLER", 12);

/**
  * Clase virtual base para los manejadores de eventos de los parsers XML de tipo SAX.
  *
  * @author Sergio Cruz <scruz@prisacom.com>
  * @version $Revision: 1.3 $
  * @access public
  * @see PAFSAXParser
  * @package PAF
  */
class PAFSAXDocumentHandler extends PAFObject
{
   /**
      * Variable de control de etiqueta actual en el parsing del XML.
      * @access private
      * @var string
      */
    var $currentTag;

    /**
      * Constructor.
      *
      * @access public
      * @param object $errorClass Clase de error asociada con PAFSAXParser.
      */
    function PAFSAXDocumentHandler ($errorClass= null)
    {
        $this->PAFObject($errorClass);  // Llamada al constructor de la clase padre.
    }

    /**
      * Método virtual que controla cuando se encuentra el comienzo de un elemento.
      *
      * @abstract
      * @param string $name Nombre del Elemento.
      * @param array $attributes Array asociativo con los nombres (keys) de los atributos y su valor
      *        para el elemento actual.
      */
    function startElement($parser, $name, $attributes)
    {
        echo "Entrando en PAFSAXDOcumentHandler->startElement [$name].<br>";
        if ( count ($attributes) > 0 )
        {
            echo "<pre>";
                var_dump ($attributes);
            echo "</pre>";
        }
    }

    /**
      * Método virtual que controla cuando se encuentra el comienzo de un elemento.
      *
      * @abstract
      * @param string $name Nombre del Elemento.
      * @param array $attributes Array asociativo con los nombres (keys) de los atributos y su valor
      *        para el elemento actual.
      */
    function endElement($parser, $name)
    {
        //echo "Entrando en PAFSAXDOcumentHandler->endElement [$name].<br>";
    }

    /**
      * Método virtual para controlar el evento lanzado por los nodos tipo TEXT del XML.
      * @abstract
      * @param string $data Contenido del nodo texto que lanza el evento.
      */
    function characters ($parser, $data)
    {
        echo "Entrando en PAFSAXDocumentHandler->characters.<br>";
        echo "<pre>";
            var_dump ($data);
        echo "</pre>";
    }

    /**
      * Método estático para recuperar el identificador de la clase.
      *
      * @access public
      * @return int Identificador único de clase.
      */
    function getClassType()
    {
        return CLASS_PAFSAXDOCUMENTHANDLER;
    }

    /**
      * Método estático que retorna el nombre de la clase.
      *
      * @access public
      * @return string Nombre de la clase.
      */
    function getClassName()
    {
        return "PAFSAXDocumentHandler";
    }

    /**
      * Devuelve la etiqueta en la que se encuentra actualmente el parser XML
      *
      * @access public
      * @return string
      */
    function getCurrentTag()
    {
        return $this->currentTag;
    }

    /**
      * Método para dar valor a la etiqueta que se está procesando actualmente.
      * @access public
      * @param string $value Nombre de la Etiqueta que se está procesando.
      */
    function setCurrentTag($value)
    {
        $this->currentTag= $value;
    }

    /**
      * Método de consulta para determinar si una clase es de un tipo determinado.
      * Reimplementado de PAFObject.
      *
      * @access public
      * @param int $tipo Número entero con el Código de clase por el que queremos preguntar .
      * @return boolean
      */
    function isTypeOf ($tipo)
    {
        return  ( (PAFSAXDocumentHandler::getClassType() == $tipo) || PAFObject::isTypeOf($tipo) );
    }

}

?>
