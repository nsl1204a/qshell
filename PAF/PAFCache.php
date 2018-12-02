<?php

  // *****************************************************************************
  // Lenguaje: PHP
  // Copyright 2002 Prisacom S.A.
  // ---------
  // ChangeLog
  // ---------
  // $Log: PAFCache.php,v $
  // Revision 1.12  2009/05/27 11:07:09  fjalcaraz
  // *** empty log message ***
  //
  // Revision 1.11  2009/05/27 11:06:46  fjalcaraz
  // Touch en fichero a actualizar
  //
  // Revision 1.10  2007/04/18 15:36:11  orodriguezp
  // *** empty log message ***
  //
  // Revision 1.9  2007/04/12 11:22:14  mrak
  // Tuneado el metodo crearRuta para que genere bien las rutas para setTopLinks
  //
  // Revision 1.8  2005/09/01 15:24:14  fjalcaraz
  // *** empty log message ***
  //
  // Revision 1.7  2004/10/21 15:59:38  fjalcaraz
  // *** empty log message ***
  //
  // Revision 1.6  2003/01/14 15:36:27  scruz
  // Modificaciones en la documentación del acceso de algunos métodos.
  //
  // Revision 1.5  2002/09/03 09:43:53  scruz
  // Eliminada sentencia if (defined())
  //
  // Revision 1.4  2002/08/23 10:57:22  scruz
  // Comprobación de existencia del fichero de caché.
  // Incorporación del método getCacheExpireTime().
  //
  // Revision 1.3  2002/08/07 14:14:30  scruz
  // Arreglos en el método hashNameFile y mejoras generales.
  // Control de cerrado de ficheros.
  //
  // Revision 1.2  2002/08/07 09:44:29  gustavo
  // Modificacion del metodo hashNameFile, devuelve el path completo al fichero
  //
  // Revision 1.1  2002/08/06 16:33:49  scruz
  // Primera versión.
  //
  // *****************************************************************************

  require_once "PAF/PAFObject.php";

  define ("CLASS_PAFCACHE", 14);

/**
* Clase que implementa la caché de fichero.
*
* @author Sergio Cruz <scruz@prisacom.com>, Gustavo Núñez <gnunez@prisacom.com>
* @version $Revision: 1.12 $
* @package PAF
*/
class PAFCache extends PAFObject
{
    /**
      * Almacena el path donde se escribirán los ficheros de caché.
      * @var string
      * @access public
      */
    var $pathCache;

    /**
      * Subdirectorio dentro del path de caché donde queremos grabar los ficheros de caché.
      * @var string
      * @access public
      */
    var $subdir;

    /**
      * Constructor.
      *
      * @param string $subdir Subdirectorio dentro del directorio general de caché donde queremos grabar
      *        los ficheros. En el caso nuestro generalmente este directorio será el que identifique el
      *        medio en el que nos encontremos.
      * @param string $pathCache Path por defecto donde empieza la estructura de ficheros de la caché.
      * @access public
      */
    function PAFCache (
                        $subdir,
                        $pathCache= "/SESIONES/cacheFile"
                      )
    {
        $this->PAFObject();
        $this->pathCache= $pathCache;
        $this->subdir= $subdir;
    }


