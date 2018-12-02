<?php

require_once "PAF/PAFObject.php";
require_once "PAF/PAFHeader.php";

/**
  * Clase para la gestion del protocolo HTTP básico (envio de cabeceras)
  *
  * @author Francisco Alcaraz <fjalcaraz@prisacom.com>
  * @version $Revision: $
  * @access public
  * @package PAF
  */
class PAFHttpPage extends PAFObject
{
    /**
      * Contiene el valor de la caché de página
      *
      * @access private
      * @var int
      */
    var $cacheTime= 0;

    /**
      * Contiene la fecha de la página (timestamp)
      *
      * @access private
      * @var int
      */
    var $date=0;

    /**
      * Contenido de la página
      *
      * @access private
      * @var string
      */
    var $content='';

    function PAFHttpPage($cacheTime, $date=0, $regen_secs=0)
    {
        $this->hdrs = new PAFHeader();
        $this->cacheTime = $cacheTime;
	$this->setDate($date, $regen_secs);
    }

    function setCacheTime($cacheTime)
    {
        $this->cacheTime = $cacheTime;
    }

    function setDate($date, $regen_secs)
    {
	if ($date)
	{
	     // Fecha de la página = mayor multiplo de $regen_secs, el más cercano a la hora original.
	     // Con esto controlamos las regeneraciones
	     if ($regen_secs)
	     {
	        $this->date = $date + $regen_secs * intval((time() - $date) / $regen_secs);
	     }
	     // Si no hay regeneracion, fijamos la fecha pasada como la fecha de la página
	     else $this->date = $date;

	     // Fijamos fecha
             $this->hdrs->setDate($this->date, $this->$cacheTime);
	}
    }

    function setHeader($header)
    {
        $this->hdrs->setHeader($header);
    }

    function setContent($content, $date=0)
    {
        $this->content= $content;
	if (!$this->date)
	{
	    if (!$date) $date = time();
	    $this->date = $date;
	    $this->hdrs->setDate($this->date, $this->$cacheTime);
	}
    }

    function setContentFile($filename)
    {
        if ($fp = fopen($filename))
	{
	    $st = fstat($fp);
	    $this->content = fread($fp, $st["size"]);
	    $this->date = $st["mtime"];
	    fclose($fp);
	    $this->hdrs->setDate($this->date, $this->$cacheTime);
	}
    }

    function send()
    {
	$this->hdrs->setCacheTime($this->cacheTime);
	if (!PEAR::isError($this->content) && strlen($this->content)>0)
	{
	    $this->hdrs->setLength(strlen($this->content));
	    $this->hdrs->sendHeaders();
	    echo $this->content;
	}
	else
	{
	    $this->hdrs->repeatPage();
	    $this->hdrs->sendHeaders();
	}
    }
}


?>
