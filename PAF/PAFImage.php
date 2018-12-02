<?php
// *****************************************************************************
// Lenguaje: PHP
// Copyright 2006 Prisacom S.A.
//
// *****************************************************************************
// 

require_once "PAF/PAFObject.php";
/**
* Se utilizará para hacer tratamiento de imágenes, reducciones y almacenamiento en disco 
* de las mismas
*
* @author Pablo Salvadó Paz 
* @version $Revision 2.0$
* @package PAF
*/

class PAFImage extends PAFObject
{

	/**
	* Atributo que contiene la anchura de la imagen en miniatura
	* @access private
	* @var Int thAnchura 
	*/
	var $thAnchura;
	
	/**
	* Atributo que contiene la altura de la imagen en miniatura
	* @access private
	* @var Int thAltura
	*/
	var $thAltura;

	/**
	* Atributo que contiene la calidad de la imagen en miniatura
	* @access private
	* @var Int thCalidad
	*/
	var $thCalidad = 75;

	/**
	* Atributo que contiene la ruta de origen de la imagen en miniatura
	* @access private
	* @var string thOrigen
	*/
	var $thOrigen;

	/**
	* Atributo que contiene la ruta de destino de la imagen en miniatura
	* @access private
	* @var string thDestino
	*/
	var $thDestino;

	/**
	* Atributo que define el prefijo que tienen las imagenes en miniatura
	* @access private
	* @var String prefijoImagenMiniatura
	*/
	var $prefijoImagenMiniatura;

	/**
	* Atributo que define si las imagenes deben ser marcadas
	* @access private
	* @var String bMarcar
	*/
	var $bMarcar = null;

	/**
	* Atributo que define la ruta de la imagen que se usará de marca
	* @access private
	* @var String textoMarca
	*/
	var $imagenMarca = "";

	/**
	* Atributo que define el texto que usa para la marca
	* @access private
	* @var String textoMarca
	*/
	var $textoMarca = "";

	/**
	* Atributo que define el color de texto en hexadecimal
	* @access private
	* @var String colorTexto
	*/
	var $colorTexto = "#000000";
	
	/**
	* Atributo que define el color de la sombra en hexadecimal
	* @access private
	* @var String colorSombra
	*/
	var $colorSombra = "#FFFFFF";

	/**
	* Atributo que define el color del borde en hexadecimal
	* @access private
	* @var String colorBorde
	*/
	var $colorBorde = "#000000";

	/**
	* Atributo que define la posicion horizontal de la marca de agua
	* @access private
	* @var String alineacionHorizontalMarca ( Posibles valores izquierda,centro,derecha )
	*/
	var $alineacionHorizontalMarca = "derecha";

	/**
	* Atributo que define la posicion vertical de la marca de agua
	* @access private
	* @var String alineacionVerticalMarca ( Posibles valores arriba,medio,abajo )
	*/
	var $alineacionVerticalMarca = "abajo";

	/**
	* Atributo que define si hay borde
	* @access private
	* @var bool grosorBorde
	*/
	var $ponerBorde = false;
	
	/**
	* Atributo que define el modo de poner la imagen cuando hay que estrecharla o ampliarla
	* @access private
	* @var string modo (Posibles valores deformar, ajustar)
	*/
	var $modoDeformacion = "deformar";
	
	/**
	* Atributo que define el color de fondo cuando la imagen se ajusta (modoDeformacion = ajustar);
	* @access private
	* @var string modo (Posibles valores "color en hexadecimal", 0)
	* Con el valor 0 se busca automaticamente un color basandose en las esquinas de la imagen
	*/
	var $colorFondoAjuste = 0;

	/**
	* Metodo contructor que se le pasa como parametros el nombre de la imagen y la altura a la que queremos reducirla
	*
	* @access private
	* @return true/false
	*/

	function PAFImage(){

	}//Fin método PAFImage

