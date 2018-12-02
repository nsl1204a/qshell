<?php

    // --------------------------------------------
    // Ejemplo de utilización de la clase PAFCache
    // --------------------------------------------

    require_once "PAF/PAFCache.php";

    $dir= ".";                      // Directorio donde se guardan los ficheros de caché.
    $subdir= "/cacheFiles";         // Subdirectorio dentro del directorio anterior donde se guardan los ficheros de caché.
    $idCache= "miPaginacacheada";   // Id de caché para la página de este script.
    $cacheTime= "15";               // Tiempo de caché en segundos.

    $cacheObj= new PAFCache ($subdir, $dir);

    // -------------------------------------------------------------------------------------------
    // Proceso de generación de nuestra página:
    // 1.- Se comprueba si ha expirado la caché para esa página en cuyo caso la generamos
    //     y la cacheamos de nuevo.
    // 2.- Si no ha expirado el tiempo de caché se lee el fichero que contiene la página cacheada
    //     y se miuestra.
    // -------------------------------------------------------------------------------------------
    if ( $cacheObj->updatedCache($cacheTime, $idCache) )
    {
        echo "Generación dinámica de la página.<br>";
        // Generamos la página y la cacheamos.
        $pagina.="  <center>\n";
        $pagina.="    <h1>\n";
        $pagina.="      Ejemplo de utilización de la clase PAFCache.\n";
        $pagina.="      <br>\n";
        $pagina.=   phpversion();
        $pagina.="    </h1>\n";
        $pagina.="  </center>\n";
        $cacheObj->writeCache ( $idCache, $pagina );
        echo $pagina;
    }
    else
    {
        echo "Restaurando página desde la caché<br>";
        echo "Caduca en " . $cacheObj->getCacheExpireTime($cacheTime, $idCache) . " segundos.<br>";
        $pagina= $cacheObj->readCache ( $idCache );
        echo $pagina;
    }

?>