<?php

// *****************************************************************************
// Lenguaje: PHP
// Copyright 2002 Prisacom S.A.
// *****************************************************************************

require_once "PAF/PAFOutput.php";
require_once "PAF/PAFOJD.php";
require_once 'PAF/PAFApplication.php';

define ("BANNERFILE_EXTENSION", ".ban");
define ("PAFBANNER_VERBOSE", 1);
define ("DIR_BANNER_FLAGS", "/SESIONES/bannercontrol");

/**
  * Representa la salida f�sica de un Banner determinado.
  * Un banner (su c�digo a incorporar en la p�gina) viene definido dentro de
  * un fichero .ban que se encuentra situado en un directorio espec�fico para
  * cada web.
  * El nombre de un fichero de banner se compone de tres partes:
  * 1.- Nombre de la p�gina (proporcionado por el anchor correspondiente).
  * 2.- Posici�n del banner ("top", "left", "right", "left") separada de la
  *     secci�n anterior por un "_".
  * 3.- Extensi�n del fichero de banner (generalmente ".ban") definido en esta clase
  *
  * As� por ejemplo el nombre de fichero de un banner para una portada que se vaya
  * a mostrar en la parte superior tiene como nombre:
  *
  *         portada_top.ban
  *
  * @author Sergio Cruz <scruz@prisacom.com>
  * @version $Revision: 1.27 $
  * @access public
  * @package PAF
  */
class PAFBanner extends PAFOutput {

    /**
      * Contiene el directorio donde se encuentran los ficheros .ban con el
      * c�digo de cada banner.
      * @var string
      */
    var $dirBanners= "";

    /**
      * Contiene la parte principal del nombre de banner, esto es, sin la parte
      * del nombre que identifica la posici�n del banner (p.ej portada).
      * @var string
      */
    var $name= "";

    /**
      * Contiene la porci�n del nombre de fichero que identifica la posici�n del banner.
      * @var string
      */
    var $position="";

    /**
      * Objeto PAFTemplate que controla el aspecto con el que se muestra el banner,
      * @var object
      */
    var $tpl= null;

    /**
      * True para adjuntar el tag ojd al banner o false en caso contrario
      * @var boolean
      */
    var $ojd = 1;

    /**
      * True para adjuntar el tag ojd al banner o false en caso contrario
      * @var boolean
      */
    var $keywords = array();

    /**
      * Constructor.
      *
      * @access public
      * @param string $dirBanners Directorio donde se encuentran los ficheros .ban con el
      *        c�digo del Banner.
      * @param string $page Parte principal del nombre del fichero .ban (sin _<posicion>).
      * @param string $position Parte secundaria del nombre del fichero .ban que identifica
      *        la posici�n que ocupa el banner. Puede tomar los valores "top", "right",
      *        "left" o "bottom".
      * @param object $tpl Objeto template con el dise�o que queremos incorporar al banner ai acaso.
      */
    function PAFBanner  (
                            $dirBanners,
                            $page,
                            $position,
                            $tpl= null,
			    $ojd=1
                        )
    {
        $this->PAFOutput();

        $this->dirBanners= $dirBanners;
        $this->page= $page;
        $this->position= $position;
        $this->tpl= $tpl;
	$this->ojd = $ojd;
    }

    /**
      * Devuelve el directorio del que se recuperar�n los ficheros de banners.
      *
      * @access public
      * @return string.
      */
    function getDirBanners() {

        return $this->dirBanners;
    }

    /**
      * Devuelve el nombre de la p�gina en la que se mostrar� el banner.
      * @access public
      * @return string
      */
    function getPage() {

        return $this->page;
    }

    /**
      * Devuelve la posici�n del banner.
      *
      * @access public
      * @return string Con los posibles valores "top", "bottom", "right", "left".
      */
    function getPosition() {

        return $this->position;
    }

    /**
      * Devuelve el estado del tag ojd.
      *
      * @access public
      * @return boolean.
      */
    function getOjd() {

        return $this->ojd;
    }

    /**
      * Establece el directorio del que se recuperar�n los ficheros de banners.
      *
      * @access public
      * @param string $value Nombre del Directorio completo donde se buscar�n los banners.
      */
    function setDirBanners($value) {

        $this->dirBanners= $value;
    }

    /**
      * Establece el nombre de la p�gina en la que se mostrar� el banner. Este dato
      * forma parte del nombre de fichero del que se recupera el c�digo de script
      * propio del banner.
      *
      * @access public
      * @param string $value
      */
    function setPage($value) {

        $this->page= $value;
    }

    /**
      * Establece la posici�n del Banner.
      *
      * @access public
      * @param string $value Puede tomar los valores "top", "bottom", "right" o "left".
      */
    function setPosition($value) {

        $this->position= $value;
    }

