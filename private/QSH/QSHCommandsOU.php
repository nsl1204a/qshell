<?php
require_once('private/conf/configuration.inc');
require_once('private/QSH/QSHfunctions.inc');
//require_once("PAF/PAFOutput.php");
require_once('private/conf/QSHProcess.inc');
require_once('private/conf/QSHDatabases.inc');

/**
 *
 * Ejecucion de procesos y elaboracion de salidas QShell
 *
 * @author mcalvo
 *
 */
class QSHCommandsOU /*extends PAFOutput*/
{

    /**
     * array de resultados
     */
    var $aResult;

    /**
     * Tipo de contenido requerido por el usuario
     */
    var $cType;

    /**
     * momento de esta ejecucion
     */
    var $moment;

    /**
     * informacion de una aplicacion
     */
    var $aApp;

    /**
     * indicador para pintar enlaces csv
     */
    var $justPrintCSVLinks;

    /**
     * nombre del fichero de descarga csv
     */
    var $attachmentFileName;

    /**
     * constructor de la clase
     */
    function QSHCommandsOU()
    {

        $this->aResult           = [];
        $this->moment            = date('d-m-Y H:i:s');
        $this->cType             = QSH_HTML_OUTPUT;
        $this->justPrintCSVLinks = false;

    }

    /**
     * Estandar getoutput de toda clase PAFOutput
     * Obtiene los resultados de ejecucion de procesos y elabora las salidad conforme al tipo de contenido requerido
     */
    function getOutput()
    {

        $paraminput = $this->parseParamsInput();

        if (!$paraminput) {
            $out         = '<h1>No hay un contexto correcto para ejecutar este proceso</h1>';
            $this->cType = QSH_TRUE_HTML_OUTPUT;

            return $out;
        }

        /**
         * cuando se ha elegido csv para los resultados, pudiendo haber m�s de un comando por funcion
         * y no pudiendose descargar varios ficheros a la vez, se pinta un enlace por cada comando que se
         * ejecutar� individualmente para hacer una sola descarga por comando. Cuando se entre en condiciones de
         * no haber indicado el comando que se tiene que ejecutar solo se pintan los enlaces.
         */
        if ($this->cType == QSH_CSV_OUTPUT && (!isset($paraminput['inputs']['command']) || strlen($paraminput['inputs']['command']) == 0)) {

            $this->justPrintCSVLinks = true;
            $this->getResult($paraminput);
            $out = $this->getCSVLinks($paraminput);
            $this->cType = QSH_HTML_OUTPUT;
        } else {
            $this->getResult($paraminput);

            if (isset($this->aResult) && $this->aResult) {

                switch ($this->cType) {
                    case QSH_HTML_OUTPUT:
                    case QSH_XML_OUTPUT:
                        $out = $this->getXMLResult($paraminput);
                        break;
                    case QSH_CSV_OUTPUT:
                        $out = $this->getCSVResult($paraminput);
                        break;
                    default:
                        $out = $this->getXMLlResult($paraminput);
                        break;
                }
            }
        }

        return trim($out);
    }

    function getCtype()
    {

        return $this->cType;

    }

    /**
     * genera el nombre de fichero de descarga para un attachment csv
     */
    function getAttachmentFileName()
    {

        return $this->attachmentFileName;
    }

    /**
     * Verifica si los parametros recibios contiene la firma requerida para poder ejecutar los procesos
     */
    function safetyChecks($params)
    {

        $exparams = explode('#', base64_decode($params));

        $xyide    = $exparams[0];
        $rand     = $exparams[1];
        $function = $exparams[2];
        $app      = $exparams[3];
        $usuario  = $exparams[4];//esto deber� ser el usuario en sesion para verificar que no hay cambio de usuario.

        $token = md5($function . $app . $usuario . QSH_ENCRYPTION_WORD);

        if ($xyide != $token) {
            return false;
        }

        return $exparams;

    }

