<?php

require_once "Text/CAPTCHA.php";

/** 
 * @author lediaz 
 */


/**
 * Clase de gestion de captcha, tanto para la creacion como para la comprobacion 
 * Para que funcione todo correcto hay que hacer un session_start() antes de llamar a los metodos de esta clase
 * La razon es que la imagen que se guarda es un md5 del session_id() y ha de ser la misma siempre para que podamos borrarla de forma correcta
 * Esta clase también almacena en session el valor correcto del captcha   
 * 
 * @access static
 */
class PAFCaptcha{	
				
	function PAFCaptcha(){
		die("PAFCaptcha es una clase STATIC");
	}	
	
	
	/**
	 * Genera un captcha, devolviendo el nombre de la imagen de captcha (no la ruta de la imagen, dado que esa dependera del medio) 
	 * A su vez ha almacenado en sesion la frase de solucion de captcha
	 *
	 * @param String $rutaDestinoImagen
	 * @param Array $params Parametros de configuracion para el captcha
	 * @return String Nombre de la imagen creada, sin ruta
	 */
	function generarCaptcha($rutaDestinoImagen,$params = null){

		//parametros
		if($params == null || count($params) == 0){
			$params = PAFCaptcha::getParamsDefault();
		}
		
		// Opciones de la imagen del captcha
        $imageOptions = array(
            "font_size"        => intval($params["font_size"]),
            "font_path"        => $params["font_path"],
            "font_file"        => $params["font_file"],
            "text_color"       => $params["text_color"],
            "lines_color"      => $params["lines_color"],        	
            "background_color" => $params["background_color"]
        );

        // Opciones del captcha
        $options = array(
            "width" =>  intval($params["width"]),
            "height" => intval($params["height"]),        	
            "output" => "png",
            "imageOptions" => $imageOptions
        );
                
        // Generamos el objeto captcha (tipo Text image)
        $captcha = Text_CAPTCHA::factory("Image");
        $retval = $captcha->init($options);
        
        if (PEAR::isError($retval)) {
            PEAR::raiseError("Error initializing CAPTCHA: %s!",$retval->getMessage());            
        }		
    	
        // Creamos el string de captcha y lo guadamos en sesion
        $_SESSION["captcha"] = $captcha->getPhrase();
                            
        // Creamos la imagen de captcha
        $imagen = $captcha->getCAPTCHA();
        if (PEAR::isError($imagen)) {
            PEAR::raiseError("Error generating CAPTCHA: %s!",$imagen->getMessage());            
        }
        
        //guardamos la imagen
        $nombreImagen = $nombreImagen = md5(session_id()) . ".png";
                
		if (!function_exists("file_put_contents")) {	        
			//guardamos el fichero
			$file = fopen($rutaDestinoImagen.$nombreImagen, "w");	        
	        fwrite($file, $imagen);
	        //cerramos el fichero
	        fclose($file);	        
		}else{
			//llamamos a la funcion
			file_put_contents($rutaDestinoImagen.$nombreImagen, $imagen);
		}
        
		//devolvemos la ruta hacia la imagen creada
        return $nombreImagen;
		
	}
	
	/**
	 * Indica si hemos acertado con el captcha
	 *
	 * @param String $phrase Texto del usuario
	 * @param String $rutaDestinoImagen Imagen de destino, junto con su ruta absoluta. Se usa para eliminarla de disco
	 * @return unknown
	 */
	function comprobarCaptcha($phrase,$rutaDestinoImagen){
						
		$ok = false;		
		$sessionPhrase = $_SESSION["captcha"];
				
		if (!empty($phrase) && !empty($sessionPhrase) && is_string($phrase) && ($phrase == $sessionPhrase)) {
            //coincide
            $ok = true;
            
            //borramos de session
            unset($_SESSION["captcha"]);                        
        }
        
        //borramosla imagen si existe
        $nombreImagen = $nombreImagen = md5(session_id()) . ".png";        
        if($rutaDestinoImagen && $nombreImagen && is_file($rutaDestinoImagen.$nombreImagen)){
        	unlink($rutaDestinoImagen.$nombreImagen);
        }
        
        //devolvemos el resultado
        return $ok;
        
	}
	
	/**
	 * Genera un array de parametros para configurar el captcha
	 * Este metodo puede servir de ejemplo para que cada medio sepa las propiedades que necesita la funcion generarCaptcha
	 *
	 * @return array
	 */
	function getParamsDefault(){
		
		$params = array(
	            "font_size"        => 20,
	            "font_path"        => "/usr/share/ghostscript/fonts/",
	            "font_file"        => "a010013l.pfb",
	            "text_color"       => "#DDFF99",
	            "lines_color"      => "#CCEEDD",
	            "background_color" => "#555555",
	            "width" => 128,
	            "height" => 50	            
	        );
	        
		return $params; 
	}
	
}
?>