    /**
      * Establece el tag ojd.
      *
      * @access public
      * @param boolean.
      */
    function setOjd($value) {

        $this->ojd = $value;
    }

    /**
      * Establecen las keywords
      *
      * @access public
      * @param array.
      */
    /*function setKeywords($keywords) {

        $this->keywords = $keywords;
    }*/

    /**
      * M�todo que proporciona el c�digo HTML del Banner especificado.
      * @access public
      * @return string C�digo del banner.
      */
    function getOutput() {

        /*
         * Si el navegador es Safari en sus versiones de iPhone o iPod Touch, devolvemos vac�o
         *
         * truiz - 20090108
         */
        $browser = $_SERVER['HTTP_USER_AGENT'];
        if(strstr($browser, "iPhone") || strstr($browser, "iPod")){
                return "";
        }
        $nameBanner= $this->getBannerName();

        // Descripcion del banner
        if (PAFBANNER_VERBOSE == 1)
	    {

            $preBanner = "<!-- BANNER_".strtoupper ($this->position)." - POSITION: ".$this->position." - PAGE: ".$this->page." -->";
            $suBanner = "<!-- /BANNER_".strtoupper ($this->position)." -->";
            $tagOJD   = "";
        }
        // Tag OJD
        if ($this->ojd && $this->position=="top") {
            $objtagOJD  = new PAFOJD($this->dirBanners,$this->page);
            $tagOJD     = $objtagOJD->getOutput();
        }
	    else $tagOJD = "";

		$host_info = explode('.', $_SERVER['HTTP_HOST']);
		$host = $host_info[count($host_info)-2];

		if (file_exists(DIR_BANNER_FLAGS."/$host.flg") || file_exists(DIR_BANNER_FLAGS."/all.flg"))
	    { 
	        $banner= "<!-- >>> BANNER_" . $this->position . " DESACTIVADO <<< -->";
        }
	    elseif ( is_file ($nameBanner) )
	    {

			$bannerOAS = @file_get_contents($nameBanner);
			if (is_null($bannerOAS)) $bannerOAS='';

			/*if (is_array($this->keywords) && !empty($this->keywords))
			{
				$keys = implode(',', $this->keywords);
				$bannerOAS=str_replace("'?';", "'?".$keys."';", $bannerOAS, 1);
			}*/

			$lomas_ranking = PAFApplication::_getStaticProperty('PAFApplication','lomas_ranking');
			$key_iptc = $lomas_ranking;
			$key_iptc_ = PAFApplication::_getStaticProperty('PAFApplication','keywords');

			if($key_iptc_)
				$key_iptc .= ',' . $key_iptc_;
				
			$iptc = PAFApplication::_getStaticProperty('PAFApplication','iptc');

            $cont = sizeof($iptc);
            for($i=0; $i<$cont;$i++){
                if(!$key_iptc)
                    $key_iptc = array_shift($iptc);
                else
                    $key_iptc.= ',' . array_shift($iptc);
            }

			if($key_iptc)
                $bannerOAS=str_replace("'?';", "'?search=" . $key_iptc . "';", $bannerOAS);

            if (!is_null ($this->tpl)) {

                $tpl = $this->tpl;
                $tpl->setVar ("BANNER", $bannerOAS);
                $banner = $tpl->parse ();
            }

            else $banner = $bannerOAS;
        }
        else $banner= "<!-- >>> BANNER_" . $this->position . " NO DEFINIDO <<< -->";

        return $tagOJD.$preBanner.$banner.$suBanner;
    }

    /**
      * Devuelve el nombre completo (path inclu�do) del banner actual.
      *
      * @access private
      * @return string
      */
    function getBannerName () {

        $auxName= $this->dirBanners . "/" . $this->page . "_" . $this->position . BANNERFILE_EXTENSION;
        return $auxName;
    }

    function isPubli($pageOAS) {
    
    	//Control posiciones x01...x99
	$pattern = "/x[0-9][0-9]/";
	if (! preg_match($pattern,$this->position)) 
		$pos=ucfirst($this->position);
	else 
		$pos=$this->position;
	//
	
        $i_aleatorio = rand(100000000,999999999);
        $s_url_oas   = "http://ads.prisacom.com/RealMedia/ads/adstream_jx.ads/".
                       $pageOAS.
                       "/1".
                       $i_aleatorio.
                       "@".
                       $pos;

        if (!$fp =fopen($s_url_oas,"r")) return false;

        while( !feof($fp)) {
           $s_aux   = fgets($fp);
           $s_resp .= $s_aux;
        }
        
        fclose($fp);

        if (empty($s_resp)) return false;

        if ( stristr($s_resp,"empty.gif")) {
            // No hay campa�a
            return false;
        }
        else {
            return true;
        }

    } 

}

?>