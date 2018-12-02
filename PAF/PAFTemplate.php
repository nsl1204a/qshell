<?php

// *****************************************************************************
// Lenguaje: PHP
// Copyright 2002 Prisacom S.A.
// ---------
// ChangeLog
// ---------
// $Log: PAFTemplate.php,v $
// Revision 1.39  2008/07/29 12:25:09  jgomez
// Vuelta atr�s de los filtros de utf8 (ya exist�a encode:utf8) y a�adido escape:utf8_decode por
// homogeneizar la clase.
//
// Revision 1.38  2008/07/29 07:44:16  jgomez
// Incluidos dos filtros nuevos de variable (utf8encode y utf8decode).
//
// Revision 1.37  2007/08/01 11:23:59  fjalcaraz
// debug_backtrace ante errores
//
// Revision 1.36  2007/08/01 11:23:02  fjalcaraz
// Templates de length 0
//
// Revision 1.35  2006/11/10 15:38:19  fjalcaraz
// filtro strip_tags
//
// Revision 1.34  2006/06/16 17:03:59  fjalcaraz
// *** empty log message ***
//
// Revision 1.33  2006/06/16 16:46:58  fjalcaraz
// existsVar y existsVarBlock
//
// Revision 1.32  2005/10/19 15:13:58  fjalcaraz
// *** empty log message ***
//
// Revision 1.31  2005/10/19 14:44:58  fjalcaraz
// *** empty log message ***
//
// Revision 1.30  2005/10/19 12:18:09  fjalcaraz
// Nuevo compilador
//
// Revision 1.29  2005/10/19 09:38:20  fjalcaraz
// *** empty log message ***
//
// Revision 1.28  2005/08/25 09:40:19  ljimenez
// modificado el m�todo parseBlock para incluir un par�metro que realiza la inicializaci�n o no del bloque
//
// Revision 1.27  2005/08/24 15:57:21  ljimenez
// Inicializa el hash de variables cuando termina de parsear un bloque
//
// Revision 1.26  2005/03/11 09:18:25  fjalcaraz
// Tratamiento de fechas nulas en modificadores
//
// Revision 1.25  2005/02/24 13:39:07  fjalcaraz
// Inicializacion del $this->vars en compilacion.
// Facilita el saber externamente las variables definidas en un bloque.
//
// Revision 1.24  2005/02/04 17:05:43  fjalcaraz
// *** empty log message ***
//
// Revision 1.23  2004/08/31 11:43:59  fjalcaraz
// Mejora en el report de errores
//
// Revision 1.22  2004/05/12 11:43:53  fjalcaraz
// Escape en separadores de modificadores y par�metros de variables
//
// Revision 1.21  2004/05/04 09:37:11  fjalcaraz
// Arreglo de la gestion del separador de Modificadores
//
// Revision 1.19  2004/05/03 10:57:59  fjalcaraz
// Modificadores de Variables
//
// Revision 1.17  2004/04/13 16:16:12  fjalcaraz
// Mejora en los mensajes ante error
//
// Revision 1.16  2004/04/13 11:46:11  fjalcaraz
// Error de variable no reconocida dentro de comentario
//
// Revision 1.15  2004/04/13 11:28:32  fjalcaraz
// *** empty log message ***
//
// Revision 1.14  2004/04/13 08:35:45  fjalcaraz
// Nuevo Compilador
//
// Revision 1.13  2004/04/01 11:14:35  fjalcaraz
// Se elimina el timePre=0
//
// Revision 1.12  2003/11/27 18:44:23  fjalcaraz
// @ delante de filemtime para evitar warnings
//
// Revision 1.11  2003/09/23 09:16:43  agomariz
// Asignaci�n duplicada
//
// Revision 1.10  2003/09/04 14:49:41  agomariz
// Quitamos el \n solo en los bloques con mas de una linea
//
// Revision 1.9  2003/08/27 13:54:54  agomariz
// Suprimimos el \n al final de los bloques
//
// Revision 1.8  2002/09/20 08:28:36  fjalcaraz
// Uso de la funcion correcta para captura del UID del proceso
//
// Revision 1.7  2002/09/06 11:41:55  agomariz
// Al generar los pres hemos puesto el uid para no pisarlos
//
// Revision 1.6  2002/08/26 11:19:55  fjalcaraz
// *** empty log message ***
//
// Revision 1.5  2002/08/21 16:56:54  scruz
// Arreglo en el m�todo printResult() (antes no parseaba)
//
// Revision 1.4  2002/07/17 15:02:05  sergio
// Formaci�n del path completo del fichero de template antes de hacer nada.
//
// Revision 1.3  2002/05/21 09:55:16  sergio
// Arreglo moment�neo a expensas de comprender un poco mejor el m�todo
// getResult().
//
// Revision 1.2  2002/05/10 15:51:52  sergio
// Modificaciones en la documentaci�n general de la clase y los autores.
//
// *****************************************************************************

  require_once "PAF/PAFObject.php";

