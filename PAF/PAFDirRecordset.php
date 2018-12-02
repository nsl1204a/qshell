<?php

require_once('PAF/PAFDBRecordSet.php');
require_once('PAF/PAFDirRecordData.php');

/**
 * PAFDirRecordset 
 * Clase Recordset para recuperar ficheros de un directorio
 * 
 * @uses PAFDBRecordset
 * @package 
 * @version $Id: $
 * @author $Author: gandres@prisadigital.com $ 
 */
class PAFDirRecordset extends PAFDBRecordset 
{

    /**
     * params configuracion con la que se realiza la busqueda de ficheros
     * 
     * @var mixed
     * @access public
     */
    var $params;

    /**
     * time  marca de tiempo generada cuando se construye el objeto para comparacion homogenea de tiempo de ficheros.
     * 
     * @var mixed
     * @access public
     */
    var $time;

    /**
     * result  array con la lista de ficheros encontrados
     * 
     * @var mixed
     * @access public
     */
    var $result;

    /**
     * _pointer puntero que indica el archivo actual en el lista de datos
     * 
     * @var mixed
     * @access protected
     */
    var $_pointer;

    /**
     * _pointer_counter almacena el total de ficheros en la lista de datos
     * 
     * @var mixed
     * @access protected
     */
    var $_pointer_counter;

    /**
     * PAFDirRecordset 
     * 
     * @param mixed $params Array clave/valor con los campos a filtrar y que tiene los valores de esos campos de filtro.
     *  dir: directorio donde buscar ficheros
     *  recursive:  indica si debe buscar en subdirectorios
     *  filemask:   expresion regular a utilizar con el nombre de cada fichero
     *  dirmask:    expresion regular a utilizar con los subdirectorios si la busqueda es recursiva 
     *  extmask:    expresion regular a utilizar con la extension de los ficheros encontrados
     *  mtime_seconds_old: maximo tiempo de antiguedad de los ficheros encontrados
     *
     * @access public
     * @return void
     */
    function __construct($params) 
    {

        // Preparacion de parametros
        $params['dir'] = preg_replace("/\/$/", "", $params['dir']);

        $params['recursive'] = isset($params['recursive']) ? $params['recursive'] : true;

        $params['filemask'] = isset($params['filemask']) ? '/' . $params['filemask'] . '/' : '/.*/';

        $params['dirmask'] = isset($params['dirmask']) ? '/' . $params['dirmask'] . '/' : '/.*/';

        $params['extmask'] = isset($params['extmask']) ? '/' . $params['extmask'] . '/' : '/.*/';

        $params['mtime_seconds_old'] = isset($params['mtime_seconds_old']) ? $params['mtime_seconds_old'] : null;

        $this->params = $params;
        $this->time = time();
        $this->result = array();
        $this->_pointer = 0;
        $this->_pointer_counter = 0;
    }
    
    /**
     * PAFDirRecordset 
     * 
     * @param mixed $params 
     * @access public
     * @return void
     */
    function PAFDirRecordset($params)
    {
        $this->__construct($params);
    }

    /**
     * &getRD obtiene una instancia de PAFDirRecordData con los datos del fichero actual
     * 
     * @param mixed $row 
     * @access public
     * @return void
     */
    function &getRD($row)
    {
        return new PAFDirRecordData($row, $x=null);
    }


    /**
     * &getLog obtiene el objeto de log
     * 
     * @access public
     * @return void
     */
    function &getLog()
    {
        return LoggerManager::getLogger($this->logName);
    }

    /**
     * exec 
     * 
     * @access public
     * @return void
     */
    function exec()
    {

        // Obtenemos el log
        $log =& $this->getLog();

        // Si estamos en nivel debug, ponemos el tiempo de la query
        if ($log->isDebugEnabled())
        {
            $timeStart = PAFLogFormat::getMicroTime();
        }

        // Si estamos en modo debug escribimos en log el tiempo que tarda
        if ($log->isDebugEnabled())
        {
            $timeStop = PAFLogFormat::getMicroTime();
            $lapso = number_format($timeStop - $timeStart, 4);
            $log->debug(PAFLogFormat::format(__FILE__, __LINE__, "Tiempo en QUERY ($lapso) '$query'"));
        }


        // Chequeos iniciales
        if (!array_key_exists('dir', $this->params) || !is_dir($this->params['dir']))
        {
            $this->setPEARError(PEAR::raiseError(__FILE__, __LINE__, 'Directorio incorrecto.'));
            return;
        }

        // realizamos la consulta
        $result =& $this->realExec($this->params['dir']);

        if (!is_array($result))
        {
            $result = PEAR::raiseError('Error al ejecutar la peticion.');
        }

        if (PEAR::isError($result))
        {

            if ($log->isErrorEnabled())
            {
                $log->error(PAFLogFormat::format(__FILE__, __LINE__, $result->getMessage()));
            }

            if ($log->isFatalEnabled())
            {
                $log->fatal(PAFLogFormat::format(__FILE__, __LINE__, $result->toString()));
            }

            if ($log->isDebugEnabled())
            {
                $log->debug(PAFLogFormat::format(__FILE__, __LINE__, $result->getDebugInfo()));
            }

            return $result;
        }

        // almacenamos las filas
        $this->result =& $result;

        //establecemos pointers para el recorrido de la lista de resultados
        $this->_count = $this->reset();

        return true;
    }

