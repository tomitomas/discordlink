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
     * Fonction exécutée automatiquement toutes les heures par Jeedom*/
      public static function cronHourly() {
		discordlink::updateobject();
      }

    /*
     * Fonction exécutée automatiquement tous les jours par Jeedom
      public static function cronDaily() {

      }
     */



    /*     * *********************Méthodes d'instance************************* */

	public static function getchannel() {
		$deamon = discordlink::deamon_info();

		if ($deamon['state'] == 'ok') {
			$request_http = new com_http("http://" . config::byKey('internalAddr') . ":3466/getchannel");
			$request_http->setAllowEmptyReponse(true);//Autorise les réponses vides
			$request_http->setNoSslCheck(true);
			$request_http->setNoReportError(true);
			$result = $request_http->exec(3,1);//Time out à 3s 3 essais
			if (!$result) return "null";
			//$result = substr($result, 1, -1);
			log::add('discordlink', 'DEBUG', $result);
			$json = json_decode($result, true);
			return $json;
		}
	}

	public static function getinvite() {
		$deamon = discordlink::deamon_info();
		if ($deamon['state'] == 'ok') {
			$request_http = new com_http("http://" . config::byKey('internalAddr') . ":3466/getinvite");
			$request_http->setAllowEmptyReponse(true);//Autorise les réponses vides
			$request_http->setNoSslCheck(true);
			$request_http->setNoReportError(true);
			$result = $request_http->exec(3,1);//Time out à 3s 3 essais
			if (!$result) return "null";
			$result = substr($result, 1, -1);
			log::add('discordlink', 'DEBUG', $result);
			$json = json_decode($result, true);
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
		$joueA = 'Travailler main dans la main avec votre Jeedom';
		if(config::byKey('joueA', 'discordlink') != ''){
			$joueA = config::byKey('joueA', 'discordlink');
		}
		$cmd = 'nice -n 19 nodejs ' . $sensor_path . '/discordlink.js ' . network::getNetworkAccess('internal') . ' ' . config::byKey('Token', 'discordlink') . ' '.log::getLogLevel('discordlink') . ' ' . $url . ' ' . jeedom::getApiKey('discordlink') . ' ' . rawurlencode($joueA);
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

	public static function geticon($_icon) {
		$icon = "";
		if (config::byKey('themeIcon', 'discordlink') == 2) {
			switch ($_icon) {
				case 'ok':
					$icon = ":green_circle: ";
				break;
				case 'progress':
					$icon = ":orange_circle: ";
				break;
				case 'nok':
					$icon = ":red_circle: ";
				break;
				case 'mouvement':
					$icon = ":person_walking: ";
				break;
				case 'porte':
					$icon = ":door: ";
				break;
				case 'fenetre':
					$icon = ":frame_photo: ";
				break;
				case 'lumiere':
					$icon = ":bulb: ";
				break;
				case 'prise':
					$icon = ":electric_plug: ";
				break;
				case 'thermometer':
					$icon = ":thermometer: ";
				break;
				case 'tint':
					$icon = ":droplet: ";
				break;
				case 'luminosite':
					$icon = ":sunny: ";
				break;
				case 'elect':
					$icon = ":cloud_lightning: ";
				break;
				case 'other':
					$icon = ":interrobang: ";
				break;
				case 'alerte':
					$icon = ":rotating_light: ";
				break;
				case 'volet':
					$icon = ":beginner: ";
				break;
			}
		} else {
			switch ($_icon) {
				case 'ok':
					$icon = ":white_check_mark: ";
				break;
				case 'progress':
					$icon = ":arrows_counterclockwise: ";
				break;
				case 'nok':
					$icon = ":x: ";
				break;
				case 'mouvement':
					$icon = ":person_walking: ";
				break;
				case 'porte':
					$icon = ":door: ";
				break;
				case 'fenetre':
					$icon = ":frame_photo: ";
				break;
				case 'lumiere':
					$icon = ":bulb: ";
				break;
				case 'prise':
					$icon = ":electric_plug: ";
				break;
				case 'thermometer':
					$icon = ":thermometer: ";
				break;
				case 'tint':
					$icon = ":droplet: ";
				break;
				case 'luminosite':
					$icon = ":sunny: ";
				break;
				case 'elect':
					$icon = ":cloud_lightning: ";
				break;
				case 'other':
					$icon = ":interrobang: ";
				break;
				case 'alerte':
					$icon = ":rotating_light: ";
				break;
				case 'volet':
					$icon = ":beginner: ";
				break;
			}
		}
		return $icon;
	}

	public static function CreateCmd() {

		$eqLogics = eqLogic::byType('discordlink');
		foreach ($eqLogics as $eqLogic) {

			$TabCmd = array(
				'sendMsg'=>array('Order' => 0, 'Libelle'=>'Envoi message', 'Type'=>'action', 'SubType' => 'message','request'=> 'sendMsg?message=#message#', 'visible' => 1, 'Template' => 'discordlink::message'),
				'sendMsgTTS'=>array('Order' => 1,'Libelle'=>'Envoi message TTS', 'Type'=>'action', 'SubType' => 'message', 'request'=> 'sendMsgTTS?message=#message#', 'visible' => 1, 'Template' => 'discordlink::message'),
				'sendEmbed'=>array('Order' => 2,'Libelle'=>'Envoi message évolué', 'Type'=>'action', 'SubType' => 'message', 'request'=> 'sendEmbed?color=#color#&title=#title#&url=#url#&description=#description#&field=#field#&footer=#footer#&timeout=#timeout#', 'visible' => 0),
				'sendFile'=>array('Order' => 3,'Libelle'=>'Envoi fichier', 'Type'=>'action', 'SubType' => 'message', 'request'=> 'sendFile?patch=#patch#&name=#name#&message=#message#', 'visible' => 0),
				'deamonInfo'=>array('Order' => 4,'Libelle'=>'Etat des démons', 'Type'=>'action', 'SubType'=>'other','request'=>'deamonInfo?null', 'visible' => 1),
				'dependanceInfo'=>array('Order' => 5,'Libelle'=>'Etat des dépendances', 'Type'=>'action', 'SubType'=>'other','request'=>'dependanceInfo?null', 'visible' => 1),
				'globalSummary'=>array('Order' => 6,'Libelle'=>'Résumé général', 'Type'=>'action', 'SubType'=>'other','request'=>'globalSummary?null', 'visible' => 1),
				'objectSummary'=>array('Order' => 7,'Libelle'=>'Résumé Par Object', 'Type'=>'action', 'SubType'=>'select','request'=>'objectSummary?null', 'visible' => 1),
				'batteryinfo'=>array('Order' => 8,'Libelle'=>'Résumé des batterie', 'Type'=>'action', 'SubType'=>'other','request'=>'batteryinfo?null', 'visible' => 1),
				'1oldmsg'=>array('Order' => 9,'Libelle'=>'Dernier message', 'Type'=>'info', 'SubType'=>'string', 'visible' => 1),
				'2oldmsg'=>array('Order' => 10,'Libelle'=>'Avant dernier message', 'Type'=>'info', 'SubType'=>'string', 'visible' => 1),
				'3oldmsg'=>array('Order' => 11,'Libelle'=>'Avant Avant dernier message', 'Type'=>'info', 'SubType'=>'string', 'visible' => 1)
			);
			
			//Chaque commande
			foreach ($TabCmd as $CmdKey => $Cmd){
				$Cmddiscordlink = $eqLogic->getCmd(null, $CmdKey);

				if (!is_object($Cmddiscordlink) ) {
					$Cmddiscordlink = new discordlinkCmd();
				}

				$Cmddiscordlink->setName($Cmd['Libelle']);
				$Cmddiscordlink->setEqLogic_id($eqLogic->getId());
				$Cmddiscordlink->setType($Cmd['Type']);
				$Cmddiscordlink->setSubType($Cmd['SubType']);
				$Cmddiscordlink->setLogicalId($CmdKey);
				$Cmddiscordlink->setEventOnly(1);
				$Cmddiscordlink->setIsVisible($Cmd['visible']);
				if ($Cmd['Type'] == "action" && $CmdKey != "deamonInfo") {
					$Cmddiscordlink->setConfiguration('request', $Cmd['request']);
					$Cmddiscordlink->setConfiguration('value', 'http://' . config::byKey('internalAddr') . ':3466/' . $Cmd['request'] . "&channelID=" . $eqLogic->getConfiguration('channelid'));
				}
				if ($Cmd['Type'] == "action" && $CmdKey == "deamonInfo") {
					$Cmddiscordlink->setConfiguration('request', $Cmd['request']);
					$Cmddiscordlink->setConfiguration('value', $Cmd['request']);
				}

				$Cmddiscordlink->setDisplay('generic_type','GENERIC_INFO');
				if (!empty($Cmd['Template'])) {
					$Cmddiscordlink->setTemplate("dashboard", $Cmd['Template']);
					$Cmddiscordlink->setTemplate("mobile", $Cmd['Template']);
				}
				$Cmddiscordlink->setOrder($Cmd['Order']);
				$Cmddiscordlink->setDisplay('message_placeholder', 'Message a envoyer sur discord');
				$Cmddiscordlink->setDisplay('forceReturnLineBefore', true);
				$Cmddiscordlink->save();
			}
		}
	}

    public function postSave() {
		discordlink::CreateCmd();
		discordlink::updateobject();
	}

    public function preUpdate() {

    }

    public function postUpdate() {
    }

    public function preRemove() {

    }

    public function postRemove() {

    }

	public function updateobject() {

		$listValue = '';
		foreach(jeeObject::all() as $object) {
			$listValue .= $object->getId()."|".$object->getName().";";
		}

		if ($listValue != '') {
			$eqLogics = eqLogic::byType('discordlink');
			foreach ($eqLogics as $eqLogic) {
				$Cmddiscordlink = $eqLogic->getCmd(null, 'objectSummary');
				$Cmddiscordlink->setConfiguration('listValue', $listValue);
				$Cmddiscordlink->save();
			}
		}
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
			if ($request != 'truesendwithembed') {
				$request_http = new com_http($request);
				$request_http->setAllowEmptyReponse(true);//Autorise les réponses vides
				if ($this->getConfiguration('noSslCheck') == 1) $request_http->setNoSslCheck(true);
				if ($this->getConfiguration('doNotReportHttpError') == 1) $request_http->setNoReportError(true);
				if (isset($_options['speedAndNoErrorReport']) && $_options['speedAndNoErrorReport'] == true) {// option non activée
					$request_http->setNoReportError(true);
					$request_http->exec(0.1, 1);
					return;
				}

				$result = $request_http->exec($this->getConfiguration('timeout', 6), $this->getConfiguration('maxHttpRetry', 1));//Time out à 3s 3 essais

				if (!$result) throw new Exception(__('Serveur injoignable', __FILE__));

				return true;
			} else {
				return true;
			}
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
				case 'deamonInfo':
					$request = $this->build_deamonInfo($_options);
				break;
				case 'dependanceInfo':
					$request = $this->build_dependanceInfo($_options);
				break;
				case 'globalSummary':
					$request = $this->build_globalSummary($_options);
				break;
				case 'batteryinfo':
					$request = $this->build_baterieglobal($_options);
				break;
				case 'objectSummary':
					$request = $this->build_objectSummary($_options);
				break;
				default:
					$request = '';
				break;
			}
			if ($request != 'truesendwithembed') {
				$request = scenarioExpression::setTags($request);
				if (trim($request) == '') throw new Exception(__('Commande inconnue ou requête vide : ', __FILE__) . print_r($this, true));
				$channelID=str_replace("_player", "", $this->getEqLogic()->getConfiguration('channelid'));
				return 'http://' . config::byKey('internalAddr') . ':3466/' . $request . '&channelID=' . $channelID;
			} else {
				return $request;
			}
		}

		private function build_ControledeSliderSelectMessage($_options = array(), $default = "Une erreur est survenu") {

			$request = $this->getConfiguration('request');
			if ((isset($_options['message'])) && ($_options['message'] == "")) $_options['message'] = $default;
			if (!(isset($_options['message']))) $_options['message'] = "";
			$request = str_replace(array('#message#'),
			array(urlencode(self::decodeTexteAleatoire($_options['message']))), $request);
			log::add('discordlink_node', 'info', '---->RequestFinale:'.$request);
			return $request;
		}

		private function build_ControledeSliderSelectFile($_options = array(), $default = "Une erreur est survenu") {
			$patch = "null";
			$nameFile = "null";
			$message = "null";

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
				$message = $_options['message'];

			} else {
				$patch = $_options['patch'];
				$nameFile = $_options['Name_File'];
			}

			$request = str_replace(array('#message#'),
			array(urlencode(self::decodeTexteAleatoire($message))), $request);
			$request = str_replace(array('#name#'),
			array(urlencode(self::decodeTexteAleatoire($nameFile))), $request);
			$request = str_replace(array('#patch#'),
			array(urlencode(self::decodeTexteAleatoire($patch))), $request);

			log::add('discordlink_node', 'info', '---->RequestFinale:'.$request);
			return $request;
		}

		private function build_ControledeSliderSelectEmbed($_options = array(), $default = "Une erreur est survenu") {

			$request = $this->getConfiguration('request');

			$titre = "null";
			$url = "null";
			$description = "null";
			$footer = "null";
			$field = "null";
			$colors = "null";
			$timeout = "null";

			if (isset($_options['answer'])) {
				if (("" != ($_options['title']))) $titre = $_options['title'];
				$colors = "#1100FF";

				$answer = $_options['answer'];
				$timeout = $_options['timeout'];
				$description = "";

				$a = 0;
				$url = "[";
				$choix = [":regional_indicator_a:",":regional_indicator_b:",":regional_indicator_c:",":regional_indicator_d:",":regional_indicator_e:",":regional_indicator_f:",":regional_indicator_g:",":regional_indicator_h:",":regional_indicator_i:",":regional_indicator_j:",":regional_indicator_k:",":regional_indicator_l:",":regional_indicator_m:",":regional_indicator_n:",":regional_indicator_o:",":regional_indicator_p:",":regional_indicator_q:",":regional_indicator_r:",":regional_indicator_s:",":regional_indicator_t:",":regional_indicator_u:",":regional_indicator_v:",":regional_indicator_w:",":regional_indicator_x:",":regional_indicator_y:",":regional_indicator_z:"];
				while ($a < count($answer)) {
					$description .=	$choix[$a] . " : ". $answer[$a];
					$description .= "
";
					$url .= '"'.$answer[$a].'",';
					$a ++;
				}
				$url = rtrim($url, ',');
				$url .= ']';
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
			$request = str_replace(array('#timeout#'),
			array(urlencode(self::decodeTexteAleatoire($timeout))), $request);

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

		public function build_deamonInfo($_options = array()) {
			$message='';
			$colors = '#00ff08';

			foreach(plugin::listPlugin(true) as $plugin){
				if($plugin->getHasOwnDeamon() && config::byKey('deamonAutoMode', $plugin->getId(), 1) == 1) {
					$deamon_info = $plugin->deamon_info();
					if ($deamon_info['state'] != 'ok') {
						$message .='|'.discordlink::geticon("nok").$plugin->getName().' ('.$plugin->getId().')';
						if ($colors != '#ff0000') $colors = '#ff0000';
						log::add('discordlink', 'DEBUG', 'Deamon Non OK : ' . $deamon_info['state']);
					} else {
						$message .='|'.discordlink::geticon("ok").$plugin->getName().' ('.$plugin->getId().')';
						log::add('discordlink', 'DEBUG', 'Deamon OK : ' . $deamon_info['state']);
					}

				}
			}

			$message=str_replace("|","\n",$message);

			$cmd = $this->getEqLogic()->getCmd('action', 'sendEmbed');

			$_options = array('Titre'=>'Etat des démons', 'description'=> $message, 'colors'=> $colors, 'footer'=> 'By DiscordLink');

			$cmd->execCmd($_options);
			return 'truesendwithembed';
		}

		public function build_dependanceInfo($_options = array()) {
			$message='';
			$colors = '#00ff08';

			foreach(plugin::listPlugin(true) as $plugin){
				if($plugin->getHasDependency()) {
					$dependency_info = $plugin->dependancy_info();
					if ($dependency_info['state'] == 'ok') {
						$message .='|'.discordlink::geticon("ok").$plugin->getName().' ('.$plugin->getId().')';
						log::add('discordlink', 'DEBUG', 'Dependance OK : ' . $dependency_info['state']);
					} elseif ($dependency_info['state'] == 'in_progress') {
						$message .='|'.discordlink::geticon("progress").$plugin->getName().' ('.$plugin->getId().')';
						if ($colors == '#00ff08') $colors = '#ffae00';
						log::add('discordlink', 'DEBUG', 'Dependance En cours d\'install : ' . $dependency_info['state']);
					} else {
						$message .='|'.discordlink::geticon("nok").' ('.$plugin->getId().')';
						if ($colors != '#ff0000') $colors = '#ff0000';
						log::add('discordlink', 'DEBUG', 'Dependance Non OK : ' . $dependency_info['state']);
					}

				}
			}

			$message=str_replace("|","\n",$message);
			$cmd = $this->getEqLogic()->getCmd('action', 'sendEmbed');
			$_options = array('Titre'=>'Etat des dépendances', 'description'=> $message, 'colors'=> $colors, 'footer'=> 'By DiscordLink');
			$cmd->execCmd($_options);
			return 'truesendwithembed';
		}

		public function build_globalSummary($_options = array()) {

			$objects = jeeObject::all();
			$def = config::byKey('object:summary');
			$values = array();
			$message='';

			foreach ($def as $key => $value) {

				$result ='';

				log::add('discordlink', 'debug', 'test : '.$def[$key]['name']);
				log::add('discordlink', 'debug', 'Résumé général : '. $key . ' = ' . jeeObject::getGlobalSummary($key));

				$result = jeeObject::getGlobalSummary($key);
				if ($result == '') continue;

				if ($key == "motion") {
					$message .='|'.discordlink::geticon("mouvement").' *** '. $result.' ***		(Mouvements)';
				} elseif ($key == "door") {
					$message .='|'.discordlink::geticon("porte").' *** '. $result.' ***		(Portes)';
				} elseif ($key == "windows") {
					$message .='|'.discordlink::geticon("fenetre").' *** '. $result.' ***		(Fenêtres)';
				} elseif ($key == "light") {
					$message .='|'.discordlink::geticon("lumiere").' *** '. $result.' ***		(Lumières)';
				} elseif ($key == "outlet") {
					$message .='|'.discordlink::geticon("prise").' *** '. $result.' ***		(Prises)';
				} elseif ($key == "temperature") {
					$message .='|'.discordlink::geticon("thermometer").' *** '. $result.' '.$def[$key]['unit']. ' ***		(Température)';
				} elseif ($key == "humidity") {
					$message .='|'.discordlink::geticon("tint").' *** '. $result.' '.$def[$key]['unit'].' ***		(Humidité)';
				} elseif ($key == "luminosity") {
					$message .='|'.discordlink::geticon("luminosite").' *** '. $result.' '.$def[$key]['unit'].' ***		(Luminosité)';
				} elseif ($key == "power") {
					$message .='|'.discordlink::geticon("elect").' *** '. $result.' '.$def[$key]['unit'] .' ***		(Puissance)';
				} elseif ($key == "security") {
					$message .='|'.discordlink::geticon("alerte").' *** '. $result.' '.$def[$key]['unit'] .' ***		(Alerte)';
				} elseif ($key == "shutter") {
					$message .='|'.discordlink::geticon("volet").' *** '. $result.' '.$def[$key]['unit'] .' ***		(Volet)';
				} else {
					$message .='|'.discordlink::geticon("other").' *** '. $result.' '.$def[$key]['unit'] .' ***		('.$def[$key]['name'].')';
				}

			}
				$message=str_replace("|","\n",$message);
				$cmd = $this->getEqLogic()->getCmd('action', 'sendEmbed');
				$_options = array('Titre'=>'Résumé général', 'description'=> $message, 'colors'=> '#0033ff', 'footer'=> 'By DiscordLink');
				$cmd->execCmd($_options);

			return 'truesendwithembed';
		}

		public function build_baterieglobal($_options = array()) {
			$message='null';
			$colors = '#00ff08';
			$seuil_alert = 30;
			$seuil_critique = 10;
			$nb_alert = 0;
			$nb_critique = 0;
			$nb_battery = 0;
			$nb_total = 0;

			$eqLogics = eqLogic::all();

			foreach($eqLogics as $eqLogic)
			{
				$nb_total = $nb_total + 1;
				if((is_numeric(eqLogic::byId($eqLogic->getId())->getStatus('battery')) == 1) && strpos($eqLogic->getHumanName(), 'Aucun') == false) {
					$nb_battery = $nb_battery + 1;
					if(eqLogic::byId($eqLogic->getId())->getStatus('battery') <= $seuil_alert) {
						if(eqLogic::byId($eqLogic->getId())->getStatus('battery') <= $seuil_critique) { 
							$list_battery .= "\n".discordlink::geticon("nok").substr($eqLogic->getHumanName(), strrpos($eqLogic->getHumanName(), '[',-1) + 1, -1) . ' => __***' . eqLogic::byId($eqLogic->getId())->getStatus('battery') . "%***__";
							$nb_critique = $nb_critique + 1; 
							if ($colors != '#ff0000') $colors = '#ff0000';
						} else { 
							$list_battery .= "\n".discordlink::geticon("progress").substr($eqLogic->getHumanName(), strrpos($eqLogic->getHumanName(), '[',-1) + 1, -1) . ' =>  __***' . eqLogic::byId($eqLogic->getId())->getStatus('battery') . "%***__";
							$nb_alert = $nb_alert + 1;
							if ($colors == '#00ff08') $colors = '#ffae00';
						}
					} else {
						$list_battery = $list_battery . "\n" .discordlink::geticon("ok"). substr($eqLogic->getHumanName(), strrpos($eqLogic->getHumanName(), '[',-1) + 1, -1) . ' =>  __***' . eqLogic::byId($eqLogic->getId())->getStatus('battery') . "%***__";
					}
				}
			}

			$cmd = $this->getEqLogic()->getCmd('action', 'sendEmbed');

			$message = $list_battery;
			$message=str_replace("|","\n",$message);
			$_options = array('Titre'=>'Résumé Batteries : ', 'description'=> $message, 'colors'=> $colors, 'footer'=> 'By DiscordLink');
			$cmd->execCmd($_options);

			$message2 = "Battery en alerte : __***" . $nb_alert . "***__\n Battery critique : __***".$nb_critique."***__";

			$message2=str_replace("|","\n",$message2);
			$_options2 = array('Titre'=>'Résumé Batterie', 'description'=> $message2, 'colors'=> $colors, 'footer'=> 'By DiscordLink');
			$cmd->execCmd($_options2);

			return 'truesendwithembed';
		}

		public function build_objectSummary($_options = array()) {

			$idobject = $_options['select'];
			log::add('discordlink', 'debug', 'idobject : '.$idobject);
			$object = jeeObject::byId($idobject);
			$def = config::byKey('object:summary');

			$message='';
			
			foreach ($def as $key => $value) {

				$result == '';

				log::add('discordlink', 'debug', 'test : '.$def[$key]['name']);
				log::add('discordlink', 'debug', 'Résumé object : '. $key . ' = ' . $object->getSummary($key));

				$result = $object->getSummary($key);

				if ($result == '') continue;

				if ($key == "motion") {
					$message .='|'.discordlink::geticon("mouvement").' *** '. $result.' ***		(Mouvements)';
				} elseif ($key == "door") {
					$message .='|'.discordlink::geticon("porte").' *** '. $result.' ***		(Portes)';
				} elseif ($key == "windows") {
					$message .='|'.discordlink::geticon("fenetre").' *** '. $result.' ***		(Fenêtres)';
				} elseif ($key == "light") {
					$message .='|'.discordlink::geticon("lumiere").' *** '. $result.' ***		(Lumières)';
				} elseif ($key == "outlet") {
					$message .='|'.discordlink::geticon("prise").' *** '. $result.' ***		(Prises)';
				} elseif ($key == "temperature") {
					$message .='|'.discordlink::geticon("thermometer").' *** '. $result.' '.$def[$key]['unit']. ' ***		(Température)';
				} elseif ($key == "humidity") {
					$message .='|'.discordlink::geticon("tint").' *** '. $result.' '.$def[$key]['unit'].' ***		(Humidité)';
				} elseif ($key == "luminosity") {
					$message .='|'.discordlink::geticon("luminosite").' *** '. $result.' '.$def[$key]['unit'].' ***		(Luminosité)';
				} elseif ($key == "power") {
					$message .='|'.discordlink::geticon("elect").' *** '. $result.' '.$def[$key]['unit'] .' ***		(Puissance)';
				} elseif ($key == "security") {
					$message .='|'.discordlink::geticon("alerte").' *** '. $result.' '.$def[$key]['unit'] .' ***		(Alerte)';
				} elseif ($key == "shutter") {
					$message .='|'.discordlink::geticon("volet").' *** '. $result.' '.$def[$key]['unit'] .' ***		(Volet)';
				} else {
					$message .='|'.discordlink::geticon("other").' *** '. $result.' '.$def[$key]['unit'] .' ***		('.$def[$key]['name'].')';
				}

			}
				$message=str_replace("|","\n",$message);
				$cmd = $this->getEqLogic()->getCmd('action', 'sendEmbed');
				$_options = array('Titre'=>'Résumé général', 'description'=> $message, 'colors'=> '#0033ff', 'footer'=> 'By DiscordLink');
				$cmd->execCmd($_options);

			return 'truesendwithembed';
		}

		public function getWidgetTemplateCode($_version = 'dashboard', $_noCustom = false) {
			if ($_version != 'scenario') return parent::getWidgetTemplateCode($_version, $_noCustom);
			list($command, $arguments) = explode('?', $this->getConfiguration('request'), 2);
			if ($command == 'sendMsg')
				return getTemplate('core', 'scenario', 'cmd.sendMsg', 'discordlink');
			if ($command == 'sendMsgTTS')
				return getTemplate('core', 'scenario', 'cmd.sendMsgtts', 'discordlink');
			if ($command == 'sendEmbed')
				return getTemplate('core', 'scenario', 'cmd.sendEmbed', 'discordlink');
			if ($command == 'sendFile')
				return getTemplate('core', 'scenario', 'cmd.sendFile', 'discordlink');
			//if ($command == 'objectSummary')
			//	return getTemplate('core', 'scenario', 'cmd.objectSummary', 'discordlink');
			return parent::getWidgetTemplateCode($_version, $_noCustom);
		}
		/*     * **********************Getteur Setteur*************************** */
	}
?>