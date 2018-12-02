<?php

// Importa la clase padre de la que deriva este Recordset.
require_once "PAF/PAFRecordSet.php";

// Importa las dos clases que implementan las fuentes de datos sobre las que puede
// actuar el Recordset.
require_once "PAF/PAFDBDataSource.php";
require_once "PAF/PAFFileDataSource.php";
require_once "userRecordData.php";

/**
  * Implementación de un Recordset para la obtención de los datos de usuario de una determinada fuente
  * de datos.
  *
  * Actualmente este recordset puede tomar sus datos tanto de una Base de Datos mysql como de un fichero
  * plano. En el caso del fichero plano
  *
  * Admite filtro por nombre así como por dirección de correo de los usuarios recuperados
  *
  */

class userRecordset extends PAFRecordset
{
    /**
      * Índice entero del registro actual en caso de que el conjunto de resultados se guarde en un
      * array.
      *
      * @access private
      */
    var $resultIndex= null;

    /**
      * Atributo de tipo cadena para mantener el valor del filtro por nombre de usuario.
      *
      * @access private
      */
    var $nameFilter= null;

    /**
      * Atributo de tipo cadena para mantener el valor del filtro por dirección de correo del usuario.
      *
      * @access private
      */
    var $mailFilter= null;

    /**
      * Valor de cadena que almacena el operador lógico de combinación de las dos condiciones
      * que forman los filtros. Puede tomar los valores AND u OR.
      * Si el valor es AND el filtro será $nameFilter AND $mailFilter.
      * Si el valor es OR el filtro será $nameFilter OR $mailFilter.
      *
      * @access private
      */
    var $condition= null;

    /**
      * Constructor
      *
      * @param $ds Objeto de tipo PAFDataSource del que obtendrá los datos el
      *        Recordset.
      * @param $nameFilter Valor del filtro por nombre de usuario.
      * @param $mailFilter Valor del filtro por dirección de correo del usuario.
      * @param $condition Condición a aplicar en la combinación de los dos filtros anteriores.
      *        (AND | OR). Por defecto toma "AND".
      *
      * @access public
      */
    function userRecordset($ds, $nameFilter= null, $mailFilter= null, $condition= "AND")
    {
        $this->PAFRecordset($ds);   // Llamada al constructor de la clase padre.

        $this->nameFilter= $nameFilter;
        $this->mailFilter= $mailFilter;
        $this->condition= $condition;
    }

    /**
      * Ejecución de la consulta sobre la Fuente de datos de usuarios.
      * Discrimina de qué tipo de fuente de datos estamos y deriva la ejecución
      * al método privado que contiene la implementación de la consulta sobre la fuente
      * de datos que corresponda.
      * Sobreescrita de la clase virtual PAFRecordset.
      *
      * @access public
      * @return true si se ha conseguido ejecutar con éxito la consulta sobre la fuente de datos
      *         o un objeto error en caso contrario.
      */
    function exec()
    {
        $ds= $this->getDataSource();

        switch ($ds->getClassType())
        {
            // La fuente de datos sobre la que se ejecuta el filtrado es una base
            // de datos.
            case CLASS_PAFDBDATASOURCE:
                return $this->execFromDB();
            break;

            // La fuente de datos sobre la que se ejecuta el filtrado es un
            // fichero plano de texto en formato CSV.
            case CLASS_PAFFILEDATASOURCE:
                return $this->execFromFile();
            break;

            // Comportamiento por defecto si se le pasa una fuente de datos
            // para la cual no se encuentra programado el Resultset.
            default:
                return PEAR::raiseError ("¡¡¡ ERROR !!! => Fuente de Datos no soportada en este ResultSet (userResultset)<br>");
        }
    }

    /**
      * Devuelve el siguiente registro del Resultset.
      * Dependiendo del tipo de fuente de datos utilizada para la obtención de los mismos este método
      * deriva su flujo hacia alguno de los métodos privados escritos al efecto.
      * Sobreescrita de la clase virtual PAFRecordset.
      *
      * @access public
      * @return Objeto PAFRecordData estándar para el tratamiento de los datos.
      */
    function next()
    {
        $ds= $this->getDataSource();

        switch ($ds->getClassType())
        {
            case CLASS_PAFDBDATASOURCE:
                return $this->nextFromDB();
            break;

            case CLASS_PAFFILEDATASOURCE:
                return $this->nextFromFile();
            break;
        }
    }

    /**
      * Devuelve el número de registros del Recordset.
      * Dependiendo del tipo de fuente de datos se deriva el flujo de ejecución hacia un método
      * privado que realiza la cuenta (dependiendo del tipo de objeto que sea $result -DBResult o array).
      *
      * @access public
      * @return int con el número de registros del Recordset.
      */
    function count()
    {
        $ds= $this->getDataSource();

        switch ( $ds->getClassType() )
        {
            case CLASS_PAFDBDATASOURCE:
                return $this->countFromDB();
            break;

            case CLASS_PAFFILEDATASOURCE:
                return $this->countFromFile();
            break;
        }
    }

