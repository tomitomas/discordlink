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

/* * ***************************Includes********************************* */
require_once __DIR__  . '/../../../../core/php/core.inc.php';

class discordlink extends eqLogic {
    /*     * *************************Attributs****************************** */

	public static function templateWidget() {
		$return['action']['message']['message'] =    array(
				'template' => 'message',
				'replace' => array("#_desktop_width_#" => "100","#_mobile_width_#" => "50", "#title_disable#" => "1", "#message_disable#" => "0")
		);
		$return['action']['message']['embed'] =    array(
			'template' => 'embed',
			'replace' => array("#_desktop_width_#" => "100","#_mobile_width_#" => "50", "#title_disable#" => "1", "#message_disable#" => "0")
	);
		return $return;
	}	

    /*     * ***********************Methode static*************************** */

    /*
     * Fonction exécutée automatiquement toutes les minutes par Jeedom
      public static function cron() {

      }
     */


    /*
     * Fonction exécutée automatiquement toutes les heures par Jeedom
      public static function cronHourly() {

      }
     */

    /*
     * Fonction exécutée automatiquement tous les jours par Jeedom
      public static function cronDaily() {

      }
     */



    /*     * *********************Méthodes d'instance************************* */

	public static function getchannel() {
		$deamon = discordlink::deamon_info();
		if ($deamon['state'] == 'ok') {
			$json = file_get_contents("http://" . config::byKey('internalAddr') . ":3466/getchannel");
			$json = json_decode($json, true);
			return $json;
		}
	}

	public static function getinvite() {
		$deamon = discordlink::deamon_info();
		if ($deamon['state'] == 'ok') {
			$json = file_get_contents("http://" . config::byKey('internalAddr') . ":3466/getinvite");
			$json = json_decode($json, true);
			$json = $json[0];
			return $json['invite'];
		}
	}

	public static function dependancy_info() {
		$return = array();
		$return['log'] = 'discordlink_dep';
		$resources = realpath(dirname(__FILE__) . '/../../resources/');
		$packageJson=json_decode(file_get_contents($resources.'/package.json'),true);
		$state='ok';
		foreach($packageJson["dependencies"] as $dep => $ver){
			if(!file_exists($resources.'/node_modules/'.$dep.'/package.json')) {
				$state='nok';
			}
		}
		$return['progress_file'] = jeedom::getTmpFolder('discordlink') . '/dependance';
		$return['state']=$state;
		return $return;
    }

	public static function dependancy_install($verbose = "false") {
		if (file_exists(jeedom::getTmpFolder('discordlink') . '/dependance')) {
			return;
		}
		log::remove('discordlink_dep');
		$_debug = 0;
		if (log::getLogLevel('discordlink') == 100 || $verbose === "true" || $verbose === true) $_debug = 1;
		log::add('discordlink', 'info', 'Installation des dépendances : ');
		$resource_path = realpath(dirname(__FILE__) . '/../../resources');
		return array('script' => $resource_path . '/install.sh ' . $resource_path . ' discordlink ' . $_debug, 'log' => log::getPathToLog('discordlink_dep'));
	}
      
    
	public static function deamon_info() {
		$return = array();
		$return['log'] = 'discordlink_node';
		$return['state'] = 'nok'; 
		
		// Regarder si discordlink.js est lancé
		$pid = trim(shell_exec('ps ax | grep "resources/discordlink.js" | grep -v "grep" | wc -l'));
		if ($pid != '' && $pid != '0') $return['state'] = 'ok';
		
		// Regarder si le token est ok
		if (config::byKey('Token', 'discordlink', 'null') != "null") $return['launchable'] = 'ok';
		else {
			$return['launchable'] = 'nok';
			$return['launchable_message'] = "TOKEN DISCORD ABSENT ";
		}
		return $return;
	}

