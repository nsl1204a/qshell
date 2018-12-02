<?php

  // *****************************************************************************
  // Lenguaje: PHP
  // Copyright 2002 Prisacom S.A.
  // *****************************************************************************

require_once "PAF/PAFObject.php";
require_once 'PAF/PAFHeader.php';
require_once 'PAF/PAFHttpEnv.php';
require_once 'PAF/PAFApplication.php';
/**
  * Clase para la validacion de variables y control de vulnerabilidades.
  * @access public
  * @package PAF
  */

class PAFSecure extends PAFObject
{
	// DESCRIPCION: Clase que realiza la comprobación de las variables _GET y _POST _REQUEST _COOKIE y URL para evitar ataques 
	//				tipo SQL injection,	ejecución de javascript.
	// 				También asigna el contenido limpio a las variables que se esperan.


    // Por defecto controlamos todos los tipos accesos 
    var $controlGet     = true;
    var $controlPost    = true; 
    var $controlRequest = true;
    var $controlCookie  = true; 
    var $controlUrl     = true; 
    var $controlSession = true;
    var $versionPhp     = PHP_VERSION;
    var $registerGlob   = true;

    // Control para eliminar las palabras que pueden ocasionar sql injection
    // Este control por defectos no está habilitado
    var $palabrasProhibidas=false;
    
    // Esta es la lista de caracteres peligrosos. Se eliminan para evitar ataques XSS
    var $listadoCaracteres = array(
        "\x0B",
        "\x00",
        "\x08",
        "\x09",
        "\x0A",
        "\x0C",
        "\x0D",
        "\x0E",
        "\x0F",
    );

    // Listado de Palabras que son prohibidas 
    var $listadoPalabras = array(
          "script", 
          "show",
          "select", 
          "union", 
          "concat", 
          "insert", 
          "drop",
          "delete",
          "join",
          "from",
          "merge",
          "truncate", 
          "orde by",
          "*",
          " and",
          "and "

    );

    var $allowedTags = array();

    var $raisedAlerts = array();

    // Activación y desactivación de tipos de accesos
    function setControlGet($var=true)       { $this->controlGet = $var; }
    function setControlPost($var=true)      { $this->controlPost = $var; }
    function setControlRequest($var=true)   { $this->controlRequest = $var; }
    function setControlCookie($var=true)    { $this->controlCookie = $var; }
    function setControlUrl($var=true)       { $this->controlUrl = $var; }
    function setControlSession($var=true)   { $this->controlSession = $var; }

    // Activación y desactivación de palabras Prohibidas
    function setPalabrasProhibidas($var){ $this->palabrasProhibidas = $var; }

    // Modificación de listado de Palabras prohibidas
    function setListadoPalabras(&$var){ $this->listadoPalabras = $var; }

    //Contructor
    function PAFSecure(){
       $this->PAFObject(); 
       
       if ($this->versionPhp{0} != '5') {
          $this->esPhpCinco = false;
       }else{
          $this->esPhpCinco = true;
       }

       if (ini_get('register_globals')) {
          $this->registerGlob = true;          
       }else{
          $this->registerGlob = false;
       }
    }
 
    function secureGlobals() {

        //control de variables por URL
        if($this->controlUrl){
            $this->functionUrl();
        }

        //control de variables por GET
        if($this->controlGet && !empty($_GET)){
            $_GET = $this->functionControl(&$_GET, "_GET");
            if (!$this->esPhpCinco) {
                global $HTTP_GET_VARS;
                $HTTP_GET_VARS = $_GET;
            }
        }

	    //control de variables por POST
        if($this->controlPost && !empty($_POST)){
            $_POST = $this->functionControl(&$_POST, "_POST");
            if (!$this->esPhpCinco) {
                global $HTTP_POST_VARS;
                $HTTP_POST_VARS = $_POST;
            }
        }  

	    //control de variables por REQUEST
        if($this->controlRequest && !empty($_REQUEST)){
            $_REQUEST = $this->functionControl(&$_REQUEST, "_REQUEST");
        }    
 
        //control de variables por SESSION
        if($this->controlSession && !empty($_SESSION)) {
           $_SESSION = $this->functionControl(&$_SESSION, "_SESSION");
           if (!$this->esPhpCinco) {
              global $HTTP_SESSION_VARS;
              $HTTP_SESSION_VARS = $_SESSION;
           }
        }   

        //control de variables por COOKIE
        if($this->controlCookie && !empty($_COOKIE)){
           $_COOKIE = $this->functionControl(&$_COOKIE, "_COOKIE");
           if (!$this->esPhpCinco) {
              global $HTTP_COOKIE_VARS;
              $HTTP_COOKIE_VARS = $_COOKIE;
           }
        }    
	}


