<?
/*
 * PAFReadStatic.php
 * Clase estática que devuelve el contenido de un fichero estático
 * @access public
 */
 

class PAFReadStatic
{

    function getContent($file) {

        if (!file_exists($file))
            return '';
        return file_get_contents ($file);
    }

    function getContentBuffer($filename) {
        if (is_file($filename)) {
            ob_start();
            include $filename;
            $contents = ob_get_contents();
            ob_end_clean();
            return $contents;
        }
        return  '';
    }

}

?>