	public static function deamon_start($_debug = false) {
		self::deamon_stop();
		$deamon_info = self::deamon_info();
		if ($deamon_info['launchable'] != 'ok') throw new Exception(__('Veuillez vérifier la configuration', __FILE__));
		log::add('discordlink', 'info', 'Lancement du bot');
		$url = network::getNetworkAccess('internal', 'proto:127.0.0.1:port:comp') . '/plugins/discordlink/core/api/jeeDiscordlink.php?apikey=' . jeedom::getApiKey('discordlink');
		$log = $_debug ? '1' : '0';
		$sensor_path = realpath(dirname(__FILE__) . '/../../resources');
		$cmd = 'nice -n 19 nodejs ' . $sensor_path . '/discordlink.js ' . network::getNetworkAccess('internal') . ' ' . config::byKey('Token', 'discordlink') . ' '.log::getLogLevel('discordlink') . ' ' . $url . ' ' . jeedom::getApiKey('discordlink');
		log::add('discordlink', 'debug', 'Lancement démon discordlink : ' . $cmd);
		$result = exec('NODE_ENV=production nohup ' . $cmd . ' >> ' . log::getPathToLog('discordlink_node') . ' 2>&1 &');
		if (strpos(strtolower($result), 'error') !== false || strpos(strtolower($result), 'traceback') !== false) {
			log::add('discordlink', 'error', $result);
			return false;
		}
		$i = 0;
		while ($i < 30) {
			$deamon_info = self::deamon_info();
			if ($deamon_info['state'] == 'ok') break;
			sleep(1);
			$i++;
		}
		if ($i >= 30) {
			log::add('discordlink', 'error', 'Impossible de lancer le démon discordlink, vérifiez le port', 'unableStartDeamon');
			return false;
		}
		message::removeAll('discordlink', 'unableStartDeamon');
		log::add('discordlink', 'info', 'Démon discordlink lancé');
		return true;
	}

	public static function deamon_stop() {
		log::add('discordlink', 'info', 'Arrêt du service discordlink');
		@file_get_contents("http://" . config::byKey('internalAddr') . ":3466/stop");
		sleep(3);
		if(shell_exec('ps aux | grep "resources/discordlink.js" | grep -v "grep" | wc -l') == '1') {
			exec('sudo kill $(ps aux | grep "resources/discordlink.js" | grep -v "grep" | awk \'{print $2}\') &>/dev/null');
			$deamon_info = self::deamon_info();
			if ($deamon_info['state'] == 'ok') {
				sleep(1);
				exec('sudo kill -9 $(ps aux | grep "resources/discordlink.js" | grep -v "grep" | awk \'{print $2}\') &>/dev/null');
			}
			$deamon_info = self::deamon_info();
			if ($deamon_info['state'] == 'ok') {
				sleep(1);
				exec('sudo kill -9 $(ps aux | grep "resources/discordlink.js" | grep -v "grep" | awk \'{print $2}\') &>/dev/null');
			}
		}
    }
    

    public function preInsert() {
        
    }

    public function postInsert() {
        
    }

    public function preSave() {
        $channel = $this->getConfiguration('channelid');
			if (isset($channel)) {
				$this->setLogicalId($channel);
				log::add('discordlink', 'debug', 'setLogicalId : ' . $channel);
			}
	}
	
