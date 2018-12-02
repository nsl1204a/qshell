<?php

require_once "PAF/PAFObject.php";

class PAFMetadata extends PAFObject{

      /**
        * Name del meta.
        * @access private
        * @var string
        */
      var $name = "";

      /**
        * http-equiv del meta
        * @access private
        * @var string
        */
      var $equiv = "";

      /**
        * content del meta
        * @access private
        * @var string
        */
      var $content = "";

      /**
        * scheme del meta.
        * @access private
        * @var string
        */
      var $scheme = "";

      /**
        * Constructor
        * @access public
        *
        */
      function PAFMetadata ($errorClass = null) {

               $this->PAFObject ($errorClass);
      }

    /**
      * Metodo estatico pra saber el nombre de la clase.
      * @access public
      * @return string
      */
    function getClassName () {

             return "PAFMetadata";
    }

    /**
      * Metodo para determinar si una clase es de un tipo determinado.
      * @access public
      * @return boolean
      */
    function isTypeOf ($tipo) {

             return (PAFObject::isTypeOf ($tipo));
    }



      /**
        * Fija el Name del meta
        * @access public
        * @param string  $value Name del meta
        */
      function setName ($value) {

               $this->name = $value;
      }

      /**
        * Devuelve el Name del meta.
        * @access public
        * @return string
        */
      function getName () {

               return $this->name;
      }

      /**
        * Fija el http-equiv del meta
        * @access public
        * @param string $value http-equiv del meta.
        */
      function setEquiv ($value) {

               $this->equiv = $value;
      }

      /**
        * Devuelve el http-equiv del meta.
        * @access public
        * @return string
        */
      function getEquiv () {

               return $this->equiv;
      }

      /**
        * Fija el content del meta.
        * @access public
        * @param string $value Content del meta
        */
      function setContent ($value) {

               $this->content = $value;
      }

      /**
        * Devuelve el Content del meta
        * @access public
        * @return string
        */
      function getContent () {

               return $this->content;
      }

      /**
        * Establece el Scheme del meta.
        * @access public
        * @param string $value Scheme del meta
        */
      function setScheme ($value) {

               $this->scheme = $value;
      }

      /**
        * Devuelve el Scheme del meta.
        * @access public
        * @return string
        */
      function getScheme () {

               return $this->scheme;
      }

}

?>