<?php

require_once "PAF/PAFObject.php";
require_once "PAF/PAFOptionAnchor.php";
require_once "PAF/PAFMetadata.php";
require_once "PAF/PAFSAXParser.php";
require_once "PAF/PAFAnchorSAXHandler.php";
require_once "NAV/NAVMenuSAXHandler.php";
require_once "NAV/NAVMenu.php";

define ("PA_SEPARATOR", ";");
define ("PA_ENDLINE", "\n");
define ("PA_STARTMENUFILE", "SF");
define ("PA_STARTMETA", "ST");
define ("PA_DATA", "DT");
define ("PA_STARTSTYLE", "SS");
define ("PA_STARTSCRIPT", "SC");
define ("PA_STARTMENUTEMPLATE", "SP");
define ("PA_SERIALIZE_EXT", "ser");
define ("PA_XML_EXT", "xml");


/**
  * Clase que implementa Anchor.
  * El contenido de este objeto es rellenado por medio del parseo de un fichero
  * XML específico con la definición del ambito.
  *
  * @author Gustavo Núñez <gnunez@prisacom.com>
  * @version $Revision: 1.8 $
  * @package PAF  
  */
  
class PAFAnchor extends PAFObject{

    /**
      * Array de objetos PAFOptionAnchor (Durante el parseo).
      * Propiedad inexistente (Objeto PAFAnchor serializado).
      * @access private
      * @var array
      */
    var $menuData = array ();

    /**
      * Codigo anchor.
      * @access private
      * @var string
      */
    var $anchorCode = "";

    /**
      * Contiene el par Desc - File
      * @access private
      * @var array
      */
    var $menuFile = array ();

    /**
      * Contiene el par Desc - TPL
      * @access private
      * @var array
      */
    var $menuTemplate = array ();

    /**
      *  Array de objetos NAVmetadata.
      *  @access private
      *  @var array
      */
    var $meta = array ();

    /**
      * Nombre de pagina para un anchor.
      * @access private
      * @var string
      */
    var $page = "";

    /**
      * Tiempo de cache.
      * @access private
      * @var integer
      */
    var $cache = 0;

    /**
      * Tiempo de refresh.
      * @access private
      * @var integer
      */
    var $refresh = 0;

    /**
      * Prefijo de una direccion.
      * @access private
      * @var string
      */
    var $prefix = "";

    /**
      * Estilos de una pagina determinada
      * @access private
      * @var array
      */
    var $style = array ();

    /**
      * Scripts de una pagina determinada
      * @access private
      * @var array
      */
    var $script = array ();

    /**
      * Titulo de una pagina.
      * @access private
      * @var string
      */
    var $documentTitle = "";

    /**
      * Template general de la pagina (rejilla con bloques).
      * @acces private
      * @var string
      */
    var $documentTemplate = "";

    /**
      * Constructor.
      * @access public
      * @param string $anchorId Identificador de anchor
      * @param string $pathAnchorSer Path de los anchors serializados (.ser)
      * @param string $pathAnchorXML Path de los anchors XML
      * @param string $pathMenuXML Path de los menus XML.
      * @return object Si se le pasa un fichero devuelve el objeto serializado (PAFAnchor)
      */
    function PAFAnchor ($anchorId = null, $pathAnchorSer = null, $pathAnchorXML = null, $pathMenuSer = null, $pathMenuXML = null) {

             $this->PAFObject ();

             if ($anchorId) {

                 $anchorSer = $pathAnchorSer.$anchorId.".".PA_SERIALIZE_EXT;
                 $anchorXML = $pathAnchorXML.$anchorId.".".PA_XML_EXT;
                 if (!is_file($anchorXML)){
                        if (substr($anchorId,0,3)=="elp") $anchorId="elpeputec";
                        $anchorSer = $pathAnchorSer.$anchorId.".".PA_SERIALIZE_EXT;
                        $anchorXML = $pathAnchorXML.$anchorId.".".PA_XML_EXT;

                 }

                 if (!is_file ($anchorSer)) 
                 {
					 $valReturn = $this->regenerateSer ($anchorXML, $anchorSer, $pathMenuXML, $pathMenuSer);
					 if (PEAR::isError($valReturn))
                     {
                        $this = $valReturn;
                        return $this;
                     }
                 }

                 if ($pf = fopen ($anchorSer, "r")) {

                     $fileContent = fread ($pf, filesize ($anchorSer));
                     fclose ($pf);
                 }
                 else {

                    $this = PEAR::raiseError ("¡¡¡ ERROR !!! (".__FILE__.",". __LINE__.") => No se ha podido abrir el fichero para leer<br>");
                    return $this;
                 }

                 $this = PAFAnchor::unserialize ($fileContent);

                 if ($pathAnchorXML) {
                     if ($this->updateSerializes ($anchorSer, $anchorXML, $pathMenuXML, $pathMenuSer))
                        $this->regenerateSer ($anchorXML, $anchorSer, $pathMenuXML, $pathMenuSer);
                 }

                 $this->anchorCode = $anchorId;
             }
    }