/**
  * @const CLASS_PAFTEMPLATE Constante con el identificador �nico de clase.
  */
define ("CLASS_PAFTEMPLATE", 9);

/**
  * @const DEBUG_MODE Constante para el modo de DEBUG ver constructor.
  * Puede tomas los valores 0 | 1.
  */
define ("DEBUG_MODE", 0);

/**
  * Clase para el manejo de templates.
  * Esta clase proporciona un m�todo de saber qu� templates se est�n recompilando.
  * Para ello existe el modo debug controlado por el atributo $debug.
  * El modo de debug a 1 saca a fichero un listado de las templates que se est�n
  * compilando, es decir, aquellas cuyos fichero de template y pre no se encuentran sincronizados.
  * De utilidad para Sistemas. Para cambiarlo solo hay que cambiar el valor de la constante "DEBUG_MODE" definida
  * arriba.
  *
  * @author Gustavo Nu�ez <gnunez@prisacom.com>,Alfonso Gom�riz <agomariz@prisacom.com>,Sergio Cruz <scruz@prisacom.com>
  * @version $Revision: 1.39 $
  * @package PAF
  */
class PAFTemplate extends PAFObject {

    /**
      * Atributo que contiene el nombre y ruta del fichero TPL.
      *
      * @access private
      * @var string
      */
    var $nameTemplate;

    /**
      * Atributo que contiene el nombre y ruta del fichero PRE (precompilado de templates).
      *
      * @access private
      * @var string
      */
    var $namePrecompile;

    /**
      * Atributo que contiene el bloque, la variable y el valor a parsear a la template.
      *
      * @access private
      * @var string
      */
    var $vars;

    /**
      * Atributo que contiene el resultado del parseo de las variables en la template.
      *
      * @access private
      * @var string
      */
    var $result;

    /**
      * Atributo que contiene el contenido del fichero TPL.
      *
      * @access private
      * @var string
      */
    var $content;

    /**
      * Atributo que contiene bloques, variables y su posicion dentro del fichero TPL.
      *
      * @access private
      * @var array
      */
    var $bloque;

    /**
      * Constructor.
      *
      * @access public
      * @param string $nameTpl Nombre del fichero TPL.
      * @param string $pathTpl Path del fichero TPL.
      * @param string $pathPre Path del fichero PRE.
      * @return mixed Un nuevo Objeto PAFTemplate si no se produce ning�n error o un PEAR:Error en caso contrario.
      *
      */
    function PAFTemplate ($nameTpl, $pathTpl = "." , $pathPre = ".", $errorClass = null){

        $this->PAFObject ($errorClass); // Llamada al constructor de la clase padre

        $pathTpl = $this->fullPath ($pathTpl);
        $pathPre = $this->fullPath ($pathPre);
        $pathSpt = $this->fullPath (__FILE__);

        if (!is_file ($pathTpl."/".$nameTpl)) {

            $this = PAFTemplate::raiseError("No existe el fichero".$pathTpl."/".$nameTpl.".<br>");
            return $this;
        }

        if (!is_dir($pathTpl)) {
        	//TODO: puesto esto para entorno local
			mkdir_recursive($pathTpl);
            //$this = PAFTemplate::raiseError("No existe el directorio de template $pathTpl.<br>");
            //return $this;
        }

        if (!is_dir($pathPre)) {

            $this = PAFTemplate::raiseError("No existe el directorio de precompilado $pathPre.<br>");
            return $this;
        }

        $pos_punto = strrpos ($nameTpl, '.');
	//$namePre = substr($nameTpl,0,$pos_punto).posix_getuid().".pre";
	$namePre = substr($nameTpl,0,$pos_punto).rand(0,99999999).".pre";

        $this->nameTemplate = $pathTpl."/".$nameTpl;
        $this->namePrecompile = $pathPre."/".$namePre;

        $timeTpl = @filemtime($this->nameTemplate);
        $timePre = @filemtime($this->namePrecompile);
	$timeSpt = @filemtime($pathSpt);

        // Si la tpl es m�s moderna o ha cambiado la version de esta lib
        if (($timeTpl > $timePre) || ($timeSpt > $timePre))
        {
            if ( PEAR::isError ($retValue = $this->compiler()) )
            {
                $this= $retValue;
                return $this;
            }

        }
        else
        {
            include ($this->namePrecompile);
        }

        $this->result=NULL;
    }

