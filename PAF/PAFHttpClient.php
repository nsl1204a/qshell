<?php

// ****************************************************************************
// Lenguaje: PHP
// Copyright 2002 Prisacom S.A.
// ---------
// ChangeLog
// ---------
// $Log: PAFHttpClient.php,v $
// Revision 1.28  2009/05/28 14:32:16  fjalcaraz
// *** empty log message ***
//
// Revision 1.27  2009/05/12 10:21:11  fjalcaraz
// Gestión de cookies habilitada
// Comentarios de los métodos para PHPDOC
//
// Revision 2.0  2006/08/31 10:53:22  dboillos
// Revisada compatibilidad con php5. Remodelada documentacion.
//
// Revision 1.26  2006/08/31 10:53:22  fjalcaraz
// Añadido soporte para cookies a la clase. Las funciones añadidas son:
// enableCookies, readCookies, readCookiesFile, addCookies, sendCookies, 
// storeCookies
//
// Revision 1.25  2006/08/31 10:53:22  vperulero
// Arreglamos el permitir varios parametros con el mismo nombre, es necesario para llamada a la pasarela de Sogecable.
//
// Revision 1.24  2006/04/25 12:18:08  fjalcaraz
// Se ha eliminado la línea con contenido (body) que se mandaba en peticiones GET (debe ir vacio)
//
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
 * Clase para hacer peticiones HTTP a un server. Actua como un cliente
 * http. Se pueden hacer envios con datos por POST, GET,
 * incluyendo cookies y archivos
 *
 * @author    Virgilio Sanz <vsanz@prisacom.com> 
 * @copyright Prisacom S.A.
 */
class PAFHttpClient {

    /**
     *    Contiene el socket al que hacemos las peticiones.
     *
     *	  @var PAFSocket
     *    @access private
     */
    var $socket = null;

    /**
     * Contiene el username con el que llamar al script si necesario.
     * Util para realizar llamadas estando autenticado
     * 
     * @var string
     * @access private
     */
    var $user;
    
    /**
     * Contiene el password asociado al user.
     * 
     * @var string
     * @access private
     */
    var $passwd;

    /**
     * Contiene la version de HTTP utilizada
     * 
     * @var string
     * @access private
     */
    var $versionHTTP="HTTP/1.1";

    /**
     * Soporte para cookies
     * 
     * @access private
     */
	var $cookies=null;
	
    /**
     * Path donde se guardaran las cookies
     * 
     * @var string
     * @access private
     */	
	var $cookies_path=null;
    
    /**
     *    Constructor.
     *    
     *    @access public 
     *    
     *    @param PAFSocket $socket Conexión valida al host. 
     *    @param string $user  
     *    @param string $passwd  
     */
    function PAFHttpClient(&$socket, $user=false, $passwd=false) {
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
     * @return string username con el que llamar al formulario
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
     * @return string password.
     */
    function getPasswd() {
        return $this->passwd;
    }

    /**
     * Asigna el socket al que queremos hacer la petición.
     *
     * @access public
     * @param PAFSocket $socket socket al que nos conectamos
     */
    function setSocket(&$socket) {
        $this->socket =& $socket;
    }

   /**
   * Devuelve el socket al que queremos hacer la petición.
   *
   * @access public
   * @return PAFSocket socket al que nos conectamos
   */   
   function &getSocket() {
       return $this->socket;
   }
   
   /**
   * Asigna la version de HTTP que se utilizara
   *
   * @access public
   * @param string $version version de HTTP que se asignará
   */  
   function setVersionHTTP($version) {
   	$this->versionHTTP=trim($version);
   }

   /**
    *    Hace un post al script que se pasa como parámetro. 
    *    
    *    @access public 
    *    
    *    @return bool true si ok PEAR_Error si error
    *    @param string $script url a la que se hace la petición
    *    @param array $params Lista de variables para el post.
    *    @param array $host
    *    @param array $method
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

	
   /**
    *    Hace un post al script que se pasa como parámetro mandandolo
    *    con tipo de contenido Content-Type: multipart/form-data. Es decir 
    *    pueden ir archivos. Es una funcion private auxiliar de POST.
    *    
    *    @access private
    *    
    *    @param string $script url a la que se hace la petición
    *    @param array $params Lista de variables para el post.
    *    @param array $host host al que se envia el POST
    *    
    *    @return
    */
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
		if ($this->cookies_path) $this->sendCookies($host, $script, $str);
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
   	   return $this->send($host, $str);	
   	   
	}
	