    /**
      * Comprueba si se han modificado los XML, desde que se serializaron.
      * @access private
      * @param string $anchorSer Ruta y fichero anchor serializado (.ser)
      * @param string $anchorXML Ruta y fichero anchor XML
      * @param string $pathMenuXML Ruta de los fichero XML (.xml)
      * @param string $pathMenuSer Ruta de los ficheros serializados (.ser)
      */
    function updateSerializes ($anchorSer, $anchorXML, $pathMenuXML = null, $pathMenuSer = null) {

        $updateSer = 0;

        if (@filemtime ($anchorXML) > @filemtime ($anchorSer))
            $updateSer = 1;

        if ($pathMenuXML && $pathMenuSer) {
            $menuFiles = $this->getMenuFiles ();

            while (list ($key, $value) = each ($menuFiles)) {

                if (@filemtime ($pathMenuXML.$key.".".PA_XML_EXT) > @filemtime ($pathMenuSer.$value.".".PA_SERIALIZE_EXT))
                    $updateSer = 1;
            }
        }

        return $updateSer;
    }

    /**
      * Metodo que mezcla las caracteristicas del anchor (.xml) con los menus (.xml) y crea
      * los fichero serializado en la ruta adecuada.
      * @access private
      * @param string $anchorXML Ruta y fichero anchor XML
      * @param string $anchorSer Ruta y fichero anchor serializado (.ser)
      * @param string $pathMenuXML Ruta de los fichero XML (.xml)
      * @param string $pathMenuSer Ruta de los ficheros serializados (.ser)
      */
    function regenerateSer ($anchorXML, $anchorSer, $pathMenuXML, $pathMenuSer) {

        $handlerAnchor = new PAFAnchorSAXHandler ();
        $parser = new PAFSAXParser ($handlerAnchor);
        if (PEAR::isError ($parser))
        {
            return $parser;
        }
        $retValue = $parser->parseFile($anchorXML);
        if (PEAR::isError ($retValue))
        {
            return $retValue;
        }
        $h_anchor =& $parser->getDocumentHandler();
        $anchor = $h_anchor->getAnchor ();

        $anchorMenuData =& $anchor->getMenuData ();
        $menuFile = $anchor->getMenuFiles ();
        while (list ($file,$anchorOptions) = each ($anchorMenuData)) { 

               $fileMenuAux = $pathMenuXML . $file.".".PA_XML_EXT; // XML que contiene las opciones de menu.
               $handlerMenu = new NAVMenuSAXHandler ();

               unset ($parser);
               $parser = new PAFSAXParser ($handlerMenu);
                if (PEAR::isError ($parser))
                {
                    return $parser;
                }
               $retValue = $parser->parseFile($fileMenuAux);
                if (PEAR::isError ($retValue))
                {
                    return $retValue;
                }
               $h_menu =& $parser->getDocumentHandler();

               $menu = $h_menu->getMenu ();
               $menuOptions =& $menu->getMenuOption (); // Obtenemos las opciones del menu.

               $this->modifyOptionMenu ($menuOptions, $anchorOptions); // Establecemos las propiedades en el menu a partir del anchor.

               # Serializamos el objeto NAVMenu.
               $serializeFile = $pathMenuSer . $menuFile [$file] .".".PA_SERIALIZE_EXT; // Nombre del fichero serializado lo obtenemos del anchor.
               $numero ++;
               $menuSerializado = $menu->serialize (); //Serializamos el objeto NAVMenu.

               if (true) { //is_writeable ($serializeFile)) {
                   $pf = fopen ($serializeFile, "w");
                   fwrite ($pf, $menuSerializado);
                   fclose ($pf);
               }
        }

        # Eliminamos los objetos PAFOptionAnchor del objeto PAFAnchor para serializarlo.
        $anchor->unsetMenuData ();

        $anchorSerialize = $anchor->serialize ();

        if (true) { //is_writeable ($anchorSer)) {

            $pf = fopen ($anchorSer, "w");
            fwrite ($pf, $anchorSerialize);
            fclose ($pf);
            $this = $anchor;
        }
        return true;
    }

