<?php

  // *****************************************************************************
  // Lenguaje: PHP
  // Copyright 2002 Prisacom S.A.
  // ---------
  // ChangeLog
  // ---------
  // $Log: PAFLog.php,v $
  // Revision 1.4  2005/04/05 14:43:08  fjalcaraz
  // *** empty log message ***
  //
  // Revision 1.3  2003/03/13 09:08:01  scruz
  // Cambio en el nombre de constantes.
  //
  // Revision 1.2  2003/03/11 16:03:32  scruz
  // Correci�n de errores.
  //
  // Revision 1.1  2003/03/06 12:29:16  scruz
  // Primera versi�n clase PAFLog.
  //
  // *****************************************************************************

    require_once "PAF/PAFObject.php";

    /**
    * @const PL_DEFAULTEXTENSION Extensi�n por defecto para el fichero log.
    */
    define ("PL_DEFAULT_EXTENSION", "log");
    
    /**
    * @const PL_DEFAULT_FIELD_SEPARATOR Separador por defecto para los campos que forman una l�nea
    *        de log.
    */
    define ("PL_DEFAULT_FIELD_SEPARATOR", "|");
    
    /**
    * @const PL_BALANCEMODE_YEAR Modo de distribuci�n de logs por a�os.
    */
    define ("PL_BALANCEMODE_YEAR", 1000);
    
    /**
    * @const PL_BALANCEMODE_YEAR_MONTH Modo de distribuci�n de logs por a�os.
    */
    define ("PL_BALANCEMODE_YEAR_MONTH", 1001);

    /**
    * @const PL_BALANCEMODE_YEAR_MONTH_DATE Modo de distribuci�n de logs por a�os.
    */
    define ("PL_BALANCEMODE_YEAR_MONTH_DATE", 1002);

    /**
    * @const PL_BALANCEMODE_YEAR_MONTH_DATE_HOUR Modo de distribuci�n de logs por a�os.
    */
    define ("PL_BALANCEMODE_YEAR_MONTH_DATE_HOUR", 1003);

    /**
    * @const PL_BALANCEMODE_NONE Modo sin distribuci�n de logs.
    */
    define ("PL_BALANCEMODE_NONE", 1004);
    
    /**
    * Clase para hacer log a fichero. Escribe las columnas a longitud fija y permite balanceo
    * del fichero en directorios por a�o, mes, d�a. hora. La clase posee un path base a partir del cual
    * se escribe el fichero de log (atributo $logPath). Si el log es balanceado se generar�n los directorios
    * para el balanceo a partir de este directorio base. Si no en un log balanceado se generar� el fichero
    * log en dicho directorio base.
    *
    * @author Sergio Cruz <scruz@prisacom.com>
    * @version $Revision: 1.4 $
    * @package PAF
    */

    class PAFLog2 extends PAFObject
    {
        /**
        * Nombre del fichero de log.
        * @access private
        * @var string
        */
        var $logFileName= null;
        
        /**
        * Extensi�n para el fichero de log.
        * @access private
        * @var string
        */
        var $extension= PL_DEFAULT_EXTENSION;

        /**
        * Ruta base del fichero log.
        * @access private
        * @var string
        */
        var $logPath= ".";

        /**
        * Atributo que indica si el log es balanceado o no.
        * @access private
        * @var boolean
        */
        var $balanced= false;
        
        /**
        * Atributo que contiene el tipo de balanceo
        * @access private
        * @var int
        */
        var $balancingMode= PL_BALANCEMODE_NONE;
        
        /**
        * Contiene el car�cter de separaci�n entre los distintos campos de un registro de log.
        * @access private
        * @var string
        */
        var $fieldSeparator= PL_DEFAULT_FIELD_SEPARATOR;
        
        /**
        * Array que contiene la definici�n de la longitud de los campos.
        * @access private
        * @var array
        */
        var $fieldDefinition= null;
        
        /**
        * Atributo para establecer si se har� distinci�n del log por ip de m�quina o no.
        * El efecto de poner este atributo a true es la de generar un directorio adicional 
        * (con el nombre de m�quina) justo por debajo del directorio base para el log. 
        * @access private
        * @var boolean.
        */
        var $machineDistinction= false;

        /**
        * Constructor.
        *
        * @param string $logNameValue Nombre del fichero de log (puede ir con o sin extensi�n).
        * @param array $fieldsDefinitionValue Array con la definici�n de campos. Se trata de un hash en el
        *        que se introduce como "key" el nombre del campo y como valor asociado a la "key" la longitud
        *        fija con la que pintar ese campo.
        * @param string $logPathValue Ruta base del fichero log.
        * @param boolean $balancedValue Indica si el log se balancear� por distintos directorios dependiendo
        *        del a�o, mes, dia, hora.
        *
        * @return Objeto PAFLog creado o un PEAR_Error en caso de que no se proporcione un nombre para el
        *        fichero log.
        *
        * @access protected
        */
        function PAFLog2(
                        $logNameValue,
                        $logPathValue=".",
                        $fieldsDefinitionValue=null,
                        $extensionValue= PL_DEFAULT_EXTENSION,
                        $balancedValue= PL_BALANCEMODE_NONE
                       )
        {
            $this->PAFObject();

            // Comprobaci�n de par�metros obligatorios.
            if ( empty($logNameValue) )
            {
                $this= PEAR::raiseError("�ERROR! [PAFLog]=> No se ha proporcionado un nombre v�lido para el fichero log.");
                return $this;
            }

            // Establece el atributo para el nombre del fichero log y su extensi�n.
            $this->logFileName= trim($logNameValue);
            $this->extension= trim ($extensionValue);
            
            // Establece el valor de la definici�n de campos.
            if ( !is_null($fieldsDefinitionValue) )
            { $this->fieldDefinition= $fieldsDefinitionValue; }
            
            // Fija el atributo para el directorio base del fichero log.
            if ( !empty($logPathValue) )
            { $this->logPath= trim($logPathValue); }

            // Establece el modo de balanceo para el log.
            if ( !empty($balancedValue) )
            {
                if (
                    $balancedValue != PL_BALANCEMODE_YEAR &&
                    $balancedValue != PL_BALANCEMODE_YEAR_MONTH &&
                    $balancedValue != PL_BALANCEMODE_YEAR_MONTH_DATE &&
                    $balancedValue != PL_BALANCEMODE_YEAR_MONTH_DATE_HOUR &&
                    $balancedValue != PL_BALANCEMODE_NONE
                   )
                {
                    $this->balancingMode= PL_BALANCEMODE_NONE;
                    $this->balanced= false;
                }
                else
                {
                    $this->balancingMode= $balancedValue;
                    if ( $balancedValue == PL_BALANCEMODE_NONE )
                    { $this->balanced= false; }
                    else
                    { $this->balanced= true; }
                }
            }
        }
        
        /**
        * Devuelve si el log es de tipo balanceado o no.
        *
        * @return boolean
        * @access protected
        */
        function isBalanced()
        { ($this->balanced)?true:false; }
        
        /**
        * Devuelve si el log actual act�a con distinci�n de m�quina o no, tomando de este modo
        * un directorio en cuenta (justo debajo del directorio base del log) que contiene el nombre
        * de m�quina.
        *
        * @return boolean
        * @access protected
        */
        function hasMachineDistinction()
        { 
            if ($this->machineDistinction)
            { return true; }
            else
            { return false; } 
        }

        /**
        * M�todo que devuelve la extensi�n del fichero de log.
        *
        * @return string
        * @access public
        */
        function getExtension()
        { return $this->extension; }
        
        /**
        * M�todo que establece la extensi�n del fichero de log.
        *
        * @param string $extensionValue
        * @access public
        */
        function setExtension($extensionValue)
        {$this->extension= $extensionValue; }
        
        /**
        * M�todo para la recuperaci�n del car�cter o cadena de separaci�n de los campos de
        * un registro de log.
        *
        * @return string
        * @access public
        */
        function getFieldSeparator()
        { return $this->fieldSeparator; }
        
        /**
        * M�todo que establece el car�cter o cadena de separaci�n entre los campos de un
        * registro de log.
        *
        * @param string $sepValue Car�cter o cadena de separaci�n entre campos del registro de log.
        */
        function setFieldSeparator($sepValue)
        { $this->fieldSeparator= $sepValue; }
        
        /**
        * M�todo que devuelve el modo de balanceo del fichero log.
        *
        * @return int
        * @access public
        */
        function getBalancingMode()
        { return $this->balancingMode; }
        
        /**
        * Fija el modo de balnaceo de log.
        *
        * @param int $mode Modo de Balanceo para el log. Puede tomar los valores definidos
        *        por el conjunto de constantes PL_BALANCEMODE...
        *
        * @access protected
        */
        function setBalancedMode($mode)
        {
            // TODO: Ser�a interesante introducir un balanceo por ip de m�quina adicionalmente y combinable con los ya existentes.
            $retValue= false;
            $mode= intval($mode);
            
            if ( $mode == PL_BALANCEMODE_NONE )
            { $this->balanced= false; }
            
            if (
                    !empty ($mode) &&
                    (
                        $mode == PL_BALANCEMODE_YEAR ||
                        $mode == PL_BALANCEMODE_YEAR_MONTH ||
                        $mode == PL_BALANCEMODE_YEAR_MONTH_DATE ||
                        $mode == PL_BALANCEMODE_YEAR_MONTH_DATE_HOUR
                    )
               )
           { $this->balanced= true; }
           
           $this->balancingMode= $mode;
        }
        
        /**
        * M�todo para establecer la definici�n de la longitud de los campos del log.
        *
        * @param array $values Array de enteros con las longitudes de cada uno de los campos del log.
        * @access public
        * @return mixed true si se ha conseguido establecer correctamente la definici�n de la
        *         longitud de los campos del log o un PEAR_Error en caso contrario.
        */
        function setLogDefinition($values)
        {
            // TODO: Estar�a bien comprobar adem�s si los valores contenidos dentro del array son integers.
            if ( empty($values) || ! is_array($values) )
            {
                $msg="�ERROR! [PAFLog::setLogDefinition]=> No se ha proporcionado un valor v�lido para la definici�n de la longitud de los campos del log.";
                return (PEAR::raiseError($msg));
            }
            
            $this->fieldDefinition= $values;
            return true;
        }
        
        /**
        * Establece distinci�n de m�quina en la generaci�n del log.
        *
        * @param boolean $value true=> Se hace distinci�n por m�quina false=> no se hace distinci�n.
        */
        function setMachineDistinction($value= true)
        {
            if ( is_bool($value) )
            { $this->machineDistinction= $value; }
            else
            { $this->machineDistinction= false; }
        }
        
        /**
        * M�todo abstracto a redefinir por las clases derivadas de esta. Debe retornar un PEAR_Error
        * en caso de que se produzca alg�n error en la escritura de los valores.
        *
        * @param string $line Cadena de texto con la informaci�n de la l�nea de log a escribir.
        * @param string $fieldSeparator Car�cter de separaci�n de los campos del registro de log.
        *        Si no se especifica se toma como car�cter separador de campo el establecido en el 
        *        atributo $fieldSeparator.
        *
        * @return mixed True si se ha conseguido escribir la l�nea de log deseada o un PEAR_Error en 
        *         caso contrario.
        *
        * @access protected
        */
        function writeLog($lineValue, $fieldSeparatorValue= null)
        {
            $logFileWithExt= "";        // Nombre del fichero con extensi�n (sin ruta).
            $logFileCompleteName= "";   // Nombre completo del fichero de log (con extensi�n y con ruta).
            $completePathLog= "";       // Ruta completa (absoluta o relativa) del fichero de log.
            $values= null;
            
            $values= $this->generatePathAndName();
            $completePathLog= $values[0];
            $logFileWithExt= $values[1];
            
            // Creamos la estructura de directorios si es necesario.
            if ( !@is_dir($completePathLog) )
            {
                if (!$this->recursiveMKDir($completePathLog))
                {
                    $msg= "�ERROR! [PAFLog::writeLog] => Se ha producido un error al generar la estructura de directorios " . $completePath . ".";
                    return PEAR::raiseError($msg);
                }
            }
            
            $logFileCompleteName= $completePathLog."/".$logFileWithExt;
            
            $filehdl= @fopen ($logFileCompleteName, "a+");
            if (!$filehdl)
            { 
                $msg= "�ERROR! [PAFLog]=> No se ha podido abrir/crear el fichero $logFileCompleteName.";
                return PEAR::raiseError($msg);
            }
            
            // Comprueba si tiene que formatear la l�nea de log en caso de que haya una definici�n de campos.
            if ( !is_null($this->fieldDefinition) )
            {
                // En el caso de que se proporcione expl�citamente un separador de campo lo utilizamos y si no
                // se utiliza el que est� fijado en el atributo de la clase $fieldSeparator.
                if ( !is_null($fieldSeparatorValue) )
                { $lineValue= $this->formatLine($lineValue, $fieldSeparatorValue); }
                else
                { $lineValue= $this->formatLine($lineValue, $this->fieldSeparator); }
            }
            
            $lineValue= trim($lineValue)."\n";
            
            $ret= @fwrite($filehdl, $lineValue);
			if ( !$ret )
			{
				$message= "Error al intentar escribir en log.";
				return PEAR::raiseError ($message);
			}
            
            @fflush($filehdl);
            @fclose($filehdl);
        }
        
        /**
        * M�todo de escritura libre en el fichero de log.
        */
        function freeLog($lineValue)
        {
            // TODO: Estudiar si este m�todo hay que realizarlo o se puede hacer lo mismo con lo que ya hay.
            
        }
        
        /**
        * Formatea una l�nea de log para que cumpla las posibles especificaciones de campo establecidas
        * en el atributo $fieldDefinitions. Si se especifica el segundo par�metro opcional se tomar�
        * dicho valor como separador de campo. Si no se especifica se utilizar� el establecido en el 
        * atributo $fieldSeparator.
        *
        * NOTA: Si el objeto PAFLog actual no contiene definici�n de campos, esto es , su atributo
        *       $fieldDefinitions es null se devuelve la l�nea de log original sin aplicarle ning�n
        *       formato.
        *
        * @param string $lineValue Valor del registro de log a formatear.
        * @param string $sepValue Valor opcional de separador de campo. Si no especifica se toma
        *        como car�cter separador de campo el establecido en el atributo $fieldSeparator.
        *
        * @return string L�nea de log formateada de acuerdo a las especificaciones de longitud
        *         de los campos (atributo $fieldDefinitions) y el separador de campo proporcionado
        *         (en caso de que se proporcione).
        *
        * @access protected
        */
        function formatLine ($lineValue, $sepValue= null)
        {
            $retValue= "";
            
            // Comprobamos que se tiene definici�n de campos establecida.
            if ( is_null($this->fieldDefinition) )
            { return $lineValue; }
            
            // Obtiene el separador de campo a utilizar.
            if ( is_null($sepValue) )
            { $sepToUse= $this->fieldSeparator; }
            else
            { $sepToUse= $sepValue; }
                
            // Obtiene de la l�nea original los campos.
            $values= explode($sepToUse, $lineValue);
            $numValues= count($values);
            $numDefinitions= count($this->fieldDefinition);
            for ($i= 0; $i<$numValues; $i++)
            {
                // Aplica la longitud asociada al campo a la cadena que representa su valor
                // haciendo trim de la cadena previamente.
                if ( $i <= $numDefinitions )
                {
                    $fieldLength= $this->fieldDefinition[$i];
                    $formatString= "%-".$fieldLength."s";
                    $values[$i]= sprintf ($formatString, trim($values[$i]));
                }
                else // Si no existe longitud asociada al n�mero de campo solo hacemos trim
                { $values[$i]= trim($values[$i]); }
                
                if ( $i<$numDefinitions-1)
                { $retValue.= $values[$i] . $sepToUse; }
                else
                { $retValue.= $values[$i]; }
            }
            
            return $retValue;
        }
        
        /**
        * M�todo que proporciona la IP "real" desde la que accede un cliente
        * @access protected
        * @return string
        */
		function getRealIP()
		{
			if(getenv("HTTP_CLIENT_IP"))
            { $ip = getenv("HTTP_CLIENT_IP"); }
            elseif(getenv("HTTP_X_FORWARDED_FOR"))
            { $ip = getenv("HTTP_X_FORWARDED_FOR"); }
            else
            { $ip = getenv("REMOTE_ADDR"); }

            return $ip;
		}
        
        /**
        * M�todo protegido para la formaci�n del nombre completo de fichero con path para que contemple
        * los modos de distribuci�n.
        *
        * @return array Dos posiciones. En la primera el Path completo y en la segunda el nombre del
        *         fichero con extensi�n.
        * @access protected.
        */
        function generatePathAndName()
        {
            $logFileWithExt= "";        // Nombre del fichero con extensi�n (sin ruta).
            $logFileCompleteName= "";   // Nombre completo del fichero de log (con extensi�n y con ruta).
            $completePathLog= "";       // Ruta completa (absoluta o relativa) del fichero de log.
            $retValue= array();
            
            // Comprueba si el log es balanceado.
            if ( $this->balanced )
            {
                switch ($this->balancingMode)
                {
                    case PL_BALANCEMODE_YEAR_MONTH_DATE_HOUR:
                        $completePathLog= $this->logPath."/".
                                          strftime("%Y")."/".
                                          strftime("%m")."/".
                                          strftime("%d")."/";
                                          
                        $logFileWithExt= $this->logFileName."_".strftime("%H").".".$this->extension;
                    break;
                    
                    case PL_BALANCEMODE_YEAR_MONTH_DATE:
                        $completePathLog= $this->logPath."/".
                                          strftime("%Y")."/".
                                          strftime("%m")."/".
                                          strftime("%d");
                        $logFileWithExt= $this->logFileName.".".$this->extension;
                    break;

                    case PL_BALANCEMODE_YEAR_MONTH:
                        $completePathLog= $this->logPath."/".
                                          strftime("%Y")."/".
                                          strftime("%m");
                        $logFileWithExt= $this->logFileName.".".$this->extension;
                    break;

                    case PL_BALANCEMODE_YEAR:
                        $completePathLog= $this->logPath."/".
                                          strftime("%Y");
                        $logFileWithExt= $this->logFileName.".".$this->extension;
                    break;
                }
            }
            else
            {
                $completePathLog= $this->logPath."/";
                $logFileWithExt= $this->logFileName.".".$this->extension;
            }
            
            $retValue[0]= $completePathLog;
            $retValue[1]= $logFileWithExt;
            return $retValue; 
        }

        /**
        * Construye la estructura de directorios indicada por la cadena $pathValue. Si la cadena
        * $pathValue viene con / por delante se respetar� dicha estructira completamente. Si no viene
        * con / se genera la estructura indicada en $pathValue a partir del directorio en el que se
        * encuentre ejecut�ndose el script ("./").
        *
        * NOTA: El valor de $permissions es obviado en sistemas operativos Windows.
        *
        * @author gjukema <gjukema@jukeware.com> Sergio Cruz <scruz@prisacom.com>
        *
        * @param string $pathValue Cadena con la estructura de directorios a crear a partir
        *        del camino base.
        * @param int $permissions Notaci�n octal para los permisos de los distintos directorios.
        *
        * @return boolean true si se ha conseguido crear la estructura de directorios correctamente o
        *         false en caso contrario.
        *
        * @access protected
        */
        function recursiveMKDir($pathValue, $permissions=0777)
        {
            $pathValue= trim($pathValue);
    
            if ( strlen($pathValue) == 0 )
            { return true; }
            if ( @is_dir($pathValue) )
            { return true; }
            else
            {
                if ( dirname($pathValue) == $pathValue )
                { return true; }
            }
            
            return ( $this->recursiveMKDir(dirname($pathValue)) and @mkdir($pathValue, $permissions) );
        }
    }

?>