    /**
     * retorna un array con parametros de procesos y campos del formulario del usuario
     */
    function parseParamsInput()
    {

        global $qsh_Apps;

        $params   = $_REQUEST['params'];
        $exparams = $this->safetyChecks($params);

        if (!$exparams) {
            return false;
        }

        $xyide    = $exparams[0];
        $rand     = $exparams[1];
        $function = $exparams[2];
        $app      = $exparams[3];
        $usuario  = $exparams[4];

        $paramInput1 = [];
        foreach ($_REQUEST as $name => $value) {

            if ($name != 'params' && $name != 'FFEnviar') {
                $paramInput1[$name] = $value;
            }

            if ($name == 'FFCtype') {
                $this->cType = $value;
            }

        }

        //Expandimos cada valor de paramentros en tantos parametros commandname_paramname como comandos donde participe el parametro
        $paramInput2 = [];
        $objFun      = $qsh_Apps[$app]['functions'][$function];

        foreach ($objFun['params'] as $paramname => $aCommandIndex) {
            foreach ($aCommandIndex as $commandIndex) {
                foreach ($paramInput1 as $name => $value) {
                    if ($paramname == $name) {
                        $commandname            = $objFun['commands'][$commandIndex - 1]['name'];
                        $fullname               = $commandname . '#' . $name;
                        $paramInput2[$fullname] = $value;
                    } else {
                        $paramInput2[$name] = $value;
                    }
                }
            }
        }

        $paramInput = [
            'app'      => $app,
            'function' => $function,
            'user'     => $usuario,
            'rand'     => $rand,
            'inputs'   => $paramInput2,
        ];

        return $paramInput;
    }

    /**
     * Ejecuta los comandos de un proceso o funcion y acumula sus resultados en un array de la clase
     */
    function getResult($paraminput)
    {

        global $qsh_Apps;

        /**
         * obtener los comandos que tiene la funcion
         */
        $app        = $paraminput['app'];
        $function   = $paraminput['function'];
        $this->aApp = $qsh_Apps[$app];

        $numCommands   = 0;
        $aCommandsText = [];

        $aCommands = $this->aApp['functions'][$function]['commands'];

        /**
         * cuando solo hay que pintar enlaces de descarga csv no hay que ejecutar los comandos
         */
        if ($this->justPrintCSVLinks) {
            return;
        }

        /**
         * Sustituci�n de simbolos en los textos de comandos
         */
        foreach ($aCommands as $command) {

            if (isset($paraminput['inputs']['command']) && strlen($paraminput['inputs']['command']) > 0) {
                if ($paraminput['inputs']['command'] != $command['name']) {
                    continue;
                } else {
                    $this->attachmentFileName = str_replace(
                        ' ',
                        '_',
                        $app . '_' . $function . '_' . $paraminput['inputs']['command'] . '_' . $this->moment . '.csv'
                    );
                }
            }

            $text        = $command['text'];
            $repacedText = $this->textReplaceSymbol($command, $paraminput, $text);

            $aCommandsText[] = [
                'name'     => $command['name'],
                'desc'     => $command['desc'],
                'type'     => $command['type'],
                'database' => $command['database'],
                'text'     => $repacedText,
            ];
            $numCommands++;
        }

        /**
         * Ejecutar el texto sustituido dependiendo del tipo de comando, excepto si solo hay
         * que pintar los enlaces de desacarga
         */

        foreach ($aCommandsText as $aText) {

            switch ($aText['type']) {

                case QSH_COMMAND_TYPE_SELECT:
                case QSH_COMMAND_TYPE_UPDATE:
                    $this->runQuery($aText);
                    break;
                case QSH_COMMAND_TYPE_PROCEDURE:
                    $this->runProcedure($aText);
                    break;
                case QSH_COMMAND_TYPE_PROCEDURE_RS:
                    $this->runProcedureRS($aText);
                    break;
                case QSH_COMMAND_TYPE_EXE:
                    //echo '<br>voy a ejecutar Exe';
                    $this->runExe($aText);
                    break;
                default:
                    $this->runQuery($aText);
                    break;
            }
        }

    }