    /**
     * Funcion que recorre el array dado en $varControl y le pasa el filtro de seguridad.
     * Solo se recorre hasta el segundo nivel por evitar hacer recursividad. Si se encuentran mas
     * niveles se lanzaran las alertas correspondientes.
     *
     * @access public
     * @param array $varControl Array a filtrar
     * @param string $pContext Opcional. Identificador del array a filtrar. Se usa para las alertas.
     */
     function functionControl($varControl, $pContext = null){

        $resultado = array();

        if(!is_array($varControl) || empty($varControl)) return array();

        reset($varControl);
        foreach ($varControl as $keyNivelCero => $valueNivelCero){
            if (is_array($valueNivelCero)) {
                foreach ($valueNivelCero as $keyNivelUno=>$valueNivelUno){
                   if (is_string($valueNivelUno)) {
                      $resultado[$this->secureFilter($keyNivelCero)][$this->secureFilter($keyNivelUno)] = $this->secureFilter($valueNivelUno);
                      // Esto es para aplicar el filtro a las $GLOBALS
                      if ($this->registerGlob) $GLOBALS[$keyNivelCero][$keyNivelUno] = $this->secureFilter($valueNivelUno);
                   }else{ $this->raiseAlert("not_contemplated", $pContext); }
                }
            } elseif (is_string($valueNivelCero)) {
               $resultado[$this->secureFilter($keyNivelCero)] = $this->secureFilter($valueNivelCero);
               
               // Esto es para aplicar el filtro a las $GLOBALS
               if ($this->registerGlob) $GLOBALS[$keyNivelCero] = $this->secureFilter($valueNivelCero);
            } else { 
               $resultado[$this->secureFilter($keyNivelCero)] = $valueNivelCero;
            } 
            
        }
        return $resultado;
   } 



   function secureFilter(&$pValue){ 
        $value=$pValue;
        if (!$value) return $value;

        // Se quitan los caracteres prohibidos
        $value = str_replace($this->listadoCaracteres, '', $value);
        
        $value = strip_tags($value, implode('', $this->allowedTags)); // Se quitan las etiquetas
        
        // Esta cosa tan rara es para asegurarse de que siempre van a estar todas las comillas comentadas
        $value = addslashes(stripslashes($value));         

        // Esto lo comento de momento por que provoca muchos conflictos con todo lo que ya este hecho
        //$value = htmlspecialchars($value); // Se convierten los caracteres especiales


        // Quita las palabras prohibidas ignorando las mayusculas
        if ($this->palabrasProhibidas && is_array($this->listadoPalabras)) { 
           foreach ($this->listadoPalabras as $palabra) {
              if ($palabraEncontrada = stristr($value, $palabra)) { // Optimizado para velocidad de ejecución
                 $value = str_replace(substr($palabraEncontrada,0,strlen($palabra)),'',$value);
              }
           }
        }

        return $value;
   }


   function functionUrl(){
        $uriInicial = PAFHttpEnv::SERVER("REQUEST_URI");
        $uriInicial = urldecode($uriInicial); //Se descodifica por que ya viene codificada en formato URL
        $modificada = $uriInicial;

        // Se quitan los caracteres prohibidos
        $modificada = str_replace($this->listadoCaracteres, '', $modificada);
        
        // eliminamos caracteres no deseados
        $modificada = strip_tags($modificada); 
        $modificada = str_replace("'","",$modificada);
        $modificada = str_replace('"','',$modificada);    
        // ------

        if($modificada != $uriInicial){
            header("Location: ".$modificada); // Esto deberia ser "Moved Permanetly" ?
            exit;
        }   
   }   

   /**
   * Lanza una alerta de seguridad.
   *
   * @access public
   * @param string $pTipoAlert Tipo de alerta a lanzar
   */
   function raiseAlert($pTipoAlert = "unknow_alert", $pMsg) {
        $this->raisedAlerts[$pTipoAlert]['msg'][] = $pMsg;

        switch($pTipoAlert) {
            case "not_contemplated":
                PAFApplication::debug("
                      
                      <strong>ALERTA DE SEGURIDAD:</strong><br>
                      Nivel del array no validado en ".$pMsg.".
                      
                ", __FILE__, __LINE__);
                break;

            default:
                PAFApplication::debug("
                      
                      <strong>ALERTA DE SEGURIDAD:</strong><br>
                      Alerta de seguridad no especificada: ".$pTipoAlert." - ".$pMsg.".
                      
                ", __FILE__, __LINE__);

                break;
        }
   }

   /**
   * Devuelve la lista de alertas lanzadas en un array donde la key es la alerta.
   *
   * @access public
   * @return Array
   */
   function getRaisedAlerts() {
        return $this->raisedAlerts;
   }


   function setAllowedTag($pTag) {
        if (!in_array($this->allowedTags, $pTag)) $this->allowedTags[] = $pTag; 
   }
}
?>
