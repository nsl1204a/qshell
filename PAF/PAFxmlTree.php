<?

/**
* An object that can parse xml docs into a treelike structure
*
* This class parses XML into a treelike structure. It should
* probably be removed and replaced with and the xmllib parser in PHP. As
* of its original writing the xmllib parser in php was not finished
*
* @version $Revision: 1.8 $
*/

if (!defined(XML_ELEMENT_NODE))
    define ("XML_ELEMENT_NODE", 1);
if (!defined(XML_TEXT_NODE))
    define ("XML_TEXT_NODE", 3);
if (!defined(XML_CDATA_SECTION_NODE))
    define ("XML_CDATA_SECTION_NODE", 4);
if (!defined(XML_DOCUMENT_NODE))
    define ("XML_DOCUMENT_NODE", 9);


/*
 * Clase para albergar los nodos inclusive los textuales
 */
class PAFDomNode
{
    var $name;
    var $type;

    function PAFDomNode($name, $type, $attributes, $content=NULL)
    {
        $this->name = $name;
        $this->type = $type;
        if ($attributes)
           $this->attributes = $attributes;
        if (!is_null($content) && $content != "")
           $this->content = utf8_encode($content);
    }

    function addContent($content)
    {
    	$this->content .= utf8_encode($content);

    }

    function addChild(&$node)
    {
        if (!$this->children)
           $this->children=array();
        $this->children[] = &$node;
    }

    function & lastTextChild()
    {
        if ($this->children)
	{
		$last = & $this->children[count($this->children) -1 ];
	        if ($last->name == "text" && $last->type == XML_TEXT_NODE) 
		   return $last;
	}
	return NULL;
    }
}

/*
 * Clase para albergar los atributos
 */
class PAFDomAttribute
{
    var $name;
        
    function PAFDomAttribute($name, $value)
    {
        $this->name = $name;
        if (!is_null($value) && $value!="")
            $this->children = array( new PAFDomNode("text", XML_TEXT_NODE, NULL, $value));
    }
}

/*
 * Clase para albergar el documento global (clase principal)
 */
class PAFDomDocument
{
    var $version = "1.0";
    var $encoding = "ISO-8859-1";
    var $standalone = -1;
    var $type = XML_DOCUMENT_NODE;
    var $root     = NULL;
    var $currnode = NULL;

    function PAFDomDocument($xmldata)
    {
        if (ereg("<?xml .*encoding=\"(.*)\".*\?>", substr($xmldata,0, strpos($xmldata, "\n")), $data))
        {
            $this->encoding = strtoupper($data[1]);
        }
        $this->currnode = &$this;
        //$xml = xml_parser_create($this->encoding);
        $xml= xml_parser_create("");

        xml_set_object($xml, $this);

        xml_parser_set_option( $xml, XML_OPTION_CASE_FOLDING, 0 );
        
        xml_set_element_handler( $xml, "__xmltree_startElement", "__xmltree_endElement" );
        xml_set_character_data_handler($xml, "__xmltree_cdata");
 
        $ampXML = str_replace ("&#", "&amp;#", $xmldata);
        xml_parse( $xml, $ampXML, true );
        xml_parser_free($xml);
        unset ($this->currnode);
    }

    function addChild(&$node)
    {
        if (!$this->children)
            $this->children=array();
        $this->children[] = &$node;
    }

    function __xmltree_startElement( $parser, $name, $attribs )
    {

       if ($attribs)
       {
          $attribs_array = array();
          while (list($attr, $value) = each($attribs))
              $attribs_array[] = new PAFDomAttribute($attr, $value);
       }
       else $attribs_array = NULL;

       $new_node = new PAFDomNode($name, XML_ELEMENT_NODE, $attribs_array);
       $new_node->parent= &$this->currnode;

       $this->currnode->addChild(&$new_node);

       if( is_null($this->root) )
           $this->root = &$new_node;

       $this->currnode = &$new_node;
    }

    function __xmltree_endElement( $parser, $name )
    {
        $old = &$this->currnode;
        $this->currnode = &$this->currnode->parent;
        unset($old->parent);
    }

    function __xmltree_cdata($parser, $data)
    {
        //$data = trim($data);
        if( $data != "" ){
	    if ($child = & $this->currnode->lastTextChild())
	    {
	        $child->addContent($data);
	    }
	    else
	    {
                $new_node = new PAFDomNode("text", XML_TEXT_NODE, NULL, $data);
                $this->currnode->addChild(&$new_node);
	    }
	    $this->currnode->addContent($data);
        }
    }
}

/**
 *  Constructor for xmltree
 *
 *  load the xml data
 *
 *  @param string The data to load
 *  @access public
 */

function & PAFxmlTree($xmldata)
{

   if (phpversion() < "4.3")
     return xmltree($xmldata);
   else
     return new PAFDomDocument($xmldata);
}

?>