    /**
      * Establece nuevas propiedades al objeto NAVMenuOption a partir de un ANCHOR (PAFAnchor)
      * @param object $menuOptions Opciones de un objeto NAVMenu (Coleccion de NAVMenuOption y NAVMenu)
      * @param object $anchorOptions Opciones de un objeto PAFAnchor
      */
    function modifyOptionMenu (&$menuOptions, $anchorOptions) {


              while (list (,$anchorOption) = each ($anchorOptions)) {

                     $id = $anchorOption->getItem ();

                     for ($i = 0; $i<count ($menuOptions); $i++) {

                          $idMenuOption = $menuOptions [$i]->getId ();

                          if ($idMenuOption == $id) {

                              $menuOptions [$i]->setFolded ($anchorOption->getFolded ());
                              $menuOptions [$i]->setActive ($anchorOption->getActive ());
                              $menuOptions [$i]->setVisible ($anchorOption->getVisible ());
                              $menuOptions [$i]->setSelected ($anchorOption->getSelected ());

                              $subMenu =& $menuOptions [$i]->getMenu ();
                              if ($subMenu) {

                                  $subMenuOptions =& $subMenu->getMenuOption ();
                                  $this->modifyOptionMenu ($subMenuOptions, $anchorOptions);
                              }
                          }
                     }
              }
    }


    /**
      * Metodo estatico que retorna el nombre de la clase.
      * @access public
      * @return string nombre de la clase
      */
    function getClassName () {

             return "PAFAnchor";
    }

    /**
      * Metodo que determina si una clase es de un tipo determinado.
      * @access public
      * @param int $tipo Numero entero con el codigo de la clase por la que preguntamos.
      * @return boolean
      */
    function isTypeOf ($tipo) {

             return (PAFObject::isTypeOf($tipo));
    }

    /**
      * Metodo que devuelve el codigo del anchor.
      * @access public
      * @return string
      */
    function getAnchorId () {

             return $this->anchorCode;
    }

    /**
      * Añade un objeto NAVMenuOption al array de opciones del menú identificados por Desc.
      * @access public
      * @param string $menuDesc Atributo desc del xml.
      * @param object $option NAVMenuOption con la opción de menú
      * correspondiente.
      */
    function addMenuData ($menuDesc, $options) {

             $this->menuData [$menuDesc] = $options;
    }

    /**
      * Elimina la propiedad menuData.
      * @access public
      */
    function unsetMenuData () {

             unset ($this->menuData);
    }

    /**
      * Devuelve un array con objetos PAFOptionAnchor.
      * @access public
      * @var array
      */
    function &getMenuData () {

             return $this->menuData;
    }


    /**
      * Añade un objeto PAFMetadata al array de metas del Anchor.
      * @access public
      * @param object $metadata PAFMetadata con el meta correspondiente.
      */

    function addMeta ($metadata) {

             $this->meta [] = $metadata;
    }

    /**
      * Devuelve un array con los objetes PAFMetadata.
      * @access public
      * @return array
      */
    function getMeta () {

             return $this->meta;
    }

    /**
      * Establece el nombre de pagina para un anchor.
      * @access public
      * @param string $namePage Nombre de la pagina
      */
    function setPage ($namePage) {

             $this->page = $namePage;
    }

    /**
      * Devuelve el nombre del banner para un anchor.
      * @access public
      * @return string
      */
    function getPage () {

             return $this->page;
    }

    /**
      * Establece el tiempo de cache para un anchor.
      * @access public
      * @param int $timeCache Tiempo de cache en segundo.
      */
    function setCache ($timeCache) {

             $this->cache = $timeCache;
    }