    /**
     * count total de ficheros encontrados
     * 
     * @access public
     * @return int
     */
    function count()
    {
        return $this->_count;
    }

    /**
     * reset reinicia el puntero de datos a la primera posicion del array
     * 
     * @access public
     * @return void
     */
    function reset () 
    {
        //establecemos pointers para el recorrido de la lista de resultados
        $this->_pointer = 0;
        $this->_pointer_counter = count($this->result);

        // comprobamos si se ha puesto un limite al numero de resultados a devolver
        if ($this->getCountLimit() && $this->_pointer_counter > $this->getCountLimit())
        {
            $this->_pointer_counter = $this->getCountLimit();
        }

        // comprobamos el numero de resultados devueltos
        if ($this->_pointer_counter > count($this->result))
        {
            $this->_pointer_counter = count($this->result);
        }

        return $this->_pointer_counter;
    }

    /**
     * countAll cuenta el total de ficheros encontrados sin aplicar limites
     * 
     * @access public
     * @return void
     */
    function countAll ()
    {
        if ($this->countAll == null)
        {
            $this->countAll = count($this->result);
        }
        return $this->countAll;
    }

    /**
     * &next obiene el siguiente fichero en la lista. Devuelve un objeto PAFDirRecordData
     * 
     * @access public
     * @return PAFDirRecordData
     */
    function &next()
    {
        $res = $this->checkResult();
        if (PEAR::isError($res))
        {
            $log =& $this->getLog();
            if ($log->isFatalEnabled())
            {
                $log->fatal(PAFLogFormat::format(__FILE__, __LINE__, $res->toString()));
            }
            return $res;
        }

        $row = null;
        if ($this->_pointer_counter)
        {
            $row = $this->result[$this->_pointer++];
            $this->_pointer_counter--;
        }
        $res = (is_null($row) ? false : $this->getRD($row));
        return $res;
    }

    /**
     * &realExec funcion sobreescrita para realizar la busqueda en un directorio
     * 
     * @param mixed $pStartDir 
     * @access public
     * @return void
     */
    function &realExec($pStartDir)
    {

        $files = array();
        if (!is_dir($pStartDir))
        {
# false if the function was called with an invalid non-directory argument
            $error = PEAR::raiseError('Directorio incorrecto.');
            return $error;
        }

        $fh = opendir($pStartDir);
        if (!$fh)
        {
            $error = PEAR::raiseError('Error al abrir el directorio.');
            return $error;
        }

        while (($file = readdir($fh)) !== false)
        {
# loop through the files, skipping . and .., and recursing if necessary
            if (strcmp($file, '.')==0 || strcmp($file, '..')==0) continue;
            $filepath = $pStartDir . '/' . $file;
            if ( is_dir($filepath) )
            {
                if ($this->params['recursive'] && preg_match($this->params['dirmask'], $filepath))
                {
                    $files = array_merge($files, $this->realExec($filepath));
                }
            } 
            else 
            {
                if (
                        preg_match($this->params['filemask'], basename($filepath)) && 
                        preg_match($this->params['extmask'], pathinfo($filepath, PATHINFO_EXTENSION))
                        )
                {

                    if( !is_null($this->params['mtime_seconds_old']) )
                    {
                        if( filemtime($filepath) > $this->time-$this->params['mtime_seconds_old'] ) 
                        {
                            $files[] = array('PEAR' => $filepath);
                        }
                    }
                    else 
                    {
                        $files[] = array('PEAR' => $filepath);
                    }
                }
            }
        }
        closedir($fh);

        return $files;
    }

    /**
     * getCountQuery implementado por compatibilidad
     * 
     * @access public
     * @return void
     */
    function getCountQuery()
    {
        return '';
    }

    /**
     * getQuery implementado por compatibilidad
     * 
     * @param mixed $count 
     * @access public
     * @return void
     */
    function getQuery($count=false)
    {
        return '';
    }

}

