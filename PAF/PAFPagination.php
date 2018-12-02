<?php

  // *****************************************************************************
  // Lenguaje: PHP
  // Copyright 2006 Prisacom S.A.
  // *****************************************************************************

require_once "PAF/PAFObject.php";
require_once "PAF/PAFHttpEnv.php";

/**
  * Clase que muestra una paginacion
  * @access public
  * @package PAF
  * @author David García Rafols <dgarcia@prisacom.com>
  * @version $Revision: 1.21 $
  */
class PAFPagination extends PAFObject
{

    /**
     * Numero de registros por pagina
     *
     * @access private
     * @var integer
     */
    
    var $RowsPerPage;

    /**
     * Numero de registros totales
     *
     * @access private
     * @var integer;
     */

    var $RowsCount;

    /**
     * Numero de paginas totales
     *
     * @access private
     * @var integer
     */

    var $PagesCount;
    
    /**
     * Nombres de las variables que vamos a utilizar para enviar el numero de pagina, la columna y sentido de ordenacion y el numero de registros por pagina
     *
     * @access private
     * @var integer
     */
    
    var $VarPagina;
    var $VarColumna;
    var $VarOrden;
    var $VarLimite;

    /**
     * Plantilla que vamos a utilizar para mostrar la paginacion
     *
     * @access private
     * @var PAFTemplate
     */
    
    var $Template;


    /**
     * Pagina que estamos viendo
     *
     * @access private
     * @var integer
     */
    
    var $Page;

    /**
     * Primer registro de la pagina
     *
     * @access private
     * @var integer
     */
    
    var $FirstRow;

    /**
     * Ultimo registro de la pagina
     *
     * @access private
     * @var integer
     */
    
    var $LastRow;

    /**
     * Numero de registros en la pagina actual
     *
     * @access private
     * @var integer
     */
    var $NumRows;

    /**
     * Indice de la columna por la que vamos a ordenar
     *
     * @access private
     * @var integer
     */
    var $OrderColumn;
    
    /**
     * Sentido de la ordenacion
     *
     * @access private
     * @var integer
     */
    var $OrderWay;

    /**
     * Array de columnas de ordenacion
     *
     * @access private
     * @var array
     */
    var $columns;

    /**
     * Ruta del script
     *
     * @access private
     * @var string
     */
    var $Ruta = null;

    /**
     * Parametros adicionales del enlace
     *
     * @access private
     * @var integer
     */
    var $Params = null;

    /**
     * Array con los posibles registros por pagina
     *
     * @access private
     * @var integer
     */
    var $arrayPaginaciones = null;

    /**
     * Booleanos que dedicen que elementos de la ordenacion se muestran y cuales no
     *
     * @access private
     * @var boolean
     */

    var $mOrdenacion = true;
    var $mColumnas = true;
    var $mSentido = true;
    var $mRegistros = true;

    var $separator = '&amp;';
    /**
      * Constructor
      *
      * @access public
      * @param PAFRecordSet $rs RecordSet que recupera los datos del listado
      * @param array $columnas Array de campos por los que vamos a ordenar
      * @param string $prefijo Prefijo para las variables GET
      * @param string $ruta URL del script para hacer las llamadas en los enlaces
      * @param PAFTemplate $template Plantilla para la paginacion
      * @param array $params array con valores GET que añadir a los enlaces
      */
    
    function PAFPagination(&$rs,$columnas,$prefijo,$ruta,$template=NULL,$params=NULL)
    {
        $this->PAFObject();
        $this->prefijo = $prefijo;

        // nombre de las variables get
        $this->VarPagina = $prefijo."p";
        $this->VarColumna = $prefijo."c";
        $this->VarOrden = $prefijo."o";
        $this->VarLimite = $prefijo."l";

        // resto de variables
        $this->Ruta = $ruta;
        $this->columns = $columnas;
        $this->Template = $template;

        // sacamos los registros por pagina
        $this->RowsPerPage = PAFHttpEnv::GET($this->VarLimite);
        
        // calculamos las paginas
        $this->RowsCount = $rs->countAll();
        //print "rowscount (1): ".$this->RowsCount."<br>";

        // recuperamos la ordenacion de GET si existe y lo establecemos en el RS
        $columna = PAFHttpEnv::GET($this->VarColumna);
        $orden = PAFHttpEnv::GET($this->VarOrden);
        if((!is_null($columna)) && (in_array($columna,array_keys($this->columns)))){
                $this->OrderColumn = $columna;
                if($orden == 0){
                    $this->OrderWay = 0;
                }else{
                    $this->OrderWay = 1;
                }
        }else{
            $this->OrderColumn = 0;
            $this->OrderWay = 0;
        }

        // parseamos los parametros
        if(is_array($params)){
            foreach ($params as $name => $value) {
                // Compatibilidad para parametros de tipo "array"
                // Author: Matyas Rak <mrak@prisacom.com>
                if (is_array($value)) {
                    foreach($value as $v2) {
                        $query_vars[] = $name . urlencode("[]=" . $v2);
                    }
                } else {
                    $query_vars[] = $name .'='.urlencode($value);
                }
            }
            $query = implode($this->separator,$query_vars);
            $this->Params = $this->separator.$query;
        }

        // modificamos el recordset

        $rs->setOrder($this->columns[$this->OrderColumn],$this->OrderWay);

        $this->calcularPaginacion();
        
        //print $this->NumRows;    
        $rs->setLimits($this->FirstRow,$this->NumRows);

    }

