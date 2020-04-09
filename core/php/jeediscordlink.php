<?php

/* This file is part of Jeedom.
*
* Jeedom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Jeedom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
*/

require_once dirname(__FILE__) . "/../../../../core/php/core.inc.php";
					
					

if (!jeedom::apiAccess(init('apikey'), 'discordlink')) {
	echo __('Vous n\'êtes pas autorisé à effectuer cette action', __FILE__);
	log::add('discordlink', 'debug',  'Clé Plugin Invalide');
	die();
}

if (init('test') != '') {
	echo 'OK';
	die();
}

$chaineRecuperee=file_get_contents("php://input");
$nom=$_GET["nom"];
log::add('discordlink', 'debug',  'Réception données sur jeediscordlink ['.$nom.']');

log::add('discordlink', 'debug',  "chaineRecuperee: ".$chaineRecuperee);

$debut=strpos($chaineRecuperee, "{");
$fin=strrpos($chaineRecuperee, "}");
$longeur=1+intval($fin)-intval($debut);
$chaineRecupereeCorrigee=substr($chaineRecuperee, $debut, $longeur);

log::add('discordlink', 'debug',  "chaineRecupereeCorrigee: ".$chaineRecupereeCorrigee);
log::add('discordlink', 'debug',  "nom: ".$nom);

$result = json_decode($chaineRecupereeCorrigee, true);


if (!is_array($result)) {
	log::add('discordlink', 'debug', 'Format Invalide');
	die();
}

$logical_id = $result['idchannel']."_player";

$discordlinkeqlogic=eqLogic::byLogicalId($result['idchannel'], 'discordlink');

switch ($nom) {
	
		case 'messagerecu':
			getdevicepuisupdate("1oldmsg", $result['message'], '1oldmsg', $result['idchannel']);
		break;	
					
		default:
			if (!is_object($discordlinkeqlogic)) {
				log::add('discordlink', 'debug',  'Device non trouvé: '.$logical_id);
				die();
			} else {
				log::add('discordlink', 'debug',  'Device trouvé: '.$logical_id);
			}
}

function getdevicepuisupdate($nom, $variable, $commandejeedom, $_idchannel) {
	$discordlinkeqlogic=eqLogic::byLogicalId($_idchannel, 'discordlink'); 
	if (is_object($discordlinkeqlogic)) updatecommande($nom, $variable, $commandejeedom, $discordlinkeqlogic);
}

function updatecommande($nom, $_value, $_logicalId, $_discordlinkeqlogic, $_updateTime = null) {
	try {
		if (isset($_value)) {
			if ($_discordlinkeqlogic->getIsEnable() == 1) {
				$cmd = is_object($_logicalId) ? $_logicalId : $_discordlinkeqlogic->getCmd('info', $_logicalId);
				if (is_object($cmd)) {
					$oldValue = $cmd->execCmd();
					if ($oldValue !== $cmd->formatValue($_value) || $oldValue === '') {
						$cmd->event($_value, $_updateTime);
					} else {
						$cmd->event(" ", $_updateTime);
						$cmd->event($_value, $_updateTime);
					}
				}
			}	
		}
	} catch (Exception $e) {
		log::add('discordlink', 'info',  ' ['.$nom.':'.$commandejeedom.'] erreur_1: '.$e);		
	} catch (Error $e) {
		log::add('discordlink', 'info',  ' ['.$nom.':'.$commandejeedom.'] erreur_2: '.$e);
    }	
}

?>



