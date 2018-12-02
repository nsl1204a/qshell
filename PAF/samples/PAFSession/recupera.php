<?php

    /**
   * Recupera el valor del objeto de usuario que ha sido almacenado en la sesi�n.
   */

   require_once "PAF/PAFSession.php";
   require_once "userClass.php";
   require_once "conf/configurationElpais.inc";

    // echo "Entrando en el recuperador de PAFSession.<br>";
   // Inicializamos el mecanismo de sesiones de PHP.
   $mySession= new PAFSession (
                                $config->getVariable("DIR_RW"),
                                "CookieTestSession",
                                "",
                                "",
                                180
                               );
   $retValue= $mySession->sessionStart();
   if ( PEAR::isError($retValue) )
   {
     echo "$retValue->getMessage()<br>";
     die;
   }
    // Recupera el objeto de usuario 1 almacenado en la sesi�n correspondiente.
   $retValue1=& $mySession->getRegisteredValue("myUser1");
   if (PEAR::isError ($retValue1))
   {
       echo $retValue1->getMessage() . "<br>";
   }
    // Recupera el objeto de usuario 2 almacenado en la sesi�n correspondiente.
   $retValue2=& $mySession->getRegisteredValue("myUser2");
   if (PEAR::isError ($retValue2))
   {
       echo $retValue2->getMessage() . "<br>";
   }
    echo "<pre>";
           if (isset ($myUser1))
          {
              var_dump ($myUser1);
              echo "<br>";
          }
          if ( isset ($myUser2) )
          {
              var_dump ($myUser2);
              echo "<br>";
          }
    echo "</pre>";
    // DesRegistra al usuario 1 de la Sesi�n.
   $success= $mySession->sessionUnregister("myUser1");
   if ( PEAR::isError ($success) )
       echo $success->getMessage();
    // Cambiamos los datos del usuario 2
   if (isset ($myUser2))
       $myUser2->setUserName("Nombre_Usuario_2_cambiado");
    // ********************************************************************************
   // NOTA: Si volvemos a ejecutar el script se recuperan los datos cambiados para el
   //       usuario 2 y un error para el caso del usuario 1.
   // ********************************************************************************
    // ********************************************************************************
   // Descomentar esta l�nea si se quiere probar la eliminaci�n de la sesi�n.
   //
   //$retValue= $mySession->sessionDestroy();
   //if ( PEAR::isError ($retValue) )
   //{
   //    echo $retValue->getMessage();
   //}
   // ********************************************************************************
?>
