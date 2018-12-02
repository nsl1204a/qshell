<?php
require_once "PAF/PAFObject.php";
require_once "PAF/PAFRecordData.php";

/**
  * @const CLASS_PAFRECORDSET Constante para el identificador único de clase.
  */
/*  
if (!defined ("CLASS_PAFUSERSCONTROL"))
     define ("CLASS_PAFUSERSCONTROL", "no se sabe cual poner");
*/


/**
  * Clase para control de acceso usuarios, desarrollada para no admitir votaciones masivas, envios masivos
  * de noticias, etc
  *
  * @author Antonio Rodriguez <antonio.rodriguez@grupomisiu.com>
  * @access public
  * @package PAF
  */

class PAFUsersControlTmp extends PAFObject
{
    /**
      * Atributo de tipo mixto que almacena la referencia a la fuente de datos actual del Recordset.
      *
      * @access private
      * @var mixed
      */
    var $dataSource= null;

    /**
      * Atributo de tipo mixto para mantener los resultados de una consulta a una fuente de datos.
      *
      * @access private
      * @var mixed
      */
    var $result= null;


    /**
      * Constructor.
      *
      * @access public
      * @param object PAFDataSource $ds Referencia a la fuente de datos (PAFDataSource) para el Recordset.
      * @param string $errorClass Nombre de la clase de error asociada a PAFUserControl.
      */
    function PAFUsersControlTmp(&$ds, $errorClass= null)
    {
        // Esta línea hace que la ejecución del script termine cuando se haga la
        // manipulación de un error lanzado desde ella.
        $this->PAFObject($errorClass);  // LLamada al constructor de la clase padre.
        $this->setErrorHandling(PEAR_ERROR_DIE); // Esto a lo mejor hay que cambiarlo pq es muy restrictivo.
        $this->dataSource=& $ds;
    }

    /**
      * Método estático para recuperar el identificador de la clase.
      *
      * @access public
      * @return int Identificador único de clase.
      */
/* Comentado porque no se sabe que tipo le corresponde      
    function getClassType()
    {
        return CLASS_PAFUSERSCONTROL;
    }
*/
    /**
      * Método estático que retorna el nombre de la clase.
      *
      * @access public
      * @return string Nombre de la clase.
      */
      
    function getClassName()
    {
        return "PAFUsersControl";
    }

    /**
      * Devuelve la fuente de datos a la que se conecta el Recordset.
      *
      * @access public
      * @return object PAFDataSource
      */
    function getDataSource()
    {
        return $this->dataSource;
    }

    /**
      * Método de consulta para determinar si una clase es de un tipo determinado.
      * Reimplementado de PAFObject.
      *
      * @access public
      * @param int $tipo Número entero con el Código de clase por el que queremos preguntar .
      * @return boolean
      */
/* Comentado porque no se sabe que tipo es      
    function isTypeOf ($tipo)
    {
        return  ( (PAFRecordset::getClassType() == $tipo) || PAFObject::isTypeOf($tipo) );
    }
*/
    /**
      * Fija la fuente de datos a la que se conecta el Recordset.
      *
      * @access public
      * @param object PAFDataSource $ds Fuente de datos.
      */
    function setDataSource(&$ds)
    {
        // To Do: Controlar que el objeto que se pasa es de tipo PAFDataSource.
        $this->dataSource= &$ds;
    }


