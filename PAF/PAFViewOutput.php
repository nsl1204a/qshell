<?php
require_once("PAF/PAFOutput.php");
require_once("PAF/PAFTemplate.php");
/**
 * Clase abstracta derivada de PAFOutput, para implementar el mismo mecanismo de 
 * vistas que se utiliza en PAFCacheViewOutput.
 * Básicamente, con los métodos _getView y _getPagPos se define la relación entre
 * vistas y tpls, que deberá ser usada en getOutput. Aquí, al no haber caché no
 * tienen sentido ni getKeyName, ni generateViews ni ninguno de los otros métodos
 * que se proporcionan con PAFCacheViewOutput.
 * @author vsanguino
 */
class PAFViewOutput extends PAFOutput {
	
	/**
	 * Vista del contenido.
	 * @var string
	 */
	var $view = null;

	/**
	 * Posición del contenido
	 * @var string
	 */
	var $position = null;

	/**
	 * Posición del contexto
	 * @var string
	 */
	var $context = null;
	
	/**
	 * Constructor.
	 *
	 * @return PAFViewOutput
	 */
	function PAFViewOutput () {
		parent::PAFOutput();
	}
	
	/**
	 * Método que establecerá la posición de la vista en caso de utilizarlas.
	 *
	 * @access public
	 * @param string $position Posición de la vista
	 */
	function setPosition ($position) {

		$this->position = $position; 
	}

	/**
	 * Método que devuelve la posición de la vista.
	 *
	 * @access public
	 * @return string $position Posición de la vista.
	 */
	function getPosition () {

		return $this->position; 
	}

	/**
	 * Método que establecerá el contexto de la vista en caso de utilizarlas
	 *
	 * @access public
	 * @param string $context Contexto de la vista.
	 */
	function setContext ($context) {

		$this->context = $context;
	}

	/**
	 * Método que devuelve el contexto de la vista.
	 *
	 * @access public
	 * @return string Contexto de la vista
	 */
	function getContext () {

		return $this->context;
	}

	/**
	 * Método que establecerá la vista a utilizar.
	 *
	 * @access public
	 * @param string $view Vista a utilizar.
	 */
	function setView ($view) {

		$this->view = $view;
	}

	/**
	 * Método que devuelve la vista a utilizar.
	 *
	 * @access public
	 * @return string Vista a utilizar
	 */
	function getView () { 

		$position = $this->getPosition ();
		$context = $this->getContext ();

		if ($context && $position) {
			$pagPos = $this->_getPagPos (); 
			return $pagPos [$context][$position];
		}
		elseif ($this->view) {
			return $this->view;
		}
		else {
			return false;
		}
	}

	/**
	 * Método que devuelve el nombre de la tpl a utilizar para la vista seleccionada.
	 *
	 * @access public
	 * @return string Template a utilizar.
	 */ 
	function getTpl () {

		$vista = $this->getView ();
		$views = $this->_getViews ();
		return $views [$vista]["tpl"];
	}

	/**
	 * Método que devuelve el array con toda la información de la vista.
	 * 
	 * @access public
	 * @return array Array con la información de la vista.
	 */
	function getViewData () {

		$vista = $this->getView ();
		return $vista;
	}

	/**
	 * Método que nos devuelve el array con el par Contexto-Posición asignado a una vista.
	 * Debe redefinirse en la clase hija.
	 * 
	 * @abstract
	 * @access protected
	 * @return array de la forma {(contexto, posicion)=>vista}
	 */
	function _getPagPos () {

		echo "HAY QUE REDEFINIR EL METODO <b>" . __FUNCTION__ . "</b> DE LA CLASE <b>" . __CLASS__ . "</b> (linea " . __LINE__ . ")";
		die;
		return;
	}

	/**
	 * Método que nos devuelve el array de vistas a manejar.
	 * Debe redefinirse en la clase hija.
	 * 
	 * @abstract 
	 * @access protected
	 * @return array con las vistas disponibles.
	 */
	function _getViews () {

		echo "HAY QUE REDEFINIR EL METODO <b>" . __FUNCTION__ . "</b> DE LA CLASE <b>" . __CLASS__ . "</b> (linea " . __LINE__ . ")";
		die;
		return;
	}
}
?>