     /**
      * Importa un array con los posibles registros por pagina que vamos a dar a elegir al usuario o bien establece un numero fijo de registros por pagina
      *
      * @access public
      * @param PAFRecordSet $rs RecordSet que recupera los datos del listado
      * @param mixed $valor array de registros por pagina posibles o integer con valor unico
      */
   
    function setRegistrosPorPagina(&$rs,$valor){

        if(is_array($valor)){

            $this->arrayPaginaciones = $valor;

            if(!in_array($this->RowsPerPage,$this->arrayPaginaciones)){
                $this->RowsPerPage = array_shift($valor); // por defecto mostramos el primer valor del array
            }

        }else{
            $this->RowsPerPage = $valor;
        }

        $this->calcularPaginacion();
        $rs->setLimits($this->FirstRow,$this->NumRows);
    }

     /**
      * Calcula la paginacion en base al numero de registros por pagina
      *
      * @access private
      * @return
      */
   
    function calcularPaginacion(){
        if(is_null($this->RowsPerPage) || $this->RowsPerPage == 0){
            $this->RowsPerPage = 10; // si no se especifica valor ni array se establece a 10 registros por pagina
        }

        $this->PagesCount = ceil($this->RowsCount / $this->RowsPerPage);

        // establecemos el numero de pagina a mostrar
        $page = PAFHttpEnv::GET($this->VarPagina);
        if((is_null($page)) ||($page > $this->PagesCount) || ($page < 1)){
            $this->Page = 1;
        }else{
            $this->Page = $page;
        }        

        // calculamos la primera fila y la ultima
        $this->FirstRow = 0 + (($this->Page - 1) * $this->RowsPerPage);
        $this->LastRow = $this->FirstRow + $this->RowsPerPage;
        
        if($this->LastRow > $this->RowsCount){
            $this->LastRow = $this->RowsCount;
        }
        //print "lastRow: ".$this->LastRow." FirstRow: ".$this->FirstRow." rowscount: ".$this->RowsCount."<br>";
        $this->NumRows = $this->LastRow - $this->FirstRow;
    }

     /**
      * Establece si vamos a mostrar la ordenacion o no
      *
      * @access public
      * @return
      */
   
    function mostrarOrdenacion($mostrar=true){
        $this->mOrdenacion = $mostrar;
    }

     /**
      * Establece si vamos a mostrar el selector de numero de registros
      *
      * @access public
      * @return
      */
   
    function mostrarRegistros($mostrar=true){
        $this->mRegistros = $mostrar;
    }

     /**
      * Establece si vamos a mostrar el selector de columnas
      *
      * @access public
      * @return
      */
   
    function mostrarColumnas($mostrar=true){
        $this->mColumnas = $mostrar;
    }

     /**
      * Establece si vamos a mostrar el selector de sentido de ordenacion
      *
      * @access public
      * @return
      */
   
    function mostrarSentido($mostrar=true){
        $this->mSentido = $mostrar;
    }
    
     /**
      * Devuelve el numero del primer registro que se muestra en la pagina
      *
      * @access public
      * @return string
      */
   
    function getFirstRow(){
        return $this->FirstRow;
    }

    
    /**
      * Devuelve el numero del ultimo registro que se muestra en la pagina
      *
      * @access public
      * @return string
      */

    function getLastRow(){
        return $this->LastRow;
    }

    
    /**
      * Devuelve el numero de registros que se muestran en la pagina
      *
      * @access public
      * @return string
      */