    /**
     * Ejecuta un comando cuyo texto es una query de consulta o actualizacion de base de datos
     */
    function runQuery($aText)
    {

        global $qsh_Databases;

        $name     = $aText['name'];
        $desc     = $aText['desc'];
        $text     = $aText['text'];
        $database = $aText['database'];
        $type     = $aText['type'];

        $host = $qsh_Databases[$database]['host'];
        $db   = $qsh_Databases[$database]['db'];
        $user = $qsh_Databases[$database]['user'];
        $pwd  = $qsh_Databases[$database]['pwd'];

        $errno     = null;
        $error     = false;
        $errdesc   = null;
        $numrows   = 0;
        $numfields = 0;
        $rowset    = [];
        $headers   = [];
        $con       = null;

        $this->DBConnect($host, $user, $pwd, $db, $con, $error, $errno, $errdesc);


        if (!$error) {
            $this->DBQuery($con, $text, $error, $errno, $errdesc, $result);
        }

        if ($error) {
            $this->aResult[] = [
                'name'       => $name,
                'desc'       => $desc,
                'type'       => $type,
                'resultType' => QSH_RESULT_TYPE_ERROR,
                'error'      => $error,
                'errno'      => $errno,
                'errdesc'    => $errdesc,
            ];
        } else {
            if ($type == QSH_COMMAND_TYPE_SELECT) {

                $numrows   = mysqli_num_rows($result);
                $numfields = mysqli_num_fields($result);
                $i         = 0;

                while ($row = mysqli_fetch_assoc($result)) {
                    $rowset[] = $row;
                    if ($i == 0) {
                        foreach ($row as $clave => $valor) {
                            $headers[] = $clave;
                        }
                    }
                    $i++;
                }
                $this->aResult[] = [
                    'name'       => $name,
                    'desc'       => $desc,
                    'type'       => $type,
                    'resultType' => QSH_RESULT_TYPE_ROWSET,
                    'numrows'    => $numrows,
                    'numfields'  => $numfields,
                    'headers'    => $headers,
                    'result'     => $rowset,
                ];
            } else {
                $numrows         = mysqli_affected_rows($con);
                $this->aResult[] = [
                    'name'       => $name,
                    'desc'       => $desc,
                    'type'       => $type,
                    'resultType' => QSH_RESULT_TYPE_UPDATE,
                    'numrows'    => $numrows,
                ];
            }
        }

        $this->DBCleanup(isset($result) ? $result : false, isset($con) ? $con : false);

    }

    /**
     * Ejecuta un comando cuyo texto es la llamada a un procedimiento almacenado de base de datos
     */
    function runProcedure($name, $text, $database)
    {
        return;
    }

    /**
     * Ejecuta un comando cuyo texto es la llamada a procedimiento almacenado de base de datos que devuelve record set
     */
    function runProcedureRS($name, $text, $database)
    {
        return;
    }

    /**
     * Ejecuta un comando cuyo texto es un comando del sistema operativo
     */
    function runExe($aText)
    {

        $name = $aText['name'];
        $desc = $aText['desc'];
        $text = $aText['text'];
        $type = $aText['type'];

        $output = [];
        $errno  = null;

        //php4: exec ($text, &$output, &$errno);
        exec($text, $output, $errno);

        $this->aResult[] = [
            'name'       => $name,
            'desc'       => $desc,
            'type'       => $type,
            'resultType' => QSH_RESULT_TYPE_COMSET,
            'errno'      => $errno,
            'result'     => $output,
        ];

    }

