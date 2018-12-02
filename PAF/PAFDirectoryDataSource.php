<?php

  // *****************************************************************************
  // Lenguaje: PHP
  // Copyright 2002 Prisacom S.A.
  //
  // *****************************************************************************


require_once "PAF/PAFDataSource.php";

/**
  * @const CLASS_PAFDIRECTORYDATASOURCE Constante para el identificador �nico de clase.
  */
if (! defined ("CLASS_PAFDIRECTORYDATASOURCE") )
    define ("CLASS_PAFDIRECTORYDATASOURCE", 15);

/**
  * Clase que encapsula la conexi�n a un directorio
  * S�lo se implementara connect y disconnect (abren y cierran el directorio).
  *
  * @author ljimenez 
  * @access public
  * @package PAF
  */
  
class PAFDirectoryDataSource extends PAFDataSource
{
    /**
      * Handler del directorio.
      *
      * @access private
      * @var mixed
      */
    var $directoryHandler= null;

    /**
      * Nombre del Directorio
      *
      * @access private
      * @var string
      */
    var $directoryName= null;


    /**
      * Constructor
      *
      * @param string $filename Nombre completo del directorio
      * @access public
      */
    function PAFDirectoryDataSource ( $directoryName)
    {
        $this->PAFDataSource();  // Llamada al constructor de la clase padre.
        $this->directoryName= $directoryName;
    }

    /**
      * M�todo est�tico para recuperar el identificador de la clase.
      *
      * @access public
      * @return int Identificador �nico de clase.
      */
    function getClassType()
    {
        return CLASS_PAFDIRECTORYDATASOURCE;
    }

    /**
      * M�todo est�tico que retorna el nombre de la clase.
      *
      * @access public
      * @return string Nombre de la clase.
      */
    function getClassName()
    {
        return "PAFDirectoryDataSource";
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
        return ( (PAFFileDataSource::getClassType() == $tipo) || PAFDataSource::isTypeOf($tipo) );
    }


    /**
      * Realiza la apertura del directorio y sus controles pertinentes.
      * @access protected.
      */
    function connect()
    {

        // Comprobamos si el directorio se puede leer
        if (!is_dir($this->directoryName))
        {
            $this->setConnectionStatus(false);
            return PEAR::raiseError ("��� ERROR !!! (".__FILE__.",". __LINE__.") => No existe el directorio $this->directoryName.<br>");
        }


        $this->directoryHandler= opendir($this->directoryName);
        if (!$this->directoryHandler)
        {
            $this->setConnectionStatus(false);
            return PEAR::raiseError ("��� ERROR !!! (".__FILE__.",". __LINE__.") => Fallo al abrir el directorio $this->directoryName.<br>");
        }
		$this->setConnectionStatus(true);
        return true;
    }


    /**
      * M�todo de desconexi�n del directorio.
      * Sobreescrito de PAFDataSource.
      *
      * @access public
      * @return mixed true si se ha conseguido desconectar con �xito de la fuente de datos o un error en caso contrario.
      */
    function disconnect()
    {
		closedir($this->directoryHandler);
        $this->setConnectionStatus(false);
        return true;
		/*
        if (!closedir($this->directoryHandler))
        {
            return PEAR::raiseError ("��� ERROR !!! (".__FILE__.",". __LINE__.") => No se ha podido desconectar la fuente de datos.<br>");
        }
        else
        {
            $this->setConnectionStatus(false);
            return true;
        }
		*/
	}

    /**
      * Devuelve el siguiente registro en orden secuencial.
      * Sobreescrita de PAFDataSource.
      * @access public
      * @return mixed array Datos del registro o false si se ha producido un error o se ha llegado al final de fichero.
      */
    function next()
    {
        return $this->readRecord();
    }

    /**
      * Se posiciona sobre el primer registro del directorio
      *
      * @access public
      */
    function first()
    {
        $this->rewindDirectory();
	}

    /**
      * Proporciona el n�mero de registros que tiene el fichero.
      * Deja el fichero posicionado sobre su primer registro de datos.
      *
	  * NOTA: Se resta dos al n�mero total de ficheros para no contar "." y ".." 
      * @access public
      * @return int N�mero total de registros.
      */
    function count()
    {
        $count= 0;
        while ( $value= $this->next() )
            $count++;
        return $count-2;
    }

    /**
      * M�todo para la lectura de un registro.
      * @access private
      * @return mixed array Datos le�dos del registro o false en caso de que se produzca alg�n error o se haya alcanzado el final de fichero.
      */
    function readRecord()
    {
        $row= readdir($this->directoryHandler);
        if (!$row)
            return false;

        return $row;
      }
    /**
      * Resetea el puntero del directorio a su primera posici�n.
      *
      * @access private
      */
    function rewindDirectory()
    {
        rewinddir($this->directoryHandler);
    }

    

    /**
      * Devuelve el nombre del directorio  al que se encuentra conectada la fuente de datos actual.
      *
      * @access public
      * @return string Nombre del fichero al que se encuentra conectada la fuente de datos
      */
    function getDirectoryName()
    { 
			return $this->directoryName; 
	}


}

?>
