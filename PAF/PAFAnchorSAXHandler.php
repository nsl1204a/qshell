<?php

require_once "PAF/PAFSAXDocumentHandler.php";
require_once "PAF/PAFAnchor.php";
require_once "PAF/PAFOptionAnchor.php";
require_once "PAF/PAFMetadata.php";

// -------------------------------------------------------------------------
// Conjunto de constantes que nos interesan dentro del documento XML.
// -------------------------------------------------------------------------
define ("NAS_TANCHOR", "ANCHOR");
define ("NAS_TMENU", "MENU");
define ("NAS_ADESC", "DESC");
define ("NAS_AFILE", "FILE");
define ("NAS_TOPTION", "OPTION");
define ("NAS_AITEM", "ITEM");
define ("NAS_AFOLDED", "FOLDED");
define ("NAS_AACTIVE", "ACTIVE");
define ("NAS_AVISIBLE", "VISIBLE");
define ("NAS_ASELECTED", "SELECTED");
define ("NAS_TPREFIX", "PREFIX");
define ("NAS_ADIR", "DIR");
define ("NAS_TPAGE", "PAGE");
define ("NAS_ANAME", "NAME");
define ("NAS_TCACHE", "CACHE");
define ("NAS_ATIME", "TIME");
define ("NAS_TMETA", "META");
define ("NAS_AEQUIV", "EQUIV");
define ("NAS_ACONTENT", "CONTENT");
define ("NAS_ASCHEME", "SCHEME");
define ("NAS_TDOCUMENT", "DOCUMENT");
define ("NAS_ATITLE", "TITLE");
define ("NAS_TTEMPLATE", "TEMPLATE");
define ("NAS_ATPL", "TPL");
define ("NAS_TSTYLE", "STYLE");
define ("NAS_ACSS", "CSS");
define ("NAS_TSCRIPT", "SCRIPT");

// -------------------------------------------------------------------------


class PAFAnchorSAXHandler extends PAFSAXDocumentHandler {

     /**
       * Anchor a devolver.
       * @access private
       *
       */

     var $returnAnchor;

     /**
       * Array de objetos PAFOptionAnchor
       * @access private
       * @var array
       */
     var $menuOptions = array ();

     /**
       * Atributo desc.
       * @access private
       * @var string
       */
     var $menuDesc = "";




    /**
      * Constructor.
      * @access public
      *
      */
    function PAFAnchorSAXHandler () {

        $this->PAFSAXDocumentHandler ();
    }

    function getAnchor () {

             return $this->returnAnchor;
    }

    /**
      * Método que controla cuando se encuentra el comienzo de un elemento.
      *
      * @access public
      * @param string $name Nombre del Elemento.
      * @param array $attributes Array asociativo con los nombres (keys) de los atributos y su valor
      * para el elemento actual.
      */
    function startElement ($parser, $name, $attributes) {

        if (!strcasecmp ($name, NAS_TANCHOR)) {

             $this->currentTag = NAS_TANCHOR;
             $this->returnAnchor = new PAFAnchor ();
        }
        elseif (!strcasecmp ($name, NAS_TMENU)) {

                 $this->currentTag = NAS_TMENU;
                 $this->menuDesc = trim ($attributes [NAS_ADESC]);
                 $this->returnAnchor->addMenuFile ($this->menuDesc, trim ($attributes [NAS_AFILE]));
                 $this->returnAnchor->addMenuTemplate ($this->menuDesc, trim ($attributes [NAS_ATPL]));
        }
        elseif (!strcasecmp ($name, NAS_TOPTION)) {

            $this->currentTag = NAS_TOPTION;
            $currentOption = new PAFOptionAnchor ();

            $currentOption->setItem (trim ($attributes [NAS_AITEM]));
            $currentOption->setFolded (trim ($attributes [NAS_AFOLDED]));
            $currentOption->setActive (trim ($attributes [NAS_AACTIVE]));
            $currentOption->setVisible (trim ($attributes [NAS_AVISIBLE]));
            $currentOption->setSelected (trim ($attributes [NAS_ASELECTED]));

            $this->menuOptions [] = $currentOption;
            unset ($currentOption);

        }
        elseif (!strcasecmp ($name, NAS_TPREFIX)) {

                 $this->currentTag = NAS_TPREFIX;
                 $this->returnAnchor->setPrefix (trim ($attributes [NAS_ADIR]));
        }
        elseif (!strcasecmp ($name, NAS_TPAGE)) {

            $this->currentTag = NAS_TPAGE;
            $this->returnAnchor->setPage (trim ($attributes [NAS_ANAME]));
        }
        elseif (!strcasecmp ($name, NAS_TCACHE)) {

            $this->currentTag = NAS_TCACHE;
            $this->returnAnchor->setCache (trim ($attributes [NAS_ATIME]));
        }
        elseif (!strcasecmp ($name, NAS_TMETA)) {

            $this->currentTag = NAS_TMETA;
            $currentMeta = new PAFMetadata ();

            $currentMeta->setName (trim ($attributes [NAS_ANAME]));
            $currentMeta->setEquiv (trim ($attributes [NAS_AEQUIV]));
            $currentMeta->setContent (trim ($attributes [NAS_ACONTENT]));
            $currentMeta->setScheme (trim ($attributes [NAS_ASCHEME]));

            $this->returnAnchor->addMeta ($currentMeta);
            unset ($currentMeta);
        }
        elseif (!strcasecmp ($name, NAS_TSTYLE)) {

            $this->currentTag = NAS_TSTYLE;
            $this->returnAnchor->addStyle (trim ($attributes [NAS_ACSS]));
        }
        elseif (!strcasecmp ($name, NAS_TSCRIPT)) {

            $this->currentTag = NAS_TSCRIPT;
            $this->returnAnchor->addScript (trim ($attributes [NAS_ANAME]));
        }
        elseif (!strcasecmp ($name, NAS_TDOCUMENT)) {

            $this->currentTag = NAS_TDOCUMENT;
            $this->returnAnchor->setDocumentTitle (trim ($attributes [NAS_ATITLE]));
        }
        elseif (!strcasecmp ($name, NAS_TTEMPLATE))  {

            $this->currentTag = NAS_TTEMPLATE;
            $this->returnAnchor->setTemplate (trim ($attributes [NAS_ATPL]));
        }

    }

    /**
      * Método que controla cuando se encuentra el final de un elemento.
      *
      * @access public
      * @param string $name Nombre del Elemento.
      *
      */
    function endElement ($parser, $name) {

        if (!strcasecmp ($name, NAS_TMENU)) {

             $this->returnAnchor->addMenuData ($this->menuDesc, $this->menuOptions);
             $this->menuOptions = array ();
        }
    }

    function characters ($parser, $data) {

        $data = trim ($data);
    }
}
?>