    function getNumRows(){
        return $this->NumRows;
    }
    
    /**
      * Devuelve el numero de columna por la que estamos ordenando
      *
      * @access public
      * @return integer
      */

    function getColumn(){
        return $this->OrderColumn;
    }

    
    /**
      * Devuelve el HTML correspondiente al area de paginacion
      *
      * @access public
      * @return string
      */
    
    function getPagination()
    {
        if ($this->set_toplinks_called) {
            return PEAR::raiseError("ERROR en la paginacion!: EL metodo setTopLinks() se debe llamar despues de getPaginacion");
        }
        if(is_null($this->Template)){
            
            return $this->createWithoutTemplate();
                
        }else{

            return $this->createWithTemplate();

        }
    
    } // fin getPagination

    /**
      * Devuelve campos hidden para incluir en un formulario en caso de querer mantener la paginacion
      *
      * @access public
      * @return string
      */
    
    function getHiddens()
    {

        $html = "<input type='hidden' name='".$this->VarPagina."' value='".$this->Page."'>";
        $html .= "<input type='hidden' name='".$this->VarColumna."' value='".$this->OrderColumn."'>";
        $html .= "<input type='hidden' name='".$this->VarOrden."' value='".$this->OrderWay."'>";
        $html .= "<input type='hidden' name='".$this->VarLimite."' value='".$this->RowsPerPage."'>";

        return $html;
    
    } // fin getPagination

    /**
      * Devuelve el HTML correspondiente al area de paginacion
      *
      * @access public
      * @return string
      */
    
    function crearRuta($var, $valor, $tl = false)
    {

        $valores["p"] = $this->Page;
        $valores["l"] = $this->RowsPerPage;
        $valores["c"] = $this->OrderColumn;
        $valores["o"] = $this->OrderWay;
        $valores[$var] = $valor;
        $ruta = $this->Ruta."?".$this->VarPagina."=".$valores["p"].$this->separator.$this->VarLimite."=".$valores["l"].$this->separator.$this->VarColumna."=".$valores["c"].$this->separator;
        if ($this->OrderColumn == $valor) {
			// para la columna, cambiamos la ordenacion
			$valores["o"] = $this->OrderWay;
            $ruta .= $this->VarOrden."=".$valores["o"];
        }elseif ($tl){
			// Para toplinks siempre =0
            $ruta .= $this->VarOrden."=0";
		} else {
			// En todas otras enlaces, permanece la ordenacion que ya hay
			$valores["o"] = $this->OrderWay;
            $ruta .= $this->VarOrden."=" . $valores['o']; 
        }
        $ruta .= $this->Params;

        return $ruta;
    
    } // fin getPagination

    /**
      * Devuelve el HTML del area de paginacion por defecto, sin usar plantilla
      *
      * @access private
      * @return string
      */
    
    function createWithoutTemplate(){

        $retorno = $this->RowsCount." registros encontrados";

        // si hay mas de una pagina se añaden los enlaces para navegacion
                
        if($this->PagesCount > 1){
            $retorno .= "<br>".$this->NumRows." registros | Páginas: ";
            for($i = 1; $i <= $this->PagesCount; $i++){
                
                if($this->Page == $i){
                    $retorno .= "<b>$i</b>";                
                }else{
                    $retorno .= "<a href='?".$this->VarPagina."=$i'>$i</a> ";
                }
                
            }
        }
        
        return $retorno;
        
        
    } // fin createWithoutTemplate
    
    
    /**
      * Devuelve el HTML del area de paginacion desde la plantilla seleccionada
      *
      * @access private
      * @return string
      */

