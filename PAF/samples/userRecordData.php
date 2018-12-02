<?php

  // *****************************************************************************
  // Lenguaje: PHP
  // Copyright 2002 Prisacom S.A.
  // ---------
  // ChangeLog
  // ---------
  // $Log: userRecordData.php,v $
  // Revision 1.1  2002/05/03 10:37:44  sergio
  // Subida inicial
  //
  // *****************************************************************************

require_once "PAF/PAFRecordData.php";


/**
  * Clase para contener los datos de un registro de un usuario obtenido por medio de un userRecordset.
  * Proporciona métodos para la obtención de cada uno de los campos pertencientes a un registro
  * de usuario.
  *
  * @author Sergio Cruz <scruz@prisacom.com>
  * @version $Revision: 1.1 $
  * @access public
  */

class userRecordData extends PAFRecordData
{
    /**
      * Constructor
      *
      * @access public
      * @param mixed $value hash con los datos del recgistro de usuario y sus nombres de campos.
      */
    function userRecordData ($value)
    {
        $this->PAFRecordData($value);
    }
    
    /**
      * Devuelve el Identificador único de usuario.
      *
      * @access public
      * @return string Identificador único de usuario.
      */
    function getUsrId()
    {
        return $this->data["usr_id"];
    }

    /**
      * Devuelve el apodo usado por el usuario.
      *
      * @access public
      * @return string Nombre/Apodo de usuario.
      */
    function getUsrNick()
    {
        return $this->data["usr_nick"];
    }

    /**
      * Devuelve el password de usuario.
      *
      * @access public
      * @return string Password de usuario.
      */
    function getUsrPassword()
    {
        return $this->data["usr_password"];
    }

    /**
      * Devuelve la dirección de correo del usuario.
      *
      * @access public
      * @return string Dirección de correo del usuario.
      */
    function getUsrEmail()
    {
        return $this->data["usr_email"];
    }

    /**
      * Devuelve la pregunta secreta del usuario.
      *
      * @access public
      * @return string Pregunta secreta del usuario.
      */
    function getUsrQuestion()
    {
        return $this->data["usr_trick_question"];
    }

    /**
      * Devuelve la respuesta secreta del usuario.
      *
      * @access public
      * @return string Respuesta secreta del usuario.
      */
    function getUsrAnswer()
    {
        return $this->data["usr_trick_answer"];
    }

    /**
      * Devuelve la fecha de alta del usuario.
      *
      * @access public
      * @return string Fecha de alta del usuario.
      */
    function getUsrStartDate()
    {
        return $this->data["usr_start_date"];
    }
}

?>
