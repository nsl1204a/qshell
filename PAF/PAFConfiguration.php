<?php

// *****************************************************************************
// Lenguaje: PHP
// Copyright 2002 Prisacom S.A.
// *****************************************************************************

require_once "PAF/PAFObject.php";

define ("CLASS_PAFCONFIGURATION", 13);

/**
  * Clase de configuracion general.
  * Consiste en varias coleccion que el usuario debera rellenar con el contenido deseado.
  * Uno de los principales usos que tiene esta pagina es cargar en un solo objeto todos los datos
  * necesarios para la ejecucion de una pagina.
  * Las colecciones que contiene son:
  *
  * TO-DO: Introducir metodo existVariable para evitar machaques.
  *
  * . Colecci?n de DataSources.
  * . Colecci?n de Recordsets.
  * . Colecci?n de Variables.
  * . Colecci?n de Globales.
  *
  * @author Sergio Cruz <scruz@prisacom.com>
  * @version $Revision: 1.20 $
  * @access public
  * @package PAF
  */

class PAFConfiguration extends PAFObject
{
    /**
      * Coleccion de tipo Hash-Map destinada a contener DataSources. Su "key" representara
      * un identificador que queramos proporcionarle a cada una de las DataSources aqui
      * contenidas.
      *
      * @access private
      * @var array
      */
    var $dataSources= array();

    /**
      * Coleccion de tipo Hash-Map destinada a contener valores de variables. Su "key" representara
      * el nombre de la variable.
      *
      * @access private
      * @var array
      */
    var $variables= array();

    /**
      * Coleccion de tipo Hash-Map destinada a contener variables globales. Su "key" representara
      * el nombre de la variable.
      *
      * @access private
      * @var array
      */
    var $globals= array();

    /**
      * Atributo que mantiene el estado de bloqueo de un objeto PAFConfiguration.
      * Si dicho atributo tiene valor TRUE no se podra modificar ni aniadir ningun valor de los que
      * se encuentran almacenados en el.
      *
      * @access private
      * @var boolean
      */
    var $locked= false;

    /**
      * Constructor.
      * Esta clase añade en su construcción el array de GLOBALES generales que se producen
      * para la ejecución de un script php.
      *
      * @access public
      */
    function PAFConfiguration($errorClass= null)
    {
        $this->PAFObject($errorClass);
        $this->globals=& $GLOBALS;
    }

    /**
      * Método estático para recuperar el identificador de la clase.
      *
      * @access public
      * @return int Identificador único de clase
      */
    function getClassType()
    {
        return CLASS_PAFCONFIGURATION;
    }

    /**
      * Método estático que retorna el nombre de la clase.
      *
      * @access public
      * @return string Nombre de la clase.
      */
    function getClassName()
    {
        return "PAFConfiguration";
    }

    /**
      * Método de consulta para determinar si una clase es de un tipo determinado.
      * Reimplementado de PAFObject.
      *
      * @access public
      * @param int $tipo Número entero con el C?digo de clase por el que queremos preguntar .
      * @return boolean
      */
    function isTypeOf ($tipo)
    {
        return ( (PAFConfiguration::getClassType() == $tipo) || PAFObject::isTypeOf($tipo) );
    }

    /**
      * Devuelve el estado de bloqueo del objeto PAFConfiguration actual.
      *
      * @access public
      * @return boolean
      */
    function isLocked()
    {
        return $this->locked;
    }

    /**
      * Bloquea el objeto PAFConfiguration actual.
      *
      * @access public
      */
    function lock()
    {
        $this->locked= true;
    }

    /**
      * Desbloquea el objeto PAFConfiguration actual.
      *
      * @access public
      */
    function unlock()
    {
        $this->locked= false;
    }

    /**
      * Devuelve una referencia a la datasource contenida en la colección de datasources
      * y cuyo identificador coincida con el parámetro pasado.
      *
      * @access public
      * @param string $dataSourceId Cadena con el identificador de la DataSource que queremos
      *        recuperar.
      *
      * @return mixed Referencia al objeto PAFDataSource (o derivado) contenido en la colección
      *         de DataSources o un PEAR::Error en caso de que no se encuentre.
      */
    function & getDataSource ($dataSourceId)
    {
        if ( is_array($this->dataSources) && count ($this->dataSources)>0 )
        {
            if ( isset ($this->dataSources[$dataSourceId]) && ! empty ( $this->dataSources[$dataSourceId]) )
                return $this->dataSources[$dataSourceId];
        }

        $message= "¡¡¡ERROR !!! La fuente de datos con id " . $dataSourceId . " no existe en la colección de datasources.";
        return PEAR::raiseError ($message,1,PEAR_ERROR_RETURN);
    }

