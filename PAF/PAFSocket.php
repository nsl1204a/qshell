<?php
// ****************************************************************************
// Lenguaje: PHP
// Copyright 2002 Prisacom S.A.
// ---------
// ChangeLog
// ---------
// $Log: PAFSocket.php,v $
// Revision 1.7  2005/10/19 13:57:47  fjalcaraz
// *** empty log message ***
//
// Revision 1.6  2005/09/12 18:52:28  fjalcaraz
// *** empty log message ***
//
// Revision 1.5  2004/06/01 14:38:45  ljimenez
// actualizadas las versiones de desarrollo con las de producción
//
// Revision 1.3  2003/03/17 17:00:31  scruz
// Modificación en el constructor ($host es opcional=> localhost) y añadido control de versión a la clase.
//
// Revision 1.2  2003/02/17 18:44:40  scruz
// Arreglo en la definición de la constante PAFSOCKET_USLEEP_TIME para que no de warning por no estar definida.
//
// Revision 1.1  2003/01/21 16:55:42  vsanz
// Clases para el manejo de sockets y conexiónes HTTP, primera subida.
//
//
// ****************************************************************************

require_once 'PAF/PAFObject.php';

define("PAFSOCKET_USLEEP_TIME", 100000);

/**
 * Clase genérica para el uso de sockets, utiliza sockets no bloqueantes.
 *
 * @author    Virgilio Sanz <vsanz@prisacom.com>
 * @version $Revision: 1.7 $
 * @copyright Prisacom S.A.
 * @abstract Clase genérica para el uso de sockets
 */
class PAFSocket extends PAFObject {
    // Attributes
   /**
    *    IP/hostname del host al que quermos conectar
    *    @access private
    */
    var $host = false;

   /**
    *    Puerto al que queremos conectar.
    *    @access private
    */
    var $port = 80;

   /**
    *    tiempo de espera máximo para abrir el sócket
    *    @access private
    */
    var $conexionTimeout = 5.0;

   /**
    *    Tiempo de espera máximo para una petición
    *    @access private
    */
    var $peticionTimeout = 30.0;

   /**
    *    Contiene el último número de error.
    *    @access private
    */
    var $errno;

   /**
    *    Contiene la descripción del último error.
    *    @access private
    */
   var $errstr;

   /**
    *    variable con la conexión (valor devuelto por fsockopen)
    *    @access private
    */
    var $sp = false;

    // Associations
    // Operation
   /**
    *    Inicializa el objeto.
    *
    *    @access public
    *    @param string $host IP/nombre del host a conectar
    *    @param integer $port Puerto al que quermos conectar
    */
    function PAFSocket ($host="127.0.0.1", $port = 80) {
        $this->PAFObject();
        $this->setHost($host);
        $this->setPort($port);
    }

   // Métodos GET
   /**
    * Devuelve el valor de $host
    * @access public
    * @returns string
    */
   function getHost() {
       return ($this->host);
   }

   /**
    * Devuelve el valor de $port
    * @access public
    * @returns integer
    */
   function getPort() {
       return ($this->port);
   }

   /**
    * Devuelve el valor de $port
    * @access public
    * @returns integer
    */
   function getConexionTimeout() {
       return ($this->conexionTimeout);
   }

   /**
    *    Devuelve el tiempo de espera máximo para una petición
    *    @access public
    *    @returns float
    */
    function getPeticionTimeout() {
        return ($this->peticionTimeout);
    }

   /**
    *    devuelve la conexión (valor devuelto por fsockopen)
    *    @access public

    */
    function getSocket() {
        return($this->sp);
    }

   // Métodos SET
   /**
    * Asigna el host
    * @access public
    * @param $host string nombre/IP del host.
    */
   function setHost($host) {
       $this->host = $host;
   }

   /**
    * Asigna el puerto a conectar
    * @access public
    * @param $port integer
    */
   function setPort($port) {
       $this->port = $port;
   }

   /**
    * Asigna el timeout para abrir una conexión
    * @access public
    * @param $float ct
    */
   function setConexionTimeout($ct) {
       $this->conexionTimeout = $ct;
   }

   /**
    * Asigna el timeout de espera en una petición
    * @access public
    * @param float $pt
    */
    function setPeticionTimeout($pt) {
        $this->peticionTimeout = $pt;
    }

   /**
    *    Realiza la conexión con el host
    *
    *    @access public
    *    @returns bool true si va bien, PAFError si hay un error.
    */
    function open() {
        if (is_resource($this->sp)) {
            $this->close();
        }

        $this->sp = fsockopen($this->host,
                              $this->port,
                              &$this->errno,
                              &$this->errstr,
                              $this->conexionTimeout);
        if (!$this->sp) {
            return PEAR::raiseError("PAFSocket - ".$this->errstr." - Error nº: ".$this->errno);
        }

#        socket_set_blocking($this->sp, false);
        return true;
    }

   /**
    *    Cierra la conexión con el host
    *
    *    @access public
    *    @returns
    */
    function close() {
        if (is_resource($this->sp)) {
            @fclose($this->sp);
            $this->sp = false;
        }
    }

   /**
    * Devuelve true si el socket está abierto. False en caso contrario.
    *
    * @returns boolean
    */
    function isOpened() {
        return (false == $this->sp ? false : true);
    }