    /**
      * M�todo estatico para recuperar el identificador de la clase.
      *
      * @access public
      * @return int Identificador unico de la clase.
      */
    function getClassType () {

        return CLASS_PAFTEMPLATE;
    }

    /**
      * M�todo estatico que retorna el nombre de la clase.
      *
      * @access public
      * @return string Nombre de la clase.
      */
    function getClassName () {

        return "PAFTemplate";
    }

    /**
      * M�todo de consulta para determinar si una clase es de un tipo determinado.
      * Reimplementado de PAFObject.
      *
      * @access public
      * @param int $tipo Numero entero con el codigo de la clase por el que queremos preguntar.
      * @return boolean
      */
    function isTypeOf ($tipo) {

        return ( (PAFTemplate::getClassType ($tipo) == $tipo) || PAFObject::isTypeOf ($tipo));
    }


    /**
      * M�todo que comprueba la existencia del path correcto de acceso a un fichero.
      *
      * @access private
      * @param string $path Path del fichero.
      * @return mixed El path correcto o false en caso de no existir.
      */
    function fullPath ($path) {

        if ($path[0] == '/' || $path[0] == '.' || $path[0] == '\\' || file_exists ($path))
        {
            return $path;
        }

        $incPath = ini_get ("include_path");
        // Seg�n el SO sea windows o unix, el separador ser� ; o :
        if (strtoupper(substr(PHP_OS, 0, 3) == "WIN"))
        {
            $auxIncPath = explode (";", $incPath);
        }
        else
        {
            $auxIncPath = explode (":", $incPath);
        }

        $count = count ($auxIncPath);

        for ($i = 0; $i < $count; $i++) {

            $filePath = $auxIncPath[$i]."/".$path;

            if (file_exists ($filePath))
                 return $filePath;
        }

        return false;
    }


    /**
      * M�todo que almacena en un array valores del conjunto bloque, variable.
      *
      * @access public
      * @param string $varName Nombre de la variable a parsear.
      * @param string $value Valor de la variable a parsear.
      * @param string $nameBlock Nombre del bloque de parseo.
      */
    function setVar ($varName, $value = "")
    {
        $this->setVarBlock("MAIN", $varName, $value);
    }


    /**
      * M�todo que almacena en un array valores del conjunto bloque, variable a traves de setVar.
      * @access public
      * @param string $nameBlock Nombre del bloque de parseo.
      * @param string $varName Nombre de la variable a parsear.
      * @param string $value Valor de la variable a parsear.
      */
    function setVarBlock ($nameBlock, $varName, $value = "")
    {
        if (!is_array ($varName)) {

            if (!empty ($varName))
                $this->vars[$nameBlock][$varName] = $value;
        }
        else {

            reset ($varName);

            while(list ($key, $value) = each($varName)) {
                if (!empty ($key))
                    $this->vars[$nameBlock][$key] = $value;
            }
        }
    }

    /**
      * M�todo que permite comprobar la existencia de una variable
      * @access public
      * @param string $nameBlock Nombre del bloque de parseo.
      * @param string $varName Nombre de la variable a parsear.
      */
    function existsVar($varName)
    {
    	return $this->existsVarBlock("MAIN", $varName);
    }

    function existsVarBlock($nameBlock, $varName)
    {
    	return isset($this->vars[$nameBlock][$varName]);
    }


    /**
      * M�todo que obtiene la cadena resultante del parseo de variables de toda la template.
      *
      * @access public
      * @return string Cadena resultante del parseo de toda la template.
      */
    function parse() {
        
        $f = $this->bfuncs['MAIN'];
        if ($f) $this->result = $f($this->vars['MAIN']);
        return $this->result;
    }


    /**
      * M�todo que obtiene la cadena resultante del parseo de un bloque.
      *
      * @access public
      * @param string $nameBlock Nombre del bloque.
      * @boolean $resetBlock Indica si se inicializa o no el bloque,
	  * por defecto no se inicializa
      * @return string Cadena resultante del parseo del bloque.
      */
    function parseBlock ($nameBlock,$resetBlock=false) {

        $f = $this->bfuncs[$nameBlock];
        // Si es un bloque valido ...
        if ($f) 
        {
            $data = $f($this->vars[$nameBlock]);
            if ($resetBlock)
                $this->resetVars($nameBlock);
        }
        else $data="";
        return $data;
    }