    /**
      * Añade una fuente de datos a la colección de fuentes de datos. Realmente lo que añade es una
      * referencia a la fuente de datos que le pasamos por parámetro y no una copia de la misma.
      * Si se especifica un id de DataSource existente se machaca la DataSource anterior con
      * la nueva que se pasa por parámetro.
      *
      * @access public
      * @param object $dataSource Objeto de tipo PAFDataSource o derivado.
      * @param string $dataSourceId Identificador con el que añadimos a la colección de dataSources
      *        la dataSource pasada por parámetro.
      * @return object PEAR::Error si el identificador con el que se pretende añadir la fuente de datos
      *         a la colección ya existe o el objeto PAFConfiguration se encuentra bloqueado.
      */
    function setDataSource ($dataSourceId, &$dataSource)
    {
        if ($this->isLocked())
        {
            $message= "¡¡¡ ERROR !!!=> Este objeto se encuentra bloqueado.";
            return PEAR::raiseError($message,1,PEAR_ERROR_RETURN);
        }
        // Comprobamos que realmente la colección de dataSources es un array
        // y si no lo creamos.
        if ( !is_array ($this->dataSources) )
        {
            unset ($this->dataSources);
            $this->dataSources= array();
        }

        $this->dataSources[$dataSourceId]=& $dataSource;
    }

    /**
      * Recupera de la colección de variables el valor de la variable cuyo identificador
      * se pasa por parámetro.
      *
      * @access public
      * @param string $variableId Identificador de la variable cuyo valor queremos recuperar.
      * @return mixed El valor de la variable cuyo identificador pasamos por parámetro o un PEAR::Error en
      *         caso contrario.
      */
    function & getVariable($variableId)
    {
        if ( !isset ($variableId) )
        {
            $message= "¡¡¡ ERROR !!!=> No se ha pasado un identificador de variable correcto.";
            return PEAR::raiseError($message,1,PEAR_ERROR_RETURN);
        }

        $keys= array_keys($this->variables);
        if ( ! in_array ($variableId, $keys) )
        {
            $message= "¡¡¡ ERROR !!!=> El identificador de variable " . $variableId . " no se encuentra en la colección de variables.";
            return PEAR::raiseError($message,1,PEAR_ERROR_RETURN);
        }

        return $this->variables[$variableId];
    }

    /**
      * Fija el valor de una variable dentro de la colecci?n de variables.
      * Si se especifica un id de Variable existente se machaca la Variable anterior con
      * la nueva que se pasa por par?metro.
      *
      * @access public
      * @param string $variableId Identificador que le damos a la variable.
      * @param mixed $variableValue Valor de la variable.
      * @return
      */
    function setVariable($variableId, $variableValue)
    {
        if ($this->isLocked())
        {
            $message= "¡¡¡ ERROR !!!=> Este objeto se encuentra bloqueado.";
            return PEAR::raiseError($message,1,PEAR_ERROR_RETURN);
        }

        // Comprobamos que realmente la colecci?n de variables es un array
        // y si no lo creamos.
        if ( !is_array ($this->variables) )
        {
            unset ($this->variables);
            $this->variables= array();
        }
        
        // Compruebo ?ntes si es un objeto, en este caso copio la referencia.
        if (is_object($variableValue)) {
            $this->variables[$variableId]=& $variableValue;
        }
        else {
            $this->variables[$variableId]= $variableValue;
        }
    }
    