	/**
	* Metodo que invoca a reduce con los parametros de una imagen en miniatura
	*
	* @access public
	* @param
	* @return bool
	*/
	function guardarImagenMiniatura(){
		//chequeemos a ver si podemos hacer el thumbnail
		$thErrorReq = false;
		if(empty($this->thOrigen)){
			$PError = PEAR::raiseError("Debe especificar un origen");
			$thErrorReq = true;
			return $PError;
		}
		if(empty($this->thDestino)){
			$PError = PEAR::raiseError("Debe especificar un destino");
			$thErrorReq = true;
			return $PError;
		}
		
		if(!$thErrorReq){
			$propiedades = @getimagesize($this->thOrigen);
			// pequeña ñapa para comprobar que es una imagen de verdad
			if(empty($propiedades[0])){
				$PError = PEAR::raiseError("La imagen no es válida");
				$thErrorReq = true;
				return $PError;
			}
			if(!$thErrorReq){
				if(empty($this->thAnchura)){
					if(empty($this->thAltura)){
						$PError = PEAR::raiseError("Debe especificar al menos un valor de altura o anchura para crear la imagen en miniatura");
						$thErrorReq = true;
						return $PError;
					} else {
						$this->thAnchura = (($propiedades[0]*$this->thAltura)/$propiedades[1]);
					}
				} else {
					if(empty($this->thAltura)){
						$this->thAltura = (($propiedades[1]*$this->thAnchura)/$propiedades[0]);
					}
				}
				if(empty($this->thCalidad)){
					$PError = PEAR::raiseError("Debe especificar la calidad de la imagen en miniatura");
					$thErrorReq = true;
					return $PError;
				}
			}
			if(!$thErrorReq){
				$nArchivo = basename($this->thDestino);	
				$nPath = dirname($this->thDestino);
				$nExt = strtoupper(substr($nArchivo, -3));
				// vamos a ver que tipo de archivo quiere hacer
				// no hace falta pensar que puede ser un formato distinto ya que eso
				// ya lo comprobamos en establecerOrigen
				switch($propiedades[2]){
				case 1:
					if (!$orImg = @imagecreatefromgif($this->thOrigen)) {
						$PError = PEAR::raiseError("No se puede abrir el archivo de origen");
						$thErrorReq = true;
					}
					break;
				case 3:
					if (!$orImg = @imagecreatefrompng($this->thOrigen)) {
						$PError = PEAR::raiseError("No se puede abrir el archivo de origen");
						$thErrorReq = true;
					}
					break;
				case 2:
					if (!$orImg = @imagecreatefromjpeg($this->thOrigen)) {
						$PError = PEAR::raiseError("No se puede abrir el archivo de origen");
						$thErrorReq = true;
					}
					break;
				}
				if(!$thErrorReq){
					$thImg = @imagecreatetruecolor($this->thAnchura, $this->thAltura);
					if($this->modoDeformacion=="ajustar"){
						if($this->colorFondoAjuste=="0"){
							$colorFondo = $this->obtenerColorEsquinas(&$orImg,$propiedades[0],$propiedades[1]);
						} else {
							$colorFondo = $this->convertirHex2Dec($this->colorFondoAjuste);
						}
						$backgroundColor = imagecolorallocate($thImg, $colorFondo[0], $colorFondo[1], $colorFondo[2]);
						imagefilledrectangle($thImg,0, 0, $this->thAnchura, $this->thAltura,$backgroundColor);
						
						// hay que ver que offsets tenemos
						$diffAnchura = $this->thAnchura-$propiedades[0];
						$diffAltura = $this->thAltura-$propiedades[1];
						if($diffAnchura<$diffAltura){
							$ajusteAncho = $this->thAnchura;
							$ajusteAlto = (($propiedades[1]*$this->thAnchura)/$propiedades[0]);
						} else {
							$ajusteAncho = (($propiedades[0]*$this->thAltura)/$propiedades[1]);
							$ajusteAlto = $this->thAltura;
						}
						$offsetAnchura = $this->thAnchura - $ajusteAncho;
						$offsetAltura = $this->thAltura - $ajusteAlto;
						imagecopyresampled($thImg,$orImg,($offsetAnchura/2),($offsetAltura/2),0,0,$ajusteAncho,$ajusteAlto,$propiedades[0],$propiedades[1]);
					} elseif($this->modoDeformacion=="recortar") {
						if($this->colorFondoAjuste=="0"){
							$colorFondo = $this->obtenerColorEsquinas(&$orImg,$propiedades[0],$propiedades[1]);
						} else {
							$colorFondo = $this->convertirHex2Dec($this->colorFondoAjuste);
						}
						$backgroundColor = imagecolorallocate($thImg, $colorFondo[0], $colorFondo[1], $colorFondo[2]);
						/*
						if(($propiedades[0]-$this->recorteX) < ($this->recorteW)){
							$medidaWReal = ($propiedades[0]-$this->recorteX);
						} else {
							$medidaWReal = $this->recorteW;
						}
						if(($propiedades[1]-$this->recorteY) < ($this->recorteH)){
							$medidaHReal = ($propiedades[1]-$this->recorteY);
						} else {
							$medidaHReal = $this->recorteH;
						}
						if($medidaWReal<$this->thAnchura){
							$startXPos = (($medidaWReal-$this->thAnchura)/2);
						} else {
							$startXPos = 0;
						}
						if($medidaHReal<$this->thAltura){
							$startYPos = (($medidaHReal-$this->thAltura)/2);
						} else {
							$startYPos = 0;
						}
						*/
						imagefilledrectangle($thImg,0, 0, $this->thAnchura, $this->thAltura,$backgroundColor);
						imagecopyresampled($thImg,$orImg,0,0,$this->recorteX,$this->recorteY,$this->recorteW,$this->recorteH,$this->recorteW,$this->recorteH);
					} else {
						imagecopyresampled($thImg,$orImg,0,0,0,0,$this->thAnchura,$this->thAltura,$propiedades[0],$propiedades[1]);
					}

					if($this->bMarcar!=null){
						$this->marcarImagen(&$thImg);
					}
					
					if($this->ponerBorde){
						$this->bordear(&$thImg);
					}

					switch($nExt){
					case "GIF":
						if (!$reImg = @imagegif($thImg,$this->thDestino,$this->thCalidad)) {
							$PError = PEAR::raiseError("No se puede escribir el archivo de destino");
							$thErrorReq = true;
						}
						break;
					case "PNG":
						if (!$reImg = @imagepng($thImg,$this->thDestino,$this->thCalidad)) {
							$PError = PEAR::raiseError("No se puede escribir el archivo de destino");
							$thErrorReq = true;
						}
						break;
					case "PEG": // JPEG
					case "JPG":
						if (!$reImg = @imagejpeg($thImg,$this->thDestino,$this->thCalidad)) {
							$PError = PEAR::raiseError("No se puede escribir el archivo de destino");
							$thErrorReq = true;
						}
						break;
						if(!$thErrorReq){
							@chmod($this->thDestino,0777);
							return true;
						}
					}
				}
			}
		}	
	}

