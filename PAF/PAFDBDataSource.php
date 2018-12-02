<?php

  // *****************************************************************************
  // Lenguaje: PHP
  // Copyright 2002 Prisacom S.A.
  //*****************************************************************************

require_once "PAF/PAFDataSource.php";
require_once "PAF/PAFLogFormat.php";
require_once "PEAR/DB.php";

if ($_GET['tracesql']=="SI" && (!$_SERVER['REMOTE_ADDR'] || substr($_SERVER['REMOTE_ADDR'],0,3)!='10.')) $_GET['tracesql']=NULL;

/**
  * @const CLASS_PAFDBDATASOURCE Constante para el identificador �nico de clase.
  */

define ("CLASS_PAFDBDATASOURCE", 4);

/**
  * Clase especializada de PAFDataSource que encapsula la conexi�n a fuentes de datos en Base de Datos.
  *
  * @author Sergio Cruz <scruz@prisacom.com>
  * @version $Revision: 1.38 $
  * @access public
  * @package PAF
  */

class PAFDBDataSource extends PAFDataSource
{
    /**
      * Objeto PEAR::DB de Base de Datos encargado de realizar todas las operaciones espec�ficas de Base de Datos.
      *
      * @access private
      */
    var $db_ro = null;
    var $db_rw = null;

    /**
      * Cadena que almacena el dsn que describe la fuente de la Base de datos.
      *
      * @access private
      * @var string
      */
    var $dsn_ro= null;
    var $dsn_rw= null;

    /**
      * Modo de conexi�n a la Base de datos (persistente o no)
      *
      * @access private
      * @var boolean
      */
    var $persist= false;

    /**
      * Umbral de tiempo empleado para escribir querys lentas
      * si es 0 No escribe traza
      *
      * @access private
      * @var boolean
      */
    var $t_umbral= 10;


    /**
      * Variables de Bases de datos utilizadas en la conexi�n si falla la principal
      *
      * @access private
      * @var string
      */

    var $driver;
    var $user;
    var $pwd;
    var $host;
    var $dbname;
    var $protocol;
    var $errorClass;



    /**
      * Constructor
      *
      * @access public
      * @param string $driver Motor de Base de Datos a utilizar. Puede tomar los siguientes
      *        valores mysql (MySQL), pgsql (Postgres), ibase (Interbase), msql (Mini SQL),
      *        mssql (Microsoft SQL Server), oci8 (Oracle 7/8/8i), odbc, sybase, ifx (Informix),
      *        fbsql (FrontBase).
      * @param string $user Nombde de usuario con el que conectamos con la Base de Datos.
      * @param string $pwd Clave del usuario anterior para acceder a la Base de Datos.
      * @param string $host Par de valores IP:Puerto que identifican un�vocamente donde se encuentra
      *        alojado el Servidor de Base de Datos y el puerto TCP que utiliza para establecer
      *        las comunicaciones.
      * @param string $dbname Nombre de la Base de Datos dentro del Servidor con la que queremos
      *        conectarnos.
      * @param boolean $persistCon Indica si la conexi�n a la Base de Datos ser�
      *        persistente o no. Por defecto la conexi�n es no persistente.
      * @param string $protocol Protocolo de comunicaciones utilizado para las comunicaciones
      *        con el servidor de Base de Datos (tcp, unix, etc). Por defecto se utiliza
      *        TCP.
      * @param string $errorClass Nombre de la clase de error asociada con PAFDBDataSource.
      */
    function PAFDBDataSource    (
                                    $driver,
                                    $user,
                                    $pwd,
                                    $host,
                                    $dbname,
                                    $persistCon= false,
                                    $protocol="tcp",
                                    $errorClass= null
                                    )
    {
		// CHAPU: Solo mantiene puerto si resuelve el nombre en la 10.90.40.41
		// PACO.
		if ($driver == 'mysql' && $pos = strpos($host,":"))
		{
	   		$host2 = substr($host,0,$pos);
	   		if ($ip = gethostbyname($host2) != '10.90.40.41')
	   			$host = $host2;
		}

		$this->driver     = $driver;
		$this->user       = $user;
		$this->pwd        = $pwd;
		$this->host       = $host;
		$this->dbname     = $dbname;
		$this->protocol   = $protocol;
		$this->errorClass = $errorClass;

/*
if(stristr($host, "economia.db"))
{
    $fp = fopen("/SESIONES/tmp/error_queries.log", "a");
    if ($fp)
    {
        $backtrace ='';
        $data = debug_backtrace();
        foreach($data as $entry)
            $backtrace .= $entry['file'].", linea:".$entry['line'].", function:".$entry['function']."\n";
        fwrite($fp, $backtrace . "\n");
        fclose($fp);
    }
}
*/
        
		$this->PAFDataSource($errorClass);  // Llamada al constructor de la clase padre.

		if (substr($host, -6) == '_ro.db')
		{
        	$this->dsn_ro= "$driver://$user:$pwd@$protocol+$host/$dbname";
			$this->dsn_rw= "$driver://$user:$pwd@$protocol+".substr($host, 0, -6)."_rw.db/$dbname";
		}
		else $this->dsn_rw= "$driver://$user:$pwd@$protocol+$host/$dbname";
        $this->persist= $persistCon;
		$this->setConnectionStatus(0);
    }

