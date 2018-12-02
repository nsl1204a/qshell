<?php

require_once("PAF/PAFObject.php");

/**
 * Identificador de la clase PAFReverseTemplate.
 *
 * @const CLASS_PAFREVERSETEMPLATE
 */
define("CLASS_PAFREVERSETEMPLATE", 4826);

/**
 * Parsea un documento conforme a una template. Funciona como PAFTemplate pero
 * inversamente. En PAFTemplate se genera un documento basado en datos que ya 
 * se conocen. Aquí se sacan los datos de un documento ya generado.
 *
 * @access public
 * @author Ivan Maeder <imaeder@prisacom.com>
 */
class PAFReverseTemplate extends PAFObject
{
    // {{{ Properties

    /**
     * Activa o desactiva los mensajes debug.
     *
     * @access private
     * @var boolean
     */
    var $debugMode;

   /**
    * Ruta del fichero template.
    *
    * @access private
    * @var string
    */
    var $tplFile;

    /**
     * Ruta del fichero de precompilado.
     *
     * @access private
     * @var string
     */
    var $preFile;

    /**
     * El número máximo de caracteres que se usan para determinar el cierre
     * de una variable.
     *
     * @access private
     * @var integer
     */
    var $patternSize;

    /**
     * Guarda las variables del documento parseado, en este formato:
     * $vars[bloque][variableA][0]
     *                         [1]
     *              [variableB][0]
     *                         ...
     *
     * @access private
     * @var array
     */
    var $vars;

    /**
     * Guarda las variables de bloques del documento parseado, en este formato:
     * $blockVars[bloque][0][variableA]
     *                      [variableB]
     *                   [1][variableA]
     *                      ...
     *
     * No contiene el bloque MAIN.
     *
     * @access private
     * @var array
     */
    var $blockVars;

    // }}}
    // {{{ +PAFReverseTemplate(string $tplFile, string $prePath = ".", integer $patternSize = 10, boolean $debugMode = FALSE, string $errorClass = NULL)

    /**
     * Constructor.
     *
     * Comprueba que existe el directorio de precompilado. Si la template es 
     * más reciente que el fichero de precompilado, o si el fichero de 
     * precompilado no existe, se encarga de crear el fichero de precompilado.
     *
     * El fichero de precompilado sólamente se crea si se cumplen estas 
     * condiciones. Por ejemplo, no se vuelve a crear aunque se cambie el 
     * tamaño del parametro $patternSize y exista un fichero de precompilado 
     * más reciente que la template.
     *
     * Una vez construido el objeto no se vuelve a generar el fichero de
     * precompilado.
     *
     * @access public
     * @param string $tplFile ruta del fichero template
     * @param string $prePath directorio de precompilado; por defecto, el
     * directorio actual
     * @param integer $patternSize el número de caracteres máximos que se
     * considerarán para cerrar un variable; por defecto, 10
     * @param boolean $debugMode activa información debug; por defecto, FALSE
     * @param string $errorClass; por defecto, NULL
     */
    function PAFReverseTemplate($tplFile, $prePath = ".", $patternSize = 10, $debugMode = FALSE, $errorClass = NULL)
    {
        $this->PAFObject($errorClass);

        if (!is_dir($prePath))
        {
            $this = PEAR::raiseError("<br>\n<b>Error</b>: No existe el directorio \"" . $prePath . "\" in <b>" . __FILE__ . "</b> on line <b>" . __LINE__ . "</b><br>\n");
            return;
        }

        $preFile = basename($tplFile);
        $preFile = $prePath . "/" . substr($preFile, 0, strrpos($preFile, ".")) . posix_getuid() . ".pre";

        $this->tplFile   = $tplFile;
        $this->preFile   = $preFile;
        $this->debugMode = $debugMode;

        if (filemtime($this->preFile) < filemtime($tplFile))
        {
            if (!is_writeable($prePath))
            {
                $this = PEAR::raiseError("<br>\n<b>Error</b>: No se puede escribir al directorio \"" . $this->preFile . "\" in <b>" . __FILE__ . "</b> on line <b>" . __LINE__ . "</b><br>\n");
                return;
            }

            $this->patternSize = $patternSize;

            // DEBUG
            if ($this->debugMode)
                echo "<br>\n<b>Debug</b>: Fichero template \"" . $this->tplFile . "\"<br>\n";

            $result = $this->precompile();

            if (PEAR::isError($result))
            {
                $this = $result;
                return;
            }
        }

        // DEBUG
        if ($this->debugMode)
            echo "<br>\n<b>Debug</b>: Fichero precompilado \"" . $this->preFile . "\"<br>\n";
    }
    // }}}
    // {{{ +parse(string $filename) : mixed

