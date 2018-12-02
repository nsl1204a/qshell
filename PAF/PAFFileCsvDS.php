<?php

  // *****************************************************************************
  // Lenguaje: PHP
  // Copyright 2002 Prisacom S.A.
  // ---------
  // ChangeLog
  // ---------
  // $Log: PAFFileCsvDS.php,v $
  // Revision 1.3  2003/01/03 09:22:33  scruz
  // Modificación de los modos de apertura disponibles.
  //
  // Revision 1.2  2002/08/01 15:16:48  sergio
  // Añadido paquete PAF a la documentación.
  //
  // Revision 1.1  2002/08/01 14:52:08  sergio
  // FileDataSource para ficheros CSV.
  //
  //
  // *****************************************************************************

require_once "PAF/PAFFileDataSource.php";

/**
  * Clase que encapsula la conexión a fuentes de datos de tipo Fichero CSV.
  * Un fichero CSV implementa sus registros por líneas y cada uno de los campos
  * de un registros separados con un determinado carácter. Suponemos que dichos
  * ficheros contienen en su primer registro los identificadores textuales de
  * los campos.
  *
  * @author Virgilio Sanz <vsanz@prisacom.com>, Sergio Cruz <scruz@prisacom.com>
  * @version $Revision: 1.3 $
  * @access public
  * @package PAF
  */
class PAFFileCsvDS extends PAFFileDataSource
{
    /**
      * Caracter de separación de campos.
      *
      * @access private
      * @var string
      */
    var $fieldSeparator= null;

    /**
      * Flag que indica si la primera línea del fichero de datos contiene los nombres
      * de los diferentes campos.
      *
      * @access private
      * @var boolean
      */
    var $withRecordTags= null;

    /**
      * Máxima longitud en bytes que puede tener un registro.
      *
      * @access private
      * @var integer
      */
    var $maxLengthRecord= null;

    /**
      * Array que contiene los nombres de los campos de los registros.
      *
      * @access private
      * @var array
      */
    var $fieldNames= null;

    /**
      * Constructor
      *
      * @param string $filename Nombre completo del fichero de datos.
      * @param boolean $withRecordTags inica si la primera línea del fichero contiene los nombres de los
      *        campos.
      * @param string $openMode Modo de apertura del fichero.
      * @param string $separator Caracter de separación utilizado en el fichero para
      *        separar los campos. Por defecto se utiliza "," si no se especifica
      *        de otro modo en la construcción del objeto.
      * @param int $lengthRecord Longitud máxima del registro (línea de fichero) que se puede leer.
      * @param string $errorClass Nombre de la clase de error asociada a PAFFileDataSource.
      * @access public
      *
      * NOTA: El parámetro $lengthRecord viene obligado por la función fgetscsv()
      *       que trae PHP. Si decidimos no utilizar dicha función este parámetro
      *       es muy probable que desaparezca.
      */
    function PAFFileCsvDS   (
                                $fileName,
                                $withRecordTags= true,
                                $openMode='r',
                                $separator=',',
                                $lengthRecord= 10000,
                                $errorClass= null
                            )
    {
        $this->PAFFileDataSource($fileName, $openMode, $errorClass);  // Llamada al constructor de la clase padre.

        $this->withRecordTags= $withRecordTags;
        $this->fieldSeparator= $separator;
        $this->maxLengthRecord= $lengthRecord;
    }

    /**
      * Devuelve el caracter utilizado por el fichero csv que estemos tratando para separar los campos.
      *
      * @access public
      * @return string Caracter de separación de campos utilizado por el fichero de datos.
      */
    function getFieldSeparator()
    {
        return $this->fieldSeparator;
    }

    /**
      * Devuelve la longitud máxima que puede tener un registro dentro del fichero de datos.
      */
    function getLengthRecord()
    {
        return $this->maxLengthRecord;
    }

    /**
      * Devuelve si el fichero de datos contiene en su primera línea los nombres
      * de los diferentes campos.
      *
      * @access public
      * @return boolean
      */
    function hasRecordTags()
    {
        return $this->withRecordTags;
    }

    /**
      * Devuelve el array con los nombres de las columnas (campos).
      *
      * @access public
      * @return array Nombres de los campos que forman cada uno de los registros.
      */
    function getFieldNames()
    {
        return $this->fieldNames;
    }

    /**
      * Función de conexión a la fuente de datos.
      * Sobreescrita de PAFFileDataSource.
      */
    function connect()
    {
        $con= $this->openSource();
        if ( PEAR::isError ($con) )
            return $con;

        // Rellenamos el array con los nombres de los campos en el caso de que
        // el fichero de datos incorpora en su primera línea dichos nombres.
        if ( $this->hasRecordTags() )
            $this->init();
    }

    /**
      * Devuelve el siguiente registro en orden secuencial.
      * Sobreescrita de PAFDataSource.
      * NOTA: Vamos a probar primeramente con la función que trae PHP
      *       para leer ficheros CVS fgetcsv. Si vemos que da más problemas
      *       que soluciones trataremos de programarlo nosotros.
      *
      * @access public
      * @return mixed array Datos del registro o false si se ha producido un error o se ha llegado al final de fichero.
      */
    function next()
    {
        return $this->readRecord();
    }

    /**
      * Se posiciona sobre el primer registro del fichero de datos.
      *
      * @access public
      */
    function first()
    {
        $this->rewindFile();

        // Si el fichero de datos lleva en su primera línea los nombres de los campos
        // tenemos que leer dicha línea para posicionarnos sobre el primer registro de
        // datos reales.
        if ($this->withRecordTags)
            $this->readRecord();
    }

    /**
      * Proporciona el número de registros que tiene el fichero.
      * Deja el fichero posicionado sobre su primer registro de datos.
      *
      * @access public
      * @return int Número total de registros.
      */
    function count()
    {
        $count= 0;
        $this->first();
        while ( $value= $this->next() )
            $count++;
        $this->first();
        return $count;
    }

    /**
      * Método para la lectura de un registro.
      * Si el fichero contiene nombres de campos devuelve un array cuyas keys son los nombres de las columnas.
      * Si el fichero no contiene nombres de campos devuelve el array con los datos de registro para acceder
      * a ellos por índice.
      *
      * @access private
      * @return mixed array Datos leídos del registro o false en caso de que se produzca algún error o se haya alcanzado el final de fichero.
      */
    function readRecord()
    {
        $row= fgetcsv (
                          $this->fileHandler,
                          $this->maxLengthRecord,
                          $this->fieldSeparator
                      );
        if (!$row)
            return false;

        if ($this->hasRecordTags())
        {
            $columnNames= $this->getFieldNames();
            $extendedResult= array();
            if (! is_null ($columnNames))
            {
                $numColumns= count($row);
                for ($i= 0; $i < $numColumns; $i++)
                {
                    $extendedResult[$columnNames[$i]]= $row[$i];
                }
                return $extendedResult;
            }
        }
        return $row;
      }

    /**
      * Resetea el puntero del fichero a su primera posición.
      *
      * @access private
      */
    function rewindFile()
    {
        rewind($this->fileHandler);
    }

    /**
      * Inicializa el array con los nombres de los campos de los registros.
      * Llamado en la construcción del objeto.
      *
      * @access private
      * @return boolean TRUE si se ha conseguido rellenar el array con los campos o FALSE en caso contrario.
      */
    function init()
    {
        $value= $this->readRecord();
        if ( is_array ($value) )
        {
            $this->fieldNames= array();
            $this->fieldNames= $value;
        }
    }
}
?>
