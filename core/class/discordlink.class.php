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
		$url = network::getNetworkAccess('internal', 'proto:127.0.0.1:port:comp') . '/plugins/discordlink/core/api/jeediscordlink.php?apikey=' . jeedom::getApiKey('discordlink');
		$log = $_debug ? '1' : '0';
		$sensor_path = realpath(dirname(__FILE__) . '/../../resources');
		$cmd = 'nice -n 19 nodejs ' . $sensor_path . '/discordlink.js ' . network::getNetworkAccess('internal') . ' ' . config::byKey('Token', 'discordlink') . ' '.log::getLogLevel('discordlink');
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
        
    }

    public function postSave() {
        
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

    public function execute($_options = array()) {
        
    }

    /*     * **********************Getteur Setteur*************************** */
}


