<?php

  // *****************************************************************************
  // Lenguaje: PHP
  // Copyright 2002 Prisacom S.A.
  // ---------
  // ChangeLog
  // ---------
  // $Log: PAFObject.php,v $
  // Revision 1.4  2002/05/16 16:01:32  sergio
  // Eliminación de la última línea en blanco.
  //
  // Revision 1.3  2002/05/07 09:35:23  sergio
  // Modificaciones en la documentación.
  //
  // Revision 1.2  2002/04/30 09:46:45  sergio
  // Modificaciones en la documentación de la clase.
  //
  // Revision 1.1.1.1  2002/04/22 15:01:42  sergio
  // Creación de estructura y primera subida de las clases generales
  //
  // *****************************************************************************

require_once "PEAR/PEAR.php";

/**
  * @const CLASS_PAFOBJECT Constante con el identificador de clase para PAFObject
  */

define ("CLASS_PAFOBJECT", 1);

/**
  * Clase base de la jerarquía de objetos PAF.
  * Extiende las capacidades de la clase PEAR con un identificador único de clase así como un nombre
  * para la clase y proporciona los métodos para comprobar si una clase es de un tipo determinado. Las
  * clases que deriven de PAFObject deberán sobreescribir este método para que realize las comprobaciones
  * adecuadas consigo misma y con la clase padre de la que deriva.
  *
  * @author Sergio Cruz scruz@prisacom.com
  * @version $Revision: 1.4 $
  * @package PAF
  */
class PAFObject extends PEAR
{
    /*
     * variable que contendrá el objeto PEARERROR, que se asigna cuando hay un error
     */
     var $objectPEARError;

    /**
      * Constructor de la clase PAFObject.
      *
      * NOTA: El constructor de la clase padre PEAR admite como parámetro un string que designa
      * el nombre de la clase de error que va a lanzar la clase en caso de que se produzca alguno.
      * El método utilizado por las clases derivadas de PEAR para el lanzamiento de errores es
      * raiseError. Este método crea y devuelve una nueva instancia de la clase que se ha especificado en
      * el constructor. Hay que echarle un ojo a esto a ver qué tal.
      *
      * @access public
      * @param  string $errorClass Nombre de la clase de Error utilizada para el lanzamiento de errores.
      */
    function PAFObject( $errorClass= null )
    {
        $this->PEAR($errorClass);   // Llamada al constructor de la clase padre.
        $this->objectPEARError = null;
    }

    /**
      * Método estático para recuperar el identificador de la clase.
      *
      * @access public
      * @return int Código único de clase.
      */
    function getClassType()
    {
        return CLASS_PAFOBJECT;
    }

    /**
      * Método estático que retorna el nombre de la clase.
      *
      * @access public
      * @return string Nombre de la clase.
      */
    function getClassName()
    {
        return "PAFObject";
    }

    /**
      * Método de consulta para determinar si una clase es de un tipo determinado.
      * Toda clase derivada de PAFObject debe sobreescribir este método de tal forma
      * que se pregunte por el tipo pasado por parámetro a sí misma y si no pregunte al
      * padre inmediatamente superior. En el caso de PAFObject como se trata de la clase
      * padre de todas no se redirige la pregunta a ninguna. La comparación se realiza en base
      * al id de clase que se ha definido mediante la función "define" al principio del fichero.
      *
      * @access public
      * @param  int $tipo Código de clase por el que queremos preguntar .
      * @return boolean TRUE si la clase se es del tipo indicado o derivada y FALSE en caso contrario.
      */
    function isTypeOf ($tipo)
    {
        return (PAFObject::getClassType() == $tipo);
    }
    /**
     * Permite saber si hay objeto error
     * Esta función, junto con getError y getMessage, sirven para puentear el método que se tiene
     * en prisacom de devolver error en el constructor, asignándolo a this (this = objetoError)
     * Ahora, no se asigna a this, sino que se asigna a un objeto de la clase.
     * Se ha tocado  PEAR::isError para que llame a PAFObject->isError, y getMessage llamará a PAFObject->getMesage
     * @return unknown
     */
    function existPEARError()
    {
        return ($this->objectPEARError != null);
    }
    function getPEARError()
    {
        return $this->objectPEARError;
    }

    function setPEARError($error)
    {
        $this->objectPEARError = $error;
        return true;

/*        if ($error instanceof PEAR_Error)
        {
               $this->objectPEARError = $error;

               return true;
        }

        return false; */
    } 
    /**
     * Devuelve mensaje de error del objeto error
     *
     * @return unknown
     */
    function getMessage()
    {
        if ($this->objectPEARError)
            return $this->objectPEARError->getMessage();

        return "";
    }

    function getDebugInfo()
    {
        if ($this->objectPEARError)
            return $this->objectPEARError->getDebugInfo();

        return "";
    }
}

?>
