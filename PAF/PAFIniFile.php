<?php

  // *****************************************************************************
  // Lenguaje: PHP
  // Copyright 2002 Prisacom S.A.
  // ---------
  // ChangeLog
  // ---------
  // $Log: PAFIniFile.php,v $
  // Revision 1.10  2005/04/27 09:57:25  fsuero
  // Añadida la  funcion removeGroup
  //
  // Revision 1.9  2005/04/21 10:56:39  fsuero
  // *** empty log message ***
  //
  // Revision 1.8  2005/04/21 10:46:52  fsuero
  // Añadidas las funciones de creacion y escritura de INIs
  //
  // Revision 1.7  2002/08/05 14:43:25  sergio
  // Controlado el define del ID de clase.
  //
  // Revision 1.6  2002/07/30 12:39:27  jgarcia
  // Añadida @ antes de la apertura del fichero para que no aparezca error
  //
  // Revision 1.5  2002/07/30 10:35:31  jgarcia
  // He quitado el is_file por no permitir parámetros de include_path.
  //
  // Revision 1.4  2002/07/12 09:28:15  sergio
  // Posibilidad de introducir comentarios de tipo //
  //
  // Revision 1.3  2002/07/12 09:20:29  sergio
  // Eliminada la posibilidad de introducir comentarios de la forma //
  //
  // Revision 1.2  2002/05/31 15:31:21  sergio
  // Actualizada la constante con el identificador de clase.
  //
  // Revision 1.1  2002/05/27 08:51:03  sergio
  // Cambio de PAFFichIni a PAFIniFile (siguiendo normas de nomenclatura).
  //
  // Revision 1.7  2002/05/16 16:07:42  sergio
  // no message
  //
  // Revision 1.6  2002/05/11 18:18:42  sergio
  // Modificados a inglés los métodos de la clase.
  //
  // Revision 1.5  2002/05/09 16:45:03  sergio
  // Cambiado el identificador de clase (6).
  //
  // Revision 1.3  2002/05/08 10:01:36  sergio
  // Modificación en el constructor para adminitir un parámetro más con el nombre
  // de la clase de error asociada a ella.
  //
  // Revision 1.2  2002/05/07 09:31:38  sergio
  // Añadido método listadoGrupos().
  //
  // Revision 1.1  2002/05/06 16:26:59  sergio
  // Clase de manejo de ficheros INI
  //
  // *****************************************************************************
/*
 *   JRM: Este codigo, (las tres lineas siguientes , están puestas por si alguien usa include en vez de require_once, para evitar la redefinicion de la clase
 *
 */
if (defined("_PAFIniFileIncluido"))
    return;
define("_PAFIniFileIncluido", 1);

require_once "PAF/PAFObject.php";

/**
  * @const CLASS_PAFINIFILE Constante con el identificador único de clase.
  */
if ( ! defined ("CLASS_PAFINFILE") )
    define ("CLASS_PAFINFILE", 6);

/**
  * Clase para el manejo de ficheros .INI
  *
  * @author Sergio Cruz <scruz@prisacom.com>
  * @version $Revision: 1.10 $
  * @package PAF
  */
class PAFIniFile extends PAFObject
{
    /**
      * Atributo que contiene el nombre del fichero INI.
      *
      * @access private
      * @var array
      */
    var $filename;
    var $fileContent;

    /**
      * Atributo que contiene la longitud del fichero.
      *
      * @access private
      * @var int
      */
    var $lon;

    /**
      * Constructor.
      *
      * @access public
      * @param string $name Nombre del Fichero INI que se quiere procesar.
          * @param string $errorClass Nombre de la clase de error que se lanzará.
      *
      * @return mixed Un nuevo Objeto PAFFichIni si no se produce ningún error o un PEAR:Error en caso contrario.
      */
    function PAFIniFile($name, $errorClass= null)
    {
        $this->filename=$name;
        $this->PAFObject($errorClass); // Llamada al constructor de la clase padre
        //if (version_compare(PHP_VERSION, '5') == 1)
        if (1)
        {
            if (!@($this->fileContent = file($name, 1)))
            {
                $this->setPEARError($this->raiseError ("¡¡¡ ERROR !!! (".__FILE__.",". __LINE__.") => No existe el fichero $name.<br>"));
                return;
            }

        }

        else
        {
            if (!@($this->fileContent = file($name, 1)))
            {
//                $this= PEAR::raiseError ("¡¡¡ ERROR !!! (".__FILE__.",". __LINE__.") => No existe el fichero $name.<br>");
                return $this;
            }
        }

        $this->lon = count($this->fileContent);
    }

    /**
      * Método estático para recuperar el identificador de la clase.
      *
      * @access public
      * @return int Identificador único de clase
      */
    function getClassType()
    {
        return CLASS_PAFINIFILE;
    }

    /**
      * Método estático que retorna el nombre de la clase.
      *
      * @access public
      * @return string Nombre de la clase.
      */
    function getClassName()
    {
        return "PAFIniFile";
    }