	/**
	* Metodo que marca la imagen 
	*
	* @access public
	* @param Integer $altura Altura de la nueva imagen
	* @return true  Devuelve verdadero o PearError si no es un número
	*/
	function marcarImagen(&$imagen){
		switch($this->bMarcar){
		case "texto":
			$margen = 5;
			$numFuente = 2;
			// añado unos pixeles para que no quede totalmente pegado al borde
			$anchoCadena = imagefontwidth($numFuente)*strlen($this->textoMarca);
			$altoCadena = imagefontheight($numFuente);
			// reservamos los colores que necesitamos
			$ColorTexto = $this->convertirHex2Dec($this->colorTexto);
			$cTexto = @imagecolorallocate($imagen, $ColorTexto[0], $ColorTexto[1], $ColorTexto[2]);
			$ColorSombra = $this->convertirHex2Dec($this->colorSombra);
			$cSombra = @imagecolorallocate($imagen, $ColorSombra[0], $ColorSombra[1], $ColorSombra[2]);
			switch($this->alineacionVerticalMarca){
			case "arriba":
				$vPos = $margen;
				break;
			case "medio":
				$vPos = (($this->thAltura/2)-($altoCadena/2));
				break;
			case "abajo":
				$vPos = ($this->thAltura-($altoCadena+$margen));
				break;
			}
			switch($this->alineacionHorizontalMarca){
			case "izquierda":
				$hPos = $margen;
				break;
			case "centro":
				$hPos = (($this->thAnchura/2)-($anchoCadena/2));
				break;
			case "derecha":
				$hPos = $this->thAnchura-($anchoCadena+$margen);
				break;
			}
			imagestring($imagen, $numFuente, $hPos+1, $vPos+1, $this->textoMarca, $cSombra);
			imagestring($imagen, $numFuente, $hPos, $vPos, $this->textoMarca, $cTexto);
			
			break;
		case "grafico":
			$maPp = array();
			if (!$maImg = @imagecreatefrompng($this->imagenMarca)) {
				$PError = PEAR::raiseError("No se puede abrir el archivo para la marca de agua");
				$thErrorReq = true;
			} else {
				$maPp = @getimagesize($this->imagenMarca);
				if($maPp[2]!=3){
					$PError = PEAR::raiseError("La marca de agua debe ser un PNG");
					$thErrorReq = true;
				}
			}
			if(!$thErrorReq){
				switch($this->alineacionVerticalMarca){
				case "arriba":
					$vPos = 0;
					break;
				case "medio":
					$vPos = (($this->thAltura/2)-($maPp[1]/2));
					break;
				case "abajo":
					$vPos = ($this->thAltura-$maPp[1]);
					break;
				}
				switch($this->alineacionHorizontalMarca){
				case "izquierda":
					$hPos = 0;
					break;
				case "centro":
					$hPos = (($this->thAnchura/2)-($maPp[0]/2));
					break;
				case "derecha":
					$hPos = $this->thAnchura-$maPp[0];
					break;
				}
				imagecopyresampled($imagen,$maImg,$hPos,$vPos,0,0,$maPp[0],$maPp[1],$maPp[0],$maPp[1]);
			}
			break;
		}
	}

