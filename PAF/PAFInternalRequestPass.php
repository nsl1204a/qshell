<?php

    require_once "PAF/PAFObject.php";

  /**
  * @const CLAVE_IN Constante para identificar la clave que pasamos al dominio
  */

    define ("CLAVE_IN", "8fb32f");


    /**
      * Encapsula la petíción de paginas a un dominio
      *
      * @author Alfonso Gomáriz <agomariz@prisacom.com>
      * @access public
      * @package PAF
      */

    class PAFInternalRequestPass extends PAFObject
    {

    /**
      * Atributo que contiene el dominio
      *
      * @access private
      * @var string
      */
    var $host;

    /**
      * Atributo que contiene el puerto
      *
      * @access private
      * @var int
      */
    var $port;


        /**
          * Constructor
	  * @access public
          *
          * @param string $host Dominio al que conectamos
	  * @param int $port Puerto al que conectamos
          *  
          */
        function PAFInternalRequestPass($host, $port=80)
        {
            $this->PAFObject();
	    $this->host=$host;
	    $this->port=$port;
        }

        /**
          * Metodo de petición de página
	  * @access public
          *
          * @param string $page Página que solicitamos
          * @return el parseo de la página o un objeto de error
          */


	function getPage ($page){

		if (!ereg("http",$page)) $url.="http://";
		if (ereg("\?",$page))
			$url.=$this->host.":".$this->port."/".$page."&claveIn=".CLAVE_IN;
		else
			$url.=$this->host.":".$this->port."/".$page."?claveIn=".CLAVE_IN;

		$result = readfile($url);
	
		if ($result){
			$result  = implode("", $result);
		}else{
		 $this = PEAR::raiseError ("¡¡¡ ERROR !!! => El dominio no responde. " . $this->host . ":". $this->port);
            	 return $this;
		}


	    return $result;
	  
	}

    }
?>
