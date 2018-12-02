<?php

/**
  * -----------------------------------------------------------------------------------
  * Programa de prueba general del PAF.
  * El objetivo es proporcionar un ejemplo de utilización de un Recordset derivado
  * de la clase PAFRecordset para la consulta de usuarios. Dicho Recordset es capaz
  * de filtrar su resultado de dos fuentes distintas de datos (Base de Datos y fichero
  * CSV). Así mismo realiza un profile de tiempos de ejecución para la comparación
  * de los dos tipos de filtrado.
  * -----------------------------------------------------------------------------------
  */

  require_once "PAF/PAFDBDataSource.php";
  require_once "PAF/PAFFileDataSource.php";
  require_once "PAF/samples/userRecordset.php";

  //  ------------------------------------------------------------------------------
  // A esto nos vemos obligados porque no tenemos un fichero general que podamos
  // incluir en el que se tengan todos los IDs de clases. En su lugar lo que
  // nosotros hacemos es definir cada ID de clase dentro de la misma clase.
  // Comentar con la peña la conveniencia de tener dicho fichero de identificadores
  // de clase.
  //  ------------------------------------------------------------------------------
  //define ("CLASS_PAFRECORDSET",3);

  // -----------------------------------
  // PARA PRUEBAS DE CONEXION CON MYSQL
  // -----------------------------------
  $driver= "mysql";
  $user = "root";
  $pass = "";
  $host = "10.90.100.11";
  $db_name = "users";
  // -----------------------

  // --------------------------------------
  // PARA PRUEBAS DE CONEXIÓN CON POSTGRES.
  // --------------------------------------
  //  $driver= "pgsql";
  //  $user = "nube";
  //  $pass = "";
  //  $host = "10.90.100.11:5432";
  //  $db_name = "nube";
  // -----------------------------------

  $ds1= new PAFDBDataSource (
                                $driver,
                                $user,
                                $pass,
                                $host,
                                $db_name
                            );


  echo "<b>Conectando a BD</b><br>";
  $resCon= $ds1->connect();
  if (PEAR::isError ($resCon) )
  {
      echo $resCon->getMessage();
      echo $resCon->getDebugInfo(); // Este error es de tipo DBError.
      die;
  }
  $rs1= new userRecordset ($ds1, null, "salvador.pulido@prisacom.com", null);
  $rs1->setLimits (0,1);

  $time_start1 = getmicrotime();
     $result= $rs1->exec();
  $time_end1 = getmicrotime();
  $time1 = $time_end1 - $time_start1;
  echo "Tiempo query db=> " . $time1 . "<br>";
  $totalRegs= $rs1->countAll();
  echo "Número total de registros=> " . $totalRegs . "<br>";

  $time_startpinta1 = getmicrotime();
  $count= $rs1->count();
  for ($i= 0; $i<$count; $i++)
  {
      $data= $rs1->next();
      echo "<pre>";
        var_dump ($data->getData());
      echo "</pre>";
      $data->debugData();
      unset ($data);
  }
  $time_endpinta1 = getmicrotime();
  $timepinta1 = $time_endpinta1 - $time_startpinta1;
  echo "Tiempo recorrer query db=> " . $timepinta1 . "<br>";


  unset ($result);
  unset ($count);
  unset ($i);

  echo "Desconectando de BD.<br>";
  $ds1->disconnect();

 echo "<br>";

  // ----------------------
  // UTILIZAMOS EL FICHERO.
  // ----------------------
  /*
  $fileDSN= "D:\\wwwroot\\develop\\private\\class\\php\\PAF\\samples\\users.txt";
  $ds2= new PAFFileDataSource ($fileDSN, ",");
  echo "<b>Conectando a Fichero</b><br>";
  $resCon= $ds2->connect();
  if (PEAR::isError ($resCon))
  {
    echo "Problemitas al conectar a fichero<br>";
    echo $resCon->getMessage();
    die;
  }

  $rs2= new userRecordset ($ds2, "'sergio'", "'salvador.pulido@prisacom.com'", "OR");

  $time_start2 = getmicrotime();
     $result= $rs2->exec();

  $time_end2 = getmicrotime();
  $time2 = $time_end2 - $time_start2;
  echo "Tiempo query file=> " . $time2 . "<br>";


  $time_startpinta2 = getmicrotime();
  $count= $rs2->count();
  for ($i= 0; $i<$count; $i++)
  {
      $data= $rs2->next();
      echo "<pre>";
        var_dump ($data->getData());
      echo "</pre>";
      $data->debugData();
  }

  $time_endpinta2 = getmicrotime();
  $timepinta2 = $time_endpinta2 - $time_startpinta2;
  echo "Tiempo recorrer query file=> " . $timepinta2 . "<br>";


  echo "Desconectando de file.<br>";
  $ds2->disconnect();
*/



  function getmicrotime()
  {
    list($usec, $sec) = explode(" ",microtime());
    return ((float)$usec + (float)$sec);
  }

?>