    /**
     * Parsea un documento conforme al fichero de precompilado.
     *
     * @access public
     * @param string $filename la ruta del fichero a parsear
     * @return mixed boolean TRUE si se ha parseado el documento con éxito u 
     * object error
     */
    function parse($filename)
    {
        if (!($handle = @fopen($filename, "rb")))
            return PEAR::raiseError("<br>\n<b>Error</b>: No se puede abrir el fichero \"" . $filename . "\" in <b>" . __FILE__ . "</b> on line <b>" . __LINE__ . "</b><br>\n");
        else
        {
            do {
                $data = fread($handle, 8192);
                $document .= $data;

            } while($data != "");

            fclose($handle);
        } // or $document = file_get_contents($filename) & error-handling in PHP 4 >= 4.3.0 

        return $this->parseText($document);
    }

    // }}}
    // {{{ +parseText(string $document) : mixed

    /**
     * Parsea un documento conforme al fichero de precompilado.
     *
     * @access public
     * @param string $filename la ruta del fichero a parsear
     * @return mixed boolean TRUE si se ha parseado el documento con éxito u 
     * object error
     */
    function parseText(&$document)
    {


        // DEBUG
        if ($this->debugMode)
            echo "<br>\n<b>Debug</b>: " . strlen($document) . " caracteres en documento \"" . $filename . "\"<br>\n";

        include($this->preFile);

        foreach ($this->bloque["MAIN"] as $pattern)
        {
            list ($mainVarName, $mainVarStartPos, $mainVarEndPattern) = explode("-", $pattern, 3); // n-m-*

            if ($this->bloque[$mainVarName]) // if block
            {
                list ($blockStartPos, $blockEndPos) = explode("-", $this->bloque[$mainVarName][0]); // n-m

                list ($blockEndVarName, $blockEndVarStartPos) = explode("-", $this->bloque[$mainVarName][count($this->bloque[$mainVarName]) - 1], 3); // n-m-
                $endPatternLength = ($blockEndPos - $blockStartPos) - ($blockEndVarStartPos + strlen("<!-- {" . $blockEndVarName . "} -->") - 1); // distance between the last var of the block and end of the block

                $block = substr($document, $mainVarStartPos + $mainOffset, strpos(strtolower($document), $mainVarEndPattern, $mainVarStartPos + $mainOffset) - ($mainVarStartPos + $mainOffset) + strlen($mainVarEndPattern));

                // DEBUG
                if ($this->debugMode)
                    echo "<br>\n<b>Debug</b>: " . strlen($block) . " caracteres en bloque " . $mainVarName . "(" . $mainVarStartPos . "/" . $blockStartPos . "-" . $blockEndPos . ") empezando por \"" . htmlentities(substr($document, $mainVarStartPos + $mainOffset, 100)) . "...\"\nterminando con \"" . htmlentities($mainVarEndPattern) . "\"<br>\n";

                $blockPass   = 0;
                $blockOffset = 0;
                while (TRUE) // iterates thru all passes of the block
                {
                    foreach (array_keys($this->bloque[$mainVarName]) as $pos) // iterates thru each var in one pass of the block
                    {
                        if (!$pos) // skip $this->bloque[$mainVarName][0]
                            continue;
                        else
                        {
                            list ($blockVarName, $blockVarStartPos, $blockVarEndPattern) = explode("-", $this->bloque[$mainVarName][$pos], 3);

                            // DEBUG
                            if ($this->debugMode)
                                echo "<br>\n<b>Debug</b>: Parseando " . $mainVarName . " => " . $blockVarName . "(" . ($blockVarStartPos + $blockOffset) . ") empezando por \"" . htmlentities(substr($block, $blockVarStartPos + $blockOffset, 50)) . "...\"\nterminando con \"" . htmlentities($blockVarEndPattern) . "\"<br>\n";

                            if ($blockVarStartPos + $blockOffset < 0 || $blockVarStartPos + $blockOffset > strlen($block))
                                return PEAR::raiseError("<br>\n<b>Error</b>: No se puede parsear variable \"" . $mainVarName . "\" en documento \"" . $filename . "\" (Start POS: $blockVarStartPos - blockOffset: $blockOffset - Length: ".strlen($block).")  in <b>" . __FILE__ . "</b> on line <b>" . __LINE__ . "</b><br>\n");
                            else
                                $match = substr($block, $blockVarStartPos + $blockOffset, strpos(strtolower($block), $blockVarEndPattern, $blockVarStartPos + $blockOffset) - ($blockVarStartPos + $blockOffset));

                            // DEBUG
                            if ($this->debugMode)
                                echo "<br>\n<b>Debug</b>: " .  $mainVarName . " => " . $blockVarName . " = \"" . htmlentities($match) . "\"<br>\n";

                            $this->blockVars[$mainVarName][$blockPass][$blockVarName] = $match;
                            $this->vars[$mainVarName][$blockVarName][$blockPass] = $match;

                            if ($this->bloque[$mainVarName][$pos + 1]) // do not displace offset after last var
                                $blockOffset += strlen($match) - strlen("<!-- {" . $blockVarName . "} -->");
                        }
                    }

                    $blockPass++;

                    if ($blockVarStartPos + $blockOffset < 0 || $blockVarStartPos + $blockOffset > strlen($block))
                        return PEAR::raiseError("<br>\n<b>Error</b>: No se puede parsear variable \"" . $mainVarName . "\" en documento \"" . $filename . "\" (Start POS: $blockVarStartPos - blockOffset: $blockOffset - Length: ".strlen($block).") in <b>" . __FILE__ . "</b> on line <b>" . __LINE__ . "</b><br>\n");
                    else{
                        $blockOffset = strpos(strtolower($block), $blockVarEndPattern, $blockVarStartPos + $blockOffset) + $endPatternLength;
                    }

                    if (substr($mainVarEndPattern, 1) == strtolower(substr($block, $blockOffset, strlen($mainVarEndPattern))))
                    {
                        $blockOffset--;
                        break;
                    }
                    elseif ($mainVarEndPattern == strtolower(substr($block, $blockOffset, strlen($mainVarEndPattern))))
                    {
                        break;
                    }
                }

                $mainOffset -= (strlen("<!-- @ " . $mainVarName . " @ -->") * 2) - $blockOffset + ($blockEndPos - $blockStartPos + 3); // two block delcarations and three extra carriage returns in the template
            }
            else // non-block var
            {
                // DEBUG
                if ($this->debugMode)
                    echo "<br>\n<b>Debug</b>: Parseando " . $mainVarName . "(" . ($mainVarStartPos + $mainOffset) . ") empezando por \"" . htmlentities(substr($document, $mainVarStartPos + $mainOffset, 100)) . "...\"\nterminando con \"" . htmlentities($mainVarEndPattern) . "\"<br>\n";

                $match = substr($document, $mainVarStartPos + $mainOffset, strpos(strtolower($document), $mainVarEndPattern, $mainVarStartPos + $mainOffset) - ($mainVarStartPos + $mainOffset));

                // DEBUG
                if ($this->debugMode)
                    echo "<br>\n<b>Debug</b>: " .  $mainVarName . " = \"" . htmlentities($match) . "\"<br>\n";

                $this->vars["MAIN"][$mainVarName] = $match;

                $mainOffset += strlen($match) - strlen("<!-- {" . $mainVarName . "} -->");
            }
        }

        return TRUE;
    }

