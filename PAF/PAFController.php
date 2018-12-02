<?php

// *****************************************************************************
// Lenguaje: PHP
// Copyright 2005 Prisacom S.A.
//
// $Id: PAFController.php,v 1.6 2008/11/24 13:13:05 drodriguez Exp $
//
// *****************************************************************************

require_once 'HTML/Page2.php';
require_once 'PAF/PAFHeader.php';
require_once 'PAF/PAFHttpEnv.php';

define('DEFAULT_CONTROLLER_VIEW', 'default');

/**
 * Clase para implementar "controllers" bajo el patrón MVC:
 * Esta clase hereda de HTML_Page2 de PEAR para tener toda la funcionalidad
 * de manejo de html en una página.
 *
 * @see http://phppatterns.com/index.php/article/articleview/11/1/8/
 * @see http://phppatterns.com/index.php/article/articleview/81/1/1/
 * @see http://pear.php.net/HTML_Page2
 *
 * @author    Virglio Sanz <vsanz@prisacom.com>
 * @copyright PRISACOM S.A
 * @package PAF
 * @version $Rev$
 */
class PAFController extends HTML_Page2
{
    /** Contiene los "contenidos" */
    var $_content;

    /** Contiene las cabeceras */
    var $_hdrs;
    
		/** Array que contiene los atributos que se le añadiran al body**/
		var $bodyAttributes = array();

    /** Devuelve el objeto PAFHeader de la página */
    function &getHeaders() {return $this->_hdrs;}

    /**
     * Constructor.
     * Inicializa las headers para darles de tiempo de cache lo que este en
     * la configurcion por defecto.
     */
    function PAFController()
    {
        $attributes['prolog'] = false;
        parent::HTML_Page2($attributes);
        $this->_content = array();
        $this->_hdrs = new PAFHeader();

        PAFApplication::trace("Contructor del controller", "Includes y demás");
        // si no tenemos debug enviamos cache cero
        if (PAFApplication::isDebugging()) {
            $this->_hdrs->setCacheTime(0);
            $this->_hdrs->sendHeaders();
        }
    }
    
		function setBodyAttribute($key, $value) {
			$this->bodyAttributes[$key] = $value;
		}
		
		function removeBodyAttribute($key) {
			unset($this->bodyAttributes[$key]);
		}

		function setBodyClass($value) {
			if(empty($this->bodyAttributes['class'])) {
				$this->setBodyAttribute('class', $value);
			} else {
				if(preg_match('/\b'. $value . '\b/', $this->bodyAttributes['class']) === 0) {
					$this->setBodyAttribute('class', $this->bodyAttributes['class'] . ' ' . $value);
				}
			}
		}
		
		function removeBodyClass($value) {
			if ($this->bodyAttributes['class'] === $value) {
				unset($this->bodyAttributes['class']);
			} else {
				if(preg_match('/\s'. $value . '\b/', $this->bodyAttributes['class']) === 1) {
					$this->bodyAttributes['class'] = preg_replace('/\s'. $value . '\b/', '', $this->bodyAttributes['class']);
				} else {
					$this->bodyAttributes['class'] = preg_replace('/\b'. $value . '\s/', '', $this->bodyAttributes['class']);				
				}
			}
		}

    /// ----- Las caches
    /** Tiempo de cache en segundos. */
    var $_cache;
    function setCache($tiempo) {$this->_cache = $tiempo; }
    function getCache() {return $this->_cache;}

    /// ----- Para controlar el contenido

    /**
     * añade un output o txt normal a la lista de outputs
     */
    function addContent($ou, $name='html')
    {
        $this->_content[$name] = $ou;
        return true;
    }

    /**
     * Hace un include del contenido y lo añade a la lista de ouputs
     */
    function addContentInclude($file, $name)
    {
        $app =& PAFApplication::theApp();
        $app->debug("haciendo include de <b>$file</b> en <b>$name</b>.", __FUNCTION__, __CLASS__);
        if (is_readable($file)) {
            ob_start();
            include($file);
            $contenido = ob_get_contents();
            ob_end_clean();
            //ob_end_flush();  Invalida las cabeceras de cache
        } else {
            return PAFApplication::raiseError("No puedo leer $file", __FILE__, __LINE__);
        }
        return $this->addContent($contenido, $name);
    }

