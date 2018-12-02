<?php

// ****************************************************************************
// Lenguaje: PHP
// Copyright 2002 Prisacom S.A.
// ---------
// ChangeLog
// ---------
// $Log: PAFHttpClient.php,v $
// Revision 1.23  2005/10/19 13:57:47  fjalcaraz
// *** empty log message ***
//
// Revision 1.22  2005/09/12 19:00:51  fjalcaraz
// *** empty log message ***
//
// Revision 1.21  2005/08/30 05:51:32  mfuente
// anadido el metodo setVersionHTTP, en principio afectaria solo a los post
//
// Revision 1.20  2005/03/08 12:47:05  jgomez
// Corregido error en POSTurlencoded.
//
// Revision 1.19  2004/10/21 15:57:35  fjalcaraz
// *** empty log message ***
//
// Revision 1.18  2004/08/17 17:09:13  fjalcaraz
// *** empty log message ***
//
// Revision 1.17  2004/08/17 11:26:56  fjalcaraz
// Nuevas funciones para el manejo de los códigos de respuesta HTTP
//
// Revision 1.15  2004/07/30 10:27:18  jgomez
// Modificado el POST multipart para suministrar el content-type  de los ficheros a hacer upload
// Ahora el array de $params, en su segundo índice contiene otro array con el nombre del fichero y el content-type, solo si utilizamos la @ para enviar el contenido de un fichero, en otro caso funciona como habitualmente.
//
// Revision 1.14  2004/07/29 11:03:47  jgomez
// Modificaciones para ajustar el content-length en POST multipart.
//
// Revision 1.13  2004/07/27 08:59:58  jlfernandez
// Se modifica POSTmultipart para envio de ficheros
//
// Revision 1.13  2004/06/16 11:34:24  jlfernandez
// Cambios en el metodo POSTmultipart, en caso de envio de ficheros, se trata de distinta manera 
// 
// Revision 1.12  2004/06/16 11:34:24  amesas
// Cambios en el metodo POST, ahora admite el urlencoded y el multipart
//
// Revision 1.11  2004/05/10 17:24:13  fjalcaraz
// Tratamiento de cabeceras Content-length
//
// Revision 1.10  2004/03/16 11:27:12  jgomez
// Corregido el envío por POST (se envía como multipart/form con boundaries y demás).
//
// Revision 1.9  2003/08/11 09:22:28  mcobos
// Arreglando el merge anterior
//
// Revision 1.8  2003/08/11 08:35:38  mcobos
// GET ahora toma las cabeceras que le pasan
//
// Revision 1.6  2003/07/21 12:00:58  fjalcaraz
// Login y Password en comunicacion HTTP (solo Get)
//
// Revision 1.3  2003/03/13 09:07:40  scruz
// Correcciones en el getResponse para que sea transparente.
//
// Revision 1.2  2003/02/17 17:58:44  vsanz
// Modifico GET y POST para poder hacer peticiones sin parámetros
//
// Revision 1.1  2003/01/21 16:55:42  vsanz
// Clases para el manejo de sockets y conexiónes HTTP, primera subida.
//
//
// ****************************************************************************

require_once 'PAF/PAFSocket.php';
require_once 'PAF/PAFObject.php';

/**
 * Clase para hacer peticiones HTTP a un server.
 *
 * @author    Virgilio Sanz <vsanz@prisacom.com> 
 * @copyright Prisacom S.A.
 */
class PAFHttpClient2 {

    	
    // Attributes
    /**
     *    Contiene el socket al que hacemos las peticiones.
     *
     *    @access private
     */
    var $socket = null;

    /**
     * Contiene el username con el que llamar al script si necesario.
     * @access private
     */
    var $user;
    
    /**
     * Contiene el password asociado al user.
     * @access private
     */
    var $passwd;

    var $versionHTTP="HTTP/1.1";
    
    // Associations
    // Operations
    /**
     *    Constructor.
     *    
     *    @access public 
     *    @param PAFSocket $socket Conexión valida al host. 
     */
    function PAFHttpClient2(&$socket, $user=false, $passwd=false) {
        $this->setSocket($socket);
        $this->setUser($user);
        $this->setPasswd($passwd);
    }

    /**
     * Asigna el usuario
     *
     * @access public
     * @param string $user username con el que llamar al formulario
     */
    function setUser($user) {
       $this->user = $user;
    }

    /**
     * Devuelve el usuario con el que nos conectamos.
     *
     * @access public
     * @returns string username con el que llamar al formulario
     */
    function getUser($user) {
       return $this->user;
    }
   
    /**
     * Asigna el password asociado al usuario.
     *
     * @access public
     * @param string $passwd Password asociada al $user
     */   
    function setPasswd($passwd) {
       $this->passwd = $passwd;
    }