    /**
      * Devuelve el número total de registros que proporciona el recordset. Esto es, pasa
      * por alto los límites $from y $count que se hayan podido especificar sobre la consulta.
      */
    function countAll()
    {
        $ds= $this->getDataSource();

        switch ( $ds->getClassType() )
        {
            case CLASS_PAFDBDATASOURCE:
                return $this->countAllFromDB();
            break;

            case CLASS_PAFFILEDATASOURCE:
                return $this->countAllFromFile();
            break;
        }
    }

    /**
      * Devuelve el contenido del filtro por nombre de usuario.
      *
      * @access public
      * @return String con el filtro de nombre de usuario activo.
      */
    function getNameFilter()
    {
        return $this->nameFilter;
    }

    /**
      * Devuelve el contenido del filtro por mail de usuario.
      *
      * @access public
      * @return String con el filtro de mail de usuario activo.
      */
    function getMailFilter()
    {
        return $this->mailFilter;
    }

    /**
      * Devuelve el contenido de la condicioón que combina los dos filtros.
      *
      * @access public
      * @return String con "AND" u "OR"
      */
    function getFilterCondition()
    {
        return $this->condition;
    }

    /**
      * Fija el valor para el filtro por nombre.
      *
      * @access public
      * @param $name String con el nuevo valor del filtro de nombre.
      */
    function setNameFilter($name)
    {
        $this->nameFilter= $name;
    }

    /**
      * Fija el valor para el filtro por mail.
      *
      * @access public
      * @param $mail String con el nuevo valor del filtro de mail.
      */
    function setMailFilter($mail)
    {
        $this->mailFilter= $mail;
    }

    /**
      * Fija el valor lógico con el combinan las dos condiciones de filtro.
      * Solo acepta "AND" u "OR".
      *
      * @access public
      * @param $condition String con el literal AND u OR.
      */
    function setFilterCondition($condition)
    {
        if ( strcasecmp ($condition, "AND") == 0 || strcasecmp ($condition, "OR") )
            $this->condition= $condition;
    }

    /**
      * Método privado para la consulta de usuarios sobre una Base de Datos.
      * Se encarga de formar la sentencia SQL que será enviada a la Base de datos para realizar
      * la consulta pertinente. El atributo $result de PAFRecord es fijado al manejador de resultado
      * proporcionado por la ejecución del query sobre la base de datos.
      *
      * @access private
      * @return true si se ha conseguido realizar la consulta correctamente sobre la base de datos o un
      *         objeto PEAR_Error si se ha producido algún problema.
      */
    function execFromDB()
    {
        $query= "SELECT * FROM usr_users";
        // Crea la parte Where del query.
        $this->fixWhere($query);

        // Fija los límites si los hubiera.
        if ($this->checkLimits())
            $this->result= $this->dataSource->runQuery ($query, $this->getFromLimit(), $this->getCountLimit());
        else
            $this->result= $this->dataSource->runQuery ($query);

        echo "<b>Query a ejecutar=> </b> " . $query . "<br>";            

        if ( PEAR::isError ($this->result) )
            return $this->result;
        else
            return true;
    }

    /**
      * Método que controla si hay que introducir alguna claúsula WHERE a la query que filtra los
      * datos de la base de datos si el recordset viniera con filtros especificados.
      * Este método es privado y solo se utiliza en el caso de que la consulta se realize sobre
      * una fuente de base de datos.
      *
      * @access private
      * @param string $query Referencia a la cadena que contiene el query al que se añade el filtro que
      *        corresponda.
      *
      * @return string Query modificado o no dependiendo del estado de los filtros del recordset.
      */
    function fixWhere (&$query)
    {
        if ( is_null ($this->nameFilter) && is_null($this->mailFilter) )
            return $query;

        if ( is_null ($this->nameFilter) && !is_null($this->mailFilter) )   // Filtra por mail
        {
            $query.= " WHERE usr_email='" . $this->mailFilter . "'";
        }
        elseif ( !is_null ($this->nameFilter) && is_null($this->mailFilter) )   // Filtra por nombre
        {
            $query.= " WHERE usr_nick='" . $this->nameFilter . "'";
        }
        elseif ( !is_null ($this->nameFilter) && !is_null($this->mailFilter) ) // Filtra por ambos.
        {
            if ( $this->condition == "AND")
                $query.= " WHERE usr_email='" . $this->mailFilter . "' AND usr_nick='" . $this->nameFilter . "'";
            elseif ($this->condition == "OR")
            {
                $query.= " WHERE usr_email='" . $this->mailFilter . "' OR usr_nick='" . $this->nameFilter . "'";
            }
         }

         return $query;
    }

