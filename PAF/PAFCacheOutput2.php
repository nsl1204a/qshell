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
				@fclose($f);
	 		}
			else PEAR::raiseError("ERROR: PAFCacheOutput. No pudo escribir serializado en $fullpath", 0, PEAR_ERROR_TRIGGER);
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
						$regenerar=true;
				}
				else
				{
					if ((time() - $st['mtime']) > $cacheTime)
						$regenerar = true;
	
					// Regeneracion directa por demasiado tiempo esperando
					//if ((time() - $st['mtime']) > 2*$cacheTime)
						//$this->_update = true;
				}
			}

			if (PAFHttpEnv::GET('regenerate') == true && !isset($this->_notRegenerate) )
			{
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

				$this->writeCache ($fileCacheName, $contenido);
				if ($this->log) {
					$stringLog .= " >>>>ESCRIBO CACHE $fileCacheName <<<<";
					$this->log->writeLog ($stringLog);
				}
				if ($tracecache) echo "<pre>CHECK REFRESCO: Time = $cacheTime IDCACHE=$fileCacheName TIME=".time()." <> CACHE (".$this->_cache->getCacheNameFile($fileCacheName).")=".filemtime($this->_cache->getCacheNameFile($fileCacheName))." DIF= ".(time()- filemtime($this->_cache->getCacheNameFile($fileCacheName)))."\n</pre>";
				return $contenido;
			}
			else
			{
				$regenerar=true;
				if ($tracecache) echo "<pre>ERROR EN GENERACION\n</pre>";
				PEAR::raiseError("**** ERROR GENERANDO MODULO $class: ".$contenido->getMessage(), 0, PEAR_ERROR_TRIGGER);
			}
		}

		// Envio de la regeneración del módulo
		if ($tracecache) echo "<pre>CHECK REFRESCO: Time = $cacheTime IDCACHE=$fileCacheName TIME=".time()." <> CACHE (".$this->_cache->getCacheNameFile($fileCacheName).")=".filemtime($this->_cache->getCacheNameFile($fileCacheName))." DIF= ".(time()- filemtime($this->_cache->getCacheNameFile($fileCacheName)))."\n</pre>";
		if ($tracecache && $fileToCheck) echo "<pre>REF FICHERO ($fileToCheck): $fileToCheckTime. DIF= ".($fileToCheckTime - filemtime($this->_cache->getCacheNameFile($fileCacheName)))."\n</pre>";


		if ($regenerar)
		{
			if ($tracecache) echo "<pre>*****MANDO REFRESCO**** (".(time() - filemtime($this->_cache->getCacheNameFile($fileCacheName)))."sg.)\n</pre>";
			$this->_generateStaticObject ();
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

	function setUpdate () {
		$this->_update = true;
	}

	function _getContent () {
		echo "HAY QUE REDEFINIR EL METODO <b>" . __FUNCTION__ . "</b> DE LA CLASE <b>" . __CLASS__ . "</b> (linea " . __LINE__ . ")";
		die;
		return;
	}

	function _getKeyName () {
		echo "HAY QUE REDEFINIR EL METODO <b>" . __FUNCTION__ . "</b> DE LA CLASE <b>" . __CLASS__ . "</b> (linea " . __LINE__ . ")";
		die;
		return;
	}

	function _getCachePath () {
		echo "HAY QUE REDEFINIR EL METODO <b>" . __FUNCTION__ . "</b> DE LA CLASE <b>" . __CLASS__ . "</b> (linea " . __LINE__ . ")";
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

		if ($this->_static_attrs)
		{
			$content = '$contenido='.var_export($contenido, true).";\n";
			foreach ($this->_static_attrs as $attr)
			$content .= "\$this->$attr=".var_export($this->$attr, true).";\n";
				$this->_cache->writeCache ($key, $content);
		}
		else $this->_cache->writeCache ($key, $contenido);
	}

	function readCache($key)
	{
		$contenido = $this->_cache->readCache ($key);
		if ($this->_static_attrs) eval($contenido);
		return $contenido;
	}


}

?>
