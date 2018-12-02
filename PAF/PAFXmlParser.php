<?
// *****************************************************************************
// Lenguaje: PHP
// Copyright 2002 Prisacom S.A.
// *****************************************************************************

require_once "PAF/PAFObject.php";
require_once "PAF/PAFxmlTree.php";


class PAFXmlParser extends PAFObject
{
    var $ramas;
    var $dom;
    var $error=false;

   /**
    * Constructor de la clase.
    * Se encarga de crear el árbol de objetos php y las referencias de acceso.
    * Parametros de entrada: se le pasa un string con el nombre del fichero que queremos interpretar.
    */
    function PAFXmlParser($nombfich = "")
    {
	if ("" == $nombfich) { 
	    $this->error=true;
            return;
	}

	$fd = fopen($nombfich, "r");
	$xmlstr=fread($fd, filesize($nombfich));
	fclose($fd);
	
	if(!$raiz=$this->crearArbol($xmlstr)) {
	    $this->error=true;
	}

    }

   /**
    * Crea el arbol de objetos php.
    * Dado un fichero xml, transforma las sentencias CDATA y crea el arbol de objetos php.
    * Parametros de entrada: se le pasa un string con el contenido del fichero xml.
    */
    function crearArbol( $xmlstr )
    {
	//$xmlstr=$this->quitarEncoding($xmlstr);
	$xmlstr=$this->transformarCdatas($xmlstr);
	if(!$this->dom = PAFxmlTree($xmlstr)) {
	    return false;
	}
	
	$objetos = $this->dom->children;
	for($i=0; $i< count($objetos); $i++) {
	    $this->crearReferencias( $objetos[$i] );
	}
	return $this->dom->root->name."(1)";
    }

    function quitarEncoding($str)
    {
	 $str=preg_replace("/encoding=\s*['|\"]\s*ISO-8859-1\s*['|\"]/i","",$str,1);
	 	  
	 return $str;
    }

   /**
    * Transforma los elementos CDATA del documento por sentencias legibles para el parseador. 
    * Transforma los elementos CDATA debido a un bug de la función de php xmltree, de este modo el contenido del elemento CDATA no es interpretado.
    * Parametros de entrada: se le pasa un string con el contenido del fichero xml.
    * Parametros de salida: String que almacena el contenido del elemento CDATA transformado.
    */
    function transformarCdatas($xmlstr)
    {
	$corchete="]";
	$corchete2="]]>";
	$matches=preg_match_all("|(<!\[CDATA\[)(.*)(\]\]>)|Us",$xmlstr,$cdatas);
	for($i=0;$i<$matches;$i++){
	  $elecdata[$i]="<cdata>".urlencode($cdatas[2][$i])."</cdata>";
	  $xmlstr=preg_replace("|(<!\[CDATA\[)(.*)(\]\]>)|Us",$elecdata[$i],$xmlstr,1);
	   
	}
	return $xmlstr;


    }

   /**
    * Recuperación  de los datos transformados del elemento CDATA. 
    * Recupera los datos cifrados del elemento CDATA puesto que habían sido transformados antes de crear el arbol de objetos.
    * Parametros de entrada: String que almacena el contenido del elemento CDATA transformado.
    * Parametros de salida: String que almacena el contenido del elemento CDATA en texto claro.
    */
    function recuperarCdatas($texto)
    {
	   $texto=urldecode($texto); 
	   return $texto;

    }

   /**
    * Crea las Referencias para el acceso al arbol de objetos.
    * Crea las referencias que luego utilizaremos para poder navegar por el arbol de objetos php.
    * Parametros de entrada: Objeto nodo, al que queremos asignar una referencia, String que es la referencia que usaremos.
    */
    function crearReferencias( $nodo, $etiqueta = "" ) 
    {
        if($nodo->type == XML_ELEMENT_NODE )
        {
            if ( $etiqueta == "" )
               $nombre = $nodo->name;
            else
               $nombre = $etiqueta.".".$nodo->name;
    
            if ( !isset( $this->ramas["$nombre"] ) )
               $this->ramas["$nombre"] = 1;
            else
               $this->ramas["$nombre"] ++;

            $nombre = $nombre."(".$this->ramas["$nombre"].")";

            $this->ramas["$nombre"] = $nodo;
            $children = $nodo->children;
            for($i=0; $i < count($children); $i++)
              $this->crearReferencias($children[$i], $nombre );
        }
    }

   /**
    * Obtiene un elemento del arbol.
    * Obtiene el elemento completo, su camino, sus hijos, sus atributos y el texto.
    * Parametro de entrada: String, referencia del elemento dentro del arbol.
    * Paramentro de Salida: Array de 5 elementos que contienen CAMINO, HIJOSNODO, ATRIBUTOS, TEXTO.
    */
    function obtenerElemento($camino)
    {
        $elem=array();
        $elem["CAMINO"]=$camino;
        $elem["HIJOSNODO"]=$this->obtenerHijosElemento($camino);
        $elem["ATRIBUTOS"]=$this->obtenerAtributos($camino);
        $elem["TEXTO"]=$this->obtenerTexto($camino,true);
        return $elem;

    }

