<?php

//require_once ("CRM2/CRMLogger.php");
require_once("private/CRMLoginLDAP.php");
require_once("private/conf/QSHProcess.inc");

//$prop = "qshellCRM";
//$logger = new CRMLogger($prop);
//$logger->uniqid();

$view = 0;

if (!isset($_COOKIE["usuario"])) {

    // estamos en el login
    if (!empty($_POST["username"]) && !empty($_POST["password"])) {

        $usuario  = $_POST["username"];
        $password = $_POST["password"];
        // miramos si esta el usuario
        if (array_key_exists($usuario, $userPass) && $userPass[$usuario] === $password) {

            //$logger->info('Login: El usuario '.$usuario.' se ha autenticado correctamente');

            setcookie("usuario", $usuario);
            $view = 1;
        } else {
            $view = 2;
            //$logger->info('Login: El usuario '.$usuario.' no se ha podido autenticar ('.$res["descError"].')');
        }
    }

}  // else {  // $view = 0; }

header("Location: /?l=" . $view);
exit();