    /**
      * Método de consulta para determinar si una clase es de un tipo determinado.
      * Reimplementado de PAFObject.
      *
      * @access public
      * @param int $tipo Número entero con el Código de clase por el que queremos preguntar .
      * @return boolean
      */
    function isTypeOf ($tipo)
    {
        return ( (PAFIniFile::getClassType() == $tipo) || PAFObject::isTypeOf($tipo) );
    }

    /**
      * Deja en una tabla hash (que le pasamos por referencia) todos los valores de un grupo determinado.
      * Los nombres de cada campo estan en MAYUSCULAS por defecto. El modo viene indicado por el parámetro
      * "modo".
      *
      * @access public
      * @param string $grupo Grupo del que se quiere obtener los valores.
      * @param array $varRet Array en el que se devuelven los resultados de la consulta.
      * @param $modo Modo en el que se quiere que se devuelvan las "keys" del Array de retorno
      *        Puede tomar dos valores "UP" (mayúsculas) y "LOW" (minúsculas) (cualquier otro valor
      *        deja las cosas como están).
      *
      * @return array Hash con los valores de los datos contenidos en el grupo consultado.
      */
    function getGroup( $grupo, &$varRet,$modo="UP" )
        {
        $varRet=array();
        $i = 0;
        $grpEnc = 0;
        $grupo = strtoupper( $grupo );
        while( $i < $this->lon )
            {
            $linea = $this->fileContent[$i++];
            if ( ereg( "\n", $linea ) )
            {
                $linea = chop($linea);
            }

            if ( (!empty($linea) ) && ($linea[0] == '#') )
                $linea = "";

            list($linea) = split( "//", $linea );
            if ( $grpEnc == 0 )
            {
                $linea = strtoupper( $linea );
                if ( ereg( "\[$grupo\]", $linea ) )
                    $grpEnc = 1;
            }
            else
            {

                if ( ereg( "^\[", $linea ) )
                          return;
                else
                {
                   $key=substr($linea,0,strpos($linea,"="));
                   $valor=substr($linea,strpos($linea,'=')+1);
                                    if ( strlen($key) && strlen($valor) )
                   {
                       $valor =  trim($valor);
                                                 $key= trim($key);
                                                if ($modo=="UP")
                                                  $key=strtoupper($key);
                                                elseif ($modo=="LOW")
                                                    $key=strtolower($key);
                                                $varRet[$key] = $valor;
                                    }
                }
            }
        }
    }

    /**
      * Devuelve por referencia un array con las etiquetas de los grupos que tenga el fichero INI.
      *
      * @access public
      * @param array $varRet Array con los nombres de los grupos.
      */
    function listGroup( &$varRet )
        {
                $grupos = array();
                $i = 0 ;

                while( $i < $this->lon )
                {
                        $linea = $this->fileContent[$i++];
                        if ( ereg( "\n", $linea ) ) { $linea = chop($linea); }
                                if ( (!empty($linea) ) && ($linea[0] == '#') )
                                $linea = "";
                                list($linea) = split( "//", $linea );
                        $ini = strchr( $linea, "[" ) ;
                        if ( $ini >= 0 )
                        {
                                $fin = strchr( $linea, "]" ) ;
                                if (( $fin >= 0 ) && ( $fin > $ini ))
                                        $grupos[] = substr( $linea, $ini+1, $fin-1 ) ;
                        }
                }

                $varRet = $grupos ;
                return count($grupos) ;
        }


    /**
      * Devuelve un PAFIniFile generando un fichero en caso de que no exista
      * 
      * @access public
      * 
      * @param string $fileName ruta completa y nombre del fichero
      */
    function getIni($fileName){
        if(!@file_exists($fileName)){
            if (!$nf = @fopen($fileName,'w')){
                $this->setPEARError(PEAR::raiseError ("¡¡¡ ERROR !!! (".__FILE__.",". __LINE__.") => No se puede abrir el fichero $fileName.<br>"));
//                $this= PEAR::raiseError ("¡¡¡ ERROR !!! (".__FILE__.",". __LINE__.") => No se puede abrir el fichero $fileName.<br>");
                return $this; 
            }
            $contenido="# Nuevo Fichero";
           // Escribir $contenido a nuestro arcivo abierto.
            if (@fwrite($nf, $contenido) === FALSE) {
                $this->setPEARError(PEAR::raiseError ("¡¡¡ ERROR !!! (".__FILE__.",". __LINE__.") => No se puede escribir el fichero $fileName.<br>"));
//                $this= PEAR::raiseError ("¡¡¡ ERROR !!! (".__FILE__.",". __LINE__.") => No se puede escribir el fichero $fileName.<br>");
                return $this; 
            }

            fclose($nf);
        }
        $pafinifile=new PAFIniFile($fileName);
        return $pafinifile;
    }

