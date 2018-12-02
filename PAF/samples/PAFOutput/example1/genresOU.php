<?php

  // *****************************************************************************
  // Lenguaje: PHP
  // Copyright 2002 Prisacom S.A.
  // *****************************************************************************
  
require_once "PEAR/Benchmark/Timer.php";
require_once "PAF/PAFOutput.php";
require_once "PAF/PAFTemplate.php";
require_once "genresRS.php";

/**
  * Ejemplo 1 de clase Output. Proporciona la salida física
  * Clase de salida para géneros.
  *
  * @author Sergio Cruz <scruz@prisacom.com>
  * @version $Revision: 1.6 $
  * @access public
  */

class genresOU extends PAFOutput
{
    /**
      * Referencia al objeto DataSource.
      *
      * @access private
      * @var object
      */
    var $dataSource= null;

    /**
      * Objeto Recordset para la obtención de datos.
      *
      * @access private
      * @var object
      */
    var $rs= null;

    /**
      * Referencia al objeto template que se encarga de sacar la salida.
      *
      * @access private
      * @var object de tipo PAFTemplate o derivado.
      */
    var $template= null;

    /**
      * Constructor de la clase.
      * Crea el objeto Recordset encargado de seleccionar los datos y el objeto
      * Template responsable de mostrarlos.
      *
      * @access public
      * @param object $ds Referencia a un Objeto de tipo PAFDataSource (o derivado).
      */
    function genresOU(&$ds)
    {
        $this->PAFOutput();
        $this->dataSource=& $ds;
    }

    /**
      * Sobreescritura del método virtual de la clase PAFOutput que se encarga de
      * seleccionar los datos de géneros y devuelve su salida con respecto a la template
      * que contiene.
      */
    function getOutput()
    {
        // Creamos los objetos que necesita el Output para ejecutarse.
        $this->rs= new genresRS($this->dataSource);
        // Esto es lo mismo que lo anterior.
        // $this->rs= new genresRS($ds);
        $this->template= new PAFTemplate("generos.tpl");

        $timer = new Benchmark_Timer();
        $timer->start();

        $result="";

        // 1.- Selecciona los datos
        // ------------------------
        if ( !$this->dataSource->isConnected() )
        {
            $conSuccess= $this->dataSource->connect();
            if ( PEAR::isError($conSuccess) )
                return $conSuccess;
        }

        $timer->setMarker('QueryStart');
        $datos= $this->rs->exec();
        $timer->setMarker('QueryEnd');

        if ( PEAR::isError($datos) )
            return $datos;

        // 2.- Forma la salida.
        // --------------------
        $countReg= $this->rs->count();
        $this->template->setVar("NUMREG",$countReg);

        for ($i= 0; $i<$countReg; $i++)
        {
            $row= $this->rs->next();

            // Pilla los nombres de los campos.
            if ($i==0)
            {
                $campos= array_keys($row->getData());
                $this->template->setVar("TITULO1",$campos[0]);
                $this->template->setVar("TITULO2",$campos[1]);
                $this->template->setVar("TITULO3",$campos[2]);
            }

            $genreID= $row->getGenreID();
            $genreName= $row->getGenreName();
            $genreDesc= $row->getGenreDesc();

            $this->template->setVarBlock("FILAS","CAMPO1",$genreID);
            $this->template->setVarBlock("FILAS","CAMPO2",$genreName);
            $this->template->setVarBlock("FILAS","CAMPO3",$genreDesc);
            $resultBlock.=$this->template->parseBlock("FILAS");
        }

        $this->template->setVar("FILAS", $resultBlock);
        $timer->stop();
        $profiling = $timer->getProfiling();
        $this->template->setVar("PROFILEQUERY", $profiling[2]["diff"]);
        $this->template->setVar("PROFILETOTAL", $profiling[3]["total"]);
        $result= $this->template->parse();

        return $result;
    }
}
?>