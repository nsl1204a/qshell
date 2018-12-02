<?php

// *****************************************************************************
// Lenguaje: PHP
// Copyright 2002 Prisacom S.A.
// *****************************************************************************

require_once "PAF/PAFOutput.php";

define ("OJDFILE_EXTENSION", ".ojd");

/**
  * Retorna el tag ojd para cada página.
  *
  * @author Fabian Ramos <framos@prisacom.com>
  * @version $Revision: 1.5 $
  * @access public
  * @package PAF
  */
class PAFOJD extends PAFOutput {

    /**
      * Contiene el directorio donde se encuentran los ficheros .ojd
      * @var string
      */
    var $dirBanners= "";

    /**
      * Contiene la parte principal del nombre de tag, esto es, la pagina
      * @var string
      */
    var $page= "";

    /**
      * Constructor.
      *
      * @access public
      * @param string $dirBanners Directorio donde se encuentran los ficheros.
      * @param string $page Parte principal del nombre del fichero .ojd 
      *     (sin _<posicion>).
      */

    function PAFOJD  ($dirBanners,$page)
    {
        $this->PAFOutput();

        $this->dirBanners= $dirBanners;
        $this->page= $page;
    }

    /**
      * Devuelve el directorio del que se recuperarán los ficheros.
      *
      * @access public
      * @return string.
      */
    function getDirBanners() {

        return $this->dirBanners;
    }

    /**
      * Devuelve el nombre de la página en la que se mostrará el tag ojd.
      * @access public
      * @return string
      */
    function getPage() {

        return $this->page;
    }

    /**
      * Establece el directorio del que se recuperarán los ficheros.
      *
      * @access public
      * @param string $value Nombre del Directorio completo.
      */
    function setDirBanners($value) {

        $this->dirBanners= $value;
    }

    /**
      * Establece el nombre de la página en la que se mostrará el tag ojd.
      *
      * @access public
      * @param string $value
      */
    function setPage($value) {

        $this->page= $value;
    }

    /**
      * Método que proporciona el código HTML del tag OJD.
      * @access public
      * @return string.
      */
    function getOutput() {

        $nameFileOJD = $this->getNameFileOJD();


    	if (file_exists ("/SESIONES/banners/noojd.flag")) 
	    return "<!-- >>> TAG OJD DESACTIVADO <<< -->";
        if ( is_file ($nameFileOJD) ) {

	    $size= filesize ($nameFileOJD);
	    if ($size > 0)
	    {
               $fd = fopen ($nameFileOJD, "r");
               $tagOJD = fread ($fd, $size);
               fclose ($fd);


	       srand((double)microtime()*1000000);
	       $tagOJD = str_replace("%r", rand(0, 100000), $tagOJD);

	       $url="http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	       $url = str_replace("&claveIn=Tr0T@M", "", $url);
	       $url = str_replace("?claveIn=Tr0T@M&", "?", $url);
	       $url = str_replace("?claveIn=Tr0T@M", "", $url);
	       $url = str_replace(".gen", ".html", $url);
	       $tagOJD = str_replace("%REF%", urlencode($url), $tagOJD);
	    }
	    else $tagOJD="";

	if (0)
	//JRM if (strstr($_SERVER['HTTP_HOST'], "www-org.as.com")|| strstr($_SERVER['HTTP_HOST'], "www.as.com"))
	{
        	if ( is_file ("/DATA/private/diarioas/misc/publicidad/SiteCensus.txt" ) ) 
		{

		    $size= filesize ("/DATA/private/diarioas/misc/publicidad/SiteCensus.txt");
		    if ($size > 0)
		    {
		       $fd = fopen ("/DATA/private/diarioas/misc/publicidad/SiteCensus.txt", "r");
		       $tagOJD .= fread ($fd, $size);
		       fclose ($fd);
		    }
		}
	}
            return $tagOJD;
        }
        else return "<!-- >>> TAG OJD NO DEFINIDO. PAGINA:".$this->page." <<< -->";
    }

    /**
      * Devuelve el nombre completo (path incluído) del tag ojd.
      *
      * @access private
      * @return string
      */
    function getNameFileOJD () {

        return $this->dirBanners."/".$this->page.OJDFILE_EXTENSION;
    }
}

?>