    function createWithTemplate(){

        $this->Template->setVar("total",$this->RowsCount);
        //$this->Template->setVar("showed",$this->NumRows);
                
        // si hay mas de una pagina se muestra el bloque PAGINATION
            
        if($this->PagesCount > 1){

            $this->Template->setVarBlock("PAGINATION","total",$RowsCount);
            
            $links = "";
        
            // Enlaces flecha para ir al anterior y al primero
            
            if($this->Page > 1){
                $this->Template->setVarBlock("PAGE_BACK","first_url",$this->crearRuta("p",1));
                $this->Template->setVarBlock("PAGE_BACK","previous_url",$this->crearRuta("p",$this->Page - 1));
                $links .= $this->Template->parseBlock("PAGE_BACK");
            }
            
            // Calculamos los limites inferior y superior en base al numero de links que queremos mostrar
            $down_limit = $this->Page - (AMPLITUD_PAGINACION / 2);
            if($down_limit < 1){
                $up_limit = (AMPLITUD_PAGINACION / 2) + $this->Page - ($down_limit) + 1;
                $down_limit = 1;
            }else{
                $up_limit = (AMPLITUD_PAGINACION / 2) + $this->Page;
            }

            if($up_limit > $this->PagesCount){
                $down_limit = $this->Page - (AMPLITUD_PAGINACION / 2) - ($up_limit - $this->PagesCount);
                if($down_limit < 1){
                    $down_limit = 1;
                }
                $up_limit = $this->PagesCount;
            }
                
            // si no empezamos desde el principio mostramos la primera pagina
            if($down_limit > 1){
                $this->Template->setVarBlock("PAGE_LINK","page_num",1);
                $this->Template->setVarBlock("PAGE_LINK","page_url",$this->crearRuta("p",1));
                $links .= $this->Template->parseBlock("PAGE_LINK");
                if($down_limit > 2){
                    $links .= " ... ";
                }
            }
            
            // mostramos los links de las paginas anteriores
    
            for($i = $down_limit; $i < $this->Page; $i++){
                $this->Template->setVarBlock("PAGE_LINK","page_num",$i);
                $this->Template->setVarBlock("PAGE_LINK","page_url",$this->crearRuta("p",$i));
                $links .= $this->Template->parseBlock("PAGE_LINK");
            }

            // mostramos la pagina actual
            $this->Template->setVarBlock("CURRENT_PAGE","page_num",$this->Page);
            $links .= $this->Template->parseBlock("CURRENT_PAGE");

            // Mostramos los links posteriores
            
            for($i = $this->Page + 1; $i <= $up_limit; $i++){
                $this->Template->setVarBlock("PAGE_LINK","page_num",$i);
                $this->Template->setVarBlock("PAGE_LINK","page_url",$this->crearRuta("p",$i));
                $links .= $this->Template->parseBlock("PAGE_LINK");
            }
                        
            // Si no llegamos al final mostramos la ultima pagina
            if($up_limit < $this->PagesCount){
                if($up_limit < $this->PagesCount - 1){
                    $links .= " ... ";
                }
                $this->Template->setVarBlock("PAGE_LINK","page_num",$this->PagesCount);
                $this->Template->setVarBlock("PAGE_LINK","page_url",$this->crearRuta("p",$this->PagesCount));
                $links .= $this->Template->parseBlock("PAGE_LINK");
            }
                    
                    
            // Enlaces flecha para ir al siguiente y al ultimo

            if($this->Page < $this->PagesCount){    
                $this->Template->setVarBlock("PAGE_FORWARD","last_url",$this->crearRuta("p",$this->PagesCount));
                $this->Template->setVarBlock("PAGE_FORWARD","next_url",$this->crearRuta("p",$this->Page + 1));
                $links .= $this->Template->parseBlock("PAGE_FORWARD");
        
            }

            // Bloque de paginacion                
            $this->Template->setVarBlock("PAGINATION","page_links",$links);
            $this->Template->setVar("PAGINATION",$this->Template->parseBlock("PAGINATION"));
                
        }

        // Bloque de ordenacion
        if($this->mRegistros){
            $aux = "";
            if(!is_null($this->arrayPaginaciones)){
                foreach($this->arrayPaginaciones as $clave => $valor){
                    if($valor == $this->RowsPerPage){
                        $this->Template->setVarBlock("REG_SELECTED","value",$this->crearRuta("l",$valor));
                        $this->Template->setVarBlock("REG_SELECTED","text",$valor);
                        $aux .= $this->Template->parseBlock("REG_SELECTED");
                    }else{
                        $this->Template->setVarBlock("REG_UNSELECTED","value",$this->crearRuta("l",$valor));
                        $this->Template->setVarBlock("REG_UNSELECTED","text",$valor);
                        $aux .= $this->Template->parseBlock("REG_UNSELECTED");
                    }    
                }
            }else{
                $this->Template->setVarBlock("REG_UNSELECTED","value",$this->crearRuta("l",$this->RowsPerPage));
                $this->Template->setVarBlock("REG_UNSELECTED","text",$this->RowsPerPage);
                $aux .= $this->Template->parseBlock("REG_UNSELECTED");
            }
            $this->Template->setVarBlock("REG_PER_PAGE","reg_options",$aux);
            $this->Template->setVarBlock("REG_PER_PAGE","perpage_var",$this->VarLimite);
            $this->Template->setVarBlock("ORDENATION","REG_PER_PAGE",$this->Template->parseBlock("REG_PER_PAGE"));
        }

        if($this->mColumnas){
            $this->Template->setVarBlock("ORDER_COLUMN","ruta",$this->crearRuta("c","' + this.value + '"));
            $this->Template->setVarBlock("ORDER_COLUMN","column_var",$this->VarColumna);
            $this->Template->setVarBlock("ORDER_COLUMN","column_".$this->OrderColumn," selected");
            $this->Template->setVarBlock("ORDENATION","ORDER_COLUMN",$this->Template->parseBlock("ORDER_COLUMN"));
        }

        if($this->mSentido){
            $this->Template->setVarBlock("ORDER_TYPE","ruta",$this->crearRuta("o","' + this.value + '"));
            $this->Template->setVarBlock("ORDER_TYPE","order_var",$this->VarOrden);
            $this->Template->setVarBlock("ORDER_TYPE","order_".$this->OrderWay," selected");
            $this->Template->setVarBlock("ORDENATION","ORDER_TYPE",$this->Template->parseBlock("ORDER_TYPE"));
        }

        if($this->mOrdenacion){
            $this->Template->setVar("ORDENATION",$this->Template->parseBlock("ORDENATION"));
        }

        return $this->Template->parse();

    } // fin createWithTemplate
    