   /**
    *    Hace un post al script que se pasa como parámetro mandandolo
    *    con tipo de contenido Content-Type: application/x-www-form-urlencoded. 
    *    Es una funcion private auxiliar de POST.
    *    
    *    @access private
    *    
    *    @param string $script url a la que se hace la petición
    *    @param array $params Lista de variables para el post.
    *    @param array $host host al que se envia el POST
    *    
    *    @return
    */
	function POSTurlencoded($script, $params, $host)
	{
	    // TODO: Envíar usuario y password.
        // Construimos la cabecera
        
        $socket =& $this->getSocket();
        if (is_null($host)) $host = $socket->getHost();
        $str = "POST $script ".$this->versionHTTP."\r\n";
        $str .= "Accept: */*\r\n";
        $str .= "Host: $host\r\n";
		if ($this->cookies_path) $this->sendCookies($host, $script, $str);
        $str .= "Content-Type: application/x-www-form-urlencoded\r\n";
        // Parte del mensaje
        $message = "";
        if (is_array($params)) {
            while (list($key, $val) = each($params)) {
                if (is_array($val)) {
                    $tot = count($val);
                    for ($i=0; $i<$tot; $i++)
                    {
                        $a_aux[] = "$key=" . urlencode($val[$i]);
                    }
                } else if (is_null($val)) {
                    $a_aux[]= "$key";
                } else {
                    $a_aux[]= "$key=".urlencode($val);
                }
            }
            $message=implode("&",$a_aux);
        }
        $str .= "Content-Length: " . strlen($message) . "\r\n\r\n";
        $str .= $message;
        // Enviamos al socket

	return $this->send($host, $str);
	}