    /**
      * Permite incorporar una referencia a una variable dentro de la coleccion de variables.
      * Util para el paso de objetos o arrays por referencia en vez de por valor como lo haria
      * el metodo setVariable.
      *
      * Si se especifica un id de Variable existente se machaca la Variable anterior con
      * la nueva que se pasa por parametro.
      *
      * @access public
      * @param string $variableId Identificador que le damos a la referencia dentro de la coleccion de variables..
      * @param mixed $Reference referencia a introducir dentro de la coleccion de variables.
      * @return
      */
    function setReference($variableId, &$Reference)
    {
        if ($this->isLocked())
        {
            $message= "¡¡¡ ERROR !!!=> Este objeto se encuentra bloqueado.";
            return PEAR::raiseError($message,1,PEAR_ERROR_RETURN);
        }

        // Comprobamos que realmente la colecci?n de variables es un array
        // y si no lo creamos.
        if ( !is_array ($this->variables) )
        {
            unset ($this->variables);
            $this->variables= array();
        }
        
        $this->variables[$variableId]=& $Reference;
    }
    
    
    /**
      * Recupera de la coleccion de variables el valor de la referencia cuyo identificador
      * se pasa por par?metro.
      *
      * @access public
      * @param string $variableId Identificador de la variable cuyo valor queremos recuperar.
      * @return mixed El valor de la variable cuyo identificador pasamos por par?metro o un PEAR::Error en
      *         caso contrario.
      */
    function & getReference($variableId)
    {
        if ( !isset ($variableId) || is_null($variableId) )
        {
            $message= "¡¡¡ ERROR !!!=> No se ha pasado un identificador de referencia correcto.";
            return PEAR::raiseError($message,1,PEAR_ERROR_RETURN);
        }

        if ( !isset ($this->variables[$variableId]) )
        {
            $message= "¡¡¡ ERROR !!!=> El identificador de referencia " . $variableId . " no se encuentra en la colección de variables.";
            return PEAR::raiseError($message,1,PEAR_ERROR_RETURN);
        }

        return $this->variables[$variableId];
    }
    

    /**
      * Recupera de la colección de globales el valor de la variable cuyo identificador
      * se pasa por parámetro.
      *
      * @access public
      * @param string $variableId Identificador de la variable cuyo valor queremos recuperar.
      * @return mixed El valor de la variable cuyo identificador pasamos por parámetro o un PEAR::Error en
      *         caso contrario.
      */
    function & getGlobal($globalId)
    {
        if ( !isset ($globalId) || is_null($globalId) )
        {
            $message= "¡¡¡ ERROR !!!=> No se ha pasado un identificador de global correcto.";
            return PEAR::raiseError($message,1,PEAR_ERROR_RETURN);
        }
        if ( !isset ($this->globals[$globalId]) )
        {
        	if(!isset ($this->globals['HTTP_SERVER_VARS'][$globalId]) ){
           		$message= "¡¡¡ ERROR !!!=> El identificador de global " . $globalId . " no se encuentra en la colección de globales.";
            	return PEAR::raiseError($message,1,PEAR_ERROR_RETURN);
        	}
        	else 
            	return $this->globals['HTTP_SERVER_VARS'][$globalId];
        }

        return $this->globals[$globalId];
    }

    /**
      * A?ade una global de datos a la colecci?n de globales.
      * Si se especifica un id de Global existente se machaca la Global anterior con
      * la nueva que se pasa por par?metro.
      *
      * @access public
      * @param object $dataSource Objeto de tipo PAFDataSource o derivado.
      * @param string $dataSourceId Identificador con el que a?adimos a la colecci?n de dataSources
      *        la dataSource pasada por par?metro.
      * @return object PEAR::Error si el identificador con el que se pretende a?adir la fuente de datos
      *         a la colecci?n ya existe.
      */
    function setGlobal ($globalId, $global)
    {
        if ($this->isLocked())
        {
            $message= "¡¡¡ ERROR !!!=> Este objeto se encuentra bloqueado.";
            return PEAR::raiseError($message,1,PEAR_ERROR_RETURN);
        }

        $GLOBALS[$globalId]= $global;
    }

    /**
      * Determina si el nombre de variable pasado por par?metro existe dentro del array
      * de Variables.
      *
      * @param string $name Nombre de la variable que queremos
      * @access public
      * @return boolean
      */

    function isDefinedVariable($name)
    {
        $keys= array_keys($this->variables);
        if ( in_array ( $name, $keys) )
            return true;
        else
            return false;
    }
    
	/**
      * Determina si el nombre de dataSource pasado por par?metro existe dentro del array
      * de Variables.
      *
      * @param string $name Nombre del dataSource que queremos
      * @access public
      * @return boolean
      */

    function isDefinedDataSource($name)
    {
        $keys= array_keys($this->dataSources);
        if ( in_array ( $name, $keys) )
            return true;
        else
            return false;
    }
}

?>