    /**
     * Devuelve el password asociado al usuario para esta peticion
     *
     * @access public
     * @returns string password.
     */
    function getPasswd() {
        return $this->passwd;
    }

    /**
     * Asigna el socket al que queremos hacer la petción.
     *
     * @acces public
     * @param PAFSocket socket al que nos conectamos
     */
    function setSocket(&$socket) {
        $this->socket =& $socket;
    }

   /**
   * Devuelve el socket al que queremos hacer la petción.
   *
   * @acces public
   * @returns PAFSocket socket al que nos conectamos
   */   
   function &getSocket() {
       return $this->socket;
   }
   function setVersionHTTP($version)
   {
   	$this->versionHTTP=trim($version);
   }

   /**
    *    Hace un post al script que se pasa como parámetro. 
    *    
    *    @access public 
    *    @returns bool true si ok PEAR_Error si error
    *    @param string $script url a la que se hace la petición
    *    @param array $params Lista de variables para el post.
    */
   function POST($script, $params=null, $host=null,$method = "urlencoded") {

    	switch($method)
		{
			case "urlencoded":
				$res = $this->POSTurlencoded($script,$params, $host);
			break;
			case "multipart":
				$res = $this->POSTmultipart($script,$params, $host);
			break;
		}
		
		return $res;	
	}


	function POSTmultipart($script, $params, $host)
	{
        // TODO: Envíar usuario y password.
        // Construimos la cabecera
	    $boundary="pcomPCxUX3fYGSBkaYJIFA3XTDX/sTB";  
   		$tamanyo = 0;   
		$tmp = "";
		$tmpstr = "";
		$str = "";
		$socket =& $this->getSocket();
        if (is_null($host)) $host = $socket->getHost();

        $str .= "POST $script ".$this->versionHTTP."\r\n";
        $str .= "Host: $host\n";
        $str .= "User-Agent: HTTP_POST/1.0\r\n";
        $str .= "Accept: */*\r\n";
        $str .= "Accept-Charset: iso-8859-1,*,utf-8\r\n";

        $str .= "Cache-Control: no-cache\r\n";
        $str .= "Content-Type: multipart/form-data; boundary=$boundary\r\n";

	$tmp = "";
	    
	    $n=count($params);
	    foreach($params as $key=>$val_array) {
	  
	    
              	
        	$val=$val_array[0];
        	$tipo=$val_array[1];
        	
        	if (substr($val,0,1)=="@") { 
        		
        		//Si se trata de un fichero 
        		                        

        		$tmp .= sprintf("\n--%s\r\nContent-Disposition: form-data; name=\"%s\"; filename=\"%s\"\r\n", $boundary, $key, substr($val,1));
         		

			if ($tipo=="") {
				$tipo="text/plain";
			}

			$tmp .= "Content-Type: ".$tipo."\r\n\r\n";
         		
         		
         		//Leemos el fichero a enviar (ascii o binario)
         		
         		if (is_file(substr($val,1))) {
       				$handle = fopen(substr($val,1), "rb");
				$strFile=fread($handle, filesize(substr($val,1)));
				fclose($handle);
       			}
       			
       			$tmp.=$strFile . "\r";
	     	
	     	}	
        	else {
          
          		//Parámetro normal
          
         		$tmp .= sprintf("\n--%s\r\nContent-Disposition: form-data; name=\"%s\"\r\n\r\n%s\r", $boundary, $key, $val_array);
	       }
           }
           
       	   $tamanyo += strlen($tmp);
           $tamanyo += strlen($boundary)+6;
	   $str .= "Content-Length: ".$tamanyo."\r\n";
	   $str .= $tmp;
	   $str .= "\n"; 
	     	
	   $str .= "--". $boundary."--\r\n";

	   // Enviamos al socket
   	   return $this->send($str);	
   	   
	}
	
	function POSTurlencoded($script, $params, $host)
	{
	    // TODO: Envíar usuario y password.
        // Construimos la cabecera
        
        $socket =& $this->getSocket();
        if (is_null($host)) $host = $socket->getHost();
        $str = "POST $script ".$this->versionHTTP."\r\n";
        $str .= "Accept: */*\r\n";
        $str .= "Host: $host\r\n";
        $str .= "Content-Type: application/x-www-form-urlencoded\r\n";
        // Parte del mensaje
        $message = "";
        if (is_array($params)) {
            while (list($key, $val) = each($params)) {
                $a_aux[]= "$key=".urlencode($val);
            }
            $message=implode("&",$a_aux);
        }
        $str .= "Content-Length: " . strlen($message) . "\r\n\r\n";
        $str .= $message;
        // Enviamos al socket

       
	return $this->send($str);
	}