    /**
      * Sobrescribe el fichero sustituyendo el grupo modificado y si no existe lo crea
      *
      * @access public
      *
      * @param string $group nombre del grupo a crear o modificar
      * @param array $content valores del grupo
      *
      */
    function saveGroup($group,$content){       
        $fileName=$this->filename;
        if (!$nf = @fopen($fileName,'w')){
            $this->setPEARError(PEAR::raiseError ("¡¡¡ ERROR !!! (".__FILE__.",". __LINE__.") => No se puede abrir el fichero $fileName.<br>"));
//            $this= PEAR::raiseError ("¡¡¡ ERROR !!! (".__FILE__.",". __LINE__.") => No se puede abrir el fichero $fileName.<br>");
            return $this;  
        }   
        $this->listGroup($grupos);

        foreach($grupos as $nombreGrupo){

            if($nombreGrupo!=$group){             
                if (@fwrite($nf, "\n[$nombreGrupo]\n") === FALSE) {
                    $this->setPEARError(PEAR::raiseError ("¡¡¡ ERROR !!! (".__FILE__.",". __LINE__.") => No se puede escribir el fichero $fileName.<br>"));
//                    $this= PEAR::raiseError ("¡¡¡ ERROR !!! (".__FILE__.",". __LINE__.") => No se puede escribir el fichero $fileName.<br>");
                    return $this;  
                }  
                $this->getGroup($nombreGrupo,$ret,"NORMAL");                      
                foreach($ret as $key => $value){            
                    if (@fwrite($nf, "$key=$value\n") === FALSE) {
                        $this->setPEARError(PEAR::raiseError ("¡¡¡ ERROR !!! (".__FILE__.",". __LINE__.") => No se puede escribir el fichero $fileName.<br>"));
//                        $this= PEAR::raiseError ("¡¡¡ ERROR !!! (".__FILE__.",". __LINE__.") => No se puede escribir el fichero $fileName.<br>");
                        return $this;  
                    }    
                }           
            }

        }
        if (@fwrite($nf, "\n[$group]\n") === FALSE) {
            $this->setPEARError(PEAR::raiseError ("¡¡¡ ERROR !!! (".__FILE__.",". __LINE__.") => No se puede escribir el fichero $fileName.<br>"));
//            $this= PEAR::raiseError ("¡¡¡ ERROR !!! (".__FILE__.",". __LINE__.") => No se puede escribir el fichero $fileName.<br>");
            return $this;  
        }  


        foreach($content as $key => $value){            
            if (@fwrite($nf, "$key=$value\n") === FALSE) {
                $this->setPEARError(PEAR::raiseError ("¡¡¡ ERROR !!! (".__FILE__.",". __LINE__.") => No se puede escribir el fichero $fileName.<br>"));  
//                $this= PEAR::raiseError ("¡¡¡ ERROR !!! (".__FILE__.",". __LINE__.") => No se puede escribir el fichero $fileName.<br>");  
                return $this;  
            }    
        }  

        fclose($nf);
        return true;
    } 

   /**
      * Sobrescribe el fichero eliminando un grupo
      *
      * @access public
      *
      * @param string $group nombre del grupo a eliminar
      *
      */
    function removeGroup($group){       
        $fileName=$this->filename;
        if (!$nf = @fopen($fileName,'w')){
            $this->setPEARError(PEAR::raiseError ("¡¡¡ ERROR !!! (".__FILE__.",". __LINE__.") => No se puede abrir el fichero $fileName.<br>"));
//            $this= PEAR::raiseError ("¡¡¡ ERROR !!! (".__FILE__.",". __LINE__.") => No se puede abrir el fichero $fileName.<br>");
            return $this;  
        }   
        $this->listGroup($grupos);

        foreach($grupos as $nombreGrupo){

            if($nombreGrupo!=$group){             
                if (@fwrite($nf, "\n[$nombreGrupo]\n") === FALSE) {
                    $this->setPEARError(PEAR::raiseError ("¡¡¡ ERROR !!! (".__FILE__.",". __LINE__.") => No se puede escribir el fichero $fileName.<br>"));
//                    $this= PEAR::raiseError ("¡¡¡ ERROR !!! (".__FILE__.",". __LINE__.") => No se puede escribir el fichero $fileName.<br>");
                    return $this;  
                }  
                $this->getGroup($nombreGrupo,$ret,"NORMAL");                      
                foreach($ret as $key => $value){            
                    if (@fwrite($nf, "$key=$value\n") === FALSE) {
                        $this->setPEARError(PEAR::raiseError ("¡¡¡ ERROR !!! (".__FILE__.",". __LINE__.") => No se puede escribir el fichero $fileName.<br>"));
//                        $this= PEAR::raiseError ("¡¡¡ ERROR !!! (".__FILE__.",". __LINE__.") => No se puede escribir el fichero $fileName.<br>");
                        return $this;  
                    }    
                }           
            }

        }

        fclose($nf);
        return true;
    } 

}

?>
