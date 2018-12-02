<?php

  // *****************************************************************************
  // Lenguaje: PHP
  // Copyright 2002 Prisacom S.A.
  // ---------
  // ChangeLog
  // ---------
  // $Log: PAFSAXXMLQueryHandle.php,v $
  //
  // *****************************************************************************

require_once "PAF/PAFObject.php";
require_once "PAF/PAFSAXDocumentHandler.php";

/**
  * Clase que maneja XMLs que almacenan queries
  *
  * @author Francisco Alcaraz <fjalcaraz@prisacom.com>
  * @version $Revision: $
  * @access public
  * @see PAFSAXParser
  * @package PAF
  */
class PAFSAXXMLQueryHandler extends PAFSAXDocumentHandler
{
   /**
      * Variable de control de etiqueta actual en el parsing del XML.
      * @access private
      * @var string
      */
    var $current=array();
    var $data = array();
    var $mode_append = false;

    /**
      * Constructor.
      *
      * @access public
      */
    function PAFSAXXMLQueryHandler()
    {
        $this->PAFSAXDocumentHandler();  // Llamada al constructor de la clase padre.
    }

    /**
      * Método que controla cuando se encuentra el comienzo de un elemento.
      *
      * @abstract
      * @param string $name Nombre del Elemento.
      * @param array $attributes Array asociativo con los nombres (keys) de los atributos y su valor
      *        para el elemento actual.
      */
    function startElement($parser, $name, $attributes)
    {
	$this->setCurrentTag($name);
	$this->mode_append = false;
    }

    /**
      * Método que controla cuando se encuentra el comienzo de un elemento.
      *
      * @abstract
      * @param string $name Nombre del Elemento.
      * @param array $attributes Array asociativo con los nombres (keys) de los atributos y su valor
      *        para el elemento actual.
      */
    function endElement($parser, $name)
    {
    	if ($name== 'row')
	{
	   foreach ($this->current as $field => $value)
	      $this->current[$field] = substr($value, 0, -1);
	   $this->data[] = $this->current;
	   $this->current = array();
	}
    }

    /**
      * Método para controlar el evento lanzado por los nodos tipo TEXT del XML. Recogo los datos de cada campo
      * @abstract
      * @param string $data Contenido del nodo texto que lanza el evento.
      */
    function characters ($parser, $data)
    {
	$field = $this->getCurrentTag();

        if ($field != "rowset" && $field != "row")
	{
	   // Con mode append controlamos la concatenación de los distintos trozos por los CDATA
	   if ($this->mode_append)
	      $this->current[$field] .= $data;
	   else
	      $this->current[$field] = $data;

	   $this->mode_append = true;
	}
    }

    function fetchRow ($mode)
    {
        if ($mode == DB_FETCHMODE_ASSOC)
	{
	    $data = current($this->data);
	    if (!$data) return NULL;
	    next($this->data);
    	    return $data;
	}
	else return NULL;
    }

    function numRows()
    {
	return count($this->data);
    }
}

?>
