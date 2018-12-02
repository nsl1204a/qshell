<?php

require_once 'PAF/PAFObject.php';

/**
 * Clase PAF para crear XMLs, funciona como un SAX parser y no valida el XML generado.
 *
 * @author Virgilio Sanz <vsanz@prisacom.com>
 * @version $Revision: 1.10 $
 * @package PAF
 *
 */
class PAFFastXmlWriter extends PAFObject
{
    // Attributos
    
    /**
     * Contiene incluirHeader
     * @access private
     */
    var $incluirHeader = true;

    /**
     * Contiene el XML que se está formando
     * @access private
     */
    var $theXML;

    /**
     * Contiene la cabecera del XML que se va a crear
     * @access private
     */
    var $theHead;
    
    /**
     * Array con los tags que se han ido añadiendo.
     * @access private
     */
    var $tags;
    
    /**
     * Contiene un boleano para saber si el tag anterior se le metieron caracteres
     * o es un tag contenedor.
     * @access private
     */
    var $charTag;
    
    /**
     * Contiene el nivel de "indentación" actual en el XML que se forma.
     * -1 Significa que no estamos dentro de ningún tag.
     * @access private
     */
    var $level;

    /**
     * Contiene si el XML se va a comprimir o no.
     * @access private
     */
     var $compress;
   
    /**
     * Contiene el espacio que con que se indentarán los tags
     * @access private
     */
     var $spaceForIndent;
     
    // Operations
   /**
    *    Constructor, sólo inicializa estructuras internas.
    *
    *    @param string $dtdName String con el nombre del tipo del documento.
    *    @param string $dtdURI URI de la DTD que define el documento.
    *    @param string $encoding Tipo de Encoding que tendrá el documento
    *    @param object $errorClass nombre de la Clase de error que se lanzara.
    *    
    *    @access public 
    */
    function PAFFastXmlWriter($dtdName = false, $dtdURI = '', $encoding = 'ISO-8859-1', $errorClass= null ) 
    {
        $this->init($dtdName, $dtdURI, $encoding, $errorClass); 
        $this->spaceForIndent = str_repeat(' ', 3);
    }
   
   /**
    *    Método que inicializa el writer.
    * 
    *    @param string $dtdName String con el nombre del tipo del documento.
    *    @param string $dtdURI URI de la DTD que define el documento.
    *    @param string $encoding Tipo de Encoding que tendrá el documento
    *    @param object $errorClass nombre de la Clase de error que se lanzara.
    *    
    *    @access public 
    */
   function init($dtdName = false, $dtdURI = '', $encoding = 'ISO-8859-1', $errorClass= null )
   {
        $this->PAFObject($errorClass);  // Llamada al constructor de la clase padre.

        if (!$dtdName) {
            $this->theHead = '<?xml version="1.0" encoding="' . $encoding . '" standalone="yes" ?>';
        }
        else {
            $this->theHead = '<?xml version="1.0" encoding="' . $encoding . '" ?>';
            $this->theHead .= "\n".'<!DOCTYPE ' . $dtdName . ' SYSTEM "' . $dtdURI . '" >';
        }
   }
  
   /**
    * Asigna si hay se va a comprimir el xml que se genere, quitando los espacios entre tags y los \n
    * 
    * @param $compress boolean Si true comprime si false no.
    *
    * @access public
    */
   function setCompression($compress)
   {
        $this->compress = $compress;
   }


    /**
    *
    * @param header
    * @access public
    */
    function setHeader($incluirHeader) {
        $this->incluirHeader = $incluirHeader;
    }

   
   /**
    * Asigna el espacio de indentación para cada nivel.
    *
    * @param string $s Espacio de indentacion
    * @access public
    */
    function setSpaceIndent($s) {
        $this->spaceForIndent = $s;
    }
   
   /**
    *    Empieza un XML
    *    
    *    @access public 
    */
    function startDoc() {
        
        $this->level = -1;
        $this->tags = array();
         
        // Iniciamos el XML
        if($this->incluirHeader){
          $this->theXML = $this->theHead;
        }
#        if (!$this->compress) 
#        {
#            $this->theXML .= "\n";
#        }
    }

   /**
    *    Añade un elemeto al XML (osea el tag y sus atributos)
    *    
    *    @access public 
    *    @param string $name Nombre del tag.
    *    @param hash $attrs hash con los atributos y sus valores.
    */
    function addElement($name, $attrs=false) {
        // Actualizamos variables de control de posición.
        $this->level ++;
        $this->tags[$this->level] = "$name";
        $this->charTag = false;
        
        if (!$this->charTag && !$this->compress) {
            $this->theXML .= "\n";
        }

        // Añadimos el tag
        $this->theXML .= $this->buildTag($name, $attrs, false);
        
    }

