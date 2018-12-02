<?php
/**
 * Plantillas xml para resultados
 */
$qsh_xml_template = array(
	'ParametroI' => '<ParametroI>' .
	 	'<TipoDato>@@itipdat@@</TipoDato>' .
		'<Codigo>@@icodpar@@</Codigo>' . 
		'<Nombre>@@inompar@@</Nombre>' .
		'<Valor>@@ivalpar@@</Valor>' .
		'</ParametroI>',
	
	'ParametroO' => '<ParametroO>' .
	 	'<TipoDato>@@otipdat@@</TipoDato>' . 
		'<Codigo>@@ocodpar@@</Codigo>' .
		'<Nombre>@@onompar@@</Nombre>' .
		'<Valor>@@ovalpar@@</Valor>' .
		'</ParametroO>',
	
	'Cabecera' => '<Cabecera>' .
	 	'@@cabcol@@' . 
		'</Cabecera>',
	
	'Columna' => '<Columna>' .
	 	'<Nombre>@@ncol@@</Nombre>' .
		'<Valor>@@valor@@</Valor>' .
		'<Enlace>@@enlace@@</Enlace>' .
		'</Columna>',
	
	'FilaResultado' => '<FilaResultado>' . 
		'@@Columnas@@' .
		'</FilaResultado>',
	
	'Comando' => '<Bloque>' .
	  	'<CodBloque>@@codblq@@</CodBloque>' .
		'<DesBloque>@@desblq@@</DesBloque>' . 
		'<Mensaje>@@msgblq@@</Mensaje>' .
		'<Cambios>@@cantid@@</Cambios>' .
		'<Filas>@@filres@@</Filas>' . 
		'@@ParametrosI@@' .
		'@@ParametrosO@@' .
		'@@Cabeceras@@' .
		'@@FilasResultado@@' .
		'</Bloque>',
	
	'xslPI' => '<?xml-stylesheet type="text/xsl" href="@@xslPath@@" ?>',
	
	'QSResultados' => '<?xml version="1.0" encoding="ISO-8859-1"?>' .
	   '@@xslPI@@' . 
	   '<QSResultados>' .
	   '<CodAplicacion>@@codapl@@</CodAplicacion>' .
	   '<DesAplicacion>@@desapl@@</DesAplicacion>' .
	   '<CodFuncion>@@codfun@@</CodFuncion>' .
	   '<DesFuncion>@@desfun@@</DesFuncion>' .
	   '<MomentoEx>@@momex@@</MomentoEx>' .
	   '<Usuario>@@usuar@@</Usuario>' .
	   '<Mensaje>@@msgpro@@</Mensaje>'.
		'@@Comandos@@' .
	   '</QSResultados>'
);
?>