    /**
      * M�todo est�tico para recuperar el identificador de la clase.
      *
      * @access public
      * @return int Identificador �nico de clase.
      */
    function getClassType()
    {
        return CLASS_PAFDBDATASOURCE;
    }

    /**
      * M�todo est�tico que retorna el nombre de la clase.
      *
      * @access public
      * @return string Nombre de la clase.
      */
    function getClassName()
    {
        return "PAFDBDataSource";
    }

    /**
      * Devuelve el nombre de la fuente de datos a la que se est� conectado.
      *
      * @access public
      * @return string Cadena de conexi�n a la base de datos.
      */
    function getDSN($mode_rw=false)
    {
        return ($mode_rw ? $this->dsn_rw : $this->dsn_ro);
    }

    /**
      * M�todo de consulta para determinar si una clase es de un tipo determinado.
      * Reimplementado de PAFObject.
      *
      * @access public
      * @param int $tipo N�mero entero con el C�digo de clase por el que queremos preguntar .
      * @return boolean
      */
    function isTypeOf ($tipo)
    {
        return  ( (PAFDBDataSource::getClassType() == $tipo) || PAFDataSource::isTypeOf($tipo) );
    }

    /**
      * M�todo de conexi�n a la Base de Datos.
      * Sobreescrita de PAFDataSource
      *
      * @access public
      * @return mixed TRUE si se ha podido realizar o un objeto DBError en caso contrario. Para recuperar
      *         el error proporcionado por el driver nativo de BD utilizado se puede invocar al m�todo de
      *         la clase DBError llamado getDebugInfo().
      */
    function connect($mode_rw = null)
    {
		$initial = is_null($mode_rw);
		if ($initial)
			$mode_rw = is_null($this->dsn_ro);

		$mask = ($mode_rw ? 1 : 2);
		$status = $this->getConnectionStatus();
		$status_on = ($status | $mask);
		$status_off = ($status & ~$mask);
		$dsn = ($this->dsn ?$this->dsn : ($mode_rw ? $this->dsn_rw : $this->dsn_ro));
		if ($mode_rw) $db = &$this->db_rw; else	$db = &$this->db_ro;

		if ( is_null($db) || PEAR::isError($db) )
			$this->setConnectionStatus($status_off);

		if ($this->getConnectionStatus() != $status_on )
		{
			$db= DB::connect ($dsn, $this->persist);
			if ($initial) $this->db = &$db;
			if ($_GET['tracesql']=="SI") {
			   #echo "<pre>Conexion con ".substr($dsn, strrpos($dsn,'+')+1)."  RW=$mode_rw\n</pre>";
			   echo "<pre>Conexion con $dsn RW=$mode_rw\n</pre>";
			   flush();
			}
		}

		if ( is_null($db) || PEAR::isError($db) )
			$this->setConnectionStatus($status_off);
		else
			$this->setConnectionStatus($status_on);

		/*A ELIMINAR */
		$host_opcional = $this->dbname.".db";
		if (strpos($this->host, ':'))
			$host_opcional .= substr($this->host, strpos($this->host, ':'));

		if (PEAR::isError($db) && ($this->host!=$host_opcional))
		{
			$mihost=$this->host;
			$this->host=$host_opcional;

/*
if(stristr($this->host, "economia.db"))
{
    $fp = fopen("/SESIONES/tmp/error_queries.log", "a");
    if ($fp)
    {
        $backtrace=$mihost."\n";
        $data = debug_backtrace();
        foreach($data as $entry)
            $backtrace .= $entry['file'].", linea:".$entry['line'].", function:".$entry['function']."\n";
        fwrite($fp, $backtrace . "\n");
        fclose($fp);
    }
}
*/

			$this->dsn_rw= "$this->driver://$this->user:$this->pwd@$this->protocol+$this->host/$this->dbname";
			return $this->connect(true);
		}

		return (!is_null($db) && !PEAR::isError($db));
	}