    /**
     * generar un fragmento global xml de los resultados
     */
    function getXMLResult($paraminput)
    {

        global $qsh_parameters_dictionary;

        require_once('private/conf/QSHTemplates.inc');

        //Resultado global
        $app      = $paraminput['app'];
        $function = $paraminput['function'];
        $user     = $paraminput['user'];

        $template = $qsh_xml_template;

        /**
         * formar fragmento global de resultados
         */
        $result = $template['QSResultados'];
        $result = replaceSymbol('codapl', $app, null, $result);
        $result = replaceSymbol('desapl', $this->aApp['desc'], 'cdata', $result);
        $result = replaceSymbol('codfun', $function, null, $result);
        $result = replaceSymbol('desfun', $this->aApp['functions'][$function]['desc'], 'cdata', $result);
        $result = replaceSymbol('momex', $this->moment, null, $result);
        $result = replaceSymbol('usuar', $user, null, $result);
        $result = replaceSymbol('msgpro', null, null, $result);

        /**
         * generar un fragmento xml con la parte comandos de los resultados
         */
        $aCommands = $this->aApp['functions'][$function]['commands'];

        $allCommandsResult = '';

        $i = 0;
        foreach ($aCommands as $command) {

            //si llega un comando concreto solo obtemos salida para �l
            if (isset($paraminput['inputs']['command']) && strlen(
                                                               $paraminput['inputs']['command']
                                                           ) > 0 && $paraminput['inputs']['command'] != $command['name']) {
                continue;
            }


            $commandResult = $template['Comando'];
            $parametrosI   = '';
            $parametrosO   = '';

            $commandResult = replaceSymbol('codblq', $this->aResult[$i]['name'], 'item', $commandResult);
            $commandResult = replaceSymbol('desblq', $this->aResult[$i]['desc'], 'cdata', $commandResult);

            foreach ($paraminput['inputs'] as $fullFieldname => $fieldvalue) {

//                list($commandname, $paramName) = split("#", $fullFieldname, 2);
                list($commandname, $paramName) = $this->splitCommandNameAndParamName($fullFieldname);
                if (empty($commandname)) {
                    continue;
                }

                if ($commandname == $command['name']) {

                    if (isset($qsh_parameters_dictionary[$paramName])) {
                        $paramAttrs = $qsh_parameters_dictionary[$paramName];
                    } else {
                        continue;
                    }

                    if ($paramAttrs['direction'] == QSH_PARAMETER_DIRECTION_IN) {

                        $paramI = $template['ParametroI'];
                        $paramI = replaceSymbol('itipdat', $paramAttrs['type'], 'item', $paramI);
                        $paramI = replaceSymbol('icodpar', $paramName, 'item', $paramI);
                        $paramI = replaceSymbol('inompar', $paramAttrs['desc'], 'cdata', $paramI);
                        $paramI = replaceSymbol('ivalpar', $fieldvalue, 'cdata', $paramI);

                        $parametrosI .= $paramI;

                    } elseif ($paramAttrs['direction'] == QSH_PARAMETER_DIRECTION_OUT) {

                        $paramO      = $template['ParametroO'];
                        $paramO      = replaceSymbol('otipdat', $paramAttrs['type'], 'item', $paramO);
                        $paramO      = replaceSymbol('ocodpar', $paramName, 'item', $paramO);
                        $paramO      = replaceSymbol('onompar', $paramAttrs['desc'], 'cdata', $paramO);
                        $paramO      = replaceSymbol(
                            'ovalpar',
                            null,
                            'cdata',
                            $paramO
                        ); //hasta que sepamos como llegan los parametros de salida
                        $parametrosO .= $paramO;

                    }
                }
            }

            $commandResult = replaceSymbol('ParametrosI', $parametrosI, null, $commandResult);
            $commandResult = replaceSymbol('ParametrosO', $parametrosO, null, $commandResult);


            if ($this->aResult[$i]['resultType'] == QSH_RESULT_TYPE_ERROR) {

                $commandResult = replaceSymbol('Cabeceras', null, null, $commandResult);
                $commandResult = replaceSymbol('FilasResultado', null, null, $commandResult);
                $commandResult = replaceSymbol('filres', null, null, $commandResult);
                $commandResult = replaceSymbol('cantid', null, null, $commandResult);
                $commandResult = replaceSymbol(
                    'msgblq',
                    $this->aResult[$i]['error'] . ' (errno: ' . $this->aResult[$i]['errno'] . ' => ' . $this->aResult[$i]['errdesc'] . ')',
                    'cdata',
                    $commandResult
                );
            }

            if ($this->aResult[$i]['resultType'] == QSH_RESULT_TYPE_UPDATE) {

                $commandResult = replaceSymbol('Cabeceras', null, null, $commandResult);
                $commandResult = replaceSymbol('FilasResultado', null, null, $commandResult);
                $commandResult = replaceSymbol('filres', null, null, $commandResult);
                $commandResult = replaceSymbol('cantid', $this->aResult[$i]['numrows'], 'item', $commandResult);
                $commandResult = replaceSymbol('msgblq', null, 'cdata', $commandResult);
            }

            if ($this->aResult[$i]['resultType'] == QSH_RESULT_TYPE_COMSET) {

                $filasResultado = '';

                $cabecera  = $template['Cabecera'];
                $cabecera  = replaceSymbol('cabcol', 'l�nea', null, $cabecera);
                $cabeceras = $cabecera;

                //filas de resultados

                $lineas = 0;
                foreach ($this->aResult[$i]['result'] as $row) {

                    $fila = $template['FilaResultado'];

                    $columnas = $template['Columna'];

                    $columnas = replaceSymbol('ncol', 'linea', null, $columnas);
                    $columnas = replaceSymbol('valor', '|' . $row, 'cdata', $columnas);
                    $columnas = replaceSymbol('enlace', null, null, $columnas);

                    $fila           = replaceSymbol('Columnas', $columnas, null, $fila);
                    $filasResultado .= $fila;
                    $lineas++;
                }

                $commandResult = replaceSymbol('Cabeceras', $cabeceras, null, $commandResult);
                $commandResult = replaceSymbol('FilasResultado', $filasResultado, null, $commandResult);
                $commandResult = replaceSymbol('filres', $lineas, 'item', $commandResult);
                $commandResult = replaceSymbol('cantid', null, null, $commandResult);
                $commandResult = replaceSymbol(
                    'msgblq',
                    'Codigo de retorno del sistema: ' . $this->aResult[$i]['errno'],
                    'cdata',
                    $commandResult
                );

            }

            if ($this->aResult[$i]['resultType'] == QSH_RESULT_TYPE_ROWSET) {

                $cabeceras      = '';
                $filasResultado = '';

                //cabeceras de resultados
                foreach ($this->aResult[$i]['headers'] as $headerName) {

                    $cabecera = $template['Cabecera'];
                    $cabecera = replaceSymbol('cabcol', $headerName, 'item', $cabecera);

                    $cabeceras .= $cabecera;
                }

                //filas de resultados
                foreach ($this->aResult[$i]['result'] as $row) {

                    $fila = $template['FilaResultado'];

                    $columnas = '';
                    foreach ($row as $clave => $valor) {

                        $columna = $template['Columna'];
                        $columna = replaceSymbol('ncol', $clave, 'item', $columna);
                        $columna = replaceSymbol('valor', $valor, 'cdata', $columna);
                        $columna = replaceSymbol('enlace', null, null, $columna);

                        $columnas .= $columna;
                    }

                    $fila           = replaceSymbol('Columnas', $columnas, null, $fila);
                    $filasResultado .= $fila;
                }

                $commandResult = replaceSymbol('Cabeceras', $cabeceras, null, $commandResult);
                $commandResult = replaceSymbol('FilasResultado', $filasResultado, null, $commandResult);
                $commandResult = replaceSymbol('filres', $this->aResult[$i]['numrows'], 'item', $commandResult);
                $commandResult = replaceSymbol('cantid', null, null, $commandResult);
                $commandResult = replaceSymbol('msgblq', null, 'cdata', $commandResult);

            }

            $allCommandsResult .= $commandResult;
            $i++;

        }

        $result = replaceSymbol('Comandos', $allCommandsResult, null, $result);

        if ($this->cType != QSH_HTML_OUTPUT) {
            $result = replaceSymbol('xslPI', null, null, $result);
        } else {
            $xslpi  = $template['xslPI'];
            $xslpi  = replaceSymbol('xslPath', 'static/QShellResultados.xsl', null, $xslpi);
            $result = replaceSymbol('xslPI', $xslpi, null, $result);
        }

        return trim($result);
    }