    /**
      * Método privado para la consulta de usuarios sobre un Fichero de Datos.
      * El atributo destinado a guardar el resultado de la consulta es rellenado con un array
      * de datos que se corresponden con dicho resultado.
      *
      * @access private
      */
    function execFromFile()
    {
        $colName= 1;    // Índice de la columna de Nombre.
        $colMail= 3;    // Índice de la columna de e-mail.

        $this->result= array(); // Creamos el conjunto de resultados.

        $ds= $this->getDataSource();
        $ds->rewindFile();
        $count= $ds->count();
        for ($i= 0; $i < $count; $i++)
        {
            $record= $ds->readRecord();
            $name= $record[$colName];
            $email= $record[$colMail];

            if ( is_null ($this->nameFilter) && is_null($this->mailFilter) )    // No Filtra
            {
                $this->result[]= $record;
            }
            elseif ( is_null ($this->nameFilter) && !is_null($this->mailFilter) )   // Filtra por mail
            {
                if (strcmp (trim ($email), trim ($this->mailFilter)) == 0)
                    $this->result[]= $record;
            }
            elseif ( !is_null ($this->nameFilter) && is_null($this->mailFilter) )   // Filtra por nombre
            {
                if (strcmp (trim ($name), trim ($this->nameFilter)) == 0)
                {
                    $this->result[]= $record;
                }
            }
            elseif ( !is_null ($this->nameFilter) && !is_null($this->mailFilter) ) // Filtra por ambos.
            {
                if ( strcmp (trim ($this->condition), "AND") == 0)
                {
                    if ( (strcmp (trim ($name), trim ($this->nameFilter)) == 0) && (strcmp (trim ($email), trim ($this->mailFilter)) == 0 ))
                        $this->result[]= $record;
                }
                elseif (strcmp ($this->condition, "OR") == 0)
                {
                    if ( (strcmp (trim ($name), trim ($this->nameFilter)) == 0) || (strcmp(trim ($email), trim ($this->mailFilter)) == 0))
                        $this->result[]= $record;
                }
            }
        }

        // Actualiza el índice del array de resultados para que funcione next().
        $this->resultIndex= 0;
    }

    /**
      * Método privado que devuelve el siguiente registro del Recordset si este se ha recuperado desde
      * una fuente de datos de tipo "Base de Datos".
      *
      * @access private
      * @return Un objeto PAFRecordData para acceso a los datos del registro o false si no existen
      *         más registros..
      */
    function nextFromDB()
    {
        if (!PEAR::isError ($this->result) && !is_null ($this->result) )
        {
            $row= $this->result->fetchRow (DB_FETCHMODE_ASSOC);
            if ( is_null ($row) )
                return false;
            else
                return new userRecordData($row);
        }
        else
            return null;
    }

    /**
      * Método privado que devuelve el siguiente registro del Recordset si este se ha recuperado desde
      * una fuente de datos de tipo "Fichero plano".
      *
      * @access private
      * @return Un objeto PAFRecordData para acceso a los datos del registro.
      */
    function nextFromFile()
    {
        $ds= $this->getDataSource();

        $keys= $ds->getFieldNames();
        $values= $this->result[$this->resultIndex];

        $hash= array();
        for ($i= 0; $i < count ($keys); $i++)
            $hash[$keys[$i]]= $values[$i];

        $retValue= new userRecordData($hash);
        $this->resultIndex++;
        return $retValue;
    }

    /**
      * Método privado que devuelve el número de registros del Recordset en caso de que la fuente de datos
      * sea una Base de datos.
      *
      * @access private
      * @return int con el número de Registros o null si no se puede recuperar el número de registros.
      */
   function countFromDB()
   {
       if (!PEAR::isError ($this->result) && !is_null ($this->result) )
           return $this->result->numRows();
       else
          return null;
   }

   /**
     * Proporciona el número total de registros que proporcionaría la consulta en Base de Datos si no
     * tuviera límites $from, $count.
     *
     * @access public
     * @return int
     */
   function countAllFromDB()
   {
       if ( is_null ($this->countAll))
       {
           $query= "SELECT COUNT(*) FROM usr_users";
           $this->fixWhere ($query);
           $result= $this->dataSource->runQuery ($query);
           $row= $result->fetchRow ();
           $this->countAll= $row[0];
       }
       return $this->countAll;
   }

   /**
     * Método privado que devuelve el número de registros del Recordset en caso de que la fuente de datos
     * sea un fichero de datos plano.
     *
     * @access private
     * @return int con el número de Registros.
     */
   function countFromFile()
   {
       if ( is_array ($this->result) )
           return count ($this->result);
       else
           return false;
   }

   /**
     * Proporciona el número total de registros que proporcionaría la consulta en Fichero si no
     * tuviera límites $from, $count.
     *
     * @access public
     * @return int
     */
   function countAllFromFile()
   {
       if (! is_null ($this->countAll) )
           return $this->countAll;
       else
       {
           $ds= $this->getDataSource();
           return $ds->count();
       }
   }
}

?>