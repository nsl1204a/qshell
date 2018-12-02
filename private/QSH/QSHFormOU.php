<?php
require_once('private/conf/configuration.inc');
require_once('private/conf/QSHProcess.inc');
require_once('private/conf/QSHPageTemplates.inc');
require_once('private/QSH/QSHfunctions.inc');
require_once('private/QSH/QSHCommandsOU.php');
require_once('private/HeaderManager.php');

$hd = new HeaderManager();
$hd->setCacheTime(0);
$hd->setContentType("text/html");
$hd->setAlternativeCacheCero();
$hd->sendHeaders();

class QSHFormOU /*extends PAFOutput*/{

	function getOutput(){
		
		global $qsh_global_template, $qsh_constant_desc, $qsh_Apps;
		
		$template = $qsh_global_template;
		
		$params = $_GET['params'];
		$performer = new QSHCommandsOU();
		$exparams = $performer->safetyChecks($params);
		
		
		$ariadna = '';
		$notice = '';
		$content = '';
		
		if(!$exparams){
			$notice = $template['notice'];
			$notice = replaceSymbol('notice', 'No hay un contexto correcto para ejecutar este proceso', null, $notice);
		}
		else {
			$function = $exparams[2];
			$app = $exparams[3];
			
			$ariadna = $template['ariadna'];
			$ariadna = replaceSymbol('appname', $qsh_Apps[$app]['desc'], null, $ariadna);
			$ariadna = replaceSymbol('funcname', $qsh_Apps[$app]['functions'][$function]['desc'], null, $ariadna);
			
			$notice = $template['notice'];
			$notice = replaceSymbol('notice',  htmlentities ($qsh_Apps[$app]['functions'][$function]['desc'] . ' [' .  $qsh_constant_desc[$qsh_Apps[$app]['functions'][$function]['type']] . ']'), null, $notice);
			
			$content = $template['content'];
			$forms = '';
			$forms .=  '<form  method="POST" target="_blank" name="FormShell" action="qshellExe.php">';					 
			$forms .=  '<input type="hidden" name="params" value="' . $params. '"></input>';
			$forms .=  $this->getHtmlForCommands($qsh_Apps[$app]['functions'][$function]);
			$forms .=  '<select name="FFCtype">';
			$forms .=  '<option value="H">Resolver para p&aacute;gina html</option>';
			$forms .=  '<option value="X">Resolver para p&aacute;gina xml</option>';
			$forms .=  '<option value="C">Resolver para fichero CSV</option>';
			$forms .=  '</select>';
			$forms .=  '<button><input type="submit" value="Ejecutar" name="FFEnviar" /></button>';
			$forms .=  '</form>';
			$forms .=  '<p>Los resultados se mostrar&aacute;n en una pesta&ntilde;a nueva</p>';
			$content = replaceSymbol('content', $forms, null, $content);
		}
		
		return $ariadna . $notice . $content; 
		//$result;
	}


	function getHtmlForCommands($aFunction){
		
	
		if(isset($aFunction['commands']))
			$aCommands = $aFunction['commands'];
		else 
			$aCommands  = array();
	
		$out = '';
		$i=1;
		foreach ($aCommands as $command){
			$out .= '<li>Parte ' . $i . ': ' . $command['desc'] . '</li>';
			$i++;	  
		}
		$out .= '<h2>Escriba los parametros siguientes:</h2>';
		$out .= '<hr />';
		
		if(isset($aFunction['params']))
			$aCommands = $aFunction['commands'];
		else 
			$aCommands  = array();
		
   		$out .= '<table border="0">';
		$out .= $this->getHtmlForParameters($aFunction);
		$out .= '</table><hr />';

		return $out;
	}
	
	function getHtmlForParameters($aFunction){
	
		global $qsh_usr_date_now, $qsh_usr_date_today, $qsh_usr_portals_html_options, $qsh_parameters_dictionary; 
		
		if(isset($aFunction['params']))
			$aParameters = $aFunction['params'];
		else 
			$aParameters = array();
			
		$out = '';
		foreach ($aParameters as $clave => $aCommandIndex){
			
			if(isset($qsh_parameters_dictionary[$clave]))
				$parameter = $qsh_parameters_dictionary[$clave];
			else 
				continue;
			
			$isSelectInput=false;
			$options = '';
	
			
			$out .= '<td>' . '<b>' . $parameter['desc'] . '</b>' ;
			
			$out .= '&nbsp;&nbsp;(';
			$i = 0;
			foreach($aCommandIndex as $index){
				if($i) $out .= ', ';
				$out .= 'Parte ' . $index;
				$i++;
			}
			$out .= ')';
			
			if($parameter['type'] == QSH_PARAMETER_TYPE_DATETIME)
				$out .= '&nbsp;&nbsp;<b>(aaaa-mm-dd hh24:mi:ss)</b>';
				
			if($parameter['type'] == QSH_PARAMETER_TYPE_DATE)
				$out .= '&nbsp;&nbsp;<b>(aaaa-mm-dd)</b>';
	
			if($parameter['default'] == QSH_DEFAUL_DATE_TODAY) 
				$parameter['default'] = $qsh_usr_date_today;
			elseif($parameter['default'] == QSH_DEFAUL_DATE_NOW) 
				$parameter['default'] = $qsh_usr_date_now;	
			elseif($parameter['default'] == QSH_DEFAULT_PORTALS){
				$isSelectInput = true;
				$options = $qsh_usr_portals_html_options; 
			}
			elseif($parameter['default'] == QSH_DEFAULT_SINO){
				$isSelectInput = true;
				$options = $qsh_usr_sino_html_options; 
			}
						
			$out .= '</td><td>';
			
			if($isSelectInput){
				$out .= '<select name="' .  $clave . '">';
				$out .= $options;
				$out .= '</select>';
			}
			else{		
				$out .= '<input type="text"'; 
					$out .= ' name="'  		. $clave . '"';
					$out .= ' value="'		. $parameter['default'] .'"';
					$out .= ' size="'  		. $parameter['length'] .'"';
					$out .= ' maxlength="'  . $parameter['length'] .'"';
				$out .= '></input>';
			}
			
			$out .= '</td>';
			$out .= '<tr />';
		}
	
		return $out;
	}
}

?>