    /**
     * este resultado genera el xml para pintar los enlaces de desarga en caso de solicitud de csv
     */
    function getCSVLinks($paraminput)
    {

        global $qsh_parameters_dictionary;

        require_once('private/conf/QSHTemplates.inc');

        //Resultado global
        $app      = $paraminput['app'];
        $function = $paraminput['function'];
        $user     = $paraminput['user'];

        $template = $qsh_xml_template;

        /**
         * formar fragmento global de resultados
         */
        $result = $template['QSResultados'];
        $result = replaceSymbol('codapl', $app, null, $result);
        $result = replaceSymbol('desapl', $this->aApp['desc'], 'cdata', $result);
        $result = replaceSymbol('codfun', $function, null, $result);
        $result = replaceSymbol('desfun', $this->aApp['functions'][$function]['desc'], 'cdata', $result);
        $result = replaceSymbol('momex', $this->moment, null, $result);
        $result = replaceSymbol('usuar', $user, null, $result);
        $result = replaceSymbol('msgpro', null, null, $result);

        /**
         * generar un fragmento xml con la parte comandos de los resultados
         */
        $aCommands = $this->aApp['functions'][$function]['commands'];

        $allCommandsResult = '';

        $i = 0;
        foreach ($aCommands as $command) {

            $commandResult = $template['Comando'];
            $parametrosI   = '';
            $parametrosO   = '';

            $commandResult = replaceSymbol('codblq', $command['name'], 'item', $commandResult);
            $commandResult = replaceSymbol('desblq', $command['desc'], 'cdata', $commandResult);


            foreach ($paraminput['inputs'] as $fullFieldname => $fieldvalue) {

                list($commandname, $paramName) = split("#", $fullFieldname, 2);

                if ($commandname == $command['name']) {

                    if (isset($qsh_parameters_dictionary[$paramName])) {
                        $paramAttrs = $qsh_parameters_dictionary[$paramName];
                    } else {
                        continue;
                    }

                    if ($paramAttrs['direction'] == QSH_PARAMETER_DIRECTION_IN) {

                        $paramI = $template['ParametroI'];
                        $paramI = replaceSymbol('itipdat', $paramAttrs['type'], 'item', $paramI);
                        $paramI = replaceSymbol('icodpar', $paramName, 'item', $paramI);
                        $paramI = replaceSymbol('inompar', $paramAttrs['desc'], 'cdata', $paramI);
                        $paramI = replaceSymbol('ivalpar', $fieldvalue, 'cdata', $paramI);

                        $parametrosI .= $paramI;

                    } elseif ($paramAttrs['direction'] == QSH_PARAMETER_DIRECTION_OUT) {

                        $paramO      = $template['ParametroO'];
                        $paramO      = replaceSymbol('otipdat', $paramAttrs['type'], 'item', $paramO);
                        $paramO      = replaceSymbol('ocodpar', $paramName, 'item', $paramO);
                        $paramO      = replaceSymbol('onompar', $paramAttrs['desc'], 'cdata', $paramO);
                        $paramO      = replaceSymbol(
                            'ovalpar',
                            null,
                            'cdata',
                            $paramO
                        ); //hasta que sepamos como llegan los parametros de salida
                        $parametrosO .= $paramO;

                    }
                }
            }

            $commandResult = replaceSymbol('ParametrosI', $parametrosI, null, $commandResult);
            $commandResult = replaceSymbol('ParametrosO', $parametrosO, null, $commandResult);

            $filasResultado = '';

            $cabecera  = $template['Cabecera'];
            $cabecera  = replaceSymbol('cabcol', 'descarga', null, $cabecera);
            $cabeceras = $cabecera;

            //filas de resultados

            $href = 'qshellExe.php' . request2QueryString() . '&command=' . $command['name'];

            $fila     = $template['FilaResultado'];
            $columnas = $template['Columna'];
            $columnas = replaceSymbol('ncol', 'enlace', null, $columnas);
            $columnas = replaceSymbol('valor', $command['desc'], cdata, $columnas);
            $columnas = replaceSymbol('enlace', $href, cdata, $columnas);

            $fila           = replaceSymbol('Columnas', $columnas, null, $fila);
            $filasResultado .= $fila;

            $commandResult = replaceSymbol('Cabeceras', $cabeceras, null, $commandResult);
            $commandResult = replaceSymbol('FilasResultado', $filasResultado, null, $commandResult);
            $commandResult = replaceSymbol('filres', null, null, $commandResult);
            $commandResult = replaceSymbol('cantid', null, null, $commandResult);
            $commandResult = replaceSymbol('msgblq', null, null, $commandResult);

            $allCommandsResult .= $commandResult;
            $i++;

        }

        $result = replaceSymbol('Comandos', $allCommandsResult, null, $result);

        $xslpi  = $template['xslPI'];
        $xslpi  = replaceSymbol('xslPath', 'static/QShellResultados.xsl', null, $xslpi);
        $result = replaceSymbol('xslPI', $xslpi, null, $result);

        return trim($result);

    }

