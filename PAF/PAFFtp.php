<?php


require_once "PAF/PAFObject.php";


class PAFFtp extends PAFObject {


    var $ip;
    var $port;
    var $user;
    var $pass;
    var $connId;

    /**
    * Constructor
    */
    function PAFFtp ($ip, $user, $pass, $port = 21) {

        $this->PAFObject ();
        $this->setHost ($ip, $port);
        $this->setUser ($user, $pass);
    }


    /**
    * Establece la ip y el puerto del host a conectar
    *
    * @access public
    * @param string $ip IP del host a conectar
    * @param string $ip Puerto del host a conectar
    */
    function setHost ($ip, $port) {

        $this->ip = $ip;
        $this->port = $port;
    }

    /**
    * Establece el usuario y el password de conexion al FTP
    *
    * @access public
    * @param string $user Usuario del FTP
    * @param string $pass Password del FTP
    */
    function setUser ($user, $pass) {

        $this->user = $user;
        $this->pass = $pass;
    }

    /**
    * Establece la conexion al FTP
    *
    * @access public
    * @return string Identificador de conexion.
    */
    function connect () {

        $this->closeConnection ();

        $this->connId = @ftp_connect ($this->ip, $this->port);

        if (!$this->connId){

            $this = PEAR::raiseError ("¡¡¡ ERROR !!! (".__FILE__.",". __LINE__.") => Error en la conexión. SERVER $this->ip $this->port");
            return $this;
        }

        $login = @ftp_login ($this->connId, $this->user, $this->pass);

        if (!$login) {

            $this = PEAR::raiseError ("¡¡¡ ERROR !!! (".__FILE__.",". __LINE__.") => Usuario / Password incorrectos. SERVER $this->ip $this->port");
            return $this;
        }
    }


	/**
	* Metodo para activar o desactivar el modo pasivo
	*
	* @param boolean $value true/false para activar o desactivar el modo
	* pasivo.
	*
	*/
	function passiveMode ($value) {

		@ftp_pasv ($this->connId, $value);
	}
	

    /**
    * Metodo para subir un fichero
    *
    * @access public
    * @param string $fileLocal Nombre del fichero a subir
    * @param string $mode Modo de conexion A = FTP_ASCII o B = FTP_BINARY
    * @param string $fileRemote Nombre del fichero a guardar en el servidor.
    */
    function putFile ($fileLocal, $mode, $fileRemote = false) {


        if (strtoupper ($mode) == "A")
            $mode = FTP_ASCII;
        else
            $mode = FTP_BINARY;

        if (!$fileRemote)
            $fileRemote = $fileLocal;

        $ftpResult = @ftp_put ($this->connId, $fileRemote, $fileLocal, $mode);

        if (!$ftpResult) {

            return PEAR::raiseError ("¡¡¡ ERROR !!! (".__FILE__.",". __LINE__.") => Error al subir el fichero. SERVER $this->ip $this->port");
        }
    }


    /**
    * Metodo para descargar un fichero
    *
    * @access public
    * @param string $fileRemote Nombre del fichero a descargar del servidor.
    * @param string $mode Modo de conexion A = FTP_ASCII o B = FTP_BINARY
    * @param string $fileLocal Nombre del fichero a guardar en local
    */
    function getFile ($fileRemote, $mode, $fileLocal = false) {

        if (strtoupper ($mode) == "A")
            $mode = FTP_ASCII;
        else
            $mode = FTP_BINARY;

        if (!$fileLocal)
            $fileLocal = $fileRemote;

        $ftpResult = @ftp_get ($this->connId, $fileLocal, $fileRemote, $mode);

        if (!$ftpResult) {

            return PEAR::raiseError ("¡¡¡ ERROR !!! (".__FILE__.",". __LINE__.") => Error al decargar el fichero. SERVER $this->ip $this->port");
        }
    }

    /**
    * Metodo para borrar un fichero
    *
    * @access public
    * @param string $fileRemote Nombre del fichero a borrar del servidor.
    */
    function deleteFile ($fileRemote) {

        $ftpResult = @ftp_delete ($this->connId, $fileRemote);

        if (!$ftpResult) {

            return PEAR::raiseError ("¡¡¡ ERROR !!! (".__FILE__.",". __LINE__.") => Error al borrar el fichero. SERVER $this->ip $this->port");
        }
    }

    /**
    * Metodo que devuelve un listado con los archivos de un directorio y sus propiedades como un array de ficheros
    *
    * @access public
    * @param string $dir Directorio sobre el que actua en produccion.
    * @return array
    */
    function getNList ($dir = ".") {

        $files = @ftp_nlist ($this->connId, $dir);
        return $files;
    }

    /**
    * Metodo que devuelve un listado con los archivos de un directorio y sus propiedades. El resultado es un array de lineas como la salida de un ls -l
    *
    * @access public
    * @param string $dir Directorio sobre el que actua en produccion.
    * @return array
    */
    function getList ($dir = ".") {

        $files = @ftp_rawlist ($this->connId, $dir);
        return $files;
    }

    /**
    * Crea un directorio en el servidor
    *
    * @access public
    * @param string $dir Directorio a crear en el servidor
    * @return string
    */
    function createDir ($dir) {

        $dirCreate = @ftp_mkdir ($this->connId, $dir);

        if (!$dirCreate) {
            return PEAR::raiseError ("¡¡¡ ERROR !!! (".__FILE__.",". __LINE__.") => Error al crear el directorio. SERVER $this->ip $this->port");
        }
        return $dirCreate;
    }

    /**
    * Metodo que cambia de directorio en el servidor
    *
    * @access public
    * @param string $dir Directorio destino en el servidor
    * @return bool
    */
    function changeDir ($dir) {

        $resultChange = @ftp_chdir ($this->connId, $dir);
	
	if (!$resultChange) {
            	return PEAR::raiseError ("¡¡¡ ERROR !!! (".__FILE__.",". __LINE__.") => Error al cambiar de directorio. SERVER $this->ip $this->port");
        }

        return $resultChange;
    }


    /**
    * Desconecta la conexion al servidor
    *
    * @access public
    * @param string $idConn Identificador de conexion.
    */
    function closeConnection () {

        @ftp_quit ($this->connId);
        unset ($this->connId);

	#  Funcion para PHP >= 4.2.0
	#  ftp_close ($idConn);

        return true;
    }

    function pwdFtp()
    {
	return ftp_pwd($this->connId);
    }


    /**
     * Metodo que devuelve el nombre del directorio actual
     *
     * @access public
     * @return string
     */
    function getPwd () {

        $resultChange = @ftp_pwd($this->connId);
        return $resultChange;
    }


}


?>