   /**
    *    Hace un get al script que se pasa como parámetro. 
    *    
    *    @access public 
    *    @returns bool true si ok PEAR_Error si error
    *    @param string $script url a la que se hace la petición
    *    @param array $params Lista de variables para el post.
    *    @param string $host host al que se realiza la peticion
    *    @param string $otherHeaders otras cabeceras 
    */
    function GET($script, $params=null, $host=null, $otherHeaders="") {
        
        //echo "Peticion GET $script, $params, $host, $otherHeaders";
        // Obtenemos el socket
        $socket =& $this->getSocket();

        // Parte del mensaje
        $message = "";
        if (is_array($params)) {
            while (list($key, $val) = each($params)) {
                if (strlen($message) != 0) {
                    $message .= "&";
                }
                //Arreglamos el permitir varios parametros con el mismo nombre
                if (is_array($val)) {
                    $tot = count($val)-1;
                    for ($i=0; $i<$tot; $i++)
                        $message .= $key . '=' . $val[$i] .'&';
                    $message .= $key . '=' . $val[$tot];
                } else if (is_null($val)) {
                    $message .= $key;
                } else {
                    $message .= $key . '=' . $val;
                }
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

		if ($this->cookies_path) $this->sendCookies($host, $script, $otherHeaders);

        $str .= "Host: $host\r\n$otherHeaders\r\n";
        //$str .= $message; No hace falta ¿?

        // Enviamos al socket
        return $this->send($host, $str);
    }

   /**
    * Devuelve TODAS las headers devueltas por el servidor
    * en formato string separadas por \r\n
    * 
    * @access public
    * @return string 
    */
    function getHeaders() {
        return $this->respHeaders;
    }
    
   /**
    * Devuelve el valor de un header especifico si fue 
    * devuelto por el servidor
    * 
    * @access public
    * 
    * @param string $key
    * @return string 
    */
    function getHeaderByKey($key) {
        return $this->respHeaderArray[strtolower($key)];
    }
    
   /**
    * Devuelve El contenido devuelto por el servidor.
    * 
    * @access public
    * @return string 
    */
    function getContent() {
        return $this->respContent;
    }
    
   /**
    * Devuelve El protocolo de la respuesta
    * 
    * @access public
    * @return string 
    */
    function getRespProtocol() {
        return $this->respProtocol;
    }
    
   /**
    * Devuelve El codigo de la respuesta
    * 
    * @access public
    * @return string 
    */
    function getRespCode() {
        return $this->respCode;
    }
    
   /**
    * Devuelve La explicación del código de la respuesta
    * 
    * @access public
    * @return string 
    */
    function getRespCodeExpl() {
        return $this->respCodeExpl;
    }
    
   /**
    * Envía el string $str al socket.
    * 
    * @access private
    * 
    * @param $str string Contenido a enviar al socket.
    * @return boolean true si va bien PEAR_Error en caso de error.
    */
   function send($host, $str) {
       // Limpiamos la petición anteror
       $this->respHeaders = '';
       $this->respContent = '';
       
       // Obtengo el socket y lo cierro.
       $socket =& $this->getSocket();
        
       // Abro el socket de nuevo.
       $res = $socket->open();
       
       if (PEAR::isError($res)) return PEAR::raiseError("PAFHttpClient -> 001 - Error en Socket Open ".$res->getMessage());
        
       // Envío petición

       $res = $socket->send($str);
       
       if (PEAR::isError($res))  return PEAR::raiseError("PAFHttpClient -> 002 - Error en Socket Send ".$res->getMessage());
        
       // Proceso respuesta
       $res = $this->readResponse($host);
       
       if (PEAR::isError($res))  return PEAR::raiseError("PAFHttpClient -> 003 - Error en this ReadResponse ".$res->getMessage());

       return true;
   }

   /**
    * HABILITA GESTIÓN DE COOKIES
	* crea el directorio donde se guardaran las cookies, si este no estuviera creado ya.
    * 
    * @access public
    * @param $path
    * @return unknown_type
    */
	function enableCookies($path)
	{
		$this->cookies_path = $path;
		$this->cookies = array();
		@mkdir($path, 0775, true);
	}

	/**
	 * Lee las cookies aplicables desde los ficheros de cookies
	 * 
     * @access private
	 * @param $host
	 */
	function readCookies($host)
	{
		$dominios = array();
		// Si es un dominio
		if (!preg_match('[0-9\.]*$', $host))
		{
			$dom_parts = explode('.', $host);
			$i=count($dom_parts) - 1;
			$subdomain= ".".$dom_parts[$i];
			for($i--; $i>0; $i--)
			{
			   $subdomain= ".".$dom_parts[$i].$subdomain;
			   $this->readCookiesFile($subdomain);
			}
		}
		$this->readCookiesFile($host);
	}

	/**
	 * importa las cookies de un fichero
	 * 
     * @access private
	 * @param $subdomain
	 * @return unknown_type
	 */
	function readCookiesFile($subdomain)
	{

		$file = "$subdomain.txt";
		if (!$this->cookies[$domain] && ($cookies_arr = @file($this->cookies_path."/$file")))
			$this->addCookies($subdomain, $cookies_arr);
	}

	/**
	 * Añade a la estructura interna un array de cookies en formato textual, parseandolas
	 * 
     * @access private
	 * @param $domain
	 * @param $cookies_arr
	 * @return unknown_type
	 */
	function addCookies($domain, &$cookies_arr)
	{
		if (!$this->cookies[$domain]) $this->cookies[$domain] = array();
		foreach ($cookies_arr as $cookie_txt)
		{
			$cookie_txt = rtrim($cookie_txt);
			// Dominio por defecto
			$cookie_dom = $domain;
			$cookie=array();
			$parts = explode('; ', $cookie_txt);
			foreach($parts as $part)
			{
				list($key, $value) = explode('=', $part, 2);
				switch (strtolower($key))
				{
					case 'path':
						$cookie['path']=$value;
						break;
					case 'expires':
						$cookie['expires']=$value;
						if (($cookie['expires'] = strtotime($value)) < time()) continue 3;
						break;
					case 'domain':
						$cookie_dom = $value;
						break;
					case 'secure':
					case 'httponly':
						break;
					default:
						$cookie['key'] .= ':'.$key;
						break;
				}
			}
			$cookie['txt']=$cookie_txt;
			for ($n=0; $n<count($this->cookies[$cookie_dom]); $n++)
			{
				if ($this->cookies[$cookie_dom][$n]['key']==$cookie['key'])
				{
					$this->cookies[$cookie_dom][$n] = $cookie;
					return;
				}
			}
			$this->cookies[$cookie_dom][] = $cookie;
		}
	}

	/**
	 * Añade al mensaje Http las cabeceras para enviar las cookies al servidor
	 * 
     * @access private
	 * @param $host
	 * @param $path
	 * @param $mesg
	 * @return unknown_type
	 */
	function sendCookies($host, $path, &$mesg)
	{
		$dominios = array();
		// Si es un dominio
		if (!preg_match('[0-9\.]*$', $host))
		{
			$dom_parts = explode('.', $host);
			$i=count($dom_parts) - 1;
			$subdomain= ".".$dom_parts[$i];
			for($i--; $i>0; $i--)
			{
			   $subdomain= ".".$dom_parts[$i].$subdomain;
			   $dominios[] = $subdomain;
			   $this->readCookiesFile($subdomain);
			}
		}
		$dominios[] = $host;
	    $this->readCookiesFile($host);

		foreach ($dominios as $dominio)
			if ($this->cookies[$dominio])
				foreach($this->cookies[$dominio] as $cookie)
					if (!$cookie['path'] || strpos($path, $cookie['path'])===0)
						$mesg .= "Cookie: ".$cookie['txt']."\r\n";
	}

	/**
	 * Si se han recidido cookies en la respuesta Http, las guarda en los ficheros
	 * 
     * @access private
	 * @param $host
	 * @return unknown_type
	 */
	function storeCookies($host)
	{
		// Si no hay cookies en la respuesta retorna
		if (!$this->respHeaderArray['set-cookie']) return;

		$this->addCookies($host, $this->respHeaderArray['set-cookie']);

		$dominios = array();
		// Si es un dominio
		if (!preg_match('[0-9\.]*$', $host))
		{
			$dom_parts = explode('.', $host);
			$i=count($dom_parts) - 1;
			$subdomain= ".".$dom_parts[$i];
			for($i--; $i>0; $i--)
			{
			   $subdomain= ".".$dom_parts[$i].$subdomain;
			   $dominios[] = $subdomain;
			}
		}
		$dominios[] = $host;

		foreach($dominios as $dominio)
			if ($this->cookies[$dominio])
			{
				$cookies='';

				foreach($this->cookies[$dominio] as $cookie)
					$cookies.=$cookie['txt']."\n";

				if ($cookies)
				{
			    	$fp = fopen($this->cookies_path."/$dominio.txt", 'w');
					fwrite($fp, $cookies);
					fclose($fp);
				}
				else unlink($this->cookies_path."/$dominio.txt");
			}
	}

	   
   /**
    * Procesa la respuesta de la petición Http
    * manda guardar las cookies que han llegado si están habilitadas
    * 
    * @access private
    * @param $host
    * @return boolean true si va bien PEAR_Error en caso de error.
    */
   function readResponse($host) {
        // obtengo el socket
        $socket =& $this->getSocket();
        
		// Leo cabeceras
		$clength = 0;
		$chunked = false;
		$header = rtrim($line=$socket->readline());
		list($this->respProtocol, $this->respCode, $this->respCodeExpl) = explode(' ', $header, 3);
		$this->respHeaders = $line;

		while (($header = rtrim($line=$socket->readline())) != '')
		{
			list($key,$value) = explode(': ', $header, 2);
			$key = trim($key);
			$value = trim($value);
	
			$key = strtolower($key);
			switch($key)
			{
	      		case 'content-length': 
	      			$clength = $value;
					break;
	      		case 'transfer-encoding':
	      			if ($value == 'chunked') $chunked= true;
					break;
			}
	       
			// las cabeceras Set Cookie se guardan en un ARRAY, ya que es una cabecera múltiple
			if ($key == 'set-cookie')
			{
				if (!$this->respHeaderArray[$key])
					$this->respHeaderArray[$key]=array($value);
				else
					$this->respHeaderArray[$key][]=$value;
			}
			else $this->respHeaderArray[$key]= $value;

			$this->respHeaders .= $line;
		}

		if ($this->cookies_path) $this->storeCookies($host);

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
