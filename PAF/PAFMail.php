<?php

  // *****************************************************************************
  // Lenguaje: PHP
  // Copyright 2002 Prisacom S.A.
  // *****************************************************************************

require_once "PAF/PAFObject.php";

/**
  * Clase para enviar mails.
  * @access public
  * @package PAF
  * @author Sergio Cruz <scruz@prisacom.com>
  * @version $Revision: 1.4 $
  */
class PAFMail extends PAFObject
{
    /**
      * Constructor
      * @access public
      */
    function PAFMail()
    {
        $this->PAFObject();
    }
    
    /**
      * Envía un mail
      *
      * @param string $to Destinatario del mensaje.
      * @param string $subject Asunto del mensaje.
      * @param string $message Cuerpo del mensaje
      * @access public
      * @return mixed TRUE si se ha conseguido enviar el mail o un PEAR_Error en caso contrario.
      */
    function sendMail($to, $subject, $message, $header="")
    {
        $ret= mail  (
                        $to,
                        $subject,
                        $message,
                        $header
                    );
        if (!$ret)
        {
            $code= -1;
            $message= "ERROR en PAFMail. No se ha podido enviar el mail deseado.";
            return PEAR::raiseError ($message, $code);
        }
        else
            return true;
    }
}

?>