	public static function CreateRefreshCmd() {

		$eqLogics = eqLogic::byType('discordlink');
		foreach ($eqLogics as $eqLogic) {

			$TabCmd = array(
				'sendMsg'=>array('Libelle'=>'Send message', 'Type'=>'action', 'SubType' => 'message','request'=> 'sendMsg?message=#message#', 'visible' => 1, 'Template' => 'discordlink::message'),
				'sendMsgTTS'=>array('Libelle'=>'Send message TTS', 'Type'=>'action', 'SubType' => 'message', 'request'=> 'sendMsgTTS?message=#message#', 'visible' => 1, 'Template' => 'discordlink::message'),
				'sendEmbed'=>array('Libelle'=>'Send Embed Message', 'Type'=>'action', 'SubType' => 'message', 'request'=> 'sendEmbed?color=#color#&title=#title#&url=#url#&description=#description#&field=#field#&footer=#footer#', 'visible' => 0),
				'sendFile'=>array('Libelle'=>'Send File', 'Type'=>'action', 'SubType' => 'message', 'request'=> 'sendFile?patch=#patch#&name=#name#', 'visible' => 0),
				'1oldmsg'=>array('Libelle'=>'Dernier message', 'Type'=>'info', 'SubType'=>'string', 'visible' => 0)
			);
			//Chaque commande
			foreach ($TabCmd as $CmdKey => $Cmd){
				$Cmddiscordlink = $eqLogic->getCmd(null, $CmdKey);
				if (!is_object($Cmddiscordlink) ) {
					$Cmddiscordlink = new discordlinkCmd();
					$Cmddiscordlink->setName($Cmd['Libelle']);
					$Cmddiscordlink->setEqLogic_id($eqLogic->getId());
					$Cmddiscordlink->setType($Cmd['Type']);
					$Cmddiscordlink->setSubType($Cmd['SubType']);
					$Cmddiscordlink->setLogicalId($CmdKey);
					$Cmddiscordlink->setEventOnly(1);
					$Cmddiscordlink->setIsVisible($Cmd['visible']);
					if ($Cmd['Type'] == "action") {
						$Cmddiscordlink->setConfiguration('request', $Cmd['request']);
						$Cmddiscordlink->setConfiguration('value', 'http://' . config::byKey('internalAddr') . ':3466/' . $Cmd['request'] . "&channelID=" . $eqLogic->getConfiguration('channelid'));
					}
					$Cmddiscordlink->setDisplay('generic_type','GENERIC_INFO');
					if (!empty($Cmd['Template'])) {
						$Cmddiscordlink->setTemplate("dashboard", $Cmd['Template']);
						$Cmddiscordlink->setTemplate("mobile", $Cmd['Template']);
					}
					$Cmddiscordlink->setDisplay('message_placeholder', 'Message a envoyer sur discord');
					$Cmddiscordlink->setDisplay('forceReturnLineBefore', true);
					$Cmddiscordlink->save();
				} else {
						$Cmddiscordlink->setCollectDate(date('Y-m-d H:i:s'));
						$Cmddiscordlink->event($value);
				}		
			}
		}
	}

    public function postSave() {
		discordlink::CreateRefreshCmd();
	}

    public function preUpdate() {
        
    }

    public function postUpdate() {
    }

    public function preRemove() {
        
    }

    public function postRemove() {
        
    }


    /*
     * Non obligatoire mais permet de modifier l'affichage du widget si vous en avez besoin
      public function toHtml($_version = 'dashboard') {

      }
     */

    /*
     * Non obligatoire mais ca permet de déclencher une action après modification de variable de configuration
    public static function postConfig_<Variable>() {
    }
     */

    /*
     * Non obligatoire mais ca permet de déclencher une action avant modification de variable de configuration
    public static function preConfig_<Variable>() {
    }
     */

    /*     * **********************Getteur Setteur*************************** */
}
class discordlinkCmd extends cmd {
	
		/*     * *************************Attributs****************************** */
	
	
		/*     * ***********************Methode static*************************** */
	
	
		/*     * *********************Methode d'instance************************* */
	
		/*
		 * Non obligatoire permet de demander de ne pas supprimer les commandes même si elles ne sont pas dans la nouvelle configuration de l'équipement envoyé en JS
		  public function dontRemoveCmd() {
		  return true;
		  }
		 */
	
