<?php
//************************************************************* TEXTOS DE COMANDOS ********************************************************************

$qsh_Command_myipad1_text = "select p.Name as Medio, substr(u.date_entered,1,7) as anio_mes, u.origen, u.status as estado, count(*) as registros_ipad " .
                             "from UsersByPortal u, Portal p " .
                             "where " .
                             "p.id=u.IdPortal " .
                             "and u.date_entered > '@@fecha_in@@' " .
                             "and u.date_entered < '@@fecha_hasta_in@@' " .
                             "and IdPortal= '@@portal_in@@' " .
                             "and origen like '@@origen_l40_in@@' " .
                             "group by p.Name, substr(u.date_entered,1,7), u.origen, u.status ";

$qsh_Command_myipad2_text = "select p.Name as Medio, substr(u.date_entered,1,7) as anio_mes, u.origen, s.Nombre, count(*) as registros_ipad " .
                             "from UsersByPortal u, RSDatosUsuario r, RSRedesSociales s, Portal p " .
                             "where " .
                             "p.id=u.IdPortal " .
                             "and u.id=r.IdUserByPortal " .
                             "and r.IdRedSocial = s.id " .
                             "and u.date_entered >  '@@fecha_in@@' " .
                             "and u.date_entered <  '@@fecha_hasta_in@@' " .
                             "and u.IdPortal= '@@portal_in@@' " .
                             "and origen like '@@origen_l40_in@@' " .
                             "group by p.Name, substr(u.date_entered,1,7), u.origen, s.Nombre ";

?>

