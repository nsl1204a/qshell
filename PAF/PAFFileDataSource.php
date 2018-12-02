<?php

  // *****************************************************************************
  // Lenguaje: PHP
  // Copyright 2002 Prisacom S.A.
  // ---------
  // ChangeLog
  // ---------
  // $Log: PAFFileDataSource.php,v $
  // Revision 1.9  2003/01/07 09:27:21  scruz
  // Modificados los m�todos de apertura de fichero disponibles.
  //
  // Revision 1.8  2003/01/02 16:37:24  scruz
  // Eliminados los modos de escritura y "append" de la lista de modos disponibles de apertura del DataSource.
  //
  // Revision 1.7  2002/08/01 15:15:16  sergio
  // A�adido paquete PAF a la documentaci�n.
  //
  // Revision 1.6  2002/08/01 14:51:39  sergio
  // Reestructuraci�n de la clase. Solo controla aperturas y cierres.
  //
  // Revision 1.5  2002/05/08 10:01:32  sergio
  // Modificaci�n en el constructor para adminitir un par�metro m�s con el nombre
  // de la clase de error asociada a ella.
  //
  // Revision 1.4  2002/04/30 09:46:39  sergio
  // Modificaciones en la documentaci�n de la clase.
  //
  // Revision 1.3  2002/04/29 16:11:05  sergio
  // Cambiado el m�todo getFiledNames por getFieldNames.
  //
  // Revision 1.2  2002/04/29 15:52:06  sergio
  // A�adido m�todo getFiledNames para recuperar el array con los nombres
  // de los campos que forman cada uno de los registros.
  //
  // Revision 1.1.1.1  2002/04/22 15:01:42  sergio
  // Creaci�n de estructura y primera subida de las clases generales
  //
  // *****************************************************************************


require_once "PAF/PAFDataSource.php";

/**
  * @const CLASS_PAFFILEDATASOURCE Constante para el identificador �nico de clase.
  */
if (! defined ("CLASS_PAFFILEDATASOURCE") )
    define ("CLASS_PAFFILEDATASOURCE", 5);

/**
  * Clase que encapsula la conexi�n a fuentes de datos de tipo Fichero.
  * S�lo se implementara connect y disconnect (abren y cierran el fichero).
  *
  * @author Virgilio Sanz <vsanz@prisacom.com>, Sergio Cruz <scruz@prisacom.com>
  * @version $Revision: 1.9 $
  * @access public
  * @package PAF
  */
class PAFFileDataSource extends PAFDataSource
{
    /**
      * Handler del fichero.
      *
      * @access private
      * @var mixed
      */
    var $fileHandler= null;

    /**
      * Nombre del Fichero de datos.
      *
      * @access private
      * @var string
      */
    var $fileName= null;

    /**
      * Modo de apertura,
      *
      * @see http://www.php.net/manual/es/function.fopen.php
      * @access private
      * @var string
      */
    var $openMode = null;

    /**
      * Posibles modos de apertura de la fuente de datos.
      * Contra este array se hace la comprobaci�n del modo de apertura del fichero que se especifica
      * en construcci�n.
      *
      * @access private
      * @var array
      */
    var $avOpeningModes= array ('r','rb','w','wb','a','ab');

    /**
      * Constructor
      *
      * @param string $filename Nombre completo del fichero de datos.
      * @param string $openMode Modo de apertura del fichero, por defecto "r".
      * @param string $errorClass Nombre de la clase de error asociada a PAFFileDataSource.
      * @access public
      */
    function PAFFileDataSource (
                                $fileName,
                                $openMode = 'r',
                                $errorClass= null
                               )
    {
        $this->PAFDataSource($errorClass);  // Llamada al constructor de la clase padre.
        $this->fileName= $fileName;
        $this->openMode= strtolower($openMode);
    }

    /**
      * M�todo est�tico para recuperar el identificador de la clase.
      *
      * @access public
      * @return int Identificador �nico de clase.
      */
    function getClassType()
    {
        return CLASS_PAFFILEDATASOURCE;
    }

