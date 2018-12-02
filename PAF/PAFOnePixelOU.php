<?
// *****************************************************************************
// Lenguaje: PHP
//
// Copyright:
// Ulrich Babiak, Koeln 1999/11/30 
// (color added by Thomas Schüßler, Bonn 2000/7/2) 
// if # is in front of the hex-string
// 
// ---------
// ChangeLog
// ---------
// $Log: PAFOnePixelOU.php,v $
// Revision 1.6  2004/06/01 14:38:45  ljimenez
// actualizadas las versiones de desarrollo con las de producción
//
// Revision 1.4  2003/12/18 12:23:47  vsanz
// Arreglos de parÃ¡metros en sprintf
//
// Revision 1.3  2002/11/06 13:45:50  fjalcaraz
// Espacios
//
// Revision 1.2  2002/10/25 19:07:11  vsanz
// Ahora si envía el pixel
//
// *****************************************************************************

require_once 'PAF/PAFOutput.php';

/**
 * Clase devuelve el contenido de un gif para el color que se pasa como 
 * parámetro. si es null, se escribe un pixel transparente.
 *
 * @author Virgilio Sanz <vsanz@prisacom.com>
 * @version $Revision: 1.6 $
 * @access public
 * @package PAF
 */
class PAFOnePixelOU extends PAFOutput {
    /**
     * Código rgb del color del pixel.
     *
     * @access private
     * @var integer
     */
    var $color = null;
    
    function PAFOnePixelOU($color=null, $errorClass=null) {
        $this->PAFOutput($errorClass);
        $this->color = $color;
    }
    
    /**
     * Devuelve el contenido de un gif para un color.
     *
     * @access public
     * @returns string un pixel gif del color que pasemos
     */
    function getOutput() {
        if($this->color) {
            // Calculamos el rgb
            $rgb = str_replace("#", "", $this->color);  
            $r = hexdec(substr ($rgb, 0,2)); 
            $g = hexdec(substr ($rgb, 2,2)); 
            $b = hexdec(substr ($rgb, 4,2)); 
            return(sprintf("%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c", 71,73,70,56,57,97,1,0,1,0,128,0,0,$r,$g,$b,0,0,0,44,0,0,0,0,1,0,1,0,0,2,2,68,1,0,59));  
        } else { 
            return(sprintf("%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c", 71,73,70,56,57,97,1,0,1,0,128,255,0,192,192,192,0,0,0,33,249,4,1,0,0,0,0,44,0,0,0,0,1,0,1,0,0,2,2,68,1,0,59));  
        }
    }
}