   /**
    * Obtiene los Hijos de un elemento.
    * Obtiene los hijos (unicamente de tipo Nodo) de un elemento nodo, diciendo de que tipo son y cuantos de ese tipo.
    * Parametros de entrada: String, referencia del elemento del cual queremos obtener los hijos.
    * Parametros de salida: Array que contiene como Keys los nombres de los elementos y como valores cuantos hay.
    */
    function obtenerHijosElemento($elemento,$name="")
    {
        $nodo=$this->ramas["$elemento"];
        $hijos=$nodo->children;
        if($hijos)
        {
           for($i=0;$i < count($hijos);$i++){
               if($hijos[$i]->type==XML_ELEMENT_NODE){
                   $nodosElemento[$i]=$hijos[$i]->name;
               }
           }
           if($nodosElemento)
   	   {   
	      $nodosElemento=array_count_values ($nodosElemento);
	      if(!empty($name)) $nodosElemento=$nodosElemento[$name];			   	
	      return $nodosElemento;
	   }
	   else return $nodosElemento;
        }
    }

   /**
    * Obtiene todos los atributos o uno en cuestion de un elemento.
    * Obtiene o un array con todos los atributos que tiene el elemento o el valor de un atributo que indiquemos.
    * Parametros de entrada: String, referencia del elemento, String (opcional), nombre del atributo del cual queremos saber el valor.
    * Parametros de Salida: Array, donde las Keys son los nombres de los atributos y los valores, los valores de estos, si solo accedemos al atributo es un string con el // valor
    */
    function obtenerAtributos($elemento,$attname="")
    {
        $nodo = $this->ramas["$elemento"];
        $atributos = $nodo->attributes;
        if ( $atributos )
        {
            for($i=0; $i < count($atributos); $i++){
                //$att[$atributos[$i]->name] = $atributos[$i]->children[0]->content;
                $att[$atributos[$i]->name] = utf8_decode($atributos[$i]->children[0]->content);
        
            }
            if(empty($attname)) return $att;
            else return $att[$attname];   
         }
    }

   /**
    * Obtiene el Texto de un elemento.
    * Parametros de entrada: String, referencia del elemento, boolean indica si el texto le queremos plano o no. 
    * Parametros de salida: Array con los elementos texto y elementos nodo que contiene el elemento, string que devuelve el texto plano.
    */
    function obtenerTexto($elemento,$plano)
    { 
        $text="";
        $texto=$this->obtenerHijosOrdenados($elemento);
        if($plano)
        {
            for($i=0;$i<count($texto);$i=$i+2)
            {
                switch($texto[$i])
                {
	          case XML_ELEMENT_NODE:
                       $text.=$this->obtenerTexto($texto[$i+1],$plano);
                       break;
                  case XML_TEXT_NODE:
                       $text.= $texto[$i+1];
                       break;
                  case XML_CDATA_SECTION_NODE:
                       $text.=$texto[$i+1];
                       break; 
                  default:;

                }
            }
            return $text;  
        }
        else
        {
            if(count($texto)>2) return $texto;
            else return $texto[1];
        }
             
    }

   /**
    * Obtiene los Hijos del elemento, (tipo Elemento, Texto, o CData)
    * Parametros de entrada: String, referencia del elemento. 
    * Parametros de Salida: array compuesto por los elementos pares llevan el tipo del hijo y los impares el contenido o el nombre de la etiqueta según corresponda.
    */
    function obtenerHijosOrdenados($elemento)
    {
        $hijos=array();
        $nodo=$this->ramas["$elemento"];
        $children=$nodo->children;
        $cont=0;
        $cdatas=0;
        for($i=0; $i<count($children); $i++)
        {
      
            if(($children[$i]->type==XML_TEXT_NODE)||($children[$i]->type==XML_ELEMENT_NODE))
            {
    
                $hijosOrd[$cont]=$children[$i]->type;
        
                if($children[$i]->type==XML_TEXT_NODE)
                {
                     //$hijosOrd[$cont+1]=$children[$i]->content;
                     $hijosOrd[$cont+1]=utf8_decode($children[$i]->content);
                }
                else
                {
                     if($children[$i]->name=="cdata")
                     { 
                         $texto=$this->obtenerTexto($elemento.".cdata(".($cdatas+1).")",false);
		         $cdatas++;
                         $texto=$this->recuperarCdatas($texto);
                         $hijosOrd[$cont]=XML_CDATA_SECTION_NODE;
                         $hijosOrd[$cont+1]=$texto;
          
           
                     }
                     else {
                          $numhijos[$children[$i]->name]=intval($numhijos[$children[$i]->name])+1;
                          $hijosOrd[$cont+1]=$elemento.".".$children[$i]->name."(".$numhijos[$children[$i]->name].")";
                     }
         
                }
                $cont=$cont+2;
            }
      
        }

        if(empty($hijosOrd)) return "";

        return $hijosOrd;
    }
	
    function buscarElemento($elemento,$etiq,$atributo,$valorattr)
    {
        $numhijos= $this->obtenerHijosElemento($elemento,$etiq);
        while ($i<$numhijos)
        {
            $i++;
    	    $ref=$elemento.".".$etiq."($i)";
    	    $valor=$this->obtenerAtributos($ref,$atributo);
    	    if($valor==$valorattr) $find[]=$i;
        }
        if(count($find)==1) return $find[0]; 
        return $find;
    }

   /**
    * Obtiene el Nombre de una etiqueta xml.
    * Dada la referencia del elemento dentro del arbol, nos devuelve la etiqueta xml que identifica a ese elemento.
    * Parametro de entrada: String, referencia del elemento.
    * Parametro de Salida: String, nombre de la etiqueta.
    */
   function obtenerNombreEtiqueta($elemento)
   {
      
       $elerev=strrev($elemento);
       $find=strpos($elerev,".)");
       $etiq=substr($elemento,strlen($elemento)-$find,strlen($elemento));
       return substr($etiq,0,strpos($etiq,"("));
   
   }

}

?>
