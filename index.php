<?php
require_once('private/QSH/QSHUserMenuOU.php');
require_once('private/QSH/QSHFormOU.php');
require_once('private/conf/QSHPageTemplates.inc');
require_once('private/HeaderManager.php');

$hd = new HeaderManager();
$hd->setCacheTime(0);
$hd->setContentType("text/html");
$hd->setAlternativeCacheCero();
$hd->sendHeaders();

$template = $qsh_global_template;
$result   = $template['gobalPage'];
$notice   = $template['notice'];
$login    = $template['frmlogion'];


if (!isset($_COOKIE["usuario"])) {
    // pido login o muestro error
    $valueNotice = 'Login';

    if (isset($_GET['l']) && $_GET['l'] === '2') {
        $valueNotice = 'Identificaci&oacute;n inv&aacute;lida';
    }

    $result = replaceSymbol('appMenu', "", null, $result);
    $login  = replaceSymbol('notice', $valueNotice, null, $login);
    $result = replaceSymbol('contenidos', $login, null, $result);

} else {

    /**
     * presentaciones del controlador: parametro p -> nada: contenido vacio, f: presentacion de formulario
     */
    $view = '';
    if (isset($_GET['p'])) {
        $view = $_GET['p'];
    }

    /**
     * evalua si la cookie del usuario existe, de no existir pinta y se detiene la ejecucion
     * pero si el usuario ya esta logado
     * siempre se muestra la parte izquierda: menu del usuario
     */
    $userMenu = new QSHUserMenuOU();
    $menu     = $userMenu->getOutput();

    /**
     * casos de error al generar el menu:
     */
    if ($menu == '0') {
        $notice = replaceSymbol('notice', 'Identificaci&oacute;n inv&aacute;lida', null, $notice);
    } elseif ($menu == '1') {
        $notice = replaceSymbol('notice', 'No hay aplicaciones disponibles para el usuario', null, $notice);
    }

    if ($menu == '0' || $menu == '1') {
        $result = replaceSymbol('contenidos', $notice, null, $result);
        $result = replaceSymbol('appMenu', null, null, $result);
        echo $result;

        return;
    } else {
        $result = replaceSymbol('appMenu', $menu, null, $result);
    }

    //presentar formulario en area central de contenidos
    if ($view == 'f') {
        $form   = new QSHFormOU();
        $result = replaceSymbol('contenidos', $form->getOutput(), null, $result);
    }
    //presentar resultados en area central de contenidos
    //si no hay ninguna presetacion especifica se invita al usuario a seleccionar una opcion
    else {
        $notice = replaceSymbol('notice', 'Seleccione una opci&oacute;n', null, $notice);
        $result = replaceSymbol('contenidos', $notice, null, $result);
    }
}
echo $result;

?>