   /**
    *    Hace un get al script que se pasa como parámetro. 
    *    
    *    @access public 
    *    @returns bool true si ok PEAR_Error si error
    *    @param string $script url a la que se hace la petición
    *    @param array $params Lista de variables para el post.
    */
    function GET($script, $params=null, $host=null, $otherHeaders="") {
        
        // Obtenemos el socket
        $socket =& $this->getSocket();

        // Parte del mensaje
        $message = "";
        if (is_array($params)) {
            while (list($key, $val) = each($params)) {
                if (strlen($message) != 0) {
                    $message .= "&";
                }
                $message .= $key . '=' . $val;
            }
            $message = "?$message";
        }
	elseif ($params) $message = "?$params";


        // Añadimos el mensaje al string que se envía.
        // Construimos la cabecera
	if (is_null($host)) $host = $socket->getHost();
        $str = "GET $script$message ".$this->versionHTTP."\r\n"; 

	if ($this->user)
		$str .= "Authorization: Basic ".base64_encode($this->user.":".$this->passwd)."\r\n";

        $str .= "Host: $host\r\n$otherHeaders\r\n";
        $str .= $message;

        // Enviamos al socket
        return $this->send($str);
    }

   /**
    * Devuelve TODAS las headers devueltas por el servidor
    * en formato string separadas por \r\n
    * 
    * @access public
    * @returns string 
    */
    function getHeaders() {
        return $this->respHeaders;
    }
    
   /**
    * Devuelve el valor de un header especifico si fue 
    * devuelto por el servidor
    * 
    * @access public
    * @returns string 
    */
    function getHeaderByKey($key) {
        return $this->respHeaderArray[$key];
    }
    
   /**
    * Devuelve El contenido devuelto por el servidor.
    * 
    * @access public
    * @returns string 
    */
    function getContent() {
        return $this->respContent;
    }
    
   /**
    * Devuelve El protocolo de la respuesta
    * 
    * @access public
    * @returns string 
    */
    function getRespProtocol() {
        return $this->respProtocol;
    }
    
   /**
    * Devuelve El codigo de la respuesta
    * 
    * @access public
    * @returns string 
    */
    function getRespCode() {
        return $this->respCode;
    }
    
   /**
    * Devuelve La explicación del código de la respuesta
    * 
    * @access public
    * @returns string 
    */
    function getRespCodeExpl() {
        return $this->respCodeExpl;
    }
    
   /**
    * Envía el strinf $str al socket.
    * 
    * @access private
    * @param $str string Contenido a enviar al socket.
    * @returns boolean true si va bien PEAR_Error en caso de error.
    */
   function send($str) {
       // Limpiamos la petición anteror
       $this->respHeaders = '';
       $this->respContent = '';
       
       // Obtengo el socket y lo cierro.
       $socket =& $this->getSocket();
        
       // Abro el socket de nuevo.
       $res = $socket->open();
       
       if (PEAR::isError($res)) return $res;
        
       // Envío petición
       
       $res = $socket->send($str);
       
       if (PEAR::isError($res)) return $res;
        
       // Proceso respuesta
       $res = $this->readResponse();
       
       if (PEAR::isError($res)) return $res;

       return true;
   }

   /**
    * Procesa la respuesta del socket, para 
    * 
    * @access private
    * @returns boolean true si va bien PEAR_Error en caso de error.
    */
   function readResponse() {
        // obtengo el socket
        $socket =& $this->getSocket();
        
	// Leo cabeceras
	$clength = 0;
	$chunked = false;
	$header = rtrim($line=$socket->readline());
	list($this->respProtocol, $this->respCode, $this->respCodeExpl) = explode(' ', $header, 3);
	$this->respHeaders = $line;

	echo "HOLA";
	$n=0;
	while ($n<30 && ($header = rtrim($line=$socket->readline())) != '' && $line!=EOF)
	{
	   echo "[$header]\n";
	   list($key,$value) = explode(': ', $header, 2);

	   switch(strtolower($key))
	   {
	      case 'content-length': 
	      		$clength = $value;
			break;
	      case 'transfer-encoding':
	      		if ($value == 'chunked') $chunked= true;
			break;
	   }
	       
	   $this->respHeaderArray[$key] = $value;
	   $this->respHeaders .= $line;
	   $n++;
	}
	echo "LEYENDO DATA clength=$clength transfer=$chunked\n";

        // Leo respuesta

	if ($chunked)
	   $res = $socket->readChunked();
	elseif ($clength)
	   $res = $socket->read($clength);
	else
	   $res = $socket->readfile();

        if (PEAR::isError($res)) return $res;

	$this->respContent = $res;

        $socket->close(); // Con esto eliminamos peticiones anteriores
        
        return true;
   }
   
   
   
   
   
}

?>