    /**
      * M�todo est�tico que retorna el nombre de la clase.
      *
      * @access public
      * @return string Nombre de la clase.
      */
    function getClassName()
    {
        return "PAFFileDataSource";
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
      * M�todo de conexi�n al Fichero de datos.
      * Sobreescrita de PAFDataSource
      *
      * @access public
      * @return mixed TRUE si se ha conseguido conectar con �xito o un error en caso contrario.
      */
    function connect()
    {
	// Puede que se requiera que connect funcione cuando no hay aun fichero
	// As� se puede trabajar con multiples ficheros via setFileName y OpenSource
	if (!$this->fileName) return true;

        $ret= $this->openSource();
        if ( PEAR::isError ($ret) )
            return $ret;
        else
            return true;
    }

    /**
      * Realiza la apertura del fichero y sus controles pertinentes.
      * @access protected.
      */
    function openSource()
    {
        // Comprobamos que el modo de apertura del fichero es valido.
        if ( ! in_array ($this->openMode, $this->avOpeningModes) )
        {
            $this->setConnectionStatus(false);
            return PEAR::raiseError ("��� ERROR !!! (".__FILE__.",". __LINE__.") => El modo de apertura especificado (". $this->openMode. ") no es v�lido.<br>");
        }

        // Comprobamos si el fichero se puede leer
        if (!is_readable($this->fileName))
        {
            $this->setConnectionStatus(false);
            return PEAR::raiseError ("��� ERROR !!! (".__FILE__.",". __LINE__.") => No existe el fichero $this->fileName.<br>");
        }

        // En caso que queramos abrirlo para escritura comprobamos si se puede escribir.
        if (strchr($this->openMode, 'w') && !is_writable($this->fileName))
        {
            $this->setConnectionStatus(false);
            return PEAR::raiseError ("��� ERROR !!! (".__FILE__.",". __LINE__.") => No se puede escribir en el fichero $this->fileName.<br>");
        }

        $this->fileHandler= fopen($this->fileName, $this->openMode, true);
        if (!$this->fileHandler)
        {
            // TO DO Lanzamos un error espec�fico.
            $this->setConnectionStatus(false);
            return PEAR::raiseError ("��� ERROR !!! (".__FILE__.",". __LINE__.") => Fallo al abrir el fichero $this->fileName.<br>");
        }
        else
        {
            $this->setConnectionStatus(true);
            return true;
        }
    }

    /**
      * M�todo de desconexi�n del fichero.
      * Sobreescrito de PAFDataSource.
      *
      * @access public
      * @return mixed true si se ha conseguido desconectar con �xito de la fuente de datos o un error en caso contrario.
      */
    function disconnect()
    {
        if (!fclose($this->fileHandler))
        {
            // TO DO Lanzamos el error que corresponda.
            return PEAR::raiseError ("��� ERROR !!! (".__FILE__.",". __LINE__.") => No se ha podido desconectar la fuente de datos.<br>");
        }
        else
        {
            $this->setConnectionStatus(false);
            return true;
        }
    }

    /**
      * Devuelve el nombre de fichero al que se encuentra conectada la fuente de datos actual.
      *
      * @access public
      * @return string Nombre del fichero al que se encuentra conectada la fuente de datos
      */
    function getFileName()
    { return $this->fileName; }

    /**
      * Devuelve el modo de apertura del fichero al que se encuentra conectada la fuente de datos actual.
      *
      * @access public
      * @return string Modo de apertura del fichero al que se encuentra conectada la fuente de datos.
      */
    function getOpenMode()
    { return $this->openMode; }

    /**
      * Permite fijar de nuevo el nombre del fichero y el modo de apertura
      *
      * @access public
      * @return void
      */
    function setFileName($fileName, $openMode='r')
    { 
        $this->fileName=$fileName;
	$this->openMode=$openMode;
    }

}

?>