    // }}}
    // {{{ +getVar(string $mainVarName, string $blockName = "MAIN") : string

    /**
     * Devuelve el valor de una variable una vez parseado el documento.
     *
     * @access public
     * @param string $mainVarName el nombre de la variable
     * @param string $blockName el nombre del bloque
     * @return mixed string el valor de la variable dentro de MAIN o array si 
     * la variable se trata de un bloque o una variable dentro de un bloque
     */
    function getVar($mainVarName, $blockName = "MAIN")
    {
        if ($mainVarName == NULL)
        {
            if ($blockName == "MAIN")
                return $this->vars["MAIN"];
            else
                return $this->blockVars[$blockName];
        }
        else
            return $this->vars[$blockName][$mainVarName];
    }

    // }}}
    // {{{ +getVarBlock(string $blockName, string $mainVarName = NULL) : string

    /**
     * Devuelve el valor de una variable una vez parseado el documento.
     *
     * @access public
     * @param string $mainVarName el nombre de la variable
     * @return mixed string el valor de la variable MAIN o array si la variable 
     * se trata de un bloque o una variable dentro de un bloque
     */
    function getVarBlock($blockName, $mainVarName = NULL)
    {
        return $this->getVar($mainVarName, $blockName);
    }

    // }}}
    // {{{ +getClassType() : integer

