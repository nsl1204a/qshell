<?php

require_once "PEAR/Benchmark/Timer.php";
require_once "PAF/PAFDBDataSource.php";
require_once "genresOU.php";
require_once "PAF/PAFCache.php";

$dirCache= ".";                      // Directorio donde se guardan los ficheros de caché.
$subdirCache= "/cacheFiles";         // Subdirectorio dentro del directorio anterior donde se guardan los ficheros de caché.
$idCache= "miPaginacacheada1";   // Id de caché para la página de este script.
$cacheTime= "30";               // Tiempo de caché en segundos.

$timer = new Benchmark_Timer();
$timer->start();
$timer->setMarker('PeichStart');

$cacheObj= new PAFCache ($subdirCache, $dirCache);

if ( $cacheObj->updatedCache($cacheTime, $idCache) )
{
    // -------------------------------------------------------------------------------------------
    // Proceso de generación de nuestra página:
    // 1.- Se comprueba si ha expirado la caché para esa página en cuyo caso la generamos
    //     y la cacheamos de nuevo.
    // 2.- Si no ha expirado el tiempo de caché se lee el fichero que contiene la página cacheada
    //     y se miuestra.
    // -------------------------------------------------------------------------------------------

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
    $genreOutput= new genresOU($con1);
    $result= $genreOutput->getOutput();
    if (PEAR::isError ($result))
    {
       // hacer lo que sea con ese error.
       echo "ERROR...";
    }

    $cacheObj->writeCache ( $idCache, $result );
    echo $result;

    if ($con1->isConnected())
        $con1->disconnect();
    }
else
{
    echo "<center><h2>Página cacheada</h2></center><br>";
    echo "<center>Caduca en " . $cacheObj->getCacheExpireTime($cacheTime, $idCache) . " segundos.</center><br>";
    $pagina= $cacheObj->readCache ( $idCache );
    echo $pagina;
}

$timer->setMarker('PeichEnd');
$profiling = $timer->getProfiling();
$timer->stop();
echo "<pre>";
    print_r ($profiling);
echo "</pre>";
?>
