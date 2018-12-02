<?php

// *****************************************************************************
// Lenguaje: PHP
// Copyright 2006 Prisacom S.A.
//
// *****************************************************************************

require_once "PAF/PAFObject.php";

/**
 * Agrupa las funciones de codificado y decodificado de utf-8 a iso-8859-1.
 * Complementa a las de sistema porque hay determinados carácteres que no codifican correctamente
 *
 * @author Gustavo Ramos <gramos@prisacom.com>
 * @version $Revision: 1.1 $
 * @package PAF
 */
class PAFUtf8 extends PAFObject
{
    /**
     * Constructor de la clase PAFWriter.
     *
     * @access public
     * @param  string $errorClass Nombre de la clase de Error utilizada para el lanzamiento de errores.
     */
    function PAFUtf8($errorClass= null)
    {
        $this->PAFObject($errorClass);
    }
    
    /**
     * Método estático para codificar de iso-8859-1 a utf-8.
     *
     * @access public
     * @param string. Cadena original.
     * @return string. Cadena codificada.
     */
    function encode($str)
    {
	$map = array(
	   "\xc2\x80" => "\xe2\x82\xac", /* EURO SIGN */
	   "\xc2\x82" => "\xe2\x80\x9a", /* SINGLE LOW-9 QUOTATION MARK */
	   "\xc2\x83" => "\xc6\x92",     /* LATIN SMALL LETTER F WITH HOOK */
	   "\xc2\x84" => "\xe2\x80\x9e", /* DOUBLE LOW-9 QUOTATION MARK */
	   "\xc2\x85" => "\xe2\x80\xa6", /* HORIZONTAL ELLIPSIS */
	   "\xc2\x86" => "\xe2\x80\xa0", /* DAGGER */
	   "\xc2\x87" => "\xe2\x80\xa1", /* DOUBLE DAGGER */
	   "\xc2\x88" => "\xcb\x86",     /* MODIFIER LETTER CIRCUMFLEX ACCENT */
	   "\xc2\x89" => "\xe2\x80\xb0", /* PER MILLE SIGN */
	   "\xc2\x8a" => "\xc5\xa0",     /* LATIN CAPITAL LETTER S WITH CARON */
	   "\xc2\x8b" => "\xe2\x80\xb9", /* SINGLE LEFT-POINTING ANGLE QUOTATION */
	   "\xc2\x8c" => "\xc5\x92",     /* LATIN CAPITAL LIGATURE OE */
	   "\xc2\x8e" => "\xc5\xbd",     /* LATIN CAPITAL LETTER Z WITH CARON */
	   "\xc2\x91" => "\xe2\x80\x98", /* LEFT SINGLE QUOTATION MARK */
	   "\xc2\x92" => "\xe2\x80\x99", /* RIGHT SINGLE QUOTATION MARK */
	   "\xc2\x93" => "\xe2\x80\x9c", /* LEFT DOUBLE QUOTATION MARK */
	   "\xc2\x94" => "\xe2\x80\x9d", /* RIGHT DOUBLE QUOTATION MARK */
	   "\xc2\x95" => "\xe2\x80\xa2", /* BULLET */
	   "\xc2\x96" => "\xe2\x80\x93", /* EN DASH */
	   "\xc2\x97" => "\xe2\x80\x94", /* EM DASH */
	
	   "\xc2\x98" => "\xcb\x9c",     /* SMALL TILDE */
	   "\xc2\x99" => "\xe2\x84\xa2", /* TRADE MARK SIGN */
	   "\xc2\x9a" => "\xc5\xa1",     /* LATIN SMALL LETTER S WITH CARON */
	   "\xc2\x9b" => "\xe2\x80\xba", /* SINGLE RIGHT-POINTING ANGLE QUOTATION*/
	   "\xc2\x9c" => "\xc5\x93",     /* LATIN SMALL LIGATURE OE */
	   "\xc2\x9e" => "\xc5\xbe",     /* LATIN SMALL LETTER Z WITH CARON */
	   "\xc2\x9f" => "\xc5\xb8"      /* LATIN CAPITAL LETTER Y WITH DIAERESIS*/
	);
	
	return strtr(utf8_encode($str), $map);
    }
    
    /**
     * Método estático para decodificar de iso-8859-1 a utf-8.
     *
     * @access public
     * @param string. Cadena original.
     * @return string. Cadena decodificada.
     */
    function decode($str)
    {
	$map = array(
	   "\xe2\x82\xac" => "\xc2\x80", /* EURO SIGN */
	   "\xe2\x80\x9a" => "\xc2\x82", /* SINGLE LOW-9 QUOTATION MARK */
	   "\xc6\x92" => "\xc2\x83",     /* LATIN SMALL LETTER F WITH HOOK */
	   "\xe2\x80\x9e" => "\xc2\x84", /* DOUBLE LOW-9 QUOTATION MARK */
	   "\xe2\x80\xa6" => "\xc2\x85", /* HORIZONTAL ELLIPSIS */
	   "\xe2\x80\xa0" => "\xc2\x86", /* DAGGER */
	   "\xe2\x80\xa1" => "\xc2\x87", /* DOUBLE DAGGER */
	   "\xcb\x86" => "\xc2\x88",     /* MODIFIER LETTER CIRCUMFLEX ACCENT */
	   "\xe2\x80\xb0" => "\xc2\x89", /* PER MILLE SIGN */
	   "\xc5\xa0" => "\xc2\x8a",     /* LATIN CAPITAL LETTER S WITH CARON */
	   "\xe2\x80\xb9" => "\xc2\x8b", /* SINGLE LEFT-POINTING ANGLE QUOTATION */
	   "\xc5\x92" => "\xc2\x8c",     /* LATIN CAPITAL LIGATURE OE */
	   "\xc5\xbd" => "\xc2\x8e",     /* LATIN CAPITAL LETTER Z WITH CARON */
	   "\xe2\x80\x98" => "\xc2\x91", /* LEFT SINGLE QUOTATION MARK */
	   "\xe2\x80\x99" => "\xc2\x92", /* RIGHT SINGLE QUOTATION MARK */
	   "\xe2\x80\x9c" => "\xc2\x93", /* LEFT DOUBLE QUOTATION MARK */
	   "\xe2\x80\x9d" => "\xc2\x94", /* RIGHT DOUBLE QUOTATION MARK */
	   "\xe2\x80\xa2" => "\xc2\x95", /* BULLET */
	   "\xe2\x80\x93" => "\xc2\x96", /* EN DASH */
	   "\xe2\x80\x94" => "\xc2\x97", /* EM DASH */
	
	   "\xcb\x9c" => "\xc2\x98",     /* SMALL TILDE */
	   "\xe2\x84\xa2" => "\xc2\x99", /* TRADE MARK SIGN */
	   "\xc5\xa1" => "\xc2\x9a",     /* LATIN SMALL LETTER S WITH CARON */
	   "\xe2\x80\xba" => "\xc2\x9b", /* SINGLE RIGHT-POINTING ANGLE QUOTATION*/
	   "\xc5\x93" => "\xc2\x9c",     /* LATIN SMALL LIGATURE OE */
	   "\xc5\xbe" => "\xc2\x9e",     /* LATIN SMALL LETTER Z WITH CARON */
	   "\xc5\xb8" => "\xc2\x9f"      /* LATIN CAPITAL LETTER Y WITH DIAERESIS*/
	);
	return utf8_decode(strtr($str, $map));	
    }

}

?>
