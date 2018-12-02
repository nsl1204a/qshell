<?php

    /**
      * Programa de pruebas para PAFFileDataSource.
      */

    require_once "PAF/PAFFileDataSource.php";

    // Intenta conectar una FileDataSource con un fichero existente ()para lectura).
    $file= new PAFFileDataSource ("users.txt");
    $ret= $file->connect();
    if (PEAR::isError ($ret))
        echo $ret->getMessage();
    else
        echo "PAFFileDataSource creada correctamente para el fichero <b>" . $file->getFileName() . "</b> modo <b>" . $file->getOpenMode() . "</b><br>";

    // Intenta conectar una FileDataSource a un fichero no existente.
    $file= new PAFFileDataSource ("user.txt");
    $ret= $file->connect();
    if (PEAR::isError ($ret))
        echo $ret->getMessage();
    else
        echo "PAFFileDataSource creada correctamente para el fichero <b>" . $file->getFileName() . "</b> modo <b>" . $file->getOpenMode() . "</b><br>";

    // Intenta abrir una FileDataSource con un modo de apertura inválido
    $file= new PAFFileDataSource ("users.txt", "W-");
    $ret= $file->connect();
    if (PEAR::isError ($ret))
        echo $ret->getMessage();
    else
        echo "PAFFileDataSource creada correctamente para el fichero <b>" . $file->getFileName() . "</b> modo <b>" . $file->getOpenMode() . "</b><br>";

?>