<?php
require_once('private/conf/configuration.inc');
require_once('private/conf/QSHProcess.inc'); 
require_once('private/conf/QSHPageTemplates.inc');
require_once('private/QSH/QSHfunctions.inc');
//require_once("PAF/PAFOutput.php");


class QSHUserMenuOU{
	
	function getOutput(){
		
		global $qsh_global_template, $qhs_UserProfiles, $qsh_Apps;
		
		$template = $qsh_global_template;


		$usuario='';		
		if(isset($_COOKIE["usuario"]))
			$usuario = $_COOKIE["usuario"];
			
		if(strlen($usuario) == 0) {
			return '0';

		} else{
			if(!array_key_exists($usuario, $qhs_UserProfiles))
				return '1';
			else{
				
				$usersAllowed= array_keys($qhs_UserProfiles);
				
				foreach ($usersAllowed as $userAllowed ){
					
					if($userAllowed != $usuario) continue;
						
					$allowedApps=$qhs_UserProfiles[$userAllowed];
				
					$appresults = '';
					foreach ($allowedApps as $app ){
						if(array_key_exists($app, $qsh_Apps)){
							$appAttributes = $qsh_Apps[$app];
							$appresult = $template['appMenu'];
							$appresult = replaceSymbol('appname', htmlentities($appAttributes['desc']), null, $appresult);
							$functions = $appAttributes['functions'];
							
							$funcresults = '';
							foreach ($functions as $function => $funcAttributes){
								$xyide = md5($function . $app . $usuario . QSH_ENCRYPTION_WORD); 
								$params = base64_encode($xyide . '#' . rand(0,8058) . '#' . $function .'#'. $app .'#'. $usuario);
								$funcresult = $template['funcMenu'];
								$funcresult = replaceSymbol('funcname', htmlentities($funcAttributes['desc']), null, $funcresult);
								$funcresult = replaceSymbol('funclink', 'index.php?p=f&params=' . $params, null, $funcresult);
								$funcresults .= $funcresult;
							}
							
							$appresult = replaceSymbol('functions', $funcresults, null, $appresult);
							$appresults .= $appresult;
						}
					}
				}
			}
		}
		
		return $appresults;
	}	
}
?>