<?php
require_once('private/conf/QSHUserValues.inc');
require_once('private/conf/configuration.inc');
/**
 * Aqui se definenen las aplicaciones, sus funciones, commandos y permisos de usuarios
 */

/**
 * Descripcion de contantes Qshell
 */

$qsh_constant_desc = [
    QSH_PROCESS_TYPE_UPDATE       => 'Actualización',
    QSH_PROCESS_TYPE_QUERY        => 'Consulta',
    QSH_COMMAND_TYPE_SELECT       => 'Select',
    QSH_COMMAND_TYPE_PROCEDURE    => 'Procedimiento almacenado',
    QSH_COMMAND_TYPE_UPDATE       => 'Actualización',
    QSH_COMMAND_TYPE_PROCEDURE_RS => 'Procedimiento almacenado con salida Recordset',
    QSH_COMMAND_TYPE_EXE          => 'Ejecutable del sistema',
    QSH_PARAMETER_TYPE_NUMERIC    => 'Número',
    QSH_PARAMETER_TYPE_TEXT       => 'Texto',
    QSH_PARAMETER_TYPE_DATE       => 'Fecha',
    QSH_PARAMETER_DIRECTION_IN    => 'Entrada',
    QSH_PARAMETER_DIRECTION_OUT   => 'Salida',
    QSH_EXECUTION_STATE_OK        => 'Correcto',
    QSH_EXECUTION_STATE_NOOK      => 'Incorrecto',
];
$qsh_usr_date_year_month = date('Y-m');
$today =  new DateTime('first day of this month');
$qsh_first_day_month = $today->format('Y-m-d');

//************************************************************* TEXTOS DE COMANDOS ********************************************************************		
include('private/conf/QSHCommandText.inc');

//************************************************************* DICCIONARIO DE PARAMETROS ********************************************************************

$qsh_parameters_dictionary = [
    'fecha_in'            => [
        'desc'      => 'Fecha',
        'direction' => QSH_PARAMETER_DIRECTION_IN,
        'type'      => QSH_PARAMETER_TYPE_DATE,
        'length'    => 20,
        'default'   => QSH_DEFAUL_DATE_TODAY,
    ],
    'fecha_hasta_in'      => [
        'desc'      => 'Hasta la fecha',
        'direction' => QSH_PARAMETER_DIRECTION_IN,
        'type'      => QSH_PARAMETER_TYPE_DATE,
        'length'    => 20,
        'default'   => QSH_DEFAUL_DATE_TODAY,
    ],
    'origen_my_in'       => [
        'desc'      => 'Origen',
        'direction' => QSH_PARAMETER_DIRECTION_IN,
        'type'      => QSH_PARAMETER_TYPE_TEXT,
        'length'    => 25,
        'default'   => 'my_iPad',
    ],
    'portal_in'           => [
        'desc'      => 'Portal',
        'direction' => QSH_PARAMETER_DIRECTION_IN,
        'type'      => QSH_PARAMETER_TYPE_TEXT,
        'length'    => 36,
        'default'   => QSH_DEFAULT_PORTALS,
    ],
    'momento_in'          => [
        'desc'      => 'Momento del d&iacute;a',
        'direction' => QSH_PARAMETER_DIRECTION_IN,
        'type'      => QSH_PARAMETER_TYPE_TEXT,
        'length'    => 2,
        'default'   => 'AM',
    ],
    'email_in'            => [
        'desc'      => 'Email',
        'direction' => QSH_PARAMETER_DIRECTION_IN,
        'type'      => QSH_PARAMETER_TYPE_TEXT,
        'length'    => 50,
        'default'   => '',
    ]
];

//************************************************************* COMANDOS DE FUNCIONES ********************************************************************
$qsh_Command_myipad_1 = [
    'name'     => 'COMMAND1',
    'desc'     => 'obtener estadisticas globales usuarios ipad',
    'text'     => $qsh_Command_myipad1_text,
    'type'     => QSH_COMMAND_TYPE_SELECT,
    'database' => 'bregistro',
];
$qsh_Command_myipad_2 = [
    'name'     => 'COMMAND2',
    'desc'     => 'obtener estadisticas por redes sociales usuarios ipad',
    'text'     => $qsh_Command_myipad2_text,
    'type'     => QSH_COMMAND_TYPE_SELECT,
    'database' => 'milloweb',
];

$qsh_ESTCRMFunctions = [
    'IPAD_MY'  => [
        'desc'     => 'IPAD y redes sociales',
        'type'     => QSH_PROCESS_TYPE_QUERY,
        'commands' => [$qsh_Command_myipad_1, $qsh_Command_myipad_2],
        'params'   => [
            'fecha_in'       => [1, 2],
            'fecha_hasta_in' => [1, 2],
            'portal_in'      => [1, 2],
            'origen_my_in'   => [1, 2],
        ],
    ]
];


//************************************************************* APLICACIONES ********************************************************************
define('ESTADISTICAS_REGISTRO', 1);
$qsh_Apps = [
    ESTADISTICAS_REGISTRO => ['desc' => 'Estadisticas de registro', 'functions' => $qsh_ESTCRMFunctions]
];

//************************************************* PERMISOS DE USUARIOS A APLICACIONES ********************************************************************
define('MCALVO', 'mcalvo');
define('ADMIN', 'admin');

$qhs_UserProfiles = [
    MCALVO     => [ESTADISTICAS_REGISTRO],
    ADMIN      => [ESTADISTICAS_REGISTRO],
];

$userPass = [
    'admin'   => '123456789A',
    'mcalvo' => '123456789B',
];
