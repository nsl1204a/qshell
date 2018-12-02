<?php
    
	/**
      * Programa de prueba de sesiones PAF.
      */

    require_once "PAF/PAFSession.php";
    require_once "PAF/PAFConfiguration.php";
    require_once "userClass.php";

    // Inicializamos el mecanismo de sesiones de PHP.
    $sessionConfig= new PAFConfiguration();

	$sessionConfig->setVariable ("PATH_SESSION", ".");
	$sessionConfig->setVariable ("NAME_COOKIE", "cookieTest");
    $sessionConfig->setVariable ("DOMAIN_COOKIE", "");
    $sessionConfig->setVariable ("PATH_COOKIE", "");
    $sessionConfig->setVariable ("COOKIE_LIFETIME", "3000");
    $sessionConfig->setVariable ("TYPE_CACHE","");

    $mySession= new PAFSession ( $sessionConfig );

    if ( PEAR::isError ($mySession) )
    {
        echo "$mySession->getMessage()<br>";
        die;
    }

    $id= $mySession->sessionStart();

    echo "<pre>";
        echo "ID=>" . $id . "<br>";
        print_r ($_SESSION);
        print_r($HTTP_COOKIE_VARS);
    echo "</pre>";

    if ( PEAR::isError($id) )
    {
      echo $id->getMessage() . "<br>";
      die;
    }

    echo "<pre>RESULTADOS RECUPERADOS DE LA SESION<br>";
    print_r ($HTTP_SESSION_VARS);
    print_r ($myUser1);
    print_r ($myUser2);
    echo "</pre>";


    $myUser1= new userClass("prueba1", "kkdlvk1", "1", "1901");
    $myUser2= new userClass("prueba2", "kkdlvk2", "2", "1902");
    $mySession->sessionRegister("myUser1");
    $mySession->sessionRegister("myUser2");
?>
