<?php

ini_set('display_errors', 0);

require_once('private/conf/configuration.inc');
require_once('private/QSH/QSHCommandsOU.php');
require_once('PAF/HeaderManager.php');

$charset = 'iso-8859-1';

$performer = new QSHCommandsOU();
$out       = $performer->getOutput();

$hd = new HeaderManager();
$hd->setCacheTime(0);
$hd->setAlternativeCacheCero();

$cType = $performer->getCtype();

switch ($cType) {
    case QSH_TRUE_HTML_OUTPUT:
        header("Content-type: text/html; charset=" . $charset);
        break;
    case QSH_HTML_OUTPUT:
    case QSH_XML_OUTPUT:
        header("Content-type: text/xml; charset=" . $charset);
        break;
    case QSH_CSV_OUTPUT:
        header("Content-type: text/csv; charset=" . $charset);
        header('Content-disposition: attachment; filename=' . $performer->getAttachmentFileName());
        break;
    default:
        header("Content-type: text/xml; charset=" . $charset);
        break;
}

$hd->sendHeaders();

echo $out;
exit;