    /**
      * Comprueba si ha expirado el tiempo de caché del fichero.
      *
      * @param integer $lifetime Tiempo en segundos de caché.
      * @param string  $idCacheFile Identificador del elemento a cachear.
      * @param boolean $regenerate Regeneración de la caché.
      * @access public
      * @return mixed int Tiempo hasta expiración de caché si el fichero no ha caducado o true si ha caducado.
      */
    function updatedCache (
                            $lifeTime,
                            $idCacheFile,
                            $regenerate= false
                          )
    {
        $nameFile = $this->hashNameFile($idCacheFile);
        if ( is_file ($nameFile) )
        {
            $tExpire= filemtime($nameFile) + $lifeTime;
            $t= time();

            if ( $regenerate==true || $tExpire < $t)
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        return true;
    }

    /**
      * Devuelve el tiempo que le queda para expirar a la cache cuyo id pasamos como parámetro.
      * 
      * @access public
      * @param integer $lifeTime Tiempo de cacheo que damos al identificador de caché.
      * @param string $idCacheFile Identificador de caché.
      * @return integer Número de segundos que quedan para que expire la caché de fichero.
      */
    function getCacheExpireTime($lifeTime, $idCacheFile)
    {
        $nameFile = $this->hashNameFile($idCacheFile);


        if ( is_file ($nameFile) )
        {
            $tExpire= filemtime($nameFile) + $lifeTime;
            $t= time();
            return $tExpire - $t;    // Retorna el tiempo que le queda
        }
        else
            return 0;
    }

    /**
      * Escritura en fichero del contenido a cachear.
      *
      * @access public
      * @param string $idCacheFile Identificador del elemento a cachear.
      * @param string $content Contenido a cachear.
      * @return boolean
      */
    function writeCache ($idCacheFile, $content)
    {
        $nameFile= $this->hashNameFile($idCacheFile);
        $nameFileTemp= $this->hashNameFile($idCacheFile) . "_tmp";

        if ($fp = fopen ($nameFileTemp, "w"))
        {
            $res = fwrite ($fp, $content);
            fclose ($fp);
            @rename ($nameFileTemp, $nameFile);
            return true;
        }
				/*
				if (isset($_GET['debugjavi']) && (!$fp))
				{
					echo "IDCACHE" . $idCacheFile . "\n";
					echo "HASH" . $this->hashNameFile($idCacheFile) . "\n";
					echo "ERRXX" . $nameFileTemp . "\n";
					var_dump(debug_backtrace());
				}
				*/

        $this->errorLog ("Error al escribir el fichero: " . realPath ($nameFile) . "\n");
        return  false;
    }

    /**
      * Lectura del contenido de fichero cache.
      *
      * @access public
      * @param string $idCacheFile Identificador del elemento a cachear.
      * @return string Contenido del fichero cache o falso en caso de error.
      */
    function readCache ($idCacheFile)
    {
        $nameFile = $this->hashNameFile($idCacheFile);

        if ( is_file ($nameFile) )
        {
            if ($fp = @fopen ($nameFile, "r"))
            {
                $content = fread ($fp, filesize($nameFile));
                fclose ($fp);
                return $content;
            }
        }

        $this->errorLog ("Error al leer el fichero: " . realPath($nameFile) . "\n");
        return  false;
    }

    /**
      * hashNameFile - Hash de un identificador de elemento mediante MD5
      *
      * @access private
      * @param string - Identificador del elemento a cachear.
      * @return string Nombre del fichero.
      */
    function hashNameFile ($idCacheFile)
    {
        // TO DO: Falta por pulir esto bastante. Lanzamiento de errores, comprobaciones varias, etc.
        umask(0);

        $idCacheFile= md5($idCacheFile);
        $id= substr ($idCacheFile, 0, 3);

        $parts= array (
                        $this->pathCache,
                        $this->subdir,
                        $id
                      );

        $pathToCreate= "";
        for ($i= 0; $i < count ($parts); $i++)
        {
            if ($i == 0)
                $pathToCreate.= $parts[$i];
            else
                $pathToCreate.= "/" . $parts[$i];

            if ( !is_dir ($pathToCreate) )
            {
                if (!@mkdir($pathToCreate, 0777))
                {
                    $this->errorLog ("Error al crear el directorio: " . realPath ($this->pathCache) . "\n");
                    return  false;
                }
            }
        }

        $hash= $this->pathCache . "/" . $this->subdir . "/" . $id ."/". $idCacheFile;



        return $hash;
    }

	function hashNameFileStatic ($pathCache, $subdir, $idCacheFile)
    {
        umask(0);
        $idCacheFile = md5($idCacheFile);
        $id = substr ($idCacheFile, 0, 3);
        $parts = array ($pathCache, $subdir, $id);
        $pathToCreate = "";
        for($i = 0; $i < count($parts); $i++){
            if($i == 0){
                $pathToCreate .= $parts[$i];
            }else{
                $pathToCreate .= "/" . $parts[$i];
            }
            if(!is_dir($pathToCreate)){
                return  false;
            }
        }
        $hash = $pathCache . "/" . $subdir . "/" . $id ."/". $idCacheFile;
        return $hash;
    }

    /**
      * Devuelve el nombre del fichero de cache
      *
      * @access private
      * @param string - Identificador del elemento a cachear.
      * @return string - Nombre del fichero.
      */
    function getCacheNameFile ($idCacheFile)
    {
        $nameFile = $this->hashNameFile($idCacheFile);
        return $nameFile;
    }

    /**
      * Creacion de logs de errores
      * @param string Descripcion del error
      */
    function errorLog ($s_error)
    {
    	/*
        $i_fp = fopen ($this->pathCache."/cachefile.log","a+");
        if ($i_fp)
        {
            $s_log = date ("H:i-d/m/Y=> ") . $s_error ;
            fwrite ($i_fp, $s_log);
        }
        fclose ($i_fp);
	*/
    }
}
?>