   /**
    *    Añade un elemeto de sin tag terminador al XML (osea el tag y sus atributos)
    *    
    *    @access public 
    *    @param string $name Nombre del tag.
    *    @param hash $attrs hash con los atributos y sus valores.
    */
    function addEmptyElement($name, $attrs=false) {
        $this->theXML .=  "\n".$this->buildTag($name, $attrs, true);
    }
    
   /**
    *    Añade el contenido de texto para un tag, ántes de añadirlo hace un htmlencode del texto
    *    
    *    @access public 
    *    @param string $data Cadena de caracteres a añadir
    */
    function characters($data) {
        $this->theXML .= $this->encode($data);
        $this->charTag = true;
    }

   /**
    *    Añade el contenido de texto para un tag en formato de CDATA 
    *    
    *    @access public 
    *    @param string $data Cadena de caracteres a añadir
    */
    function addCdata($data) 
    {
        $this->theXML .= "<![CDATA[$data]]>";
        $this->charTag = true;
    }

   /**
    *    Finaliza el último tag que se añadió.
    *    
    *    @access public 
    */
    function endElement() {
        if ($this->level >= 0) 
        {
            if (!$this->compress && !$this->charTag) 
            {
                $this->theXML .= "\n". $this->getTagIndentation();
            }

            $this->theXML .= sprintf("</%s>", $this->tags[$this->level]);
            
            unset($this->tags[$this->level]);
            unset($this->charTags[$this->level]);
            $this->level --;
            $this->charTag = false;
        }
    }

    /**
    *    Finaliza el último tag que se añadió de forma implícita.
    *    <tag k="3" /> en lugar de <tag k="3"></tag>
    *
    *    @access public
    */
    function endElementImplicity() {
        if ($this->level >= 0)
        {
            if (!$this->compress && !$this->charTag)
            {
                $this->theXML .= "\n". $this->getTagIndentation();
            }

            #$this->theXML .= sprintf("</%s>", $this->tags[$this->level]);
            
            $this->theXML = substr($this->theXML,0,strlen($this->theXML)-1)."/>";

            unset($this->tags[$this->level]);
            unset($this->charTags[$this->level]);
            $this->level --;
            $this->charTag = false;
        }
    }



    

   /**
    *    Finaliza el XML
    *    
    *    @access public 
    */
    function endDoc() {
        $this->level = -1;
        $this->tags = array();
        $this->charTags = array();
    }

   /**
    *    Devuelve el contenido formateado del XML
    *    
    *    @access public 
    *    @return string
    */
    function getOutput() {
        return ($this->theXML);
    }

   /**
    *    Salva el XML en el fichero $fileName
    *    
    *    @access public 
    *    @return bool
    *    @param string $fileUri Nombre del fichero donde se guardará el contenido del XML creado.
    */
    function save($fileUri) {
        $fp = fopen($fileUri, "wt");
        if (!$fp) return (false);
        fwrite($fp, $this->theXML, strlen($this->theXML));
        fclose($fp);

        return (true);
    }

   /**
    *    hace un htmlencode de $data (debería ser xmlencode, pero no existe).
    *    
    *    @access private 
    *    @param string $data String que se quiere "encodear"
    *    @return string
    */
    function encode($data) {
        $s = $data;
        $s = ereg_replace("<", "&le;", $s);
        $s = ereg_replace(">", "&gt;", $s);
        $s = ereg_replace("&", "&amp;", $s);
        $s = ereg_replace("'", "&apos;", $s);
        $s = ereg_replace('"', "&quot;", $s);

        return ($s);
    }

   /**
    *    Crea la parte inicial del tag, con su indentación, hasta ántes justo del cierre.
    *    
    *    @access private 
    *    @param string $name Nombre del tag.
    *    @param hash $attrs hash con los atributos y sus valores.
    *    @param bool $isEndTag Es un tag terminado en /> o un tag normal
    *    @return string
    */
    function buildTag($name, $attrs = false, $isEndTag = false) {
        
        $tag = "<$name";
        
        if (false != $attrs) {
            while (list($key, $data) = each($attrs)) {
                $tag .= sprintf(" %s=\"%s\"", $key, $data);
            }
        }
      
        $tag .= ($isEndTag ? "/>" : ">");
        $s = $tag;
        
        if (!$this->compress) {
            $s = $this->getTagIndentation() . $tag;
        }
        return ($s);
    }

   /**
    * Devuelve el espacio de indentación para el nivel actual 
    *
    * @return string Espacio de indentacion
    * @access private
    */
    function getTagIndentation() {
        return(str_repeat($this->spaceForIndent, $this->level));
    }
}
?>
