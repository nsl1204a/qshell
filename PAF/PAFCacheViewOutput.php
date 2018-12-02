<?

require_once ("PAF/PAFCacheOutput.php");


class PAFCacheViewOutput extends PAFCacheOutput {

	/**
	 * Vista del contenido
	 */
	var $view = null;

	/**
	 * Posicion del contenido
	 */
	var $position = null;

	/**
	 * Posicion del contexto
	 */
	var $context = null;

	function PAFCacheViewOutput () {
		parent::PAFCacheOutput ();
	}

	/**
	 * Genera todos las vistas que contempla la clase hija.
	 *
	 * @access public
	 */
	function generateViews () {

		$vistas = $this->_getViews ();
		foreach( $vistas as $vista => $Vdata ){
			$this->setView($vista);
			$this->getOutput();
		}
	}

	/**
	 * Devuelve el nombre de fichero cache. Utiliza el nombre declarado en la hija (_getStaticName) convinandolo con la vista necesaria.
	 */
	function _getKeyName () {
		
		$vista = $this->getView ();
		$cacheName = $this->_getStaticName ();
		
		if ($vista) {

			return $cacheName . "_vista_" . $vista;
		}
	}

	/**
	 * Método que establecera la posicion de la vista en caso de utilizarlas
	 *
	 * @acces public
	 * @param string $position Posicion de la vista
	 */
	function setPosition ($position) {

		$this->position = $position; 
	}

	/**
	 * Método que devuelve la posicion de la vista
	 *
	 * @acces public
	 * @return string $position Posicion de la vista
	 */
	function getPosition () {

		return $this->position; 
	}

	/**
	 * Método que establecera el contexto de la vista en caso de utilizarlas
	 *
	 * @acces public
	 * @param string $context Contexto de la vista
	 */
	function setContext ($context) {

		$this->context = $context;
	}

	/**
	 * Método que devuelve el contexto de la vista
	 *
	 * @acces public
	 * @return string Contexto de la vista
	 */
	function getContext () {

		return $this->context;
	}

	/**
	 * Método que establecera la vista a utilizar.
	 *
	 * @acces public
	 * @param string $view Vista a utilizar
	 */
	function setView ($view) {

		$this->view = $view;
	}

	/**
	 * Método que devuelve la vista a utilizar
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
		else
			return false;
	}

	/**
	 * Método que devuelve la tpl a utilizar para la vista seleccionada
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
	 * Método que devuelve el array con toda la informacion de la vista
	 * 
	 * @acces public
	 * @return array Array con la informacion de la vista.
	 */
	function getViewData () {

		$vista = $this->getView ();
		return $vista;
	}

	/**
	 * Método que nos devuelve el array con el par Contexto-Posicion asignado a una vista.
	 * Debe redefinirse en la clase hija.
	 */
	function _getPagPos () {

		echo "HAY QUE REDEFINIR EL METODO <b>" . __FUNCTION__ . "</b> DE LA CLASE <b>" . __CLASS__ . "</b> (linea " . __LINE__ . ")";
		die;
		return;
	}

	/**
	 * Método que nos devuelve el array de vistas a manejar.
	 * Debe redefinirse en la clase hija.
	 */
	function _getViews () {

		echo "HAY QUE REDEFINIR EL METODO <b>" . __FUNCTION__ . "</b> DE LA CLASE <b>" . __CLASS__ . "</b> (linea " . __LINE__ . ")";
		die;
		return;
	}


	/**
	 * Método que nos devuelve una cadena que identifica de forma unica la clase, para utilizarla como nombre a la hora de cachearla
	 * Debe redefinirse en la clase hija.
	 */
	function _getStaticName () {

		echo "HAY QUE REDEFINIR EL METODO <b>" . __FUNCTION__ . "</b> DE LA CLASE <b>" . __CLASS__ . "</b> (linea " . __LINE__ . ")";
		die;
		return;
	}



}


?>
