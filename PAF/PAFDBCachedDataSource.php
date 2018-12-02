<?php

  // *****************************************************************************
  // Lenguaje: PHP
  // Copyright 2002 Prisacom S.A.
  //*****************************************************************************

require_once "PAF/PAFDataSource.php";
require_once "PAF/PAFLogFormat.php";
require_once "PAF/PAFCache.php";
require_once "PEAR/DB.php";

define ("DIR_CHACHE_CACHED_QUERIES", "/CACHE/cachedDS");


/**
  * @const CLASS_PAFDBDATASOURCE Constante para el identificador único de clase.
  */

define ("CLASS_PAFDBDATASOURCE", 4);

/**
  * Clase especializada de PAFDataSource que encapsula la conexión a fuentes de datos en Base de Datos.
  *
  * @author Sergio Cruz <scruz@prisacom.com>
  * @version $Revision: 1.2 $
  * @access public
  * @package PAF
  */

class PAFDBCachedDataSource extends PAFDataSource
{
    /**
      * Objeto PEAR::DB de Base de Datos encargado de realizar todas las operaciones específicas de Base de Datos.
      *
      * @access private
      */
    var $db= null;

    /**
      * Cadena que almacena el dsn que describe la fuente de la Base de datos.
      *
      * @access private
      * @var string
      */
    var $dsn= null;

    /**
      * Modo de conexión a la Base de datos (persistente o no)
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
      * Objeto Cache
      *
      * @access private
      * @var object
      */
    var $cache;

    /**
      * Tiempo de Cache de Refresco. 0 => Siempre actualiza. La cache se utiliza de respaldo
      *
      * @access private
      * @var integer
      */
    var $cache_time =0;

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
      * @param string $host Par de valores IP:Puerto que identifican unívocamente donde se encuentra
      *        alojado el Servidor de Base de Datos y el puerto TCP que utiliza para establecer
      *        las comunicaciones.
      * @param string $dbname Nombre de la Base de Datos dentro del Servidor con la que queremos
      *        conectarnos.
      * @param boolean $persistCon Indica si la conexión a la Base de Datos será
      *        persistente o no. Por defecto la conexión es no persistente.
      * @param string $protocol Protocolo de comunicaciones utilizado para las comunicaciones
      *        con el servidor de Base de Datos (tcp, unix, etc). Por defecto se utiliza
      *        TCP.
      * @param string $errorClass Nombre de la clase de error asociada con PAFDBDataSource.
      */
    function PAFDBCachedDataSource    (
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

        $this->PAFDataSource($errorClass);  // Llamada al constructor de la clase padre.
        $this->dsn= "$driver://$user:$pwd@$protocol+$host/$dbname";
        $this->cache = new PAFCache($host."_".$dbname, DIR_CHACHE_CACHED_QUERIES);
        $this->persist= $persistCon;
    }

    /**
      * Método estático para recuperar el identificador de la clase.
      *
      * @access public
      * @return int Identificador único de clase.
      */
    function getClassType()
    {
        return CLASS_PAFDBDATASOURCE;
    }

    /**
      * Método estático que retorna el nombre de la clase.
      *
      * @access public
      * @return string Nombre de la clase.
      */
    function getClassName()
    {
        return "PAFDBDataSource";
    }

    /**
      * Devuelve el nombre de la fuente de datos a la que se está conectado.
      *
      * @access public
      * @return string Cadena de conexión a la base de datos.
      */
    function getDSN()
    {
        return $this->dsn;
    }

    /**
      * Método de consulta para determinar si una clase es de un tipo determinado.
      * Reimplementado de PAFObject.
      *
      * @access public
      * @param int $tipo Número entero con el Código de clase por el que queremos preguntar .
      * @return boolean
      */
    function isTypeOf ($tipo)
    {
        return  ( (PAFDBDataSource::getClassType() == $tipo) || PAFDataSource::isTypeOf($tipo) );
    }

    function setCacheTime($cache_time)
    {
        $this->cache_time=$cache_time;
    }

    /**
      * Método de conexión a la Base de Datos.
      * Sobreescrita de PAFDataSource
      *
      * @access public
      * @return mixed TRUE si se ha podido realizar o un objeto DBError en caso contrario. Para recuperar
      *         el error proporcionado por el driver nativo de BD utilizado se puede invocar al método de
      *         la clase DBError llamado getDebugInfo().
      */
    function connect()
    {
        return true;
    }

    function _connect()
    {
        global $tracesql; 
        if (!$this->getConnectionStatus())
        { 
            $this->db= DB::connect ($this->dsn, $this->persist);
            if ($tracesql=="SI" && substr($_SERVER['REMOTE_ADDR'],0,3)=='10.') { echo "<pre>Conexion con ".substr($this->dsn, strrpos($this->dsn,'+')+1)."\n</pre>"; flush();} 
        }

        if ( PEAR::isError($this->db) )
        {
            $this->setConnectionStatus(false);
            return $this->db;
        }
        else
        {
            $this->setConnectionStatus(true);
            return true;
        }
    }

    /**
      * Método de desconexión a la Base de Datos sobreescrito de PAFDataSource.
      * Realiza la desconexión de la Base de Datos.
      *
      * @access public
      */
    function disconnect()
    {
        if (!PEAR::isError($this->db)) {
            if (true == $this->getConnectionStatus()) {
                $tmp = $this->db->disconnect();
                if ($tmp) {
                    $this->setConnectionStatus(false);
                }
            }
        }
    }


    /**
     * Devuelve el número de filas afectadas en la última query.
     *
     * @access public
     * @return integer Cantidad de filas afectadas en la última query.
     */
    function affectedRows()
    {
        return($this->db->affectedRows());
    }