    /**
     * Método estático para recuperar el identificador de la clase.
     *
     * @access public
     * @return integer código único de la clase
     */
    function getClassType()
    {
        return CLASS_PAFREVERSETEMPLATE;
    }

    // }}}
    // {{{ +getClassName() : string

    /**
     * Método estático que retorna el nombre de la clase.
     *
     * @access public
     * @return string nombre de la clase
     */
    function getClassName()
    {
        return "PAFReverseTemplate";
    }

    // }}}
    // {{{ +isTypeOf(integer $tipo) : boolean

    /**
     * Método de consulta para determinar si una clase es de un tipo 
     * determinado. 
     *
     * @access public
     * @param integer $tipo código de clase por el que queremos preguntar
     * @return boolean TRUE si la clase es del tipo indicado o derivada y
     * FALSE en caso contrario
     */
    function isTypeOf($tipo)
    {
        return $this->getClassType() == $tipo || PAFObject::isTypeOf($tipo);
    }

    // }}}
    // {{{ -precompile() : mixed

    /**
     * Genera el fichero de precompilado. Copia de PAFTemplate::compiler(), 
     * cambiando el output para que saque el texto que cierra una variable,
     * y no la posición donde acaba en la template.
     *
     * @author Gustavo Nuñez <gnunez@prisacom.com>
     * @author Alfonso Gomáriz <agomariz@prisacom.com>
     * @author Sergio Cruz <scruz@prisacom.com>
     * @author Ivan Maeder <imaeder@prisacom.com>
     *
     * @access private
     * @return mixed boolean TRUE si se crea el fichero precompilado u objeto
     * error
     */
    function precompile()
    {
        if (!$template = @file($this->tplFile))
            return PEAR::raiseError("<br>\n<b>Error</b>: No se puede leer el fichero \"" . $this->tplFile . "\" in <b>" . __FILE__ . "</b> on line <b>" . __LINE__ . "</b><br>\n");

        $templateSize = count($template);
        for ($i = 0; $i < $templateSize; $i++) // iterates through $template
        {
            if (ereg("( @|\}) --><!-- (@ |{)", $template[$i]))
                return PEAR::raiseError("<br>\n<b>Error</b>: Template \"" . $this->tplFile . "\" mal construída en línea " . ($i + 1) . "<br>\n");

            $posFin = 0;
            for ($j = 0; $j < substr_count($template[$i], "<!-- {"); $j++) // parse variables on each line
            {
                $posIni = strpos($template[$i], "<!-- {", $posFin);
                $posFin = strpos($template[$i], "} -->", $posFin) + 4;

                if ($posFin <= 4)
                    return PEAR::raiseError("<br>\n<b>Error</b>: Template \"" . $this->tplFile . "\" mal construída en línea " . ($i + 1) . "<br>\n");

                $varName = substr($template[$i], $posIni + strlen("<!-- {"), ($posFin - 5) - ($posIni + 5));

                // get end pattern
                $tLines  = "";
                for ($tPos = 0; $tPos < $this->patternSize; $tPos++)
                    $tLines[$tPos] = $template[$i + $tPos];

                $tLine = substr(implode("", $tLines), $posIni + strlen("<!-- {" . $varName . "} -->"));

                if ($substr = substr($tLine, 0, strpos($tLine, "<!-- {")))
                    $tLine = $substr;

                if ($substr = substr($tLine, 0, strpos($tLine, "\n<!-- @ ")))
                    $tLine = $substr;

                $varCount++;
                $block["MAIN"][$varCount][1] = $varName . "-" . ($posIni + $posicion) . "-"  . strtolower(substr($tLine, 0, $this->patternSize));
            }

            if (ereg("<!-- @", $template[$i]))
            {
                if (ereg("( @|\}) --><!-- (@ |{)", $template[$i]))
                    return PEAR::raiseError("<br>\n<b>Error</b>: Template \"" . $this->tplFile . "\" mal construída en línea " . ($i + 1) . "<br>\n");

                $posIniBlock = strpos($template[$i], "<!-- @ ");
                $posFinBlock = strpos($template[$i], " @ -->") + 5;

                $clave = substr($template[$i], $posIniBlock + 7, ($posFinBlock - 6) - ($posIniBlock + 6));

                $clave = substr($template[$i], $posIniBlock + 7, ($posFinBlock - 6) - ($posIniBlock + 6));
                $aux = $posicion;
                $posicion = $posicion + strlen ($template[$i]);
                $i++;
                $contVarBlock = 0;
                $contPosBlock = 0;

                for ( ; !ereg ("@ -->", $template[$i]) && $i < $templateSize; $i++)
                {
                    $posFin = 0;

                    for ($j = 0; $j < substr_count ($template[$i], "<!-- {"); $j++)
                    {
                        $posIni = strpos ($template[$i], "<!-- {", $posFin);
                        $posFin = strpos ($template[$i], "} -->", $posFin) + 4;

                        if ($posFin <= 4)
                            return PEAR::raiseError("<br>\n<b>Error</b>: Template \"" . $this->tplFile . "\" mal construída en línea " . ($i + 1) . "<br>\n");

                        $variable = substr ($template[$i], $posIni + 6, ($posFin - 5) - ($posIni + 5));
                        $contVarBlock++;

                        $tLines  = "";
                        for ($tPos = 0; $tPos < $this->patternSize; $tPos++)
                            $tLines[$tPos] = $template[$i + $tPos];

                        $tLine = substr(implode("", $tLines), $posIni + strlen("<!-- {" . $variable . "} -->"));

                        if ($substr = substr($tLine, 0, strpos($tLine, "<!-- {")))
                            $tLine = $substr;

                        if ($substr = substr($tLine, 0, strpos($tLine, "\n<!-- @ ")))
                            $tLine = $substr;

                        $block[$clave][$contVarBlock][1] = $variable . "-" . ($posIni + $contPosBlock) . "-" . strtolower(substr($tLine, 0, $this->patternSize));
                    }

                     $contPosBlock = $contPosBlock + strlen ($template[$i]);
                }

                if ($i >= $templateSize && !ereg("@ -->", $template[$templateSize]))
                    return PEAR::raiseError("<br>\n<b>Error</b>: Template \"" . $this->tplFile . "\" mal construída en bloque " . $clave . "<br>\n");
                elseif (ereg("( @|\}) --><!-- (@ |{)", $template[$i]))
                    return PEAR::raiseError("<br>\n<b>Error</b>: Template \"" . $this->tplFile . "\" mal construída en línea " . ($i + 1) . "<br>\n");

                if ($contPosBlock > 0)
                    $interval = $posicion . "-" . (($contPosBlock + $posicion) - 1);
                else
                    $interval = $posicion . "-" . ($contPosBlock + $posicion);

                $block[$clave][0] = $interval;
                $varCount++;

                $tLines  = "";
                for ($tPos = 0; $tPos < $this->patternSize; $tPos++)
                    $tLines[$tPos] = $template[$i + $tPos];

                $tLine = substr(implode("", $tLines), strlen("<!-- @ " . $clave . " @ -->") + 1);
                if ($substr = substr($tLine, 0, strpos($tLine, "<!-- {")))
                    $tLine = $substr;

                if ($substr = substr($tLine, 0, strpos($tLine, "\n<!-- @ ")))
                    $tLine = $substr;

                $block["MAIN"][$varCount][1] = "$clave" . "-" . $aux . "-" . strtolower(substr($tLine, 0, $this->patternSize));
                $posicion = $posicion + $contPosBlock;
            }

            $posicion = $posicion + strlen($template[$i]);
        }

        if (!($pf2 = @fopen($this->preFile, "w")))
            return PEAR::raiseError("<br>\n<b>Error</b>: No se puede escribir al fichero \"" . $this->preFile . "\" in <b>" . __FILE__ . "</b> on line <b>" . __LINE__ . "</b><br>\n");
        else
        {
            fwrite($pf2, "<?php\n");

            foreach($block as $mainVarName => $blockIndex)
            {
                if ($mainVarName != "MAIN")
                {
                    $this->bloque[$mainVarName][0] = $block[$mainVarName][0];

                    $cadena = "\$this->bloque[\"" . $mainVarName . "\"][0]=\"" . $block[$mainVarName][0] . "\";\n";
                    fwrite($pf2, $cadena);

                    $count = count($blockIndex) - 1;
                }
                else
                    $count = count($blockIndex);

                for ($i = 1; $i < $count + 1; $i++)
                {
                    $this->bloque[$mainVarName][$i] = $block[$mainVarName][$i][1];

                    $cadena = "\$this->bloque[\"" . $mainVarName . "\"][$i]=\"" . str_replace("\"", "\\\"", str_replace("\$", "\\$", str_replace("\\", "\\\\", $block[$mainVarName][$i][1]))) . "\";\n";
                    fwrite($pf2, $cadena);
                }
            }

            fwrite($pf2, "?>");
            fclose($pf2);
        }
    }

    // }}}
}

?>