	/**
	* Metodo que pinta un borde en la imagen 
	*
	* @access private
	* @param Integer $grosor Establece el grosor del borde
	*/
	function bordear(&$imagen){
		$ColorBorde = $this->convertirHex2Dec($this->colorBorde);
		$cBorde = @imagecolorallocate($imagen, $ColorBorde[0], $ColorBorde[1], $ColorBorde[2]);
		imagerectangle($imagen,0,0,$this->thAnchura-1,$this->thAltura-1,$cBorde);
	}

	/**
	* Metodo que establece la altura 
	*
	* @access public
	* @param Integer $altura Altura de la nueva imagen
	* @return true  Devuelve verdadero o PearError si no es un número
	*/

	function establecerBorde($color=true){
		if(is_bool($color)){
			$this->ponerBorde = $borde;
		} else {
			if(strlen($color)==7){
				$this->colorBorde = $color;
			}
			$this->ponerBorde = true;
		}
	}
	
	function obtenerColorEsquinas(&$imagen,$anchura,$altura){
		$esq[] = imagecolorat($imagen,0,0);
		$esq[] = imagecolorat($imagen,$anchura-1,0);
		$esq[] = imagecolorat($imagen,0,$altura-1);
		$esq[] = imagecolorat($imagen,$anchura-1,$altura-1);
		$colorPred = null;
		$maxReps = 0;
		$reps = array();
		$colors = array();
		for($i=0;$i<count($esq);$i++){
			if(!imagecolorstotal($imagen)){
				$a = ($esq[$i] & 0x7F000000) >> 24;
				$r = ($esq[$i] & 0xFF0000) >> 16;
				$g = ($esq[$i] & 0x00FF00) >> 8;
				$b = ($esq[$i] & 0x0000FF);
			} else {
				$tmprgb = imagecolorsforindex($imagen,$esq[$i]);
				$a = $tmprgb['alpha'];
				$r = $tmprgb['red'];
				$g = $tmprgb['green'];
				$b = $tmprgb['blue'];
			}
			/*
			$r = ($esq[$i] >> 16) & 0xFF;
			$g = ($esq[$i] >> 8) & 0xFF;
			$b = $esq[$i] & 0xFF;
			*/
			$color = array($r,$g,$b);
			$cColor = $r."-".$g."-".$b;
			if(in_array($cColor,$reps)){
				$reps[$cColor]++; 
				if($reps[$cColor]>$maxReps){
					$colorPred = $cColor;
					$maxReps = $reps[$cColor];
				}
			} else {
				$colors[$cColor] = $color;
				$reps[$cColor] = 1;
				if($reps[$cColor]>$maxReps){
					$colorPred = $cColor;
					$maxReps = $reps[$cColor];
				}
			}
		}
		return $colors[$colorPred];
	}