    /**
     * Lee el contenido de un fichero y lo añade a la lista de ouputs
     */
    function addContentFromFile($file, $name)
    {
        $app =& PAFApplication::theApp();
        $app->debug("haciendo include de <b>$file</b> en <b>$name</b>.", __FUNCTION__, __CLASS__);
        if (is_readable($file)) {
	    $contenido = file_get_contents($file);
        } else {
            return PAFApplication::raiseError("No puedo leer $file", __FILE__, __LINE__);
        }
        return $this->addContent($contenido, $name);
    }

    // --------- De ejecución
   /**
    * Método que hace la ejecución... dependiendo del valor view que se
    * pasa como parámetro.
    *
    *    @access public
    *    @returns string
    */
    function run()
    {
        $app =& PAFApplication::theApp();
        $isDebugging = $app->isDebugging();
        $view = $this->_getTheView();

        $app->trace("Controller.run", "Entrando");
        
        // Método a ejecutar ántes del output de la clase
        $last_modified = $this->start($view);
	if (!$last_modified) $last_modified=time();
        
        $app->trace("Controller.run", "En método start");
       
        $msg = sprintf("Fecha del contenido: <b>%s</b> fecha en cache: <b>%s</b>", 
                       strftime('%c', $last_modified), 
                       strftime('%c', PAFHttpEnv::IfModifiedSince()));
        $app->debug($msg, __FUNCTION__, __CLASS__);

        if (!$isDebugging) {
            // Este metodo comprueba si la fecha con la cacbecera If-Modified-since
            // si no hay cambios envia un 304 y mata el script.
            // Si estamos en modo debug no hace esto.
            #$this->_hdrs->setDate($last_modified, 1800); // Revalidamos cada media hora
        }

        // Ejecutamos el método real, en caso de error intenta devolver un 304
        $app->debug("Vista por parámetro <b>$view</b>", __FUNCTION__, __CLASS__);

        $class = get_class($this);
        $method = "do$view";
        //echo $method;
		$app->debug(sprintf('Llamando a %s->%s', $class, $method), __FUNCTION__, __CLASS__);
        if (!method_exists($this, $method)) {
            $app->debug('<b>'.$class.'->'.$method.'</b> no existe', __FUNCTION__, __CLASS__);
            $method = 'do'.DEFAULT_CONTROLLER_VIEW;
            $app->debug("Probando con <b>$method</b>", __FUNCTION__, __CLASS__);
            if (!method_exists($this, $method)) {
                return PAFApplication::raiseError(__FILE__, __LINE__, $class.'->'.$method.' no existe');
            }
        }

        $ret = call_user_func(array(&$this, $method));
        $app->trace("Controller.run", "En método $method");
        // Si nos devuelve un error intentar jugar con la cache.
        if (PEAR::isError($ret)) {
            if (!$isDebugging) {
                $app->log($app);
                if (PAFHttpEnv::IfModifiedSince() != -1) {
                    $this->_hdrs->sendNotModified();
                } else {
                    $this->_hdrs->sendServiceUnavailable();
                }
            } else {
                $app->debug($ret, __FUNCTION__, __CLASS__);
            }
        }

        // Método a ejecutar después del output de la clase
        $app->debug('Entrando en método <b>end</b>', __FUNCTION__, __CLASS__);
        $ret = $this->end();
        if (PEAR::isError($ret)) {$app->debug($ret, __FUNCTION__, __CLASS__);}

        return true;
    }


    /**
     * Envia los contenidos y las cabeceras de la pagina
     */
    function send()
    {
        $app =& PAFApplication::theApp();

        // Si estamos en modo debug mandamos no-cache
        $isDebugging = $app->isDebugging();

        if ($isDebugging) {
            $this->_hdrs->setCacheTime(0);
        } else {
            $this->_hdrs->setCachetime($this->getCache());
        }

        $app->trace("Controller.send", "al entrar");
        $html = $this->getOutput();
        $app->trace("Controller.send", "Obteniendo html");
        if (PEAR::isError($html)){
            if (!$isDebugging) {
                $app->log($html);
                // si está cacheado se envía un 304
                if (PAFHttpEnv::IfModifiedSince() != -1) {
                    $this->_hdrs->sendNotModified();
                } else {
                    // si no está cacheado damos un error.
                    $this->_hdrs->sendServiceUnavailable();
                }
            } else {
                $app->debug($html, __FUNCTION__);
            }
        } else {
            if (!$isDebugging) {
                $this->_hdrs->setLength(strlen($html));
                $this->_hdrs->sendHeaders();
            }
            echo $html;
        }
        $app->trace("Controller.send", "Enviado");
        
        // Pintar el timer?
        if ($isDebugging) {
            $this->_printStatistics();
        }

    }

