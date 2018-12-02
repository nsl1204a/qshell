<?php

require_once "PAF/PAFObject.php";

class PAFOptionAnchor extends PAFObject {


      /**
        * Identificador de opcion
        * @var string
        */
      var $item = "";

      /**
        * Indica si esta plegado o desplegado
        * @var boolean
        */
      var $folded = "";

      /**
        * Indica si la opcion esta activa.
        * @var boolean
        */
      var $active = "";

      /**
        * Indica si la opcion esta visible.
        * @var boolean
        */
      var $visible = "";

      /**
        * Indica si la opcion esta seleccionada.
        * @var boolean
        */
      var $selected = "";

      /**
        * Constructor
        *
        */
      function PAFOptionAnchor ($errorClass = null) {

               $this->PAFObject ($errorClass);
      }

    /**
      * Metodo estatico pra saber el nombre de la clase.
      * @return string
      */
    function getClassName () {

             return "PAFOptionAnchor";
    }

    /**
      * Metodo para determinar si una clase es de un tipo determinado.
      * @return boolean
      */
    function isTypeOf ($tipo) {

             return (PAFObject::isTypeOf ($tipo));
    }


      /**
        * Establece el id de la opcion
        * @param string $value
        */
      function setItem ($value) {

               $this->item = $value;
      }

      /**
        * Devuelve el id de la opcion
        * @return string
        */
      function getItem () {

               return $this->item;
      }

      /**
        * Establece si esta deplegada la opcion
        * @param boolean $value
        */
      function setFolded ($value) {

               $this->folded = $value;
      }

      /**
        * Devuelve si esta desplegada la opcion
        * @return boolean
        */
      function getFolded () {

               return $this->folded;
      }

      /**
        * Establece si esta activa la opcion
        * @param boolean $value
        */
      function setActive ($value) {

               $this->active = $value;
      }

      /**
        * Devuelve si esta activa la opcion
        * @return boolean
        */
      function getActive () {

               return $this->active;
      }

      /**
        * Establece si es visible la opcion
        * @param boolean $value
        */
      function setVisible ($value) {

               $this->visible = $value;
      }

      /**
        * Devuelve si es visible la opcion
        * @return boolean
        */
      function getVisible () {

               return $this->visible;
      }

      /**
        * Establece si esta seleccionada la opcion
        * @param boolean $value
        */
      function setSelected ($value) {

               $this->selected = $value;
      }

      /**
        * Devuelve si esta seleccionada la opcion
        * @return boolean
        */
      function getSelected () {

               return $this->selected;
      }
}

?>