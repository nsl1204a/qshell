<?php

    require_once "PAF/PAFConfiguration.php";

    $config= new PAFConfiguration();

    if (PEAR::isError ($ret= $config->setVariable("uno", 1)) )
    {
        echo "Al meter variable 1=> " . $ret->getMessage();
        exit();
    }

    if (PEAR::isError ($ret= $config->setVariable("dos", 2)) )
    {
        echo "Al meter variable 2=> " . $ret->getMessage();
        exit();
    }

    // bloqueamos el objeto.
    $config->lock();

    if (PEAR::isError ($ret= $config->setVariable("tres", 3)) )
    {
        echo "Al meter variable 3=> " . $ret->getMessage();
        exit();
    }

    // Descomentar las siguientes líneas para visualizar el contenido del objeto.
    /*
        echo "<pre>";
            print_r ($config);
        echo "</pre>";
    */
?>