    /**
      * M�todo que resetea las variables del bloque
      *
      * @access public
      * @param string $nameBlock Nombre del bloque.
      * @return void
      */
    function resetVars($nameBlock="MAIN")
    {
        if (!is_null($this->vars[$nameBlock]))
        {
            foreach(array_keys($this->vars[$nameBlock]) as $var)
                $this->vars[$nameBlock][$var]="";
        }
    }


    /**
      * M�todo que muestra por pantalla el resultado del parseo.
      *
      * @access public
      */
    function printResult() {
        if (is_null($this->result)) $this->parse();
        print $this->result;
    }


    /**
      *  Genera el fichero pre
      */
    function compiler() {

        $curr_blk="MAIN";

        // Stacks para guardar las longitudes de los bloques (para la deteccion de fin de bloque)
	// y cu�l es el bloque pr�vio (contexto en el que se defini� el bloque en curso)
        $sp=-1;
        $block_len_stk=array();
        $block_prev_stk=array();

	$length = filesize ($this->nameTemplate);
	if ($length>0)
	{
        	$pf = fopen ($this->nameTemplate, "r");
        	$content = fread ($pf, filesize ($this->nameTemplate));
        	fclose($pf);
	}
	else $content = '';

        $content = addcslashes($content, "\"\\\$");

        $bloque["MAIN"][] = array(0,strlen($content));

        $pos=0;

	while (($pos=strpos($content, '<!-- ', $pos)) !== false )
	{
            $posIni = $pos;
            $pos+=5;
            $tipo=$content[$pos];
            if ($tipo != '@' && $tipo != '{') continue;

            if ($tipo == '{')
            {
                $pos++;
                $pos2 = strpos($content, '} -->', $pos);

                // Notar que falla (echo aposta) cuando la var es nula !! <!-- {} -->
                if (!$pos2)
                    return PAFTemplate::raiseError("$this->nameTemplate. Linea ".$this->getLineAtOffset($content,$pos).". Variable no cerrada<br>");

                $varData=substr($content, $pos, $pos2-$pos);
		list($varName, $varMods) = $this->getVarModifiers($varData);
                $this->vars[$curr_blk][$varName]="";
                $pos= $pos2+5;
                $bloque[$curr_blk][]=array($varName,$posIni,$pos,$varMods);
            }

            if ($tipo == '@')
            {
                $pos++;
                if ($content[$pos++] != ' ') continue;

                $pos2 = strpos($content, ' @ -->', $pos);

                // Notar que falla (echo aposta) cuando la var es nula !! <!-- @  @ -->
                if (!$pos2)
                    return PAFTemplate::raiseError("$this->nameTemplate. Linea ".$this->getLineAtOffset($content,$pos).". Variable de bloque no cerrada<br>");

                $varName=substr($content, $pos, $pos2-$pos);
                $pos= $pos2+6;

                // Fin de Bloque, hacemos pop del stack de bloques anidados
                if ($sp >=0 && $pos == $block_len_stk[$sp])
                {
                    $curr_blk = $block_prev_stk[$sp];
                    $sp--;
                }

                // Nuevo bloque
                else
                {
                    $posFin = strpos($content, "<!-- @ $varName @ -->", $pos);
                    if (!$posFin)
                        return PAFTemplate::raiseError("$this->nameTemplate. Linea ".$this->getLineAtOffset($content,$pos).". Fin de bloque \"$varName\" no hallado<br>");

                    $tagLen = strlen("<!-- @ $varName @ -->");
                    $bloque[$curr_blk][]=array($varName,$posIni,$posFin + $tagLen, NULL);

                    $sp++;
                    $block_len_stk[$sp] = $posFin + $tagLen;
                    $block_prev_stk[$sp] = $curr_blk;
                    $curr_blk = $varName;

                    if (isset($bloque[$curr_blk]))
                          PAFTemplate::raiseError("$this->nameTemplate. Linea ".$this->getLineAtOffset($content,$pos).". Bloque \"$varName\" duplicado", 0, PEAR_ERROR_TRIGGER);
                    $bloque[$curr_blk][]=array($pos,$posFin);
                    $this->vars[$curr_blk][$varName]="";
                }
            }
	}

	// Escritura del precompilado
        if (!$fp = @fopen ($this->namePrecompile,"w"))
            PAFTemplate::raiseError("$this->namePrecompile ($this->nameTemplate) no pudo abrirse para escritura", 0, PEAR_ERROR_TRIGGER);
	if ($fp) fputs($fp, "<?\n");
	foreach ($bloque as $block_name=>$vars)
	{
                if (list($ini, $fin) = $vars[0])
                   $blk_content = substr($content, $ini, $fin-$ini);
                else { $blk_content = $content; $ini=0; }
                $correccion=-$ini;

                for ($i=1; $i<count($vars); $i++)
                {
                    list($varName, $ini, $fin, $mods) = $vars[$i];

                    if ($fp)
                        fputs($fp, "\$this->vars['$block_name']['$varName']='';\n");

                    if ($mods)
                        $str2 = "\".PAFTemplate::applyModifiers(\$vars[\"$varName\"],\"$mods\").\"";
                    else
                        $str2 = "\".\$vars[\"$varName\"].\"";
                    $blk_content = substr_replace($blk_content, $str2, $ini+$correccion, $fin-$ini);

                    $correccion += strlen($str2) - ($fin-$ini);
                }

                $this->bfuncs[$block_name]=create_function('$vars', "return \"$blk_content\";");
                $blk_content = addcslashes($blk_content, "\'");
                if ($fp) fputs($fp, "\$this->bfuncs['$block_name']=create_function('\$vars','return \"$blk_content\";');\n");
        }
        if ($fp)
        {
	    fputs($fp, "?>\n");
	    fclose($fp);
	}

	return true;
	
    }

