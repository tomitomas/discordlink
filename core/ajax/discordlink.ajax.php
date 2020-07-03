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

try {
    require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
    include_file('core', 'authentification', 'php');

//    if (!isConnect('admin')) {
//        throw new Exception(__('401 - Accès non autorisé', __FILE__));
//    }
    ajax::init();
    
    if (init('action') == 'saveemojy') {

        $arrayemojy = init('arrayemojy');
        $emojyconfig = array();

        foreach ($arrayemojy as $emojy) {
            $key = $emojy['keyEmojy'];
            $emojyconfig[$key] = $emojy['codeEmojy'];
        }
        //$emojy = json_encode($emojyconfig);
        config::save('emojy', $emojyconfig, 'discordlink');
        ajax::success();
    }

    if (init('action') == 'getemojy') {
        $emojyarray = config::byKey('emojy', 'discordlink');
        $emojycommandetable = array();
        foreach ($emojyarray as $key => $emojy) {
            $emojycmdligne = array('keyEmojy' => $key, 'codeEmojy' => $emojy);
            array_push($emojycommandetable,  $emojycmdligne);
        }
        $emojy = $emojycommandetable;
        ajax::success($emojy);
    }

    if (init('action') == 'getinvite') {
        $deamon = discordlink::deamon_info();
		if ($deamon['state'] == 'ok') {
			log::add('discordlink', 'debug', 'Get Invite !!');
			$request_http = new com_http("http://" . config::byKey('internalAddr') . ":3466/getinvite");
			$request_http->setAllowEmptyReponse(true);//Autorise les réponses vides
			$request_http->setNoSslCheck(true);
			$request_http->setNoReportError(true);
            $result = $request_http->exec(6,3);//Time out à 3s 3 essais
            if (!$result) ajax::success("null");
			$result = substr($result, 1, -1);
			$json = json_decode($result, true);
			log::add('discordlink', 'debug', 'Invite = '.$json['invite']);
            ajax::success($json['invite']);
        }
        ajax::success("null");
    }

    if (init('action') == 'resetemojy') {
        discordlink::setemojy(1);
        ajax::success();
    }

    throw new Exception(__('Aucune méthode correspondante à : ', __FILE__) . init('action'));
    /*     * *********Catch exeption*************** */
} catch (Exception $e) {
    ajax::error(displayException($e), $e->getCode());
}