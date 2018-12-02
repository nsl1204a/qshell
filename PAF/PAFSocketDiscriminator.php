<?php
// ****************************************************************************
// Lenguaje: PHP
// Copyright 2002 Prisacom S.A.
// ---------
// ChangeLog
// ---------
// $Log: PAFSocketDiscriminator.php,v $
// Revision 1.4  2003/03/17 17:02:20  scruz
// Arreglados errores de sintaxis en el método getBalancedSocket()
//
// Revision 1.3  2003/03/17 16:54:01  scruz
// Añadido control de versiones.
//
// Revision 1.1  2003/01/21 16:55:42  vsanz
// Clases para el manejo de sockets y conexiónes HTTP, primera subida.
//
//
// ****************************************************************************

require_once 'PAF/PAFObject.php';
require_once 'PAF/PAFSocket.php';

define (PSD_BALANCED_SOCKET, 1);
define (PSD_PREFERRED_SOCKET, 2);

/**
 * Clase que devuelve un socket y marca como malos los sockets caidos.
 *
 * @author Virgilo Sanz <vsanz@prisacom.com>
 * @version $Revision: 1.4 $
 * @copyright Prisacom S.A.
 * @abstract Clase genérica para el uso de sockets
 */
class PAFSocketDiscriminator extends PAFObject {
    // Attributes
   /**
    *    Contiene la lista de host.
    *    @access private
    */
    var $hosts = array();

   /**
    *    Contiene la lista de puertos para cada host.
    *    @access private
    */
    var $ports = array();

   /**
    *    Contiene los pesos (importancia, preferencia) para cada socket.
    *    @access private
    */
    var $pesos = array();

   /**
    *    Directorio donde marcaremos los hosts que estén caidos.
    *    @access private
    */
    var $dirMarked = null;
    
    /**
     *   Tipo de seleción que haremos para la lista de hosts.
     *   @acces private
     */
    var $typeOfSelection;

    /**
     *   Tiempo máximo que tendré un host marcado como caido.
     *   @acces private
     */
    var $markedTimeout = null;

    /**
     * Tiempo máximo para arbir un socket.
     * @access private
     */
    var $socketConnectionTimeout;
    
    /**
     * Construye el objeto.
     *    
     * @access public 
     * @param string $dirMarked Directorio donde marcaremos los host caidos.
     * @param integer $markedTimeout Tiempo máximo que estará un host marcado
     * @param integer $conTimeout Tiempo máximo para hacer una conexión.
     */
     function PAFSocketDiscriminator($dirMarked, $markedTimeout, $conTimeout) {
         $this->PAFObject();
         $this->dirMarked = $dirMarked;
         $this->markedTimeout = $markedTimeout;
         $this->socketConnectionTimeout = $conTimeout;
         $this->typeOfSelection = PSD_BALANCED_SOCKET;
#         $this->typeOfSelection = PSD_PREFERRED_SOCKET;
     }

   /**
    *    Añade un host a la lista.
    *    
    *    @access public 
    *    @param string $host IP/nombre del host
    *    @param int $port Puerto al que nos conectamos
    *    @param bool $secure True para usar PAFSecureSocket false para usar 
    *    PAFSOcket
    */
    function addHost($host, $port = 80, $peso = 1) {
        $this->hosts[] = $host;
        $this->ports[] = $port;
        $this->pesos[] = $peso;
    }
    
   /**
    * Asigna el tipo de selección que queremos hacer para la lista de 
    * hosts.
    * 
    * @access public
    * @param $type integer Tipo de selección que queremos hacer.
    */
   function setSelectionType($type) {
       $this->typeOfSelection = $type;
   }
   
   /**
    * Obtiene el tipo de Selección que queremos para la lista de sockets. 
    * 
    * @access public
    * @returns integer Tipo de selección que queremos hacer.
    */
   function getSelectionType() {
       return $this->typeOfSelection;
   }
   
   
   /**
    *    Obtiene un socket bueno en caso de que de que lo hubiera, 
    *    sino devuelve PEAR_Error
    *    
    *    @access public 
    *    @returns PAFSocket PAFSocket o PEAR_Error si no hay sockets arriba.
    */
    function getSocket() {
        if (PSD_BALANCED_SOCKET == $this->typeOfSelection) {
            return $this->getBalancedSocket();
        } else {
            return $this->getPreferredSocket();
        }
    }

