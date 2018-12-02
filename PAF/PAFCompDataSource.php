<?php

  // *****************************************************************************
  // Lenguaje: PHP
  // Copyright 2002 Prisacom S.A.
  //*****************************************************************************

require_once "PAF/PAFDataSource.php";
require_once "PES/PESDirectComp.php";
require_once "PES/PESTransRoutes.php";
/**
  * @const CLASS_PAFCOMPDATASOURCE Constante para el identificador �nico de clase.
  */

// A Prisacom Desarrollo: BASE_CLASS_ID solo tiene sentido en el proyecto M40
// desarrollado por Lavinia TC, en cuanto esta clase como estandar, setear la constante absoluta
define ("CLASS_PAFCOMPDATASOURCE", (BASE_CLASS_ID+36));

/**
  * Clase especializada de PAFDataSource que encapsula la conexi�n a fuentes de datos en Ficheros de composici�n.
  *
  * @author Juane Puig <jpuig@lavinia.tc>
  * @version $Revision: 1.1 $
  * @access public
  * @package PAF
  */

class PAFCompDataSource extends PAFDataSource
{
    /**
      * Cadena que almacena la ruta base donde se encuentran los ficheros de composici�n.
      *
      * @access private
      * @var string
      */
    var $path= null;

    /**
      * Cadena que almacena el nombre del fichero de composici�n
      *
      * @access private
      * @var string
      */
    var $file= false;
	
	/**
	  * 
	  * Objetos PESTransRoutes que permitir� averiguar las rutas de los ficheros de composici�n
	  * as� como de los ficheros NITF utilizados por los RecordSet
	  * @access private
	  * @var PESTranRoutes
	  */
	 var $trs_obj = null;
	 
	 /**
	  * Array de rutas completas a ficheros de composici�n
	  * @access private
	  * @var Array
	  */ 
	 var $cmpFiles = array();
	 
	 /**
	  * Array de objetos PAFDirectComp
	  * @access private
	  * @var Array
	  */ 
	 var $cmpObjs = array();
	 
	 /**
	 * M�scara para construir el fichero de composici�n
	 * Formato YYYYMMDDmedpubsec (%s%s%s%s)
	 * Donde 		YYYYMMDD: 	fecha
	 * 				med: 		medio
	 * 				pub:		publicacion
	 * 				sec:		secci�n
	 */
	 var $mask = null;

    /**
      * Constructor
      * Constructor al que se le pasa la ruta base donde se encontraran los ficheros de composici�n y el nombre del fichero.
      * @access public
      * @param string $path Ruta base donde se encuentran los ficheros de composici�n
      * @param string $file Nombre del fichero de composici�n o mascara
      * @param string $errorClass Nombre de la clase de error asociada con PAFCompDataSource.
      */
    function PAFCompDataSource    (
                                    $path,
                                    $file=0,
                                    $errorClass= null
                                    )
    {
        $this->PAFDataSource($errorClass);  // Llamada al constructor de la clase padre.
        $this->path= $path;
		$this->trs_obj = new PESTransRoutes($this->path);
		
		if (strpos($file,'%')>=0)
		{
			// Tenemos una m�scara, la definici�n del datasource ser�
			// detallada en el RecorSet
			$this->mask = $file;
		}
		elseif ($file!=0)
		{
	        $this->file= $file;
			$adCmp = $this->addCompFile($this->file);
			if (PEAR::isError($adCmp)){
				echo $adCmp->getMessage() ;
				die( );
			}
		}
	}
	
	/**
	 * PAFCompDataSource::addCompFile()
	 * A�ade un nombre de fichero de composici�n al conjunto de ficheros que conforman la fuente de datos
	 * @access public
	 * @param $file nombre de fichero de composici�n
	 * @return mixed TRUE si todo va bien, o un PEAR:error
	 */
	function addCompFile($file)
	{
		$this->trs_obj->checkRoutes($file);
		$aux = $this->trs_obj->XmlPath()."/".$file;

		if (!file_exists($aux))
		{
			return  PEAR::raiseError("Fichero de composici�n inexistente ($aux)");
		}
		$this->cmpFiles[count($this->cmpFiles)] = $aux;
	}
	
	function countCmpObj()
	{
		return count($this->cmpObjs);
	}
	
	/**
	 * PAFCompDataSource::addCompFile()
	 * A�ade un nuevo fichero de composici�n al conjunto de ficheros que conforman la fuente de datos
	 * @access public
	 * @param $file
	 * @return 
	 */
	function getCmpObj($index)
	{
		if ($index>count($this->cmpObjs)-1){
			$this = PEAR::raiseError("Se ha sobrepasado el rango del array de objetos Comp");
			return $this;
		}
		return $this->cmpObjs[$index];
	}

	function getMask()
	{
		return $this->mask;
	}
	
    /**
      * Devuelve el nombre de la fuente de datos a la que se est� conectado.
      *
      * @access public
      * @return string Cadena de conexi�n a la base de datos.
      */
    function NOT_USED_getDSN()
    {
        return $this->dsn;
    }

    /**
      * Iniciar las estructuras de datos que tratan los ficheros de composicion'.
      * Sobreescrita de PAFDataSource
      *
      * @access public
      * @return mixed TRUE si se ha podido realizar o un objeto PEARError en caso contrario. P
      */
    function connect()
    {
		   	reset($this->cmpObjs);
			for ($i=0; $i<count($this->cmpFiles); $i++)
			{
				$this->cmpObjs[$i] = new PESDirectComp($this->cmpFiles[$i], true);
				if ( PEAR::isError($this->cmpObjs[$i]) )
		        {
	               $this->setConnectionStatus(false);
	               return $this->cmpObjs[$i];
		        }
			}
		$this->setConnectionStatus(true);
        return true;
	}

    /**
      * M�todo de desconexi�n a la Base de Datos sobreescrito de PAFDataSource.
      * Realiza la desconexi�n de la Base de Datos.
      *
      * @access public
      */
    function disconnect()
    {
        if (true == $this->getConnectionStatus()) {
       		$this->cmpObjs = null;
			$this->setConnectionStatus(false);
        }
    }
    

    	

    /**
      * M�todo est�tico para recuperar el identificador de la clase.
      *
      * @access public
      * @return int Identificador �nico de clase.
      */
    function getClassType()
    {
        return CLASS_PAFCOMPDATASOURCE;
    }

    /**
      * M�todo est�tico que retorna el nombre de la clase.
      *
      * @access public
      * @return string Nombre de la clase.
      */
    function getClassName()
    {
        return "PAFCompDataSource";
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
        return  ( (PAFCompDataSource::getClassType() == $tipo) || PAFDataSource::isTypeOf($tipo) );
    }


}

?>