    /**
     * generar un fichero csv por cada comando mas un enlace html a cada fichero.
     */
    function getCSVResult($paraminput)
    {


        //Resultado global
        $app      = $paraminput['app'];
        $function = $paraminput['function'];
        $user     = $paraminput['user'];

        /**
         * generar un fragmento xml con la parte comandos de los resultados
         */
        $aCommands = $this->aApp['functions'][$function]['commands'];

        $allCommandsResult = '';

        $i = 0;
        foreach ($aCommands as $command) {

            //si llega un comando concreto solo obtemos salida para �l
            if (isset($paraminput['inputs']['command']) && strlen(
                                                               $paraminput['inputs']['command']
                                                           ) > 0 && $paraminput['inputs']['command'] != $command['name']) {
                continue;
            }

            $commandResult = '';

            if ($this->aResult[$i]['resultType'] == QSH_RESULT_TYPE_ERROR) {

                $commandResult = $this->aResult[$i]['error'] . ' (errno: ' . $this->aResult[$i]['errno'] . ' => ' . $this->aResult[$i]['errdesc'] . ')' . "\n";
            }

            if ($this->aResult[$i]['resultType'] == QSH_RESULT_TYPE_UPDATE) {

                $commandResult = $this->aResult[$i]['numrows'] . ' filas actualizadas' . "\n";
            }

            if ($this->aResult[$i]['resultType'] == QSH_RESULT_TYPE_COMSET) {

                $filasResultado = '';
                $cabeceras      = 'linea' . "\n";

                foreach ($this->aResult[$i]['result'] as $row) {

                    //$filasResultado .= '|'. $row . "\n";
                    $filasResultado .= $row . "\n";
                }

                $commandResult = $cabeceras . $filasResultado;

            }

            if ($this->aResult[$i]['resultType'] == QSH_RESULT_TYPE_ROWSET) {

                $cabeceras      = '';
                $filasResultado = '';

                //cabeceras de resultados
                foreach ($this->aResult[$i]['headers'] as $headerName) {

                    $cabeceras .= $headerName . ';';
                }

                $cabeceras .= "\n";

                //filas de resultados
                $filasResultado = '';
                foreach ($this->aResult[$i]['result'] as $row) {

                    foreach ($row as $clave => $valor) {

                        $filasResultado .= $valor . ';';
                    }

                    $filasResultado .= "\n";
                }

                $commandResult = $cabeceras . $filasResultado;
            }

            $allCommandsResult .= $commandResult;
            $i++;

        }

        return trim($allCommandsResult);

    }