    /**
      * M�todo de desconexi�n a la Base de Datos sobreescrito de PAFDataSource.
      * Realiza la desconexi�n de la Base de Datos.
      *
      * @access public
      */
    function disconnect()
    {
		if ($this->db_ro && !PEAR::isError($this->db_ro)){
			$this->db_ro->disconnect();
		}
		if ($this->db_rw && !PEAR::isError($this->db_rw)){
			$this->db_rw->disconnect();
		}
		$this->setConnectionStatus(0);
	}


    /**
     * Devuelve el n�mero de filas afectadas en la �ltima query.
     *
     * @access public
     * @return integer Cantidad de filas afectadas en la �ltima query.
     */
    function affectedRows()
    {
        return($this->db_rw->affectedRows());
    }

    /**
     * Devuelve el id colocado como clave primaria en la ultima inserci�n
     *
     * @access public
     * @return integer
     */
    function lastInsertId()
    {
		$sqltype =& $this->db_rw->dbsyntax; // $sqltype = $this->ds->db->phptype;

		if ('mysql' == $sqltype) {
			$id = mysql_insert_id($this->db_rw->connection);
		} else {
			$msg = PAFLogFormat::format(__FILE__, __LINE__, "DataBase not supported");
			$id = PEAR::raiseError($msg);
		}
		return($id);
	}
	/**
	  * M�todo para la ejecuci�n de una consulta sobre la base de datos.
	  * Este m�todo no pertenece a la interface PAFDataSource, lo implementamos
	  * necesidad o comodidad.
	  *
	  * @access public.
	  * @param string $query Cadena con elquery a enviar a la Base de Datos.
	  * @param int $from L�mite inferior desde el que se quieren recuperar registros.
	  * @param int $count N�mero total de registros a recuperar.
	  * @return object DBResult PEAR::DBResult resultado de llamar a db->query.
	  */
	function runQuery ($query, $from= -1, $count= 0)
    {
        // Si ya existe una conexi�n de escritura se reaprovecha
        $mode_rw = (!is_null($this->db_rw) || !ereg('^[\( \n\t]*SELECT',strtoupper($query)));
        $dsn = ($mode_rw ? $this->dsn_rw : $this->dsn_ro);
        if ($mode_rw) $db=&$this->db_rw; else $db=&$this->db_ro;

        if (is_null($db) || PEAR::isError($db)) $this->connect($mode_rw);

        // Control para evitar error en la llamada a modifyLimitQuery() (linea 328) y query() (linea 344)
        if( !$this->getConnectionStatus() ) {
            return PEAR::raiseError("Imposible ejecutar query. No conectado. DSN: ". $dsn );
        } else if( PEAR::isError($db) ) {
            return $db;
        }


        $queryToExec= null;
        if (is_null($from)) $from=0;

        if ($from >= 0 && $count > 0)
            $queryToExec= $db->modifyLimitQuery($query, $from, $count);
        else
            $queryToExec= $query;

        if ($_GET['tracesql']=="SI") {
            echo "<pre>".substr($dsn, strrpos($dsn,'+')+1)." RW=$mode_rw\n$queryToExec\n</pre>"; 
            $data = debug_backtrace();
            foreach($data as $entry) {
                 $call = '';
                 if ($entry['class'] && $entry['function'])
                 $call = ", llamada a {$entry['class']}->{$entry['function']}";
                 echo("{$entry['PEAR']}, linea:{$entry['line']}{$call}<br>");

            }
            flush();
        }

        if ($this->t_umbral != 0)
            $t1= microtime();

        $result = $db->query ($queryToExec);

        // Reintento si Lost Connection
        if (PEAR::isError($result))
	{
		$error = $result->getUserInfo();
		if (strstr($error, 'Lost connection') || strstr($error, 'server has gone away'))
        	{
            		$this->disconnect();
            		$this->connect($mode_rw);
            		return $this->runQuery($query, $from, $count);
        	}
	}

        $t2= microtime();
        list($t1_dec, $t1_sec) = explode(" ", $t1);
        list($t2_dec, $t2_sec) = explode(" ", $t2);
        $diff = ($t2_sec - $t1_sec) + ($t2_dec - $t1_dec);

        if ($_GET['tracesql']=="SI") { printf("<pre>%.2f segundos</pre>", $diff); }

        if ($this->t_umbral != 0)
        {
            if ($diff > $this->t_umbral )
            {
                global $HTTP_SERVER_VARS;
                global $HTTP_REFERER;
                global $HTTP_HOST;

                if ($HTTP_SERVER_VARS["HTTP_X_FORWARDED_FOR"]) {
                    if ($HTTP_SERVER_VARS["HTTP_CLIENT_IP"]) {
                        $proxy = $HTTP_SERVER_VARS["HTTP_CLIENT_IP"];
                    } else {
                        $proxy = $HTTP_SERVER_VARS["REMOTE_ADDR"];
                    }
                    $ip = $proxy."+".$HTTP_SERVER_VARS["HTTP_X_FORWARDED_FOR"];
                } else {
                    if ($HTTP_SERVER_VARS["HTTP_CLIENT_IP"]) {
                        $ip = $HTTP_SERVER_VARS["HTTP_CLIENT_IP"];
                    } else {
                        $ip = $HTTP_SERVER_VARS["REMOTE_ADDR"];
                    }
                }

                $fp=fopen("/SESIONES/tmp/slowqueries.log", "a");
                fputs($fp, sprintf("===================\n%s %s%s (viniendo desde %s)\nHORA:%s TIEMPO:%.2f DSN:%s\n%s\n\n\n", $ip, $HTTP_SERVER_VARS["SERVER_NAME"], $HTTP_SERVER_VARS["REQUEST_URI"], $HTTP_REFERER, date("Ymd H:i:s"), $diff, $dsn, $queryToExec));
                fclose($fp);
                $this->_logQuery($dsn, $query, $diff, 'slow');
            }
        }
        if(  defined('LOG_ERROR_QUERIES') && LOG_ERROR_QUERIES == true && PEAR::isError($result) ) {
            global $HTTP_SERVER_VARS;
            global $HTTP_REFERER;
            global $HTTP_HOST;

            $backtrace ='';
            $data = debug_backtrace();
            foreach($data as $entry)
                $backtrace .= $entry['PEAR'] . ", linea:" . $entry['line'] . ", function:" . $entry['function'] . "\n";

            $fp=fopen("/SESIONES/tmp/error_queries.log", "a");
            fputs($fp, sprintf("===================\n%s%s (referer %s)\nHORA:%s DSN:%s\n%s\n\n\n", $HTTP_SERVER_VARS["SERVER_NAME"], $HTTP_SERVER_VARS["REQUEST_URI"], $HTTP_REFERER, date("Ymd H:i:s"), $dsn, $queryToExec));
            fputs($fp, sprintf("PEAR_ERROR message : \n%s \n", $result->getMessage()  ));
            fputs($fp, sprintf("BACKTRACE: \n%s \n\n\n", $backtrace  ));
            fclose($fp);
            $this->_logQuery($dsn, $query, $diff, 'error');
        }


        return $result;
    }
	/**
	* Comprueba que $ds está ok.
	* @access public
	* @returns true si bien PEAR_Error si mal
	*/
	function checkDS($ds)
	{
		if (PEAR::isError($ds)) {
			return $ds;
		}

		if (!is_object($ds)) {
			return PEAR::raiseError("El ds No es un un objeto");
		}

		if (CLASS_PAFDBDATASOURCE != $ds->getClassType()) {
			return PEAR::raiseError("DS no es de tipo PAFDBDataSource");
		}

		// Comprobamos conexión
		if (!$ds->isConnected()) {
			return $ds->connect();
		}

		return true;
	}
 /**
     * Registra una query en el log indicado: SLOW o ERROR
     *
     * $log acepta 'SLOW' o 'ERROR'. En cualquier otro caso la función devuelve false;
     *
     * @access protected
     * @param string $dsn
     * @param string $query
     * @param float $diff
     * @param string $log
     * @return boolean
     */
    function _logQuery($dsn, $query, $diff, $log = 'slow')
    {
        switch($log)
        {
            case 'slow' :
                $fp1=fopen("/SESIONES/tmp/pafdbdatasource_slowqueries.log", "a");
                break;
            case 'error' :
                    $fp1=fopen("/SESIONES/tmp/pafdbdatasource_errorqueries.log", "a");
                break;

            default: return false;
        }

        $data = debug_backtrace();
        $backtrace = '';
        foreach($data as $i => $entry)
        {
            if($i == 0) continue;
            $backtrace .= $entry['PEAR'] . ":" . $entry['line'] . ", " . $entry['function'] . "----";
        }
            //
        // FORMATO: cada fila: remote_ip ; dsn ; datetime ; duration ; query ; url ; backtrace
        fputs($fp1,
               $_SERVER['REMOTE_ADDR'] . "<###>"
             . $dsn . "<###>"
             . date("Y-m-d H:i:s") . '<###>'
             . sprintf("%.2f", $diff) . '<###>'
             . str_replace(array("\n", "\r"), array(' ', ' '), $query) . '<###>'
             . ($_SERVER['HTTPS'] != '' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '<###>'
             . $backtrace // . '<###>'
             . "\n");
        fclose($fp1);

        return true;
    }

}

?>
