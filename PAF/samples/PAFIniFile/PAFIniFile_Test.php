<?php

/**
  * Ejemplo de utilización de la clase PAFFichIni
  */

  require_once "PAF/PAFIniFile.php";

  $fileName="Sample.ini";

  $fichIni= new PAFIniFile($fileName);

  if ( PEAR::isError ($fichIni) )
  {
      echo "Problemas al crear el objeto PAFFichIni.<br>";
      echo $fichIni->getMessage() . "<br>";
      die;
  }

  // Consultamos el primer grupo (datos1)
  $varRet= array();
  $grupo= "datos1";
  $fichIni->getGroup( $grupo, $varRet);

  echo "<b>Contenido de la sección <i>datos1</i></b><br>";
  echo "<pre>";
    var_dump ($varRet);
  echo "</pre>";

  unset ($varRet);

  // Consultamos el segundo grupo (datos2)
  echo "<b>Contenido de la sección <i>datos2</i></b><br>";
  $varRet= array();
  $grupo= "datos2";
  $fichIni->getGroup( $grupo, $varRet, "LOW");

  echo "<pre>";
    var_dump ($varRet);
  echo "</pre>";

  unset ($varRet);

  // Obetnemos el listado de grupos.
  $varRet= array();
  $fichIni->listGroup($varRet);
  echo "<b>Listado de Grupos</b><br>";
  echo "<pre>";
    var_dump ($varRet);
  echo "</pre>";


?>