	/**
	* Metodo que establece la altura 
	*
	* @access public
	* @param Integer $altura Altura de la nueva imagen
	* @param Bool $clear Limpia la variable thAnchura, se suele utilizar cuando se realiza una nueva redimension
	* @return true  Devuelve verdadero o PearError si no es un número
	*/

	function establecerAltura($altura,$clear=false){
		if(is_numeric($altura)){
			$this->thAltura = $altura;
			if($clear){
				$this->thAnchura="";
				$this->establecerModoDeformeDeformar();
				//$this->establecerModoDeforme(); // por defecto
			}
		} else {
			$PError = PEAR::raiseError("La altura debe ser un valor numérico");
			return($PError);
		}
		
	} 
	
	/**
	* Metodo que establece la anchura 
	*
	* @access public
	* @param Integer $anchura Anchura de la nueva imagen
	* @param Bool $clear Limpia la variable thAnchura, se suele utilizar cuando se realiza una nueva redimension
	* @return true  Devuelve verdadero o PearError si no es un número
	*/

	function establecerAnchura($anchura,$clear=false){
		if(is_numeric($anchura)){
			$this->thAnchura = $anchura;
			if($clear){
				$this->thAltura="";
			}
		} else {
			$PError = PEAR::raiseError("La anchura debe ser un valor numérico");
			return($PError);
		}
	}

	/**
	* Metodo que establece la calidad de la imagen 
	*
	* @access public
	* @param Integer $calidad Calidad de la nueva imagen
	* @return true  Devuelve verdadero o PearError si no es un número
	*/

	function establecerCalidad($calidad){
		if(is_numeric($calidad)){
			$this->thCalidad = $calidad;
		} else {
			$PError = PEAR::raiseError("La calidad debe ser un valor numérico");
			return($PError);
		}
	}

	/**
	* Metodo que establece la alineacion horizontal de la marca de agua
	*
	* @access public
	* @param String $AHM Alineacion Horizontal de la Marca de agua
	*/

	function establecerAlineacionHorizontalMarca($AHM){
		switch($AHM){
		case "izquierda":
			$this->alineacionHorizontalMarca = "izquierda";
			break;
		case "centro":
			$this->alineacionHorizontalMarca = "centro";
			break;
		case "derecha":
			$this->alineacionHorizontalMarca = "derecha";
			break;
		default:
			$this->alineacionHorizontalMarca = "derecha";
			break;
		}
	}

	/**
	* Metodo que establece la alineacion vertical de la marca de agua
	*
	* @access public
	* @param String $AVM Alineacion Vertical de la Marca de agua
	*/

	function establecerAlineacionVerticalMarca($AVM){
		switch($AVM){
		case "arriba":
			$this->alineacionVerticalMarca = "arriba";
			break;
		case "medio":
			$this->alineacionVerticalMarca = "medio";
			break;
		case "abajo":
			$this->alineacionVerticalMarca = "abajo";
			break;
		default:
			$this->alineacionVerticalMarca = "abajo";
			break;
		}
	}

	/**
	* Metodo que establece el prefijo de la imagen
	*
	* @access public
	* @param String $prefijo Prefijo de la nueva imagen
	* @return true  Devuelve verdadero
	*/

	function establecerPrefijo($prefijo){
		$this->prefijoImagenMiniatura = $prefijo;
	}
	
	/**
	* Metodo que establece si la imagen debe ser marcada 
	*
	* @access public
	* @param String $v Tipo de marca ("grafico","texto",null)
	* @return true  Devuelve verdadero o PearError si la opcion no esta definida
	*/

	function establecerMarca($v,$cadena=""){
		switch($v){
		case null:
			$this->bMarcar = null;
			break;
		case "grafico":
			$this->bMarcar = "grafico";
			$this->imagenMarca = $cadena;
			break;
		case "texto":
			$this->bMarcar = "texto";
			$this->textoMarca = $cadena;
			break;
		default:
			$PError = PEAR::raiseError("La opcion de marca no es válida");
			return($PError);
			break;
		}
		
	}