    /**
      * Fija la fuente de datos a la que se conecta el Recordset.
      *
      * @access public
      * @param String $aplicacion Aplicacion para la que se hace el control de usuarios
      * @param int $time Tiempo que tiene que esperar el usuario para volver a utilizar la aplicacion
      *                  Por defecto son 5 minutos
      * @param mixed $id Identificador del usuario, puede ser la ip de la maquina que pide, login,....
      *                  por ahora nose usa, pero se podria quitar lo de coger la ip y usar este
      * @param mixed $user_agent Identificador del agente de usuario que hace la peticion (navegador cliente)
      */
    function comprobarUser($aplicacion, $time = 5, $id = null,$user_agent = null)
    {
       

	// AUTOLIMPIEZA DE LA BD

	// DSN="$driver://$user:$pwd@$protocol+$host/$dbname"
        ereg("(.*)://(.*):(.*)@(.*)\+(.*)/(.*)", $this->dataSource->getDSN(),$dsn_data);
	$dbhost = $dsn_data[5];
	$motor= $dsn_data[1];
	$defPuerto = (($motor == 'mysql')? 3306:5432);
	if (!strpos($dbhost, ':'))
	   $dbhost.=":$defPuerto";

	$min=date("i");
	if ($min < '10')
	{
	    clearstatcache();
	    $lockFile = "/SESIONES/user_control/$dbhost.lock";
	    $st = stat($lockFile);
	    if (!$st || (time() - $st[9]) > 30*60)
	    {
	    	touch($lockFile);
		$query = "delete from uc_control where uc_ctrl_time < '".date("Y-m-d H:i:s")."'";
		$this->dataSource->runQuery ($query);
	    }
	}
	// Control de referer
	// La petición debe ir con igual dominio que la página actual
	if ($ref = $_SERVER['HTTP_REFERER'])
	{
	   // Evitamos el efecto akamai
	   $ref_data = parse_url($ref);
	   $host_ref = str_replace('-org', '', $ref_data['host']);
	   $host = str_replace('-org', '', $_SERVER['HTTP_HOST']);
	   if ($host != $host_ref) return false;
	}
	//Cookie. Debe tener cookie ctrl
	/*$host_data = explode('.', $_SERVER['HTTP_HOST']);
	$cookie = $_COOKIE[$host_data[1]."_ctrl"];
	if (!$cookie) return false;*/

	// Control por IP
        $s_machine= $_SERVER['HTTP_X_FORWARDED_FOR']; 
        if (!$s_machine) { 
            $s_machine= $_SERVER['HTTP_CLIENT_IP']; 
            if (!$s_machine) { 
                $s_machine = $_SERVER['REMOTE_ADDR']; 
            } 
        } 

	// Si hay cadenas de proxies me quedo con la cadena hasta 
	// la primera IP publica
	if (strpos($s_machine, ','))
	{
	    $ips = explode(',', $s_machine);
	    $i=0;
	    $ips_buenas = array();
	    for ($i=0; $i<count($ips); $i++)
	    {
	       if ($ips[$i] == 'unknown') continue;

	       if (substr($ips[$i],0,7) == '192.168' || 
		   substr($ips[$i],0,3) == '10.' || 
		   substr($ips[$i],0,4) == '127.') 
	       {
	           $ips_buenas[] = $ips[$i];
	           continue;
	       }

	       $ips_buenas[] = $ips[$i];
	       break;
	    }
	    $s_machine = implode(',', $ips_buenas);
	}

	if (!$s_machine) return false;


	include("/SESIONES/user_control/filtros.inc");
	if ($filtro[$s_machine]) return false;
          
        if (!$id) {
            $id = $s_machine;
        }

	


	// Limitamos al espacio que hay en la BD para el campo
	$id = substr($id, 0, 255);

        // Comprobamos si el usuario ya está dado de alta
        $query = "SELECT * FROM uc_control 
                  WHERE uc_ctrl_machine='$id' 
                        AND uc_ctrl_app='".$aplicacion."' ORDER BY uc_ctrl_time ASC";
                        //AND uc_ctrl_browser='$user_agent'";

        $this->result= $this->dataSource->runQuery ($query);
        if (PEAR::isError($this->result)) {
            return $this->result;
        }

        $i_howmany = $this->result->numRows();
	$timelimit = time() + $time * 60;
        $s_timelimit = date("Y-m-d H:i:s", $timelimit);
        $s_time = date("Y-m-d H:i:s");

        // Si el usuario no esta dado de alta lo insertamos
        if ( !$i_howmany ) {
            $query = "INSERT INTO uc_control 
                                  (uc_ctrl_machine,uc_ctrl_app,uc_ctrl_browser,uc_ctrl_time)
                      VALUES ('$id', '$aplicacion' ,  '$user_agent' , '$s_timelimit')";


            $this->result= $this->dataSource->runQuery ($query);
            if (PEAR::isError($this->result)) {
                return $this->result;
            }
        }
        else { 
            // Comprobamos el tiempo transcurrido desde el ultimo evento

            $row= $this->result->fetchRow (DB_FETCHMODE_ASSOC);

            if (strtotime($row["uc_ctrl_time"]) < time()) {
                $query = "UPDATE uc_control 
                          SET uc_ctrl_time='$s_timelimit' 
                          WHERE uc_ctrl_machine='$id' 
                                AND uc_ctrl_app='$aplicacion'";
                                //AND uc_ctrl_browser='$user_agent'";

                $this->result= $this->dataSource->runQuery ($query);
                if (PEAR::isError($this->result)) {
                    return $this->result;
                }

            }
            else return false;
        }    

	$fp = @fopen("/SESIONES/user_control/".date("Ymd")."ucontrol.log", "a");
	if ($fp)
	{
	   $vars = str_replace("\n", "", print_r($_POST, true));
	   fwrite($fp, "\"$id\", \"$aplicacion\",  \"$vars\", \"$s_time\", \"$s_timelimit\"\n");
	   fclose($fp);
	}

        return true;
    }



}

?>
