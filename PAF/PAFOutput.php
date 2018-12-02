<?php

// *****************************************************************************
// Lenguaje: PHP
// Copyright 2002 Prisacom S.A.
// ---------
// ChangeLog
// ---------
// $Log: PAFOutput.php,v $
// Revision 1.5  2002/08/01 16:26:41  sergio
// Se especifica en la documentaci�n que la salida es o bien un string con
// el c�digo generado por el Output o un PEAR_Error si se produce alg�n
// tipo de error durante el procesado del mismo.
//
// Revision 1.4  2002/05/29 16:27:02  sergio
// Arreglado un ; en la sentencia require_once "PAF/PAFObject.php".
//
// Revision 1.3  2002/05/13 17:01:40  sergio
// Corregido un error de sintaxis en el m�todo isTypeOf(int).
//
// Revision 1.2  2002/05/09 16:46:10  sergio
// Cambiado el identificador de clase (8).
//
// Revision 1.1  2002/05/09 08:54:29  sergio
// Objeto PAFOutput para salidas.
//
// *****************************************************************************

require_once "PAF/PAFObject.php";

/**
  * @const CLASS_PAFOUTPUT Constante para el identificador �nico de clase.
  */
define ("CLASS_PAFOUTPUT", 8);

/**
  * Clase para la implementaci�n de salidas.
  * Proporciona un interface p�blico compuesto por dos m�todos y que debe ser implementado por las clases
  * derivadas de esta. El interface es el siguiente:
  * 1.- getOutput(). Este es el m�todo que realizar� las operaciones necesarias para proporcionar la salida
  *                  f�sica.
  * 2.- getKey(). Proporciona un identificador �nico basado en el nombre de la clase m�s los atributos que identifiquen
  *               un�vocamente cada objeto instanciado en cada clase derivada. Este identificador �nico puede ser
  *               �til para cachear las salidas generadas por cada uno de los objetos.
  *
  * @author Sergio Cruz <scruz@prisacom.com>
  * @version $Revision: 1.5 $
  * @abstract
  * @access public
  * @package PAF
  */
class PAFOutput extends PAFObject
{
    /**
      * Constructor.
      *
      * @access public
      * @param string $errorClass Nombre de la clase de error asociada a PAFOutput.
      */
    function PAFOutput($errorClass= null)
    {
        $this->PAFObject($errorClass);
    }

    /**
      * M�todo est�tico para recuperar el identificador de la clase.
      *
      * @access public
      * @return int Identificador �nico de clase.
      */
    function getClassType()
    {
        return CLASS_PAFOUTPUT;
    }

    /**
      * M�todo est�tico que retorna el nombre de la clase.
      *
      * @access public
      * @return string Nombre de la clase.
      */
    function getClassName()
    {
        return "PAFOutput";
    }

    /**
      * M�todo de consulta para determinar si una clase es de un tipo determinado.
      * Reimplementado de PAFObject.
      *
      * @access public
      * @param int $tipo N�mero entero con el C�digo de clase por el que queremos preguntar .
      * @return boolean
      */
    function isTypeOf ($tipo)
    {
        return  ( (PAFOutput::getClassType() == $tipo) || PAFObject::isTypeOf($tipo) );
    }

    /**
      * M�todo virtual puro para la obtenci�n de la salida f�sica.
      * Este m�todo debe ser sobreescrito en todas las clases que deriven de ella de forma
      * obligatoria y debe contener las operaciones necesarias para que el objeto genere su salida.
      * La salida deber� ser un string con el c�digo generado por el Output o un error.
      * La implementaci�n de este m�todo debe contemplar que la salida se produzca correctamente o no. Para ello
      * el tipo de retorno de la implementaci�n particular de este m�todo deber� ser mixto. En el caso de que la
      * salida se haya generado correctamente la retornar�. En el caso de que se haya detectado alg�n tipo de
      * error en su generaci�n se retornar� un objeto de error (PEAR_Error o derivada). De este modo el usuario
      * de la clase puede comprobar por medio de PEAR::iserror() el tipo de retorno y actuar en consecuencia.
      *
      * @access public
      * @abstract
      * @return mixed Cadena con el c�digo generado por el Output o un PEAR_Error si se produce alg�n error
      *         durante la ejecuci�n del mismo.
      */
    function getOutput()
    {
        echo $this->getClassName() . " es una Clase virtual pura. Debe sobreescribir este m�todo para su utilizaci�n";
        die;
    }

    /**
      * M�todo virtual puro para proporcionar un identificador �nico de cada objeto salida. Esto suele
      * ser utilizado para proporcionar el id necesario de una salida a la hora de cachearla. Normalmente este
      * identificador �nico ser� el resultado de aplicar una funci�n hash sobre el nombre de la clase derivada
      * m�s los datos que identifiquen un�vocamente al objeto salida que estemos tratando.
      *
      * @access public
      * @abstract
      */
    function getKey()
    {
        echo $this->getClassName() . " es una Clase virtual pura. Debe sobreescribir este m�todo para su utilizaci�n";
        die;
    }
}

?>