    /**
      * Devuelve el tiempo de cache.
      * @access public
      * @return int
      */
    function getCache () {

             return $this->cache;
    }

    /**
      * Establece el tiempo de Refresco para este anchor.
      * @access public
      * @param int $timeRefresh Tiempo de Refresh en segundo.
      */
    function setRefresh ($timeRefresh) {

        $this->refresh = $timeRefresh;
        // Añadimos meta para el refresh si es que lo necesita:
        if (0 < $this->refresh) {
             $meta = new PAFMetadata();
             $meta->setContent($this->refresh);
             $meta->setEquiv('REFRESH');
             $this->addMeta($meta);
        }
    }

    /**
      * Devuelve el tiempo de Refresco.
      * @access public
      * @return int
      */
    function getRefresh () {

             return $this->refresh;
    }


    /**
      * Establece un prefijo determinado para un determinado anchor.
      * @access public
      * @param string $value Prefijo
      */
    function setPrefix ($value) {

             $this->prefix = $value;
    }

    /**
      * Devuelve el prefijo para un determinado anchor.
      * @access public
      * @return string
      */
    function getPrefix () {

             return $this->prefix;
    }

    /**
      * Añade un nombre de fichero (.ser) a un descriptor de menu (.xml)
      * @access public
      * @param string $desc Nombre del XML de menu.
      * @param string $value Nombre del fichero SER de menu
      */
    function addMenuFile ($desc, $value) {

             $this->menuFile [$desc]= $value;
    }

    /**
      * Devuelve un array con el par Desc - File.
      * @access public
      * @return array
      */
    function getMenuFiles  () {

             return $this->menuFile;
    }

    /**
      * Añade un nombre de fichero .tpl a un descriptor de menu.
      * @access public
      * @param string $desc Nombre del XML de menu
      * @param string $value Nombre del fichero TPL de menu.
      */
    function addMenuTemplate ($desc, $value) {

             $this->menuTemplate [$desc] = $value;
    }

    /**
      * Devuelve un array con el par Desc - TPL
      * @access public
      * @return array
      */
    function getMenuTemplates () {

             return $this->menuTemplate;
    }



    /**
      * Añade los estilos a un array
      * @access public
      * @param string $value Nombre de la hoja de estilos.
      */
    function addStyle ($value) {

             $this->style [] = $value;
    }

    /**
      * Devuelve los estilos.
      * @access public
      * @return array
      */
    function getStyles () {

             return $this->style;
    }

    /**
      * Añade scripts (.js) a un array
      * @access public
      * @param string $value Fichero .js
      */
    function addScript ($value) {

             $this->script [] = $value;
    }

    /**
      * Devuelve los .js
      * @access public
      * @return array
      */
    function getScript () {

             return $this->script;
    }

    /**
      * Establece el nombre de la Template (rejilla) del documento.
      * @access public
      * @param string $value Nombre del fichero .tpl del documento.
      */
    function setTemplate ($value) {

             $this->documentTemplate = $value;
    }

    /**
      * Devuelve el nombre de la template (rejilla) del documento.
      * @acces public
      * @return string
      */
    function getTemplate () {

             return $this->documentTemplate;
    }

    /**
      * Establece el Titulo del documento.
      * @access public
      * @param string $value Titulo del documento
      */
    function setDocumentTitle ($value) {

             $this->documentTitle = $value;
    }

    /**
      * Devuelve el Titulo del documento.
      * @acces public
      * @return string
      */
    function getDocumentTitle () {

             return $this->documentTitle;
    }

