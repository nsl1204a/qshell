<?php

    /**
      * Programa de prueba para la clase PAFFileCsvDS.
      */

    require_once "PAF/PAFFileCsvDS.php";

    echo "<h3><center>Prueba de DataSource CSV</center></h3>";
    // Fichero CSV con cabeceras de campos.
    $file= new PAFFileCsvDS ("users2.txt",false,'r',';');

    // Fichero CSV sin cabeceras de campos.
    //$file= new PAFFileCsvDS ("users1.txt", false);

    $con= $file->connect();
    if ( PEAR::isError ($con) )
    {
        echo $con->getMessage();
        die;
    }

    /* Debug del objeto */
    echo "<pre>";
    print_r ($file);
    echo "</pre>";

    echo "<b>CSV DataSource abierta.</b><br>";
    $numReg= $file->count();
    echo "<b>Número de Registros:</b> " . $numReg . "<br>";

    for ($i= 0; $i < $numReg; $i++)
    {
        echo "<pre>";
            print_r ($file->next());
        echo "</pre>";
    }
?>