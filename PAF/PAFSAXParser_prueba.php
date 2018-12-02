<?php

  // *****************************************************************************
  // Lenguaje: PHP
  // Copyright 2002 Prisacom S.A.
  // *****************************************************************************

require_once "PAF/PAFObject.php";
require_once "PAF/PAFSAXDocumentHandler.php";

/**
  * @const CLASS_PAFSAXPARSER Constante para el identificador único de clase.
  */

define ("CLASS_PAFSAXPARSER", 11);

/**
  * @const ENCODING_ISO8859_1 Constante para el identificador de encoding ISO-8859-1
  */
define ("ENCODING_ISO8859_1", "ISO-8859-1");

/**
  * @const ENCODING_US_ASCII Constante para el identificador de encoding US-ASCII
  */
define ("ENCODING_US_ASCII", "US-ASCII");

/**
  * @const ENCODING_UTF_8 Constante para el identificador de encoding UTF-8
  */
define ("ENCODING_UTF_8", "UTF-8");

/**
  * Clase que implementa un Parser XML de tipo SAX. El uso de un parser SAX precisa de la creación
  * de un manejador de sucesos particular para cada documento que se quiera parsear. Para ello la clase
  * PAFSAXParser tiene un atributo denominado $documentHandler que es de tipo PAFSAXDocumentHandler.
  * El usuario deberá definir un objeto PAFSAXDocumentHandler específico para cada XML distinto que
  * quiera parsear. PAFSAXParser tiene un método llamado setDocumentHandler que permite cambiar para
  * el mismo objeto parser su manejador de sucesos. Una vez cambiado dicho manejador es necesario
  * volver a llamar al método parseFile o parseString.
  *
  * @author Sergio Cruz <scruz@prisacom.com>
  * @version $Revision: 1.11 $
  * @access public
  * @see PAFSAXDocumentHandler
  * @package PAF
  */
class PAFSAXParser_prueba extends PAFObject
{
    /**
      * Manejador de sucesos del parser.
      *
      * @var object
      */
    var $documentHandler= null;

    /**
      * Parser XML.
      * Se corresponde con el manejador de recurso asociado al parser XML.
      *
      * @var int
      */
    var $xmlParser= null;

    /**
      * Tipo de encoding utilizado por el parser.
      *
      * @var string
      */
    var $encoding= null;

    /**
      * Constructor.
      *
      * @access public
      * @param object $documentHandler Objeto de tipo PAFSAXDocumentHandler para el manejo
      *        de los eventos SAX.
      * @param $encoding Constante de cadena con el tipo de encoding a utilizar por el parser.
      * @param object $errorClass Clase de error asociada con PAFSAXParser.
      *
      * @return object Un nuevo Parser SAX listo para ser usado o un error en caso de que suceda
      *         algún problema en su creación.
      */
    function PAFSAXParser_prueba (&$documentHandler, $encoding= ENCODING_ISO8859_1, $errorClass= null)
    {
        $this->PAFObject($errorClass);  // Llamada al constructor de la clase padre.

        // Comprueba que el manejador de sucesos es un objeto de tipo PAFSAXDocumentHandler.
        if (! $this->checkDocumentHandler(&$documentHandler) )
        {
            $this= PEAR::raiseError ("¡¡¡ ERROR !!! [PAFSAXParser] => No se ha proporcionado un manejador de documento correcto.");
            return $this;
        }
        $this->encoding= $encoding;
        $this->documentHandler= &$documentHandler;

        // Crea el parser SAX.
        $retValue= xml_parser_create ($encoding);
        if ( !is_resource ($retValue) )
        {
            $this= PEAR::raiseError ("¡¡¡ ERROR !!! [PAFSAXParser] => No se ha podido construir el parser.");
            return $this;
        }
        $this->xmlParser= $retValue;
        // Para poder utilizar el parser desde este objeto.
        xml_set_object ($this->xmlParser, &$this);

        // Fijamos las opciones del manejador de sucesos para el parser SAX.
        // NOTA: Es posible que sea mejor idea separar la inicialización esta del handler
        //       en un método aparte.
        $ret= xml_set_element_handler ( $this->xmlParser, "startElement", "endElement");
        $ret= xml_set_character_data_handler ( $this->xmlParser, "characters");
    }
    
    /**
      * Destructor automático.
      *
      * @access public
      */
    function _PAFSAXParser()
    {
        xml_parser_free($this->xmlParser);
        unset ($this);
    }

    /**
      * Devuelve el objeto manejador de sucesos asociado al parser.
      *
      * @access public
      * @return object Objeto de tipo PAFSAXDocumentHandler o derivado asociado con el parser actual.
      */
    function &getDocumentHandler()
    {
        return $this->documentHandler;
    }

    /**
      * Método para cambiar el manejador de eventos para el parser.
      *
      * @access public
      * @param objet $newDocumentHandler
      * @return mixed TRUE si se ha conseguido cambiar el manejador de suscesos para el parser
      *         o un objeto de error en caso contrario.
      */
    function setDocumentHandler(&$newDocumentHandler)
    {
        if ( $this->checkDocumentHandler($newDocumentHandler) )
        {
            $this->documentHandler=& $newDocumentHandler;
            return true;
        }
        else
        {
            $errorMsg= "¡¡¡ ERROR !!! [PAFSAXParser] => El parámetro pasado al método setDocumentHandler no es un objeto PAFSAXDocumentHandler válido.";
            return PEAR::raiseError ($errorMsg);
        }
    }