   /**
    *    Lee $nchars caracteres del socket.
    *
    *    @access public
    *    @returns string
	*    @param int $nchars Número de caracteres a leer
    */
    function read($nchars) {
        if (!is_resource($this->sp)) {
            return PEAR::raiseError('No conectado');
        }

        // Calculo cuando deberé acabar de leer tenga o no lo que pido.
        $maxTime = $this->time() + $this->peticionTimeout;
        $data = "";
		$remain=$nchars;
        while (($this->time() < $maxTime) && !feof($this->sp) && $remain>0) {
            $leido = fread($this->sp, $remain);
			if ($GLOBALS['tracesocket']) echo "LEIDO /LENGTH (de $remain leidos =".strlen($leido).")=\n".$leido."\n\n";;
            if (false == $leido) {
                usleep(PAFSOCKET_USLEEP_TIME); // Duermo por medio segundo.
            } else {
                $data .= $leido;
				$remain -= strlen($leido);
            }
        }
        return($data);
    }

   /**
    *    Lee una línea del socket
    *
    *    @access public
    *    @returns string Línea leido o PEAR_Error si hubo errores.
    */
    function readline() {
        if (!is_resource($this->sp)) {
            return PEAR::raiseError('No conectado');
        }

        // Calculo cuando deberé acabar de leer tenga o no lo que pido.
        $maxTime = $this->time() + $this->peticionTimeout;
        $data = "";
        $finished = false;
        while (($this->time() < $maxTime) && !$finished) {
            $leido = fgets($this->sp, 1024);
            if (false == $leido) {
                usleep(PAFSOCKET_USLEEP_TIME); // Duermo por medio segundo.
            } else {
                $data .= $leido;
                if (strlen($data) >= 2 &&
                   (substr($data, -2) == "\r\n" ||
                    substr($data, -1) == "\n")) {
                    $finished = true;
                }
                if (feof($this->sp)) {
                    $finished = true;
                }
            }

            if (feof($this->sp) && !$finished) {
                if ($data == "") {
                    $data = EOF;
                }
                $finished = true;
            }
        }
		if ($GLOBALS['tracesocket']) echo "LEIDO LINE \n".$data."\n\n";;
        return($data);
    }

    function readChunked() {
        if (!is_resource($this->sp)) {
            return PEAR::raiseError('No conectado');
        }

        // Calculo cuando deberé acabar de leer tenga o no lo que pido.
        $maxTime = $this->time() + $this->peticionTimeout;
        $data = "";
        $finished = false;
        while (($this->time() < $maxTime) && !$finished) {
            $leido = trim(fgets($this->sp, 1024));
	    	// Lectura de la longitud del chunk en hex. Viene en linea aparte
	    	list($len)=sscanf($leido, "%x");
	    	if ($len > 0)
	    	{
				while ($len>0)
				{
                	$leido = fread($this->sp, $len);
					if ($GLOBALS['tracesocket']) echo "LEIDO CHUNCKED ($len/".strlen($leido).")=".$leido."\n\n";
                	if (false == $leido) {
                    	usleep(PAFSOCKET_USLEEP_TIME); // Duermo por medio segundo.
                	} else {
                    	$data .= $leido;
		        		$len -= strlen($leido);
                	}
				}
				$ignorado = fgets($this->sp, 3);
				if ($GLOBALS['tracesocket']) echo "LEIDO CHUNCKED IGN =\"".$ignorado."\"\n\n";
	    	}
	    	else break;
        }
        return($data);
    }

   /**
    *    Identico a la función file() de php
    *
    *    @access public
    *    @returns array Array con las líneas leídas del socket, sin el "\n"
    *             o PEAR_Error en caso de error.
    */
    function file() {
        if (!is_resource($this->sp)) {
            return PEAR::raiseError('No conectado');
        }

        $maxTime = $this->time() + $this->peticionTimeout;
        $data = array();
        while (!feof($this->sp) && ($this->time() > $maxTime)) {
            $line = $this->readline();
            if ($line != EOF) {
                $data[] = $line;
            }
        }

        if (!feof($this->sp)) {
            return PEAR::raiseError("Se produjo un timeout de espera");
        }

        return($data);
    }

   /**
    *    Identica a la función readfile de php.
    *
    *    @access public
    *    @returns string contenido del socket o PEAR_Error
    */
    function readfile() {
        if (!is_resource($this->sp)) {
            return PEAR::raiseError('No conectado');
        }

        $maxTime = $this->time() + $this->peticionTimeout;
        $data = "";
        while (!feof($this->sp) && ($this->time() < $maxTime)) {
            $leido = fread($this->sp, 1024);
		    if ($GLOBALS['tracesocket']) echo "LEIDO NORMAL FILE=".$leido."\n\n";;
            if (false == $leido) {
                usleep(PAFSOCKET_USLEEP_TIME); // Duermo por medio segundo.
            } else {
                $data .= $leido;
            }
        }

        if (!feof($this->sp)) {
            return PEAR::raiseError("Se produjo un timeout de espera");
        }

        return($data);
    }

   /**
    *    Envía el contenido de $data al socket
    *
    *    @access public
    *    @returns bool
    *    @param string $data Contenido a envíar.
    */
    function send($data) {
        if (!is_resource($this->sp)) {
            return PEAR::raiseError('No conectado');
        }

        $maxTime = $this->time() + $this->peticionTimeout;
        while ($this->time() < $maxTime) {
            $ret = fwrite($this->sp, $data, strlen($data));
            // Si hubo error duermo y vuelvo a intentarlo hasta dar timeout.
            if (false == $ret) {
                usleep(PAFSOCKET_USLEEP_TIME);
            } else {
                return true;
            }
        }
        return PEAR::raiseError("Error Sending data: TIMEOUT");
    }

   /**
    *    Devuelve el númerop de segundos desde el Unix Epoc, tiene precisión
    *    hasta microsegundos
    *
    *    @access protected
    *    @returns float
    */
    function time() {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }
}

?>