    /**
     * Selecciona un socket de la lista de hosts que no están marcados, de 
     * manera aleatoria pero atendiendo al peso.
     *
     * TODO: Tener en cuenta los pesos.
     *
     * @access private
     * @returns PAFSocket socket si alguno es bueno o PEAR_Error en otro caso.
     */
    function getBalancedSocket() {
        // Inicio número aleatorios.
        list($usec, $sec) = explode(' ', microtime());
        mt_srand((float) $sec + ((float) $usec * 100000));
        
        // obtengo los hosts marcados.
        $badHosts = $this->getMarked();
        
        // Obtengo los hosts buenos
        $nHosts = count($this->hosts);       
        $goodHosts = array();
        if ($nHosts == 1)
        { $goodHosts[]= 0; }
        else
        {
            for ($i = 0; $i < $nHosts; $i++) {
                if (!in_array($this->hosts[$i], $badHosts)) {
                    $goodHosts[] = $i;
                }
            }
        }
        
        // Creo el socket sin conectarlo a nada.
        $socket = new PAFSocket();

        // Busco un host que esté bien.
        $hostsLeft = count($goodHosts); // - 1;
        while ($hostsLeft > 0) {
            $i = mt_rand(0, $hostsLeft-1);
            $hostId = $goodHosts[$i];
            $socket->setHost($this->hosts[$hostId]);
            $socket->setPort($this->ports[$hostId]);

            $res = $socket->open();
            if (!PEAR::isError($res)) {
                $res = $this->ping($socket);
                if ($res == true) {
                    return $socket;
                } else {
                    $socket->close();
                }
            }

            // Marcamos el host como caido. $res es un PEAR_Error
            $this->setMarked($hostId, $res);
            $hostsLeft --;
            array_splice($goodHosts, $i, 1);
        }
        // Si llegamos aquí es que no encontró ningún socket bueno.
        return PEAR::raiseError("Todos los sockets están caidos.");
    }
    
    /**
     * Selecciona un socket de la lista de hosts que no están marcados, de 
     * atendiendo al peso que tienen.
     *
     * TODO: Tener en cuenta los pesos
     *
     * @access private
     * @returns PAFSocket socket si alguno es bueno o PEAR_Error en otro caso.
     */
    function getPreferredSocket() {
        $badHosts = $this->getMarked();

        $socket = new PAFSocket();
        $socket->setConexionTimeout($this->socketConnectionTimeout);
        $nHosts = count($this->hosts);
        
        for ($i = 0; $i < $nHosts; $i++) {
            $host = $this->hosts[$i];
            $port = $this->ports[$i];
            if (!in_array($host, $badHosts)) {
                $socket->setHost($host);
                $socket->setPort($port);
                $res = $socket->open();
                if (!PEAR::isError($res)) {
                    $res = $this->ping($socket);
                    if ($res == true) {
                        return $socket;
                    } else {
                        $socket->close();
                    }
                }
                // Marcamos el host como malo.
                $this->setMarked($i, $res);
            }
        }
        
        // Si llegamos aquí es que no hay sockets libres
        return PEAR::raiseError("Todos los sockets están caidos.");
    }

   /**
    * Método a sobreescribir en casos en los que abrir
    * el socket no signifique que el servicio esté vivo.
    *
    * @access protected
    * @param $socket PAFSocket socket para hacer el ping. 
    * @returns boolean true si devolvió lo que debía o PEAR_Error.
    */
    function ping(&$socket) {
        // En el modo básico nos vale con abrir el socket.
        return true;
    }

   /**
    *    Marca un host como caido en el directorio.
    *    
    *    @access protected 
    *    @param $hostId integer id del host a marcar.
    */
    function setMarked($hostId, &$err) {
        $file = str_replace('.', '_', $this->hosts[$hostId]);

        // TODO: Escribir el mensage de error de $err dentro del fichero.
        if (!@touch($this->dirMarked . "/" . $file)) {
            return PEAR::raiseError("No puede hacer touch en $file");
        }
        
        return true;
    }

    /**
     *   Obtiene los host marcados como inactivos y activa los que a los 
     *   que les espiró el tiempo de inactividad.
     *
     *   @access protected
     *   @returns array Lista de host inactivados, o PEAR_Error si error.
     */
    function getMarked() {
        $handle = @opendir($this->dirMarked);

        if (false == $handle) {
            return PEAR::raiseError("Error Leyendo de " . $this->dirMarked);
        }

        $files = array();
        $ahora = time();
        while (false != ($file = readdir($handle))) {
            if ('.' == $file || '..' == $file) {
                continue;
            }
            if (($ahora - filemtime($file)) > $this->markedTimeOut) {
                $file = str_replace('_', '.', $file);
                $files[] = $file;
            }
        }
        closedir($handle);

        sort($files);
        return $files;
    }
}

?>