    /**
      * Devuelve la serializacion del objeto.
      * @access public
      * @param object $anchor Si no se le pasa un objeto PAFAnchor, se serializa a si mismo.
      * @return string
      */
    function serialize ($anchor = null) {

         if (!$anchor)
              $anchor = $this;

         $menuFile = $anchor->getMenuFiles ();
         $metadata = $anchor->getMeta ();
         $styles = $anchor->getStyles ();
         $scripts = $anchor->getScript ();
         $menuTpl = $anchor->getMenuTemplates ();

         $srzString .= PA_STARTSTYLE.PA_SEPARATOR.implode (PA_SEPARATOR, $styles).PA_ENDLINE;
         $srzString .= PA_STARTSCRIPT.PA_SEPARATOR.implode (PA_SEPARATOR, $scripts).PA_ENDLINE;
         $srzString .= $this->menuTplToString ($menuTpl);
         $srzString .= $this->metadataToString ($metadata);
         $srzString .= $this->menuToString ($menuFile);
         $srzString .= PA_DATA.PA_SEPARATOR.$anchor->getCache ().
                               PA_SEPARATOR.$anchor->getPrefix ().
                               PA_SEPARATOR.$anchor->getTemplate ().
                               PA_SEPARATOR.$anchor->getDocumentTitle ().
                               PA_SEPARATOR.$anchor->getPage ().PA_ENDLINE;

         return $srzString;
    }

    /**
      * Combierte un array hash a string
      * @access private
      * @param array $menu Array hash con el par Desc - TPL
      * @return string
      */
    function menuTplToString ($menuTpl) {

         while (list ($key, $value) = each ($menuTpl)) {

                $string .= PA_STARTMENUTEMPLATE.PA_SEPARATOR.$key.PA_SEPARATOR.$value.PA_ENDLINE;
         }

         return $string;
    }


    /**
      * Pasa los objetos metadatas a string.
      * @access private
      * @param object $meta Objetos PAFMetadata
      * @return string
      */
    function metadataToString ($metas) {
        $noMetas = count ($metas);
        for ($i = 0; $i < $noMetas; $i++) {
              $string .= PA_STARTMETA.PA_SEPARATOR.$metas [$i]->getName ().PA_SEPARATOR;
              $string .=$metas [$i]->getEquiv ().PA_SEPARATOR;
              $string .=$metas [$i]->getContent ().PA_SEPARATOR;
              $string .=$metas [$i]->getScheme ().PA_ENDLINE;
        }

        return $string;
    }

    /**
      * Combierte un array hash a string
      * @access private
      * @param array $menu Array hash con el par Desc - File
      * @return string
      */
    function menuToString ($menu) {

         while (list ($key, $value) = each ($menu)) {

                $string .= PA_STARTMENUFILE.PA_SEPARATOR.$key.PA_SEPARATOR.$value.PA_ENDLINE;
         }

         return $string;
    }

    /**
      * Devuelve la unserializacion del objeto.
      * @access public
      * @param string $string Cadena obtenida del fichero serializado.
      * @return object
      */
    function unserialize ($string) {

         $arrayParams = explode (PA_ENDLINE, $string);

         $noParams = count ($arrayParams);

         $anchor = new PAFAnchor ();

         for ($i = 0; $i < $noParams; $i++) {

              $param = explode (PA_SEPARATOR, $arrayParams [$i]);

              if ($param [0] == PA_STARTMENUFILE) {

                      $anchor->addMenuFile ($param [1], $param [2]);
              }
              elseif ($param [0] == PA_STARTMETA) {

                      $meta = new PAFMetadata ();

                      $meta->setName ($param [1]);
                      $meta->setEquiv ($param [2]);
                      $meta->setContent ($param [3]);
                      $meta->setScheme ($param [4]);

                      $anchor->addMeta ($meta);
                      unset ($meta);
              }
              elseif ($param [0] == PA_STARTSTYLE) {

                      $noStyles = count ($param);
                      for ($j = 1; $j < $noStyles; $j++)
                           $anchor->addStyle ($param [$j]);

                      unset ($j);

              }
              elseif ($param [0] == PA_STARTSCRIPT) {

                      $noScript = count ($param);
                      for ($j = 1; $j < $noScript; $j++)
                           $anchor->addScript ($param [$j]);

                      unset ($j);
              }
              elseif ($param [0] == PA_STARTMENUTEMPLATE) {

                      $anchor->addMenuTemplate ($param [1], $param [2]);
              }
              elseif ($param [0] == PA_DATA) {

                       $anchor->setCache ($param [1]);
                       $anchor->setPrefix ($param [2]);
                       $anchor->setTemplate ($param [3]);
                       $anchor->setDocumentTitle ($param [4]);
                       $anchor->setPage ($param [5]);
              }
         }

         $anchor->unsetMenuData();
         return $anchor;
    }


}

?>
