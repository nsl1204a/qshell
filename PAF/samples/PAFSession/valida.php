<?php

/**
* Programa de prueba de sesiones en PHP.
* El objetivo es comprobar si PHP es capaz de almacenar objetos completos
* como variables de sesión y si es capaz de recuperar esos objetos de manera
* coherente.
*/

require_once "/test/sesiones/userClass.php";

// Inicializamos el mecanismo de sesiones de PHP.
$bSuccess= session_start();
if (!$bSuccess)
{
	echo "El sistema de sesiones no se ha inicalizado correctamente.<br>";
	die;
}

if ( !isset ($userName) || 
     !isset ($userPassword) ||
	 !isset ($userAge) ||
	 !isset ($userYOB)
   )
    die;

$myUser= new userClass ($userName, $userPassword, $userAge, $userYOB);
$myUser->debugUser();

session_register("myUser");

header ("Location: http://localhost/test/sesiones/recupera.php");

?>