    /**
      * Parsea el contenido de un fichero .
      *
      * @access public
      * @param string @fileName Nombre del fichero a parsear.
      * @return mixed Un error si el fichero no existe o no se puede abrir para lectura o TRUE
      *         si el fichero se ha parseado correctamente.
      */
    function parseFile($fileName)
    {
echo("<br>GUS parseFile");

        if ( !is_file ($fileName) )
            return PEAR::raiseError ("¡¡¡ ERROR !!! [PAFSAXParser] => $fileName no existe o no es un nombre de fichero válido.");

        $fd= fopen ($fileName,"r");
        if (!$fd)
           return PEAR::raiseError ("¡¡¡ ERROR !!! [PAFSAXParser] => $fileName no se puede abrir para lectura.");
        $contents = fread ($fd, filesize ($fileName));

        fclose ($fd);

        return $this->parseString($contents,$fileName);
    }

    /**
      * Parsea una cadena que contiene un XML.
      *
      * @access public
      * @param string $cadenaXML Cadena de texto XML.
      * @return mixed Un error si no se ha podido parsear la cadena XML o TRUE en caso de que
      *         todo vaya bien.
      */
    function parseString ($cadenaXML,$fileName="")
    {
echo("<br>GUS parseString");
      if (empty ($cadenaXML) )
	return PEAR::raiseError("¡¡¡ ERROR !!! [PAFSAXParser]=> cadena xml vacía");
echo("<br>GUS parseString2");
	
        if(strlen(trim($fileName))>0)
	{
		$isFinal = true;
	}else{
		$isFinal = false;
	}
	// $isFinal se usa para indicarle al parser que enviamos el Ãltimo trozo para analizar. Si no se pasa provoca fallos al parsear varios XMLs con el mismo objeto.
echo("<br>GUS xml_parse1");
echo("<br>GUS xml_parse1 isFinal = ".$isFinal);
//echo("<br>GUS xml_parse1 cadenaXML = ".$cadenaXML);
echo("<br>GUS xml_parse1 xmlParser = ".$this->xmlParser);
	$retValue= xml_parse($this->xmlParser, $cadenaXML,$isFinal);
echo("<br>GUS xml_parse");
echo("<br>GUS <pre> xml_parse");
var_dump($retValue);
        
	if ( !$retValue )
        {
            $errorCode= xml_get_error_code ($this->xmlParser);
            $errorString= xml_error_string ($errorCode);
            $errorLine= xml_get_current_line_number ($this->xmlParser);
            $errorColumn= xml_get_current_column_number ($this->xmlParser);
            return PEAR::raiseError ("¡¡¡ ERROR !!! [PAFSAXParser] => $errorString (Línea=> $errorLine, columna=> $errorColumn).<br> $fileName");
        }
    }

    /**
      * Método de redirección al método que controla el inicio de un Element
      * dentro del manejador de documento asociado.
      *
      * @access private
      * @param int $parser ID del parser XML.
      * @param string $name Nombre del Element.
      * @param array $attributes Hash con los nombres y valores de los atributos del Element.
      */
    function startElement ($parser, $name, $attributes)
    {
        $handlerXML=& $this->getDocumentHandler();
        return $handlerXML->startElement($this, $name, $attributes);
    }

    /**
      * Método de redirección al método que controla el final de un Element
      * dentro del manejador de documento asociado.
      *
      * @access private
      * @param int $parser ID del parser XML.
      * @param string $name Nombre del Element.
      */
    function endElement ($parser, $name)
    {
        $handlerXML=& $this->getDocumentHandler();
        return $handlerXML->endElement($this, $name);
    }

    /**
      * Método de redirección al método que recupera nodos de texto dentro del manejador asociado.
      *
      * @access private
      * @param int $parser ID del parser XML.
      * @param string $data Parámetro donde se recuperan los datos de texto.
      */
    function characters ($parser, $data)
    {
        $handlerXML=& $this->getDocumentHandler();
        return $handlerXML->characters($this, $data);
    }

    /**
      * Método para fijar opciones en el parser. Consultar xml_parser_set_option
      *
      * @access private
      * @param int $option Opcion a modificar
      * @param bool value. Valor a dar a la propiedad
      */
    function setOption ($option, $value)
    {
        xml_parser_set_option($this->xmlParser, $option, $value);
    }

    /**
      * Método estático para recuperar el identificador de la clase.
      *
      * @access public
      * @return int Identificador único de clase.
      */
    function getClassType()
    {
        return CLASS_PAFSAXPARSER;
    }

    /**
      * Método estático que retorna el nombre de la clase.
      *
      * @access public
      * @return string Nombre de la clase.
      */
    function getClassName()
    {
        return "PAFSAXParser";
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
        return  ( (PAFSAXParser::getClassType() == $tipo) || PAFObject::isTypeOf($tipo) );
    }

    /**
      * Comprueba que el parámetro pasado es un objeto de tipo PAFSAXDocumentHandler válido.
      *
      * @access private
      * @param object $docHandler
      * @return boolean
      */
    function checkDocumentHandler(&$docHandler)
    {
        if ( is_object ($docHandler) &&
             method_exists ($docHandler, "isTypeOf") &&
             $docHandler->isTypeOf (CLASS_PAFSAXDOCUMENTHANDLER)
           )
            return true;
        else
            return false;
    }
}

?>
