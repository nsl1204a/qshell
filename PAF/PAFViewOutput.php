<?php
require_once("PAF/PAFOutput.php");
require_once("PAF/PAFTemplate.php");
/**
 * Clase abstracta derivada de PAFOutput, para implementar el mismo mecanismo de 
 * vistas que se utiliza en PAFCacheViewOutput.
 * B�sicamente, con los m�todos _getView y _getPagPos se define la relaci�n entre
 * vistas y tpls, que deber� ser usada en getOutput. Aqu�, al no haber cach� no
 * tienen sentido ni getKeyName, ni generateViews ni ninguno de los otros m�todos
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
	 * Posici�n del contenido
	 * @var string
	 */
	var $position = null;

	/**
	 * Posici�n del contexto
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
	 * M�todo que establecer� la posici�n de la vista en caso de utilizarlas.
	 *
	 * @access public
	 * @param string $position Posici�n de la vista
	 */
	function setPosition ($position) {

		$this->position = $position; 
	}

	/**
	 * M�todo que devuelve la posici�n de la vista.
	 *
	 * @access public
	 * @return string $position Posici�n de la vista.
	 */
	function getPosition () {

		return $this->position; 
	}

	/**
	 * M�todo que establecer� el contexto de la vista en caso de utilizarlas
	 *
	 * @access public
	 * @param string $context Contexto de la vista.
	 */
	function setContext ($context) {

		$this->context = $context;
	}

	/**
	 * M�todo que devuelve el contexto de la vista.
	 *
	 * @access public
	 * @return string Contexto de la vista
	 */
	function getContext () {

		return $this->context;
	}

	/**
	 * M�todo que establecer� la vista a utilizar.
	 *
	 * @access public
	 * @param string $view Vista a utilizar.
	 */
	function setView ($view) {

		$this->view = $view;
	}

	/**
	 * M�todo que devuelve la vista a utilizar.
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
	 * M�todo que devuelve el nombre de la tpl a utilizar para la vista seleccionada.
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
	 * M�todo que devuelve el array con toda la informaci�n de la vista.
	 * 
	 * @access public
	 * @return array Array con la informaci�n de la vista.
	 */
	function getViewData () {

		$vista = $this->getView ();
		return $vista;
	}

	/**
	 * M�todo que nos devuelve el array con el par Contexto-Posici�n asignado a una vista.
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
	 * M�todo que nos devuelve el array de vistas a manejar.
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