    /**
    * metodo setTopLinks
    *
    * Va a reemplezar todas ocurencias de <!-- {sort_URL_n} --> y <!-- {sorter_n}--> con rutas de ordenacion y su flecha respectivamente
    * @autor Matyas Rak <mrak@prisacom.com>
    * @access public
    * @param PAFPagination &$pag la paginacion ya creada, iniciada y apliada a plantilla
    * @param PAFTemplate &$tpl la plantilla
    * @param String $bloque el nombre del bloque donde buscar <!-- {sort_URL} -->   
    *
    **/

    function setTopLinks(&$pag, &$tpl, $bloque=""){
        $this->set_toplinks_called = true;
        if (!$pag) {
            $pag = $this;
        }
        if(!$tpl) {
            $tpl = $pag->Template;
        }

	$orderwayoriginal = $pag->OrderWay;

        for($i=0;$i<count($pag->columns);$i++)
        {

            $flecha = "";
            if($pag->OrderColumn == $i) { // ya era sorteado de esta columna
                if($pag->OrderWay) {// ORDER DESC
                    $pag->OrderWay = 0;
                    $flecha = $tpl->parseBlock("BLOQUE_FLECHA_ARRIBA");
                } else {// ORDER ASC
                    $pag->OrderWay = 1;
                    $flecha = $tpl->parseBlock("BLOQUE_FLECHA_ABAJO");
                }
            } else {
                $flecha = ""; // no ordenacion, no flecha
            }
            if($bloque) {
                $tpl->setVarBlock($bloque, "sorter_".$i, $flecha); // set flecha
                $tpl->setVarBlock($bloque, "sort_URL_".$i, $pag->crearRuta("c", $i, 1)); // set ruta
            } else {
                $tpl->setVar("sorter_".$i, $flecha); // set flecha
                $tpl->setVar("sort_URL_".$i, $pag->crearRuta("c", $i, 1)); // set ruta
            }

            PAFApplication::debug("creando ruta para menu:". $pag->crearRuta("c", $i, 1), __CLASS__, __FUNCTION__);
        }

	 $pag->OrderWay = $orderwayoriginal;
    }


    /**
    * metodo setOrdenacion
    *
    * establece ordenacion, que no se influye por valores de GET o post.
    * Entonces puede servir como ordenacion inicial.
    *
    * @autor Matyas Rak <mrak@prisacom.com>
    * @access public
    * @param int $columna numero de la columna de ordenacion. Tiene que existir en $this->columns
    * @param int $orden el orden de ordenacion.  0 = ASC, todo otro = DESC
    *
    **/

    function setOrdenacion(&$rs, $columna, $orden)
    {

       if ((!is_null($columna)) && (in_array($columna,array_keys($this->columns))) && is_null(PAFHttpEnv::GET($this->VarOrden))) {
            $this->OrderColumn = $columna;
            if($orden == 0){
                $this->OrderWay = 0;
            } else {
                $this->OrderWay = 1;
            }
        }
        $rs->setOrder($this->columns[$this->OrderColumn],$this->OrderWay ? 0 : 1);
    }

} // fin clase PAFPagination

?>