		public function execute($_options = null) {
			if ($this->getLogicalId() == 'refresh') {
				$this->getEqLogic()->refresh();
				return;
			}

			$request = $this->buildRequest($_options);
			log::add('discordlink', 'debug', 'Envoi de ' . $request);
			$request_http = new com_http($request);
			$request_http->setAllowEmptyReponse(true);//Autorise les réponses vides
			if ($this->getConfiguration('noSslCheck') == 1) $request_http->setNoSslCheck(true);
			if ($this->getConfiguration('doNotReportHttpError') == 1) $request_http->setNoReportError(true);
			if (isset($_options['speedAndNoErrorReport']) && $_options['speedAndNoErrorReport'] == true) {// option non activée 
				$request_http->setNoReportError(true);
				$request_http->exec(0.1, 1);
				return;
			}
			if (isset($_options['answer'])) {
				$return = $request_http->exec($this->getConfiguration('timeout', 320), $this->getConfiguration('maxHttpRetry', 1));//Time out à 300s 1 essais
				$return = substr($return, 1, -1);

				$result = json_decode($return , true);
				$answer = $_options['answer'];

				$this->askResponse($answer[$result['reponse']]);

			} else {
				$result = $request_http->exec($this->getConfiguration('timeout', 3), $this->getConfiguration('maxHttpRetry', 3));//Time out à 3s 3 essais
			}
			
			if (!$result) throw new Exception(__('Serveur injoignable', __FILE__));
		
			return true;
		}

		private function buildRequest($_options = array()) {
			if ($this->getType() != 'action') return $this->getConfiguration('request');
			$cmdANDarg = explode('?', $this->getConfiguration('request'), 2);
			if (count($cmdANDarg) > 1) 
				list($command, $arguments) = $cmdANDarg;
			else {
				$command=$this->getConfiguration('request');
				$arguments="";
			}
			switch ($command) {		
				case 'sendMsg':
				case 'sendMsgTTS':
					$request = $this->build_ControledeSliderSelectMessage($_options);
				break;	
				case 'sendEmbed':
					$request = $this->build_ControledeSliderSelectEmbed($_options);
				break;	
				case 'sendFile':
					$request = $this->build_ControledeSliderSelectFile($_options);
				break;					
				default:
					$request = '';
				break;
			}
			$request = scenarioExpression::setTags($request);
			if (trim($request) == '') throw new Exception(__('Commande inconnue ou requête vide : ', __FILE__) . print_r($this, true));
			$channelID=str_replace("_player", "", $this->getEqLogic()->getConfiguration('channelid'));
			return 'http://' . config::byKey('internalAddr') . ':3466/' . $request . '&channelID=' . $channelID;
		}
	
		private function build_ControledeSliderSelectMessage($_options = array(), $default = "Ceci est un message de test") {

			$request = $this->getConfiguration('request');
			if ((isset($_options['message'])) && ($_options['message'] == "")) $_options['message'] = $default;
			if (!(isset($_options['message']))) $_options['message'] = "";
			$request = str_replace(array('#message#'), 
			array(urlencode(self::decodeTexteAleatoire($_options['message']))), $request);
			log::add('discordlink_node', 'info', '---->RequestFinale:'.$request);
			return $request;
		}	

		private function build_ControledeSliderSelectFile($_options = array(), $default = "Ceci est un message de test") {
			$patch = "null";
			$nameFile = "null";

			$request = $this->getConfiguration('request');
			if ((isset($_options['patch'])) && ($_options['patch'] == "")) $_options['patch'] = $default;
			if (!(isset($_options['patch']))) $_options['patch'] = "";

			if (isset($_options['files']) && is_array($_options['files'])) {
				foreach ($_options['files'] as $file) {
					if (version_compare(phpversion(), '5.5.0', '>=')) {
						$patch = $file;
						$files = new CurlFile($file);
						$nameexplode = explode('.',$files->getFilename());
						log::add('discordlink', 'info', $_options['title'].' taille : '.$nameexplode[sizeof($nameexplode)-1]);
						$nameFile = (isset($_options['title']) ? $_options['title'].'.'.$nameexplode[sizeof($nameexplode)-1] : $files->getFilename());
					} 
				}
			} else {
					$patch = $_options['patch'];
					$nameFile = $_options['Name_File'];
			}

			$request = str_replace(array('#name#'), 
			array(urlencode(self::decodeTexteAleatoire($nameFile))), $request);
			$request = str_replace(array('#patch#'), 
			array(urlencode(self::decodeTexteAleatoire($patch))), $request);

			log::add('discordlink_node', 'info', '---->RequestFinale:'.$request);
			return $request;
		}	