    /**
     * Coje todos los ouputs y monta la pagina completa. Si algun ouput
     * genera un error o es un error devuelve el error y para la generacion
     * del contenido.
     */
    function getOutput()
    {
        $app =& PAFApplication::theApp();
        $txt = "";
        PAFApplication::trace("getOutput", "entrando");
        foreach ($this->_content as $name => $content) {
            if (!is_object($content)) {
                $txt .= $content;
            } else if (method_exists($content, 'getOutput')) {
                $ret =& $content->getOutput();
                if (PEAR::isError($ret)) {
                    $app->debug("Error en modulo: $name", __FUNCTION__, __CLASS__);
                    // IDEA: Sería más positivo tener un html por defecto para un módulo en caso de que este diera un error en lugar de devolver el error sin más
                    return $ret;
                } else {
                    $txt .= $ret;
                }
	    } else if (method_exists($content, 'toHtml')) {
                $ret =& $content->toHtml();
                if (PEAR::isError($ret)) {
                    $app->debug("Error en modulo: $name", __FUNCTION__, __CLASS__);
                    // IDEA: Sería más positivo tener un html por defecto para un módulo en caso de que este diera un error en lugar de devolver el error sin más
                    return $ret;
                } else {
                    $txt .= $ret;
                }
            } else if (PEAR::isError($content)) {
                $app->debug("Error en modulo: <b>$name</b>", __FUNCTION__, __CLASS__);
                return $content;
            } else {
                return PAFApplication::raiseError("No se que tipo de objeto contiene el modulo: $name", __FILE__, __LINE__);
            }
            PAFApplication::trace("getOutput", $name);
        }

        //Añadimos al body los atributos que han ido pasando
        if(!empty($this->bodyAttributes)) {
        	$this->setBodyAttributes($this->bodyAttributes);
        }

        // Todo los Contenidos son correctos.
        $this->setBody($txt);

        return $this->toHtml();
    }


// ------------------------------------------ PROTECTED ---------------------
/// Métodos a implementar en las clases hijas.
    /**
     * Inicia la ejecución del script.
     * Este metodo sera llamado durante el run(), justo antes de llamar al
     * do[LoqueSea] de la clase hija.
     *
     * @return integer Devuelve el timestamp de la última modificación del
     * contenido a mostrar, si es que es capaz de saberlo sino devolver time().
     */
     function start($view)
     {
         PAFApplication::debug('Haciendo start en <b>'.get_class($this).'</b> con la vista <b>'.$view.'</b>', __FUNCTION__, __CLASS__);
         return time();
     }


    /**
     * Metodo a implementar en las clases hijas.
     * Este metodo sera llamado durante el run(), justo despues de llamar al
     * do[LoqueSea] de la clase hija.
     */
    function end() {}


// ------------------------------------------ PRIVADOS ---------------------
    /**
     * Obtiene la vista que nos pasan por parámetro o la vista por defecto
     */
    function _getTheView()
    {
        return isset($_REQUEST['view']) ? $_REQUEST['view']: DEFAULT_CONTROLLER_VIEW;
    }

    /**
     * Pinta la estadísticas de lo que se hizo con el timer
     */
    function _printStatistics()
    {
        echo "<b>TIEMPOS</b><br>\n";
        PAFApplication::printTimer();
        echo "<p>";
        echo "<b>RUNTIME</b><br>\n";
        echo "<b>Listado de includes</b><br>\n";
        $ss = get_included_files();
        foreach ($ss as $s) {
            echo str_repeat("-", 4).$s."<br>\n";
        }
        echo "<hr><p>";
/*
        echo 'Uso de Memoria: <b>'.memory_get_usage();
        echo "<br><b>Lista de clases</b><br>\n";
        $ss = get_declared_classes();
        foreach ($ss as $s) {
            echo str_repeat("-", 4).$s."<br>\n";
        }
        echo "<br><b>Lista de constantes</b><br>\n";
        $ss = get_defined_constants();
        foreach ($ss as $s) {
            echo str_repeat("-", 4).$s."<br>\n";
        }

        echo "<br><b>Recursos</b><br>\n";
        $ss = getrusage();
        foreach ($ss as $k => $s) {
            echo str_repeat("-", 4).$k.' = '.$s."<br>\n";
        }
*/


    }
}

?>