    function getLineAtOffset($content,$current_pos)
    {
        $n=1;
        $pos=-1;
        while (($pos = strpos($content, "\n", $pos+1)) !== false && $pos < $current_pos)
          $n++;

        return $n;
    }

    function getVarModifiers($varStr)
    {

        if ($pos = strpos($varStr, '|'))
	   return array(substr($varStr, 0, $pos), substr($varStr, $pos+1));
	else
	   return array($varStr, NULL);
    }

    function applyModifiers($data, $modifers)
    {
	$origdata = $data;
	$modData = PAFTemplate::argExplode('|', $modifers);
	
	for ($i=0; $i<count($modData); $i++)
	{
	    $modArgs= PAFTemplate::argExplode(':', $modData[$i]);
	    $modName = $modArgs[0];
	    $modArgsCount = count($modArgs) -1;
    
	    switch ($modName)
	    {
	       case "upper":
			if ($modArgsCount == 0)
	       		  $data=strtoupper($data);
			else PAFTemplate::raiseError("Bad argument count to $modName");
			break;
	       case "lower":
			if ($modArgsCount == 0)
	       		  $data=strtolower($data);
			else PAFTemplate::raiseError("Bad argument count to $modName");
			break;
	       case "trim":
			if ($modArgsCount == 0)
	       		  $data=trim($data);
			else PAFTemplate::raiseError("Bad argument count to $modName");
			break;
	       case "ucword":
			if ($modArgsCount == 0)
	       		  $data=ucwords(strtolower($data));
			else PAFTemplate::raiseError("Bad argument count to $modName");
			break;
	       case "ucfirst":
			if ($modArgsCount == 0)
	       		  $data=ucfirst(strtolower($data));
			else PAFTemplate::raiseError("Bad argument count to $modName");
			break;
	       case "escape":
			if ($modArgsCount <= 1)
			{
			    if ($modArgsCount == 0) $modArgs[1]="html";

			    switch ($modArgs[1])
			    {
			       case "html":
			             $data=htmlspecialchars($data);
				     break;
			       case "html_all":
			             $data=htmlentities($data);
				     break;
	                       case "quotes":
			             $data=addslashes($data);
				     break;
	                       case "url":
			             $data=urlencode($data);
			 	     break;
	                       case "raw_url":
			             $data=rawurlencode($data);
			 	     break;
	                       case "javascript":
			             $data=addcslashes($data, "\0..\37'\"\\\177..\377");
			 	     break;
	                       case "utf8":
			             $data=utf8_encode($data);
			 	     break;
	                       case "utf8decode":
			             $data=utf8_decode($data);
			 	     break;
	                       case "json_string":
			             $data=addcslashes($data, '"');
			 	     break;
			       default: PAFTemplate::raiseError("Bad argument for $modName");
	                    }
			}
			else PAFTemplate::raiseError("Bad argument count to $modName");
			break;
	       case "nl2br":
			if ($modArgsCount == 0)
	       		  $data=nl2br($data);
			else PAFTemplate::raiseError("Bad argument count to $modName");
			break;
           case "nl2p":
				if ($modArgsCount == 0) {
					/* usamos expresi�n regular para controlar los casos en que vienen varios nl seguidos.
					* Si usuaramos str_replace no podr�amos controlarlo
					*/
					$data='<p>' . preg_replace("((\n)+)", '</p><p>', $data) . '</p>';
				} else {
					PAFTemplate::raiseError("Bad argument count to $modName");
				} 
				break;
	       case "ifnull":
			if ($modArgsCount == 1)
			{
	       		  if (is_null($data)) return($modArgs[1]);
			}
			else PAFTemplate::raiseError("Bad argument count to $modName");
			break;
	       case "signon":
			if ($modArgsCount == 0)
			{
	       		  if ($origdata>0) $data="+$data";
			}
			else PAFTemplate::raiseError("Bad argument count to $modName");
			break;
	       case "wordwrap":
			if ($modArgsCount == 1)
	       		  $data=wordwrap($data, $modArgs[1]);
			else if ($modArgsCount == 2)
	       		  $data=wordwrap($data, $modArgs[1], $modArgs[2]);
			else PAFTemplate::raiseError("Bad argument count to $modName");
			break;
	       case "number_format":
			if ($modArgsCount == 1)
	                  $data=number_format($data, $modArgs[1]);
			else if ($modArgsCount == 3)
	                  $data=number_format($data, $modArgs[1], $modArgs[2], $modArgs[3]);
			else PAFTemplate::raiseError("Bad argument count to $modName");
			break;
	       case "date_format":
			if ($data)
			{
			   if ($modArgsCount == 1)
	                     $data=strftime($modArgs[1], $data);
			   else PAFTemplate::raiseError("Bad argument count to $modName");
			}
			break;
	       case "replace":
			if ($modArgsCount == 2)
	                  $data=str_replace($modArgs[1], $modArgs[2], $data);
			else PAFTemplate::raiseError("Bad argument count to $modName");
			break;
	       case "strip_tags":
			if ($modArgsCount == 1)
	                  $data=strip_tags ($data, $modArgs[1]);
			else if ($modArgsCount == 0)
	                  $data=strip_tags ($data);
			else PAFTemplate::raiseError("Bad argument count to $modName");
			break;
	       case "pad":
			$modes = array("L"=>STR_PAD_LEFT, "R"=>STR_PAD_RIGHT, "B"=>STR_PAD_BOTH);
			if ($modArgsCount == 3)
	                  $data=str_pad($data, $modArgs[1], $modArgs[2], $modes[$modArgs[3]]);
			else PAFTemplate::raiseError("Bad argument count to $modName");
			break;
	       case "truncate":
			if ($modArgsCount > 0 && $modArgsCount <= 3)
			{
			   $length = $modArgs[1];
			   $tail = (($modArgsCount>1) ? $modArgs[2] : "...");
			   $atword = (($modArgsCount>2) ? ($modArgs[3]=="true") : true);
			   if ($atword)
			      for ($pos=$length; $pos>0 && $data[$pos] != ' '; $pos--);
			   else
			      $pos = $length;

			   $data = substr($data,0,$pos).$tail;
			}
			else PAFTemplate::raiseError("Bad argument count to $modName");
			break;

	       default:
			PAFTemplate::raiseError("Unknown modifier $modName");
			break;
	    }

	}
	return $data;

    }

    function raiseError($err)
    {
        $info = '';
        $data = debug_backtrace();
        foreach($data as $entry)
            $info .= $entry['PEAR'] . ", linea:" . $entry['line'] . "\n";

        return PEAR::raiseError ("ERROR: PAFTemplate - $err\n$info", 0, PEAR_ERROR_TRIGGER);
    }

    function argExplode($sep, $str)
    {

       $pos=0;
       $ret = array();
       while (($pos2 = strpos($str, $sep, $pos)) !== false)
       {
          if ($pos2 == 0 || $str[$pos2 - 1] != '\\')
          {
                $ret[] = $chunk.substr($str, $pos, $pos2 - $pos);
                $chunk = "";
          }
          else $chunk .= substr($str, $pos, $pos2 - $pos -1).$sep;
          $pos = $pos2+1;
       }
       $ret[] = $chunk.substr($str, $pos);

       return $ret;
    }

}

?>
