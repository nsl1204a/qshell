<?php


// *****************************************************************************
// Lenguaje: PHP
// Copyright 2006 Prisacom S.A.
//
// *****************************************************************************

require_once "PAF/PAFCache.php";
require_once "PAF/PAFHttpEnv.php";
require_once "PAF/PAFLog.php";


define ("PREFIX_NEWS_FILE_CACHE", "TES");

/**
  * Clase para control de outputs cacheados.
  */
class PAFCacheOutput
{

	
	var $_update = false;
	var $log = false;
	var $_cache;
	var $_static_attrs = array();
    var $_sendToSGE = false;
    var $_preview = false;

	/**
	  * Constructor.
	  *
	  */
	function PAFCacheOutput () {

		$pathCache = $this->_getCachePath ();
		$this->_cache = new PAFCache (basename($pathCache), dirname($pathCache));
		if ($_GET['log']=='cinco')
			$this->log = new PAFLog ("CacheOutput", DIR_CACHE_OUTPUT_LOGS);

	}


	function _generateStaticObject () {

		global $tracecache;

        $fileCacheName = $this->_getKeyName();
        if (PEAR::isError($fileCacheName)) return PEAR::raiseError("ERROR: PAFCacheOutput. El KeyName es un PEAR::Error ==> ".$fileCacheName->getMessage(), 0, PEAR_ERROR_TRIGGER);
		$fullpath = DIR_CACHE_OUTPUT.get_class($this)."#".$this->_getKeyName().".ser";
		if ($tracecache) echo "<pre>Fichero ser: $fullpath</pre>";
		if (!@stat($fullpath))
		{
		 	if ($f = @fopen($fullpath,"w"))
		 	{
			  	if(@fwrite($f,serialize($this)))
				{
					@fclose($f);
				}
	 		}
			else
			{
				PEAR::raiseError("ERROR: PAFCacheOutput. No pudo escribir serializado en $fullpath", 0, PEAR_ERROR_TRIGGER);
				if ($tracecache) echo "<pre> ERROR: PAFCacheOutput. No se pudo escribir serializado en $fullpath</pre>\n";
			}
		}
	}

	function _unlinkStaticObject () {
		@unlink(DIR_CACHE_OUTPUT.get_class($this)."#".$this->_getKeyName().".ser");
	}

	/**
	  * Metodo que gestiona la cache de disco del output.
	  *
	  */
	function getOutput() {
		$fileCacheName = $this->_getKeyName ();
		$cacheTime=intval($this->_getCacheTime ());
		$pathFC = $this->_cache->getCacheNameFile($fileCacheName);
		$fileToCheck = $this->_getFileToCheck ();
		if ($fileToCheck) $fileToCheckTime = @filemtime($fileToCheck);
		$regenerar = false;
	  

		global $tracecache;
		if ($tracecache) echo "<pre>---------------\nCLASS ".strtoupper(get_class($this))."\n</pre>";

        if (file_exists("/DATA/conf/appworx/flagapp.inc"))
            $this->_update=true;
            
		if (!$this->_update)
		{
			if (!($st = @stat($pathFC)))
			{
				$this->_update = true;
			}
			else{
				if (!$cacheTime)  //EVENTO
				{
					if ($fileToCheck && ($st['mtime'] < $fileToCheckTime))
					{
						$regenerar=true;
					}
				}
				else
				{
					if ((time() - $st['mtime']) > $cacheTime)
					{
						$regenerar = true;
					}
	
					// Regeneracion directa por demasiado tiempo esperando
					//if ((time() - $st['mtime']) > 2*$cacheTime)
						//$this->_update = true;
				}
			}

			if (PAFHttpEnv::GET('regenerate') == true && !isset($this->_notRegenerate) )
			{
				$this->_update = true;
			}

			if (PAFHttpEnv::GET('sendtosge') == true && !isset($this->_notRegenerate) )
			{
				$regenerar = true;
			}

			if ($this->_sendToSGE == true && !isset($this->_notRegenerate) )
			{
				$regenerar = true;
			}

			if (PAFHttpEnv::GET('preview') == true && !isset($this->_notRegenerate) )
			{
				$this->_preview = true;
                $this->_update = true;
			}

		}

		if ($tracecache) echo "<pre>FORZADO = $this->_update \n</pre>";

		if ($this->_update)
		{
			$class = strtoupper(get_class($this));
			$stringLog = date ("d-m-Y H:m:s") . " CLASS: $class CACHE NAME: $fileCacheName";

			$stringLog .=  " CACHE TIME: $cacheTime";
			$contenido = $this->_getContent ();
			$this->_unlinkStaticObject ();
			if (!PEAR::isError ($contenido)) {

				// Limpio de blancos
				$contenido=preg_replace('/([ \t]*[\r\n]+[ \t]*)+/', "\n", $contenido);
				$contenido=preg_replace('/[ \t]+/', ' ', $contenido);

                if (!$this->_preview) // Si previsualizamos no se escribe el fichero de cache.
    				$this->writeCache ($fileCacheName, $contenido);
                    
				if ($this->log) {
					$stringLog .= " >>>>ESCRIBO CACHE $fileCacheName <<<<";
					$this->log->writeLog ($stringLog);
				}
				if ($tracecache) echo "<pre>CHECK REFRESCO: Time = $cacheTime IDCACHE=$fileCacheName TIME=".time()." <> CACHE ($pathFC)=".filemtime($pathFC)." DIF= ".(time()- filemtime($pathFC))."\n</pre>";
				return $this->_evaluateString ($contenido, true);
			}
			else
			{
				#$regenerar=true;
                touch ($pathFC);
				if ($tracecache) echo "<pre>ERROR EN GENERACION :".$contenido->getMessage().". Haciendo un touch del fichero para renovar el tiempo de cache\n</pre>";
				PEAR::raiseError("**** ERROR GENERANDO MODULO $class: ".$contenido->getMessage(), 0, PEAR_ERROR_TRIGGER);
			}
		}

		// Envio de la regeneración del módulo
		if ($tracecache) echo "<pre>CHECK REFRESCO: Time = $cacheTime IDCACHE=$fileCacheName TIME=".time()." <> CACHE ($pathFC)=".filemtime($pathFC)." DIF= ".(time()- filemtime($pathFC))."\n</pre>";
		if ($tracecache && $fileToCheck) echo "<pre>REF FICHERO ($fileToCheck): $fileToCheckTime. DIF= ".($fileToCheckTime - filemtime($pathFC))."\n</pre>";


		if ($regenerar)
		{
			if ($tracecache) echo "<pre>*****MANDO REFRESCO**** (".(time() - filemtime($pathFC))."sgs.)\n</pre>";
			$retSO = $this->_generateStaticObject ();
            if (PEAR::isError($retSO))
			{
				if ($tracecache) 
				{
					echo "<pre>" . $retSO->getMessage() . "\n</pre>";
				}
				return "";
			}
			if ($this->log) {
				$stringLog .= " >>>>ESCRIBO SERIALIZADO OBJETO $fileCacheName <<<<";
				$this->log->writeLog ($stringLog);
			}
		}
		if ($tracecache) echo "<pre>LEO CACHE\n</pre>";		
			$contenido = @$this->readCache ($fileCacheName);
		if ($this->log) {
			$stringLog .= " >>>>LEO CACHE $fileCacheName <<<<";
			$this->log->writeLog ($stringLog);
		}
		return $contenido;
	}