	/**
	* Metodo que establece los colores de primer y segundo plano.
	* por defecto son negro sobre blanco.
	*
	* @access public
	* @param String $colorPrimario valor del color del texto en hexadecimal (ej: #FF0000 para rojo)
	* @param String $colorSecundario valor del color de la sombra en hexadecimal (ej: #FF0000 para rojo)
	*/

	function establecerColores($colorPrimario="#000000",$colorSecundario="#FFFFFF"){
		if(strlen($colorPrimario)==7){
			$this->colorTexto = $colorPrimario;
		}
		if(strlen($colorSecundario)==7){
			$this->colorSombra = $colorSecundario;
		}
	}
	
	/**
	* Metodo que establece el color con el que se rellena el fondo cuando 
	* haces una redimension con la opcion establecerModoDeforme = ajustado
	*
	* @access public
	* @param String|Int $color valor del color del texto en hexadecimal (ej: #FF0000 para rojo) o 0 para que lo calcule de las esquinas
	*
	* Sería interesante que se pueda elegir la forma de obtener este color, para los logos lo que es necesario es el color de las esquinas
	* y ese método voy a implementar, pero para otros usos puede ser necesario cambiar el método
	*/
	
	function establecerColorParaAjustado($color=0){
		$this->colorFondoAjuste = $color;
	}

	/**
	* Metodo que establece el destino de la imagen 
	*
	* @access public
	* @param String $destino Destino de la imagen
	* @return true  Devuelve verdadero o PearError si el fichero existe
	*/

	function establecerDestino($destino,$sobreescribir=false){
		if(!file_exists($destino)){
			$this->thDestino = $destino;
		} else {
			if($sobreescribir){
				$this->thDestino = $destino;
			} else {
				$PError = PEAR::raiseError("El destino no puede existir");
				return($PError);
			}
		}
	}

	/**
	* Metodo que establece el origen de la imagen
	*
	* @access public
	* @param String $origen Origen de la imagen
	* @return true  Devuelve verdadero o PearError si el fichero no existe o el formato no es válido
	*/

	function establecerOrigen($origen){
		if(file_exists($origen)){
			$this->thOrigen = $origen;
			$propiedades = @getimagesize($origen);
			// solo permito GIF, JPG, PNG
			if(($propiedades[2]!=1) AND ($propiedades[2]!=2) AND ($propiedades[2]!=3)){
				$PError = PEAR::raiseError("El formato de la imagen no es válido");
				return($PError);
			}
		} else {
			$PError = PEAR::raiseError("El origen debe existir");
			return($PError);
		}
	}

	/**
	* Metodo que elimina el origen 
	*
	* @access public
	* @param String $origen Origen de la imagen
	*/
	
	function limpiarOrigen(){
		if(!empty($this->thOrigen)){
			unlink($this->thOrigen);
		}
	}
	
	/**
	* Metodo que traduce html en rgb
	*
	* @access private
	* @param String $hex
	* @return Array devuelve un array con los valores para R G B
	*/
	
	function convertirHex2Dec($hex){
		$hex = str_replace("#","",$hex);
		$rgb[0] = hexdec(substr($hex,0,2));
		$rgb[1] = hexdec(substr($hex,2,2));
		$rgb[2] = hexdec(substr($hex,-2));
		return $rgb;
	}
	
	/**
	* Método para establecer el modo en el que se van a tratar las imagenes 
	* en las que se especifique alto y ancho para la redimension.
	*  @modo string "deformar", "ajustar" o "recortar"
	*/
	
	function establecerModoDeforme($modo){
		if(!empty($modo)){
			$this->modoDeformacion = $modo;
		}
	}
	
	function establecerModoDeformeDeformar(){
		$this->establecerModoDeforme("deformar");
	}
	
	function establecerModoDeformeAjustar(){
		$this->establecerModoDeforme("ajustar");
	}
	
	function establecerModoDeformeRecortar($rX=null,$rY=null,$rW=null,$rH=null){
		if(!is_null($rX) && (!is_null($rY)) && (!is_null($rW)) && (!is_null($rH))){
			$this->recorteX = $rX;
			$this->recorteY = $rY;
			$this->recorteW = $rW;
			$this->recorteH = $rH;
			$this->establecerModoDeforme("recortar");
		}
	}

}//Fin clase
?>
