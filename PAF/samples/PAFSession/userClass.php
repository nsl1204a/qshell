<?php

/**
  * Clase de prueba de usuarios.
  */
class userClass
{
    /**
      * Atributo para alamacenar el nombde de usuario.
      *
      * @access private
      * @var string
      */
    var $name;

    /**
      * Atributo para alamacenar el password de usuario.
      *
      * @access private
      * @var string
      */
    var $pwd;

    /**
      * Atributo para alamacenar la edad del usuario.
      *
      * @access private
      * @var string
      */
    var $age;

    /**
      * Atributo para alamacenar el año de nacimiento del usuario.
      *
      * @access private
      * @var string
      */
    var $yob;

    /**
      * Constructor.
      *
      * @param string $userName Nombre de usuario.
      * @param string $userPassword Password de usuario
      * @param string $userAge Edad del usuario.
      * @param string $userYOB Año de nacimiento del usuario.
      */
    function userClass ($userName, $userPassword, $userAge, $userYOB)
    {
        $this->name= $userName;
        $this->pwd= $userPassword;
        $this->age= $userAge;
        $this->yob= $userYOB;
    }

    /**
    * Devuelve el nombre de usuario.
    *
    * @access public
    */
    function getUserName()
    {
        return $this->name;
    }

    /**
    * Devuelve el password de usuario.
    *
    * @access public
    */
    function getUserPassword()
    {
        return $this->pwd;
    }

    /**
    * Devuelve la edad del usuario.
    *
    * @access public
    */
    function getUserAge()
    {
        return $this->age;
    }

    /**
    * Devuelve el año de nacimiento del usuario.
    *
    * @access public
    */
    function getUserYOB()
    {
        return $this->yob;
    }

    /**
    * Muestra por pantalla un debug de la información del usuario.
    *
    * @access public
    */
    function debugUser()
    {
        echo "<pre>";
                var_dump ($this);
        echo "</pre>";
    }

    /**
    * Fija el valor del nombre del usuario.
    *
    * @access public
    * @param string $userName Nuevo nombre de usuario.
    */
    function setUserName($userName)
    {
        $this->name= $userName;
    }

    /**
    * Fija el valor del password del usuario.
    *
    * @access public
    * @param string $userPassword Nueva clave de usuario.
    */
    function setUserPassword($userPassword)
    {
        $this->pwd= $userPassword;
    }
}

?>