<?php

// *****************************************************************************
// Lenguaje: PHP
// Copyright 2002 Prisacom S.A.
//
// $Id: PAFHeader.php,v 1.21 2008/02/11 14:28:35 amolina Exp $
// *****************************************************************************

require_once "PAF/PAFObject.php";

/**
* Clase para el establecimiento y envío de Headers en un PAFPage.
* Si se especifica un tiempo de caché por medio del método setCacheTime se enviará el Header
* correspondiente a dicho tiempo de cache. Adicionalmente se pueden incluir dentro del array
* $headers tantos como se deseen enviar, si bien esto ya tienen que ir en un formato adecuado y
* añadirlo a dicho array por medio del método setHeader(). Al realizar un sendHeaders() se enviará
* el correspondiente al tiempo de cache y los que contenga el array.
*
* @author Gustavo Núñez <gnunez@prisacom.com>,Sergio Cruz <scruz@prisacom.com>
* @version $Revision: 1.21 $
* @access public
* @package PAF
*/
class PAFHeaderTest extends PAFObject
{
    /**
    * Contiene el valor de la caché de página que se enviará por medio
    * de este Header. Este valor suele venir determinado por el Anchor de una determinada
    * página.
    *
    * @access private
    * @var int
    */
    var $cacheTime= 120;

    /**
    * Contiene el valor de la caché de página que se enviará por medio
    * de este Header. Este valor suele venir determinado por el Anchor de una determinada
    * página.
    *
    * @access private
    * @var int
    */
    var $contentType= NULL;

    /**
    * Colección de headers a enviar
    *
    * @access private
    * @var string
    */
    var $headers= array();


	/**
	* Contiene el valor timestamp de la fecha de expiracion
	* @access private
	* @var integer
	**/
	var $expires = 0;

	var $initialDate;

    /**
    * Constructor.
    *
    * @access public
    */
    function PAFHeaderTest()
    {
        $this->PAFObject();
		$this->initialDate=time();
    }

    /**
    * Establece el valor de la caché para la página que enviará estos headers.
    *
    * @access public
    * @param int $value Valor en segundos de la caché de página.
    */
    function setCacheTime($value){

    //if (is_numeric($value))
           $this->cacheTime= $value;
    }

    /**
    * Establece el valor del Header a enviar.
    *
    * @access public
    * @param string $value
    */
    function setHeader($value)
    {
        $this->headers[]= $value;
    }

    /**
    * Esta funcion genera cabeceras de fecha de la pagina y determina
    * en funcion de las cabeceras recibidas si puede enviar un 304
    * ATENCION: Debe ser llamada despues de fijar cache
    *           Puede hacer un die;
    *
    * @access public
    */
    function setDate($time, $seconds_to_revalidate=NULL, $secur_check=true)
    {
		// DURANTE LOS 15 SEGUNDOS PRIMEROS, generamos de nuevo
		// no sea que el fichero no esté actualizado en el filer
		$current = $this->initialDate;
		$current = time();
		$time = (($time + 15)>$current?$current:($time + 15));


       	if ($cached_data = $_SERVER['HTTP_IF_MODIFIED_SINCE'])
	   	{
       		$cached_time = strtotime(ereg_replace(";.*","",$cached_data));
    		if (strstr($cached_data, "length=0")) $cached_time = 0;
		}	
		else $cached_time=0;

        if ($cached_time && $time <= $cached_time) {
            if (!$secur_check || ($current - $cached_time) < 3600) {
                if (!is_null($seconds_to_revalidate)) {
                    $this->setCacheTime($seconds_to_revalidate);
                }
                $this->sendNotModified();
                die;
            } else $time = $current;
        }
		else if ($secur_check && (time() - $time) > 3600) $time = time();

		$dw = gmdate("w", $time);
		$dw_txt = array('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat');
        $lastModified = $dw_txt[$dw].', '.gmdate("d M Y H:i:s", $time)." GMT";
        $this->setHeader("Last-Modified: $lastModified");
    }

    /**
     * Esta función añade la cebecera del content-typpe.
     */
    function setContentType($tipo)
    {
    $this->contentType=$tipo;
    }