		private function build_ControledeSliderSelectEmbed($_options = array(), $default = "Ceci est un message de test") {

			$request = $this->getConfiguration('request');

			$titre = "null";
			$url = "null";
			$description = "null";
			$footer = "null";
			$field = "null";
			$colors = "null";

			if (isset($_options['answer'])) {
				if (("" != ($_options['title']))) $titre = $_options['title'];
				$colors = "#1100FF";

				$answer = $_options['answer'];
				$description = "";
				
				$a = 0;
				$choix = [":regional_indicator_a:",":regional_indicator_b:",":regional_indicator_c:",":regional_indicator_d:",":regional_indicator_e:",":regional_indicator_f:",":regional_indicator_g:",":regional_indicator_h:",":regional_indicator_i:",":regional_indicator_j:",":regional_indicator_k:",":regional_indicator_l:",":regional_indicator_m:",":regional_indicator_n:",":regional_indicator_o:",":regional_indicator_p:",":regional_indicator_q:",":regional_indicator_r:",":regional_indicator_s:",":regional_indicator_t:",":regional_indicator_u:",":regional_indicator_v:",":regional_indicator_w:",":regional_indicator_x:",":regional_indicator_y:",":regional_indicator_z:"];
				while ($a < count($answer)) {
					$description .=	$choix[$a] . " : ". $answer[$a];
					$description .= "
					";
					$a ++;
				}
				$field = count($answer);

			} else {
				if (("" != ($_options['Titre']))) $titre = $_options['Titre']; 
				if (("" != ($_options['url']))) $url = $_options['url'];
				if (("" != ($_options['description']))) $description = $_options['description'];
				if (("" != ($_options['footer']))) $footer = $_options['footer'];
				if (("" != ($_options['colors']))) $colors = $_options['colors'];
			}

			$request = str_replace(array('#title#'), 
			array(urlencode(self::decodeTexteAleatoire($titre))), $request);
			$request = str_replace(array('#url#'), 
			array(urlencode(self::decodeTexteAleatoire($url))), $request);
			$request = str_replace(array('#description#'), 
			array(urlencode(self::decodeTexteAleatoire($description))), $request);
			$request = str_replace(array('#footer#'), 
			array(urlencode(self::decodeTexteAleatoire($footer))), $request);
			$request = str_replace(array('#field#'), 
			array(urlencode(self::decodeTexteAleatoire($field))), $request);
			$request = str_replace(array('#color#'), 
			array(urlencode(self::decodeTexteAleatoire($colors))), $request);

			log::add('discordlink_node', 'info', '---->RequestFinale:'.$request);
			return $request;
		}	
	
		public static function decodeTexteAleatoire($_text) {
			$return = $_text;
			if (strpos($_text, '|') !== false && strpos($_text, '[') !== false && strpos($_text, ']') !== false) {
				$replies = interactDef::generateTextVariant($_text);
				$random = rand(0, count($replies) - 1);
				$return = $replies[$random];
			}
			preg_match_all('/{\((.*?)\) \?(.*?):(.*?)}/', $return, $matches, PREG_SET_ORDER, 0);
			$replace = array();
			if (is_array($matches) && count($matches) > 0) {
				foreach ($matches as $match) {
					if (count($match) != 4) {
						continue;
					}
					$replace[$match[0]] = (jeedom::evaluateExpression($match[1])) ? trim($match[2]) : trim($match[3]);
				}
			}
			return str_replace(array_keys($replace), $replace, $return);
		}
	
		public function getWidgetTemplateCode($_version = 'dashboard', $_noCustom = false) {
			if ($_version != 'scenario') return parent::getWidgetTemplateCode($_version, $_noCustom);
			list($command, $arguments) = explode('?', $this->getConfiguration('request'), 2);
			if ($command == 'sendEmbed')
				return getTemplate('core', 'scenario', 'cmd.sendEmbed', 'discordlink');
			if ($command == 'sendFile')
				return getTemplate('core', 'scenario', 'cmd.sendFile', 'discordlink');
			return parent::getWidgetTemplateCode($_version, $_noCustom);
		}

		/*     * **********************Getteur Setteur*************************** */
	}
?>	