    /**
     * Devuelve el id colocado como clave primaria en la ultima inserción
     *
     * @access public
     * @return integer
     */
    function lastInsertId()
    {
        $sqltype =& $this->db->dbsyntax; // $sqltype = $this->ds->db->phptype;

        if ('mysql' == $sqltype) {
            $id = mysql_insert_id($this->db->connection);
        } else {
            $msg = PAFLogFormat::format(__FILE__, __LINE__, "DataBase not supported");
            $id = PEAR::raiseError($msg);
        }
        return($id);
    }
    /**
      * Método para la ejecución de una consulta sobre la base de datos.
      * Este método no pertenece a la interface PAFDataSource, lo implementamos
      * necesidad o comodidad.
      *
      * @access public.
      * @param string $query Cadena con elquery a enviar a la Base de Datos.
      * @param int $from Límite inferior desde el que se quieren recuperar registros.
      * @param int $count Número total de registros a recuperar.
      * @return object DBResult PEAR::DBResult resultado de llamar a db->query.
      */
    function runQuery ($query, $from= -1, $count= 0)
    {
       $queryToExec= null;

       if ($from >= 0 && $count > 0)

           $queryToExec= $this->db->modifyLimitQuery($query, $from, $count);
       else
           $queryToExec= $query;

       $queryToExec = trim($queryToExec);
       $run=false;
       $is_cached = (strtoupper(substr($queryToExec, 0, 6)) == "SELECT");
       if ($this->cache_time>0 && $is_cached)
       {
           $clave = md5($queryToExec);
           $run = $this->cache->updatedCache ($this->cache_time, $clave);
       }
       else $run = true;

       if ($run)
       {
           $result = $this->_runQuery($queryToExec);
           if (PEAR::isError($result)) PEAR::raiseError($this->dsn.": ".$result->getMessage(),0,PEAR_ERROR_TRIGGER);
	   
           if ($is_cached)
           {
               if (!PEAR::isError($result))
               {
                   $row_set = array();
                   while ($row_set[] = $result->fetchRow (DB_FETCHMODE_ASSOC)); 

                   if ($is_cached)
                        $this->cache->writeCache($clave, "\$row_set=".var_export($row_set, true).";\n");
                   return new DBResult($row_set);
               }
	       // En caso de error seguimos leyendo de cache
           }
           else return $result;
        }

        $txt = $this->cache->readCache($clave);
        eval($txt);
        return new DBResult($row_set);
    }
        


    function _runQuery($queryToExec)
    {
       $result = $this->_connect();

       if (PEAR::isError($result)) return $result;

       global $tracesql; if ($tracesql=="SI" && substr($_SERVER['REMOTE_ADDR'],0,3)=='10.') { echo "<pre>".substr($this->dsn, strrpos($this->dsn,'+')+1)."\n$queryToExec\n</pre>"; flush(); }

       $t1= microtime();
       $result = $this->db->query ($queryToExec);
       if (PEAR::isError($result)) return $result;

       $t2= microtime();
       list($t1_dec, $t1_sec) = explode(" ", $t1);
       list($t2_dec, $t2_sec) = explode(" ", $t2);
       $diff = ($t2_sec - $t1_sec) + ($t2_dec - $t1_dec);

       if ($tracesql=="SI" && substr($_SERVER['REMOTE_ADDR'],0,3)=='10.') { printf("<pre>%.2f segundos</pre>", $diff); }

       if ($this->t_umbral != 0 && $diff > $this->t_umbral)
       {
           global $HTTP_SERVER_VARS;
           global $HTTP_REFERER;
           global $HTTP_HOST;

           if ($HTTP_SERVER_VARS["HTTP_X_FORWARDED_FOR"]) 
           {
               if ($HTTP_SERVER_VARS["HTTP_CLIENT_IP"])
                   $proxy = $HTTP_SERVER_VARS["HTTP_CLIENT_IP"];
               else
                   $proxy = $HTTP_SERVER_VARS["REMOTE_ADDR"];
               $ip = $proxy."+".$HTTP_SERVER_VARS["HTTP_X_FORWARDED_FOR"];
           }
           else
           {
               if ($HTTP_SERVER_VARS["HTTP_CLIENT_IP"])
                   $ip = $HTTP_SERVER_VARS["HTTP_CLIENT_IP"];
               else
                   $ip = $HTTP_SERVER_VARS["REMOTE_ADDR"];
           }


           if ($HTTP_HOST=="www.los40.com"){
               $fp=fopen("/SESIONES/tmp/slowqueries_los40.log", "a");
           
           }else{
               $fp=fopen("/SESIONES/tmp/slowqueries.log", "a");
           }
           fputs($fp, sprintf("===================\n%s %s%s (viniendo desde %s)\nHORA:%s TIEMPO:%.2f DSN:%s\n%s\n\n\n", $ip, $HTTP_SERVER_VARS["SERVER_NAME"], $HTTP_SERVER_VARS["REQUEST_URI"], $HTTP_REFERER, date("Ymd H:i:s"), $diff, $this->dsn, $queryToExec));
           fclose($fp);
       }

       return $result;
    }
    /**
    * Comprueba que $ds estÃ¡ ok.
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

        // Comprobamos conexiÃ³n
        if (!$ds->isConnected()) {
            return $ds->connect();
        }

        return true;
    }

}

class DBResult
{
        var $row_set;
        function DBResult($row_set)
        {
                $this->row_set = $row_set;
		reset($this->row_set);
        }

        function fetchRow($mode=NULL)
        {
                $row = current($this->row_set);
                next($this->row_set);
		if ($mode != DB_FETCHMODE_ASSOC) $row = array_values($row);
		return $row;
        }
        
        function numRows()
        {
                return count($this->row_set);
        }
}
?>
