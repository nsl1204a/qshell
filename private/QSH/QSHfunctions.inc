<?php
/**
 * Realizar el reemplazo de un simbolo en un texto por un valor
 * La sustitucion de simbolos consiste en cambiar los simbolos @@nombre@@ por valores
 */	
function replaceSymbol($fieldname, $fieldvalue='', $valueDelimiter='', $string){
	
	$leftDelimiter = $valueDelimiter;
	$rightDelimiter = $valueDelimiter;
	
	if($valueDelimiter == 'cdata'){
		$leftDelimiter = '<![CDATA[';
		$rightDelimiter = ']]>'; 
	}
	elseif($valueDelimiter == 'item'){
		$leftDelimiter = '';
		$rightDelimiter = '';
		if(strlen($fieldvalue) > 0)
			$fieldvalue = htmlentities($fieldvalue);
	}
	
	
	$symbolName = '@@' . $fieldname .  '@@';
	//$symbolValue = ' ' . $leftDelimiter . $fieldvalue . $rightDelimiter . ' ';
	$symbolValue =  $leftDelimiter . $fieldvalue . $rightDelimiter;
	
	
	$string = str_replace($symbolName, $symbolValue , $string);
	
	return $string;
	
}

function request2QueryString(){
	
	$queryString='?';
	foreach ($_REQUEST as $name=>$value) {
		if ($queryString != '?')
			$queryString .= '&';
			
		$queryString .= $name . '=' .$value;
	}
	return $queryString;
}
?>