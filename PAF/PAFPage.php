<?php

// *****************************************************************************
// Lenguaje: PHP
// Copyright 2002 Prisacom S.A.
// ---------
// ChangeLog
// ---------
// $Log: PAFPage.php,v $
// Revision 1.6  2003/01/20 12:35:19  scruz
// Asignaci�n de referencia al contenido del bloque en vez de copia.
//
// Revision 1.5  2003/01/15 15:30:47  scruz
// A�adido un reset del array de bloques despu�s de utilizar la funci�n array_keys().
//
// Revision 1.4  2003/01/15 11:15:07  scruz
// A�adido el atributo $tpl y modificaci�n del tipo de acceso a los atributos (todos protected).
//
// Revision 1.3  2003/01/14 12:09:29  scruz
// Modificaciones en la documentaci�n y correci�n ortogr�fica.
//
// Revision 1.2  2002/10/10 10:49:08  scruz
// El parametro "anchor" del constructor pasa a ser opcional.
//
// Revision 1.1  2002/07/17 09:45:00  sergio
// Primera versi?n
//
// *****************************************************************************

require_once "PAF/PAFOutput.php";
require_once "PAF/PAFAnchor.php";
require_once "PAF/PAFConfiguration.php";
require_once "PAF/PAFHeader.php";
require_once "PAF/PAFTemplate.php";

/**
  * Clase abstracta para la implementaci�n de p�ginas HTML.
  * Se debe derivar de esta clase para la implementaci�n de p�ginas particulares.
  * Una p�gina se compone de una serie de bloques definidos en su array miembro $blocks. Las
  * implementaciones particulares de PAFPage deber�n crear este array con sus bloques predeterminados.
  * El contenido de dicho array-hash ser� un conjunto de Outputs identificados por un id de Bloque de la
  * siguiente manera:
  *
  * $this->blocks= array (
  *                         "A" => Output1,
  *                         "B" => Output2,
  *                         ...
  *                      )
  *
  * La operaci�n consistente en fijar a un bloque un determinado Output (PAFOutput o derivado) se realizar�
  * haciendo uso del m�todo setBlock (<nombre_bloque>, <Output_deseado>) que proporciona esta clase al efecto.
  *
  * @author Sergio Cruz <scruz@prisacom.com>
  * @version $Revision: 1.6 $
  * @abstract
  * @package PAF
  */
class PAFPage extends PAFOutput
{
    /**
      * Colecci�n de bloques que tiene la p�gina
      *
      * @access protected
      * @var array
      */
    var $blocks= array();

    /**
      * �mbito en el que se mostrar� la p�gina.
      *
      * @access protected
      * @var object Se trata de un objeto PAFAnchor pasado en el constructor.
      */
    var $anchor= null;

    /**
      * Conjunto de variables y fuentes de datos necesarios para la ejecuci�n de la p�gina.
      *
      * @access protected
      * @var objeto Se trata de un objeto PAFConfiguration.
      */
    var $configuration= null;

    /**
      * Cadena con el resultado final de la p�gina.
      * @access protected
      * @var string
      */
    var $result= "";
    
    /**
      * Template utilizada para la definici�n de bloques de la p�gina.
      * @access protected
      * @var object Objeto de tipo PAFTemplate.
      */
    var $tpl= null;

    /**
      * Constructor.
      *
      * @param object $configuration Objeto de tipo PAFConfiguration que contendr� toda la informaci�n
      *        acerca de variables y fuentes de datos necesarios para la p�gina.
      * @param object $anchor Objeto de tipo PAFAnchor que define el �mbito en el que se muestra la p�gina
      * @access public
      */
    function PAFPage    (
                            &$configuration,
                            $anchor=null
                        )
    {
        $this->PAFOutput();

        $this->configuration=& $configuration;
        if ( !is_null ($anchor) )
            $this->anchor=& $anchor;
    }

    /**
      * Devuelve el objeto PAFConfiguration con los datos necesarios para la ejecuci�n de la p�gina.
      *
      * @access public
      * @return object PAFConfiguration.
      */
    function getConfiguration()
    {
        return $this->configuration;
    }

    /**
      * Devuelve el objeto PAFAnchor asociado a la p�gina.
      *
      * @access public
      * @return object PAFAnchor
      */
    function getAnchor()
    {
        return $this->anchor;
    }

    /**
      * Devuelve el array de Outputs que contiene la p�gina.
      *
      * @access public
      * @return array
      */
    function getBlocks()
    {
        return $this->blocks;
    }

    /**
      * M�todo est�tico que retorna el nombre de la clase.
      *
      * @access public
      * @return string Nombre de la clase.
      */
    function getClassName()
    {
        return "PAFPage";
    }

    /**
      * Funci�n para establecer qu� output ser� el que se muestre en el bloque de
      * la p�gina especificado.
      *
      * @access public
      * @param string $blockName Nombre del bloque dentro del array de bloques que tiene la p�gina.
      * @param object $output Objeto de tipo PAFObject o derivado que queremos asociar con el bloque anterior.
      *
      * @return objetc PEAR::Error si el bloque al que queremos darle contenido no se encuentra definido
      *         dentro de la colecci�n de bloques de la p�gina actual.
      */
    function setBlock ($blockName, &$output)
    {
        // Comprobamos que el array de bloques sea un array y que no est� vac�o.
        if ( is_array ($this->blocks) && count ($this->blocks)>0 )
        {
            // Comprobamos que el nombre del bloque existe.
            $keys= array_keys($this->blocks);
            reset ($this->blocks);
            if ( in_array ($blockName, $keys) )
                $this->blocks[$blockName]=& $output;
            else
            {
                $message= "??? ERROR !!! (";
                $message.= __FILE__ ."," . __LINE__ . ")=> El nombre de bloque que se desea rellenar no se encuentra definido en la colecci�n de bloques de la p�gina.<br>";
                $this= PEAR::raiseError ($message);
                return $this;
            }
        }
        else
        {
            $message= "??? ERROR !!! (";
            $message.= __FILE__ ."," . __LINE__ . ")=> La colecci�n de bloques no est� definida o est� vac�a.<br>";
            $this= PEAR::raiseError ($message);
            return $this;
        }
    }

    /**
      * M�todo que implementa la l�gica de salida para la p�gina y proprorciona la salida
      * f�sica. Se debe redefinir para implementar la l�gica de construcci�n para cada tipo de
      * p�gina que se desee implementar.
      *
      * @access public
      * @abstract
      */
    function getOutput()
    {
        $this->PAFOutput->getOutput();
    }

    /**
      * M�todo virtual puro para proporcionar un identificador �nico de cada objeto salida. Esto suele
      * ser utilizado para proporcionar el id necesario de una salida a la hora de cachearla. Normalmente este
      * identificador �nico ser� el resultado de aplicar una funci�n hash sobre el nombre de la clase derivada
      * m�s los datos que identifiquen un�vocamente al objeto salida que estemos tratando.
      *
      * @access public
      * @abstract
      */
    function getKey()
    {
        $this->PAFOutput->getKey();
    }
}

?>