    /**
    * Envía los headers de caché así como los que se hayan introducido en la colección
    * de headers adicional.
    *
    * @access public
    */
    function sendHeaders()
    {
        // Envía los headers definidos. Así si se hubiera definido un status, iría el primero

        $numHeaders= count($this->headers);
		$sentLastMod = false;
        for ($i= 0; $i < $numHeaders; $i++)
		{
        	if (substr($this->headers[$i], 0, 13) == "Last-Modified")
            $sentLastMod = true;

            Header ($this->headers[$i]);
		}

		 Header( 'Date: '.gmdate('D, d M Y H:i:s',time()).' GMT' );
        if (!$sentLastMod) {
            Header( "Last-Modified: ".gmdate("D, d M Y H:i:s", $this->initialDate)." GMT" );
        }

    	$this->sendCacheHeaders();
    	$this->sendContentType();

    }


    /**
    * Envía los headers de caché 
    *
    * @access public
    */
    function sendCacheHeaders()
    {
		if ($this->expires > time()) {
			Header ("Cache-Control: private");
			Header ('Expires: '.gmdate('D, d M Y H:i:s', $this->expires).' GMT');
		} 
		else
		{
	        // Envía el Header correspondiente al tiempo de caché.
    	    if ($this->cacheTime == 0)
        	{
            	Header('Cache-Control: max-age=0,private,must-revalidate');
	            Header('Edge-Control: max-age=0s,no-cache');
    	        Header('Pragma: no-cache');
        	    Header('Expires: Wed, 11 Nov 1998 11:11:11 GMT');
	        }
    	    else
        	{
    	       	Header( "Cache-Control: max-age=" . $this->cacheTime );
	            Header( "Edge-Control: max-age=". $this->cacheTime ."s" );
    	    }
		}
    }


    /**
    * Envía los headers de tipo 
    *
    * @access public
    */
    function sendContentType()
    {

        if ($this->contentType)
        Header('Content-Type: '.$this->contentType);
    }

    /**
    * Esta funcion intenta repetir página si algo falla
    * @access public
    */

    function repeatPage()
    {
        $this->sendNotModified();
    }


    /**
     * Hace una redirección a una página.
     *
     * @access public
     * @static
     * @param strin $url Url a la que le queremos mandar.
     */
    function redirect($url = false)
    {
        if($url === false) {
            $url = $_SERVER('REQUEST_URI');
            if(($pos = strpos($url, '?')) !== false) {
                $url = substr($url, 0, $pos);
            }
        }
        header('Location: '.$url);
        die();
    }

    // Metodos de ayuda, recordatorio de cabeceras.
    // 200
    function sendNoContent() 
    {
        header('HTTP/1.0 204 No Content');
        die();
    }

    // 300
    function sendMovedPermanently($location) 
    { 
        header("HTTP/1.0 301 Moved Permanently"); 
        PAFHeader::redirect($location);
    }
    function sendNotModified() 
    { 
        header("HTTP/1.0 304 Not Modified"); 
        PAFHeader::sendCacheHeaders();
        PAFHeader::sendContentType();
        die();
    }
    function sendTemporaryRedirect() 
    {
        header("HTTP/1.0 307 Temporary Redirect");
        die();
    }

    // 400
    function sendBadRequest() {
        header("HTTP/1.0: 400 Bad Request");
        die();
    }
    function sendUnauthorized() {
        header("HTTP/1.0: 401 Unauthorized");
        die();
    }
    function sendForbidden() {
        header("HTTP/1.0: 403 Forbidden");
        die();
    }
    function sendNotFound() {
        header("HTTP/1.0: 404 Not Found");
        die();
    }
    function sendMethodNotAllowed() {
        header("HTTP/1.0: 405 Method Not Allowed");
        die();
    }

    // 500
    function sendInternalServerError() {
        header("HTTP/1.0: 500 Internal Server Error");
        die();
    }
    function sendServiceUnavailable() {
        header("HTTP/1.0: 503 Service Unavailable");
        die();
    }


    /**
      * Esta funcion genera cabeceras de tamaño
      * @access public
      */

    function setLength($size)
    {
        $this->setHeader("Accept-Ranges: bytes");
        $this->setHeader("Content-Length: $size");
    }

	/***
	* Esta funcion setea el atributo expires
	* @ access public
	***/
	function setExpires($exp) {
		$this->expires = $exp;
	}

}

?>
