<?php
// *****************************************************************************
// Lenguaje: PHP
// Copyright 2003 Prisacom S.A.
// ---------
// ChangeLog
// ---------
// $Log: PAFLogFormat.php,v $
// Revision 1.3  2003/10/24 15:22:42  vsanz
// Primera versión funcional
//
// Revision 1.2  2003/10/23 14:02:03  vsanz
// Algunos arreglos
//
// Revision 1.1  2003/10/16 17:29:58  vsanz
// Primera subida que compila, todav�a no hay tests
//
//
// *****************************************************************************

require_once 'PAF/PAFObject.php';

/**
 * Clase que encapsula el formato del mensaje de log en PAF.
 * tanto para la escritura como la lectura.
 *
 * @author Virgilio Sanz <vsanz@prisacom.com>
 * @ver $Revision: 1.3 $
 * @package PAF
 *
 */
class PAFLogFormat extends PAFObject
{
  /**
   * Formatea el mesagge de log
   *
   * @access public
   * @param $file string fichero donde se origina el error, habitualmente __FILE__
   * @param $line integer fichero donde se origina el error, habitualmente __LINE__
   * @param $message string a text error message or a PEAR error object
   *
   * @return string contenido formateado del mensaje
   */
  function format($file, $line, $message)
  {
      global $REQUEST_URI, $HTTP_REFERER;
      global $HTTP_X_FORWARDED_FOR, $HTTP_CLIENT_IP, $REMOTE_ADDR;
      global $REMOTE_USER, $SERVER_NAME;

      // Calculamos la url de la petici�n/p�gina
      $url = '';
      if (isset($REQUEST_URI)) {
          $url = $REQUEST_URI;
      }

      $referer = '';
      if (isset($HTTP_REFERER)) {
          $url = $HTTP_REFERER;
      }
      
      // Obtenemos informaci�n del peticionario.
      $proxy_ip = ''; $ip = '';
      if ($HTTP_X_FORWARDED_FOR) {
          if ($HTTP_CLIENT_IP) {
              $proxy_ip = $HTTP_CLIENT_IP;
          } else {
              $proxy_ip = $REMOTE_ADDR;
          }
          $ip = $HTTP_X_FORWARDED_FOR;
      } else {
          if ($HTTP_CLIENT_IP) {
              $ip = $HTTP_CLIENT_IP;
          } else {
              $ip = $REMOTE_ADDR;
          }
      }

      // Obtenemos el nombre del usuario.
      $user =  (!isset($REMOTE_USER) || '' == trim($REMOTE_USER)) ?
          'Anonimo' : $REMOTE_USER;
      // Obtenemos el servidor que atendió la petición.
      $server = $SERVER_NAME;

      $new_message = sprintf("%s - %d - %s - %s - %s - %s - %s - %s - %s",
          $file, $line, $server, $url, $referer, $user, $ip, $proxy_ip, $message);

      return $new_message;
  }

  /**
   * Parsea una línea de log, sólo la parte del mensaje. La parte propia
   * de log4php iría por otro lado.
   *
   * @param $message  a text error message or a PEAR error object
   *
   * @return array con el contenido de los siguientes campos:
   *    1 - file: Fichero donde se generó el log.
   *    2 - line: Línea del fichero
   *    3 - server: Nombre del servidor que atendió la petición
   *    4 - url: Url de la Página donde se generó el error
   *    5 - user: username del usuario remoto
   *    6 - ip: Ip del usuario
   *    7 - proxy: proxy del usuario
   *    8 - msg : Mesanje informativo
   */
  function parse($line)
  {
      $campos = array('log',
          'PEAR', 'line', 'server', 'url', 'referer', 'user', 'ip',
                      'proxy', 'message');
      // Parseamos la línea
      $trozos = explode('-', $line);

      $ret = array();
      // Hacemos trim a todos los valores y los ponemos en su sitio.
      $N = count($campos);
      for ($i = 0; $i < $N; $i ++) {
          $ret[$campos[$i]] = trim($trozos[$i]);
      }

      // Junto todo el mensaje
      if ($N > count($trozos)) {
          $ret[$campos[$N-1]] .= implode(' ', array_slice($trozos, $N));
      }

      return $ret;
  }

  /**
   * devuelve el microtime formateado
   *
   * @access public
   * @return float
   */
  function getMicroTime() {
    list($usec, $sec) = explode(" ",microtime());
    return ((float)$usec + (float)$sec);
  }
}

?>