	function setUpdate ($sge=false) {

        if ($sge)
            $this->_sendToSGE = true;
        else
		    $this->_update = true;
	}
    
	function setPreview () {

        $this->_preview = true;
	}

	function _getContent () {
		echo "HAY QUE REDEFINIR EL METODO <b>" . __FUNCTION__ . "</b> DE LA CLASE <b>" . get_class($this) . "</b> (linea " . __LINE__ . ")";
		die;
		return;
	}

	function _getKeyName () {
		echo "HAY QUE REDEFINIR EL METODO <b>" . __FUNCTION__ . "</b> DE LA CLASE <b>" . get_class($this) . "</b> (linea " . __LINE__ . ")";
		die;
		return;
	}

	function _getCachePath () {
		echo "HAY QUE REDEFINIR EL METODO <b>" . __FUNCTION__ . "</b> DE LA CLASE <b>" . get_class($this) . "</b> (linea " . __LINE__ . ")";
		die;
		return;
	}

	function _getCacheTime () {
		return false;
	}
	
	function _getFileToCheck () {

		return false;
	}

	function start() {

		$fileCacheName = $this->_getKeyName ();
		$pathFC = $this->_cache->getCacheNameFile($fileCacheName);

		$st = @stat($pathFC);

		if (!$st)
		{
			$this->setUpdate ();
			$this->getOutput ();
			$st = @stat($pathFC);
			if (!$st) return 0;
		}

		if ($st['size'] == 0)
			return 0;

		return $st['mtime'];
	}

	function writeCache($key, $contenido)
	{

        /*
        $contenido = explode ("#DYN_ATTRS#", $contenido);
		$content = '$contenido='.var_export($contenido, true).";\n";
        */
        $content = $this->_processContent ($contenido);

        
		if ($this->_static_attrs) {

			foreach ($this->_static_attrs as $attr)
			$content .= "\$this->$attr=".var_export($this->$attr, true).";\n";
        }

        /*
		if ($contenido == '' && empty($this->_static_attrs))
		{
			$fileCacheName = $this->_getKeyName ();
			$pathFC = $this->_cache->getCacheNameFile($fileCacheName);

			$fp = fopen("/SESIONES/elpais4/logs/CacheOutput.log", "a");
			fwrite($fp, date("d-m-Y H:i:s")." ".$this->_getKeyName ()."  ".get_class($this)." $pathFC\n");
			fclose($fp);
		}
        */
		$this->_cache->writeCache ($key, $content);
	}

	function readCache($key)
	{

		$contenido = $this->_cache->readCache ($key);
		return $this->_evaluateString ($contenido);

		/*
		if (substr($contenido,0,3)=='$co')
			eval($contenido);
        
        		if (is_array($contenido)){
            		for ($i = 0; $i < count ($contenido); $i++) {
                		$auxCont = $contenido [$i];
		        	$auxCont = ltrim($auxCont);
                		if ('$this' == substr($auxCont,0,5))
                    			@eval ("\$auxCont = $auxCont;");
                		$result .= $auxCont;
            		}
        	} else {
            		return $contenido;
        	}

		return $result;
		*/
	}

    function _processContent ($contenido) {

        $contenido = explode ("#DYN_ATTRS#", $contenido);
		$content = '$contenido='.var_export($contenido, true).";\n";

        return $content;
    }

	function _evaluateString ($contenido, $exp = false) {

        if ($exp) {
            $contenido = $this->_processContent ($contenido);
        }

		if (substr($contenido,0,3)=='$co')
			eval($contenido);
        
        	if (is_array($contenido)){
            	for ($i = 0; $i < count ($contenido); $i++) {
               		$auxCont = $contenido [$i];
		       		$auxCont = ltrim($auxCont);
               		if ('$this' == substr($auxCont,0,5))
                   		@eval ("\$auxCont = $auxCont;");
               		$result .= $auxCont;
            	}
        	} 
			else {
            		return $contenido;
        	}

		return $result;

	}


}

?>
