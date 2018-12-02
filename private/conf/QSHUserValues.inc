<?php
/**
 * Aqui se definen valores especiales de parametros
 */

/**
 * valores para fechas y monentos
 */
define('QSH_DEFAUL_DATE_NOW', 'NOW');
define('QSH_DEFAUL_DATE_TODAY', 'TODAY');
define('QSH_DEFAULT_DATE_YEAR_MONTH', 'YEAR_MONTH');

$qsh_usr_date_year_month = date('Y-m');
$qsh_usr_date_now   = date('Y-m-d H:i:s');
$qsh_usr_date_today = substr($qsh_usr_date_now, 0, 10);


/**
 * portales
 */

define('QSH_DEFAULT_PORTALS', 'PORTALS');

$qsh_usr_portals = [
    '1' => 'portal1',
    '2' => 'portal2',
    '3' => 'protal3',
    '%' => 'Todos',
];

$qsh_usr_portals_html_options = '';
foreach ($qsh_usr_portals as $key => $value) {
    $qsh_usr_portals_html_options .= '<option value="' . $key . '">' . $value . '</option>';
}
/**
 * Si o No
 */

define('QSH_DEFAULT_SINO', 'SINO');

$qsh_usr_sino = [
    '0' => 'No',
    '1' => 'Si',
    '%' => 'Todos',
];

$qsh_usr_sino_html_options = '';
foreach ($qsh_usr_sino as $key => $value) {
    $qsh_usr_sino_html_options .= '<option value="' . $key . '">' . $value . '</option>';
}

?>
