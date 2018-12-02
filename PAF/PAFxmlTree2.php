<?

/**
* An object that can parse xml docs into a treelike structure
*
* This class parses XML into a treelike structure. It should
* probably be removed and replaced with and the xmllib parser in PHP. As
* of its original writing the xmllib parser in php was not finished
*
* @version $Revision: 1.3 $
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

    function compare($node)
    {
    	if ($this->type != $node->type)
	{
		echo "Tipos distintos ".$this->type."/".$node->type." en nodo ".$this->name."\n";
		return false;
	}
    	if ($this->name != $node->name)
	{
		echo "Nombres distintos ".$this->name."/".$node->name."\n";
		return false;
	}
    	if (trim($this->content) != trim($node->content))
	{
		echo "Contenidos distintos \n".$this->content."\n/\n".$node->content."\n";
		return false;
	}


	if (isset($this->attributes) && isset($node->attributes))
	{
		if (count($this->attributes) != count($node->attributes))
		{
			echo "Numero de Atributos distintos ".count($this->attributes)."/".count($node->attributes)." en nodo ".$this->name."\n";
			return false;
		}

		$ret=true;
		for ($i=0; $ret && $i< count($this->attributes); $i++)
		    $ret = $this->attributes[$i]->compare($node->attributes[$i]);
	        if (!$ret) { echo "Padre: ".$this->name."\n"; return false; }
	}
	else if (isset($this->attributes) != isset($node->attributes))
	{
		echo "Uno tiene atributos y el otro no ".$this->name."/".$node->name."\n";
		return false;
	}
		

	if (isset($this->children) && isset($node->children))
	{
		if (count($this->children) != count($node->children))
		{
			echo "Numero de Hijos distintos ".count($this->children)."/".count($node->children)." en nodo ".$this->name."\n";
		for ($i=0; $i< count($this->children); $i++)
		    echo $this->children[$i]->name."\n";

		echo "\n";
		for ($i=0; $i< count($node->children); $i++)
		    echo $node->children[$i]->name."\n";
			return false;
		}

		$ret=true;
		for ($i=0; $ret && $i< count($this->children); $i++)
		    $ret = $this->children[$i]->compare($node->children[$i]);
	        if (!$ret) { echo "Padre: ".$this->name."\n"; return false; }
	}
	else if (isset($this->children) != isset($node->children))
	{
		echo "Uno tiene Hijos y el otro no ".$this->name."/".$node->name."\n";
		var_dump($this->children);
		var_dump($node->children);
		return false;
	}

	return true;
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

    function compare($node)
    {
    	if ($this->name != $node->name)
	{
		echo "Attr: Nombres distintos ".$this->name."/".$node->name."\n";
		return false;
	}

	if (isset($this->children) && isset($node->children))
	{
		if (count($this->children) != count($node->children))
		{
			echo "Attr: Numero de Hijos distintos ".count($this->children)."/".count($node->children)." en nodo ".$this->name."\n";
		for ($i=0; $i< count($this->children); $i++)
		    echo $this->children[$i]->name."\n";

		echo "\n";
		for ($i=0; $i< count($node->children); $i++)
		    echo $node->children[$i]->name."\n";
			return false;
		}

		$ret=true;
		for ($i=0; $ret && $i< count($this->children); $i++)
		    $this->children[$i]->compare($node->children[$i]);
	        if (!$ret) { echo "Padre: ".$this->name."\n"; return false; }
	}
	else if (isset($this->children) != isset($node->children))
	{
		echo "Attr: Uno tiene Hijos y el otro no ".$this->name."/".$node->name."\n";
		var_dump($this->children);
		var_dump($node->children);

		return false;
	}

	return true;
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
            $this->encoding = strtoupper($data[1]);

        $this->currnode = &$this;
        $xml = xml_parser_create($this->encoding);
        xml_set_object($xml, $this);

        xml_parser_set_option( $xml, XML_OPTION_CASE_FOLDING, 0 );
        
        xml_set_element_handler( $xml, "__xmltree_startElement", "__xmltree_endElement" );
        xml_set_character_data_handler($xml, "__xmltree_cdata");
        
        xml_parse( $xml, $xmldata, true );
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

     $tree1= xmltree($xmldata);
     $tree2= new PAFDomDocument($xmldata);

   if ($tree2->root->compare($tree1->root))
      echo "SON IDENTICOS!!!";
   die;
   return $tree2;
}

?>