    /**
     * conectar a la base de datos
     */

    function DBConnect($host, $user, $pwd, $db, &$con, &$error, &$errno, &$errdesc)
    {

        $con = mysqli_connect($host, $user, $pwd);

        if (!$con) {
            $error   = 'Conexion no establecida en host: ' . $host . ' usuario: ' . $user;
            $errno   = mysqli_errno();
            $errdesc = mysqli_error();

        }

        if (!$error) {
//            $seldb = mysql_select_db($db, $con);
            $seldb = mysqli_select_db($con, $db);
            if (!$seldb) {
                $error   = 'Base de datos no cambiada a : ' . $db;
                $errno   = mysqli_errno($con);
                $errdesc = mysqli_error($con);
            }
        }

        return;

    }

    /**
     * limpiar buffers y desconectar de la base de datos
     */

    function DBCleanup($result, $con)
    {

        if ($result) {
            mysqli_free_result($result);
        }

        if ($con) {
            mysqli_close($con);
        }

        return;

    }

    /**
     * Ejecutar una query de base de datos
     */

    function DBQuery($con, $text, &$error, &$errno, &$errdesc, &$result)
    {
//        $result = mysql_query($text, $con);
        $result = mysqli_query($con, $text);
        if (!$result) {
            $error   = 'No se ha obtenido resultado de ejecuci�n de query: ' . $text;
            $errno   = mysqli_errno($con);
            $errdesc = mysqli_error($con);
        }

        return;

    }

    /**
     * La sustitucion de simbolos consiste en cambiar los simbolos @@xxx@@ de los textos de los comandos por los valores de los datos de usuario
     */
    function textReplaceSymbol($command, $paraminput, $text)
    {
        global $qsh_parameters_dictionary;
        foreach ($paraminput['inputs'] as $fullname => $fieldvalue) {
            list($commandname, $paramname) = $this->splitCommandNameAndParamName($fullname);

            if ($commandname == $command['name']) {

                if (isset($qsh_parameters_dictionary[$paramname])) {
                    $paramAttrs = $qsh_parameters_dictionary[$paramname];
                } else {
                    continue;
                }

                //$valueDelimiter = "'";
                //if($paramAttrs['type'] == QSH_PARAMETER_TYPE_NUMERIC || $command['type'] == QSH_COMMAND_TYPE_EXE)
                $valueDelimiter = null;

                $text = replaceSymbol($paramname, $fieldvalue, $valueDelimiter, $text);
            }
        }

        return $text;
    }

    /**
     * @param $fullname
     *
     * @return array
     */
    private function splitCommandNameAndParamName($fullname)
    {
        $temp = preg_split('(#)', $fullname, 2);
        if (count($temp) > 1) {
            $commandname = $temp[0];
            $paramname   = $temp[1];
        } else {
            $commandname = null;
            $paramname   = null;
        }

        return [$commandname, $paramname];
    }
}

?>
