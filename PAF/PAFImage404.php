<?
/**
 * Clase estatica para comprabar los 404 
 *
 * @access public
 * @package PAF
 */

class PAFImage404 {
	/**
	* Comprueba si el 404 es del tipo gif, jpg., etc..
	* y devuelve una 0.gif
	* 
	* @access public
	*/
	function chechUri($url){
		if (eregi("(\.gif|\.jpg|\.ico|\.swf|\.dll|\.css|\.jpeg|.\png|.\xml)$",$_SERVER[REQUEST_URI])){
			//de moemento no mandamos el 301
			//Header( "HTTP/1.1 301 Moved Permanently" );
			//Header( "Location: /comunes/images/0.gif" );
			
			# De esta formaa sale un 404
			Header( "Cache-Control: max-age=86400" );
			Header( "Content-type: image/jpeg");
			include ("PAF/PAFOnePixelOU.php");
			$pixel = new PAFOnePixelOU();
			echo $pixel->getOutput();
			die;
		}
	}
}
?>
