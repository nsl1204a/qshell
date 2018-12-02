<?php

  // *****************************************************************************
  // Lenguaje: PHP
  // Copyright 2002 Prisacom S.A.
  // ---------
  // ChangeLog
  // ---------
  // $Log: PAFFileXMLQueryDataSource.php,v $
  //
  // *****************************************************************************

require_once "PAF/PAFFileDataSource.php";
require_once "PAF/PAFSAXXMLQueryHandler.php";
require_once "PAF/PAFSAXParser.php";

/**
  * Clase que encapsula la conexión a fuentes de datos de tipo XMLQuery
  * Los ficheros XMLQuery tienen tres niveles de tags: rowset, row y los campos devueltos por la query
  *
  * @author Francisco Alcaraz <fjalcaraz@prisacom.com>
  * @version $Revision: $
  * @access public
  * @package PAF
  */

if (!defined("XMLQUERY_CACHE_DIR"))
   define("XMLQUERY_CACHE_DIR", "/SESIONES/cacheFile/xmlQuery");

class PAFFileXMLQueryDataSource extends PAFFileDataSource
{

    /**
      * Constructor
      *
      * @param string $filename Nombre completo del fichero de datos.
      * @param string $openMode Modo de apertura del fichero.
      * @param string $errorClass Nombre de la clase de error asociada a PAFFileDataSource.
      * @access public
      *
      */
    function PAFFileXMLQueryDataSource   (
                                $errorClass= null
                            )
    {
        $this->PAFFileDataSource(NULL, NULL, $errorClass);  // Llamada al constructor de la clase padre.
    }

    function & runQuery($query)
    {
        $filename = XMLQUERY_CACHE_DIR."/".md5($query);

    	$this->setFileName($filename);
	$this->OpenSource();

	if ($this->isConnected())
	{
            $handler = new PAFSAXXMLQueryHandler();
	    $sax = new PAFSAXParser(&$handler);
	    $sax->setOption(XML_OPTION_CASE_FOLDING, false);
    
	    $stat = fstat($this->fileHandler);
	    $data = fread($this->fileHandler, $stat[size]);
	    $sax->parseString($data, $this->fileName);
	    $this->disconnect();

	    return $handler;

	}
	else return false;
    }

}
?>
