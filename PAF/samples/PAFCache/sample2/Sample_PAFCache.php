<?php

    // --------------------------------------------
    // Ejemplo de utilizaci�n de la clase PAFCache
    // --------------------------------------------

    require_once "PAF/PAFCache.php";

    $dir= ".";                      // Directorio donde se guardan los ficheros de cach�.
    $subdir= "/cacheFiles";         // Subdirectorio dentro del directorio anterior donde se guardan los ficheros de cach�.
    $idCache= "miPaginacacheada";   // Id de cach� para la p�gina de este script.
    $cacheTime= "15";               // Tiempo de cach� en segundos.

    $cacheObj= new PAFCache ($subdir, $dir);

    // -------------------------------------------------------------------------------------------
    // Proceso de generaci�n de nuestra p�gina:
    // 1.- Se comprueba si ha expirado la cach� para esa p�gina en cuyo caso la generamos
    //     y la cacheamos de nuevo.
    // 2.- Si no ha expirado el tiempo de cach� se lee el fichero que contiene la p�gina cacheada
    //     y se miuestra.
    // -------------------------------------------------------------------------------------------
    if ( $cacheObj->updatedCache($cacheTime, $idCache) )
    {
        echo "Generaci�n din�mica de la p�gina.<br>";
        // Generamos la p�gina y la cacheamos.
        $pagina.="  <center>\n";
        $pagina.="    <h1>\n";
        $pagina.="      Ejemplo de utilizaci�n de la clase PAFCache.\n";
        $pagina.="      <br>\n";
        $pagina.=   phpversion();
        $pagina.="    </h1>\n";
        $pagina.="  </center>\n";
        $cacheObj->writeCache ( $idCache, $pagina );
        echo $pagina;
    }
    else
    {
        echo "Restaurando p�gina desde la cach�<br>";
        echo "Caduca en " . $cacheObj->getCacheExpireTime($cacheTime, $idCache) . " segundos.<br>";
        $pagina= $cacheObj->readCache ( $idCache );
        echo $pagina;
    }

?>