<?php

/**
  * Ejemplo 1 de utilizaci�n de la clase PAFOutput
  */

require_once "PAF/PAFDBDataSource.php";
require_once "genresOU.php";

// Configuraci�n de acceso a la fuente de datos.
$values["driver"] = "pgsql";
$values["userName"] = "nube";
$values["password"] = "";
$values["BDServer"] = "10.90.100.11";
$values["BDName"] = "nube";

$con1= new PAFDBDataSource (
                          $values["driver"],
                          $values["userName"],
                          $values["password"],
                          $values["BDServer"],
                          $values["BDName"]
                         );
                         
// Creaci�n de la clase Output.
$genreOutput= new genresOU($con1);

// Obtenci�n de la salida fisica del Output.
$result= $genreOutput->getOutput();

// Control del error que se haya podido producir durante
// el proceso de obtenci�n de la salida.
if (PEAR::isError ($result))
{
   // hacer lo que sea con ese error.
   die;
}

// Salida final
echo $result;

/*
if ($con1->isConnected())
    $con1->disconnect();
*/

?>