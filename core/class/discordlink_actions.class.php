<?php

class discordlink_actions {

	private static $LINK_FOOTER = 'Thibaut Trarbach';

    public static function lastUser(): array
	{
        $message = "";
        $userConnect_list_new = '';
        $userConnect_list = '';
        $nbEnLigne = 0;
        $nbJourAvantSupprUser = 31+30;
        $cronOk = false;
        $cron=65;
        $timeNow = date("Y-m-d H:i:s");
        $maxLine = log::getConfig('maxLineLog');
        // Récupération du niveau de log du log Connection (//100=debug | 200=info | 300=warning | 400=erreur=defaut | 1000=none)
        $level = log::getLogLevel('connection');
        $levelName = log::convertLogLevel($level);

        //Add Emojy
        $emo_warning = discordlink::addemojy("lastUser_warning",":warning:");
        $emo_mag_right = discordlink::addemojy("lastUser_mag_right",":mag_right:");
        $emo_mag = discordlink::addemojy("lastUser_mag",":mag:");
        $emo_check = discordlink::addemojy("lastUser_check",":white_check_mark:");
        $emo_internet = discordlink::addemojy("lastUser_internet",":globe_with_meridians:");
        $emo_connecter = discordlink::addemojy("lastUser_connecter",":green_circle:");
        $emo_deconnecter = discordlink::addemojy("lastUser_deconnecter",":red_circle:");
        $emo_silhouette = discordlink::addemojy("lastUser_silhouette",":busts_in_silhouette:");


        if($level > 200){
            $niveauLog = "\n"."\n".$emo_warning."Plus d'informations ? ".$emo_warning."\n"."veuillez mettre le log **connection** sur **info** dans *Configuration/Logs* (niveau actuel : **".$levelName."**)";
        } else {
            $niveauLog = "";
        }
        $delaiHorsLigne = 10;
        $var_nbUser = 0;
        foreach (user::all() as $utilisateur) {
            $var_nbUser++;
                $userConnect_Date[$var_nbUser] = $utilisateur->getOptions('lastConnection');
            if($userConnect_Date[$var_nbUser] == ""){
                $userConnect_Date[$var_nbUser] = "1970-01-01 00:00:00";
            }
            if(strtotime($timeNow) - strtotime($userConnect_Date[$var_nbUser]) < $delaiHorsLigne*60){
                $userConnect_Statut[$var_nbUser] = 'en ligne';
            }else{
                $userConnect_Statut[$var_nbUser] = 'hors ligne';
            }
            $userConnect_Name[$var_nbUser] = $utilisateur->getLogin();
            if($userConnect_list != ''){
                $userConnect_list = $userConnect_list.'|';
            }
            $userConnect_list .= $userConnect_Name[$var_nbUser].';'.$userConnect_Date[$var_nbUser].';'.$userConnect_Statut[$var_nbUser];
        }
        
        $userConnect_list_new = '';
        // Récupération des lignes du log Connection
        $logConnection_list = log::get('connection', 0, $maxLine);
        $plageRecherche = date("Y-m-d H:i:s", strtotime($timeNow)-$cron);
        $log_nbUser = 0;
        $logConnection_Name_tmp = '';
        if (is_array($logConnection_list)) {
            foreach ($logConnection_list as $value) {
                $logConnection = explode("]", $value);
                $logConnection = substr($logConnection[0], 1);
                if (strtotime($timeNow) - strtotime($logConnection) > $cron) {
                    if ($log_nbUser == 0) {
                        $message = "\n" . "**Pas de connexion** ces **" . $cron . "** dernières minutes !";
                    }
                    break;
                } else {
                    $log_nbUser++;
                    $logConnection_Date[$log_nbUser] = $logConnection;
                    $logConnection = explode(" : ", $value);
                    $logConnection_Name[$log_nbUser] = strtolower($logConnection[2]);
                    if (strpos($logConnection[1], 'clef') !== false) {
                        $logConnection_Type[$log_nbUser] = 'clef';
                    } elseif (strpos($logConnection[1], 'API') !== false) {
                        $logConnection_Type[$log_nbUser] = 'api';
                    } else {
                        $logConnection_Type[$log_nbUser] = 'navigateur';
                    }
                    if ($log_nbUser == 1) {
                        $message .= "\n" . $emo_mag_right . "__Récapitulatif de ces " . $cron . " dernières secondes :__ " . $emo_mag;
                    }
                    $nbEnLigne++;
                    $message .= "\n" . $emo_check . "**" . $logConnection_Name[$log_nbUser] . "** s'est connecté par **" . $logConnection_Type[$log_nbUser] . "** à **" . date("H", strtotime($logConnection_Date[$log_nbUser])) . "h" . date("i", strtotime($logConnection_Date[$log_nbUser])) . "**";
                    $cronOk = true;
                    $userNum = 0;
                    $nbtrouv = 0;
                    if (strpos($logConnection_Name_tmp, $logConnection_Name[$log_nbUser]) === false) {
                    } else {
                        continue;
                    }
                    $logConnection_Name_tmp = $logConnection_Name[$log_nbUser];
                    foreach ($userConnect_Name as $utilsateurName) {
                        $userNum++;
                        if ($logConnection_Name[$log_nbUser] == $userConnect_Name[$userNum]) {        ///Utilisateur déjà enregistré
                            $nbtrouv++;
                            if ($userConnect_Statut[$userNum] == 'hors ligne') {
                                $userConnect_Date[$userNum] = $logConnection_Date[$log_nbUser];
                                $userConnect_Statut[$userNum] = 'en ligne';
                            }
                        }
                        if ($userConnect_list_new != '') {
                            $userConnect_list_new = $userConnect_list_new . '|';
                        }
                        $userConnect_list_new .= $userConnect_Name[$userNum] . ';' . $userConnect_Date[$userNum] . ';' . $userConnect_Statut[$userNum];
                    }
                    if ($nbtrouv == 0) {                                                                //Utilisateur nouveau
                        $userConnect_Name[$userNum] = $logConnection_Name[$log_nbUser];
                        $userConnect_Date[$userNum] = $logConnection_Date[$log_nbUser];
                        $userConnect_Statut[$userNum] = 'en ligne';
                        if ($userConnect_list_new != '') {
                            $userConnect_list_new = $userConnect_list_new . '|';
                        }
                        $userConnect_list_new .= $userConnect_Name[$userNum] . ';' . $userConnect_Date[$userNum] . ';' . $userConnect_Statut[$userNum];
                    }
                    $userConnect_list = $userConnect_list_new;
                }
            }
        }
        
        $sessions = listSession();
        $nbSessions=count($sessions);												//nombre d'utilisateur en session actuellement
        
        $message .= "\n"."\n".$emo_mag_right."__Récapitulatif des sessions actuelles :__ ".$emo_mag;
        // Parcours des sessions pour vérifier le statut et le nombre de sessions
        $userNum=0;
        $userConnect_list_new = '';
        foreach($userConnect_Name as $value){
            $userNum++;
            $userSession=0;
            $nbtrouv = 0;
            $userConnect_Statut[$userNum] = 'hors ligne';
            $userConnect_IP[$userNum] = '';

            foreach($sessions as $id => $session){
            //foreach($sessions as $session){
                $userSession++;
                
                $userDelai = strtotime(date("Y-m-d H:i:s")) - strtotime($session['datetime']);

                if($userConnect_Name[$userNum] == $session['login']){
                    if($userDelai < $delaiHorsLigne*60){
                        $nbtrouv++;
                        $nbEnLigne++;
                        $userConnect_Statut[$userNum] = 'en ligne';
                        $userConnect_IP[$userNum] .= "\n"."-> ".$emo_internet." IP : ".$session['ip'];
                    }else{
                    }
                }			
            }
            if(date("Y-m-d",strtotime($userConnect_Date[$userNum])) == date("Y-m-d",strtotime($timeNow))){
                $heures = date("H",strtotime($userConnect_Date[$userNum]));
                $minutes = date("i",strtotime($userConnect_Date[$userNum]));
                $date = $heures."h".$minutes;
            }else{
                $nomJour = date_fr(date("l", strtotime($userConnect_Date[$userNum])));
                $numJour = date("d",strtotime($userConnect_Date[$userNum]));
                $nomMois = date_fr(date("F", strtotime($userConnect_Date[$userNum])));
                $numAnnee = date("Y",strtotime($userConnect_Date[$userNum]));
                $heures = date("H",strtotime($userConnect_Date[$userNum]));
                $minutes = date("i",strtotime($userConnect_Date[$userNum]));
                $date = $nomJour." ".$numJour." ".$nomMois." ".$numAnnee."** à **".$heures."h".$minutes;
            }
            if($nbtrouv > 0){
                $message .= "\n".$emo_connecter." **".$userConnect_Name[$userNum]."** est **en ligne** depuis **".$date."**";
                $message .= $userConnect_IP[$userNum];
            }else{
                if(strtotime($timeNow) - strtotime($userConnect_Date[$userNum]) < ($nbJourAvantSupprUser*24*60*60)){
                    $message .= "\n".$emo_deconnecter." **".$userConnect_Name[$userNum]."** est **hors ligne** (dernière connexion **".$date."**)";
                }
            }
            if($userConnect_list_new != ''){
                $userConnect_list_new = $userConnect_list_new.'|';
            }
            $userConnect_list_new .= $userConnect_Name[$userNum].';'.$userConnect_Date[$userNum].';'.$userConnect_Statut[$userNum];
            $userConnect_list=$userConnect_list_new;
        }
        
        // Préparation des tags de notification
        $titre = $emo_silhouette.'CONNEXIONS '.$emo_silhouette;
        return array(
            'Titre'=>$titre,
            'Message'=>$message.$niveauLog,
            'nbEnLigne'=>$nbEnLigne,
            'cronOk'=>$cronOk,
        );
    }

	public function deamonInfo($_options = array()) {
		$message = new discordlink_message();
		$embed = new discordlink_embed();
		$embed->setTitle('Etat des démons');
		$embed->setFooter(self::$LINK_FOOTER);
		$embed->setColor('#00ff08');

		$deamon = array(
			"ok"=>array(),
			"nok"=>array(),
		);

		foreach(plugin::listPlugin(true) as $plugin){
			if($plugin->getHasOwnDeamon() && config::byKey('deamonAutoMode', $plugin->getId(), 1) == 1) {
				$deamon_info = $plugin->deamon_info();
				if ($deamon_info['state'] != 'ok') {
					$deamon['nok'][] = array(
						'id'=>$plugin->getId(),
						'name'=>$plugin->getName()
					);
				} else {
					$deamon['ok'][] = array(
						'id'=>$plugin->getId(),
						'name'=>$plugin->getName()
					);
				}
			}
		}

		if (count($deamon['ok']) > 0) {
			$field = new discordlink_field();
			$field->setName("Deamon OK");
			$field->setInline(TRUE);

			$fieldsOK = "";
			foreach ($deamon['ok'] as $deamon) {
				$fieldsOK .= discordlink::geticon("deamon_ok") . "[". $deamon['id'] . "]" . " " . $deamon['name']. "\n";
			}

			$field->setValue($fieldsOK);
			$embed->addFields($field);
		}

		if (count($deamon['nok']) > 0) {
			$field = new discordlink_field();
			$field->setName("Deamon NOK");
			$field->setInline(TRUE);

			$fieldsNOK = "";
			foreach ($deamon['nok'] as $deamon) {
				$fieldsNOK .= discordlink::geticon("deamon_nok") . "[". $deamon['id'] . "]" . " " . $deamon['name']. "\n";
			}

			$field->setValue($fieldsNOK);
			$embed->addFields($field);
			$embed->setColor('#ff0000');
		}

		if (discordlink::testplugin('blea')) {
			$field = new discordlink_field();
			$field->setName("BLEA");
			$field->setInline(TRUE);

			$blea = "";
			$remotes = blea_remote::all();
			foreach ($remotes as $remote) {
				$last = $remote->getCache('lastupdate', '0');
				if ($last == '0' || time() - strtotime($last) > 65) {
					$blea .=  discordlink::geticon("deamon_nok") . ' Antenne : ' . $remote->getRemoteName(). '\n';
					$embed->setColor('#ff0000');
				} else {
					$blea .=  discordlink::geticon("deamon_ok") . ' Antenne : ' . $remote->getRemoteName(). '\n';
				}
			}
		}

		$message->addEmbed($embed);
		return $message;
	}

	public function dependanceInfo($_options = array()) {
		$message='';
		$colors = '#00ff08';

		foreach(plugin::listPlugin(true) as $plugin){
			if($plugin->getHasDependency()) {
				$dependency_info = $plugin->dependancy_info();
				if ($dependency_info['state'] == 'ok') {
					$message .='|'.discordlink::geticon("dep_ok").$plugin->getName().' ('.$plugin->getId().')';
				} elseif ($dependency_info['state'] == 'in_progress') {
					$message .='|'.discordlink::geticon("dep_progress").$plugin->getName().' ('.$plugin->getId().')';
					if ($colors == '#00ff08') $colors = '#ffae00';
				} else {
					$message .='|'.discordlink::geticon("dep_nok").' ('.$plugin->getId().')';
					if ($colors != '#ff0000') $colors = '#ff0000';
				}

			}
		}

		if (isset($_options['cron']) && $colors == '#00ff08') return 'truesendwithembed';
		$message=str_replace("|","\n",$message);
		$cmd = $this->getEqLogic()->getCmd('action', 'sendEmbed');
		$_options = array('Titre'=>'Etat des dépendances', 'description'=> $message, 'colors'=> $colors, 'footer'=> 'By DiscordLink');
		$cmd->execCmd($_options);
		return 'truesendwithembed';
	}

	public function globalSummary($_options = array()) {

		$objects = jeeObject::all();
		$def = config::byKey('object:summary');
		$values = array();
		$message='';
		foreach ($def as $key => $value) {
			$result ='';
			$result = jeeObject::getGlobalSummary($key);
			if ($result == '') continue;
			$message .='|'.discordlink::geticon($key).' *** '. $result.' '.$def[$key]['unit'] .' ***		('.$def[$key]['name'].')';
		}
		$message=str_replace("|","\n",$message);
		$cmd = $this->getEqLogic()->getCmd('action', 'sendEmbed');
		$_options = array('Titre'=>'Résumé général', 'description'=> $message, 'colors'=> '#0033ff', 'footer'=> 'By DiscordLink');
		$cmd->execCmd($_options);

		return 'truesendwithembed';
	}

	public function baterieglobal($_options = array()) {
		$message='null';
		$colors = '#00ff08';
		$seuil_alert = 30;
		$seuil_critique = 10;
		$nb_alert = 0;
		$nb_critique = 0;
		$nb_battery = 0;
		$nb_total = 0;
		$nb_ligne = 0;

		$eqLogics = eqLogic::all(true);
		$cmd = $this->getEqLogic()->getCmd('action', 'sendEmbed');

		foreach($eqLogics as $eqLogic)
		{
			$nb_total = $nb_total + 1;
			if((is_numeric(eqLogic::byId($eqLogic->getId())->getStatus('battery')) == 1)) {
				$nb_battery = $nb_battery + 1;
				if(eqLogic::byId($eqLogic->getId())->getStatus('battery') <= $seuil_alert) {
					if(eqLogic::byId($eqLogic->getId())->getStatus('battery') <= $seuil_critique) {

						$list_battery .= "\n".discordlink::geticon("batterie_nok").substr($eqLogic->getHumanName(), strrpos($eqLogic->getHumanName(), '[',-1) + 1, -1) . ' => __***' . eqLogic::byId($eqLogic->getId())->getStatus('battery') . "%***__";
						$nb_critique = $nb_critique + 1;
						if ($colors != '#ff0000') $colors = '#ff0000';
					} else {
						$list_battery .= "\n".discordlink::geticon("batterie_progress").substr($eqLogic->getHumanName(), strrpos($eqLogic->getHumanName(), '[',-1) + 1, -1) . ' =>  __***' . eqLogic::byId($eqLogic->getId())->getStatus('battery') . "%***__";
						$nb_alert = $nb_alert + 1;
						if ($colors == '#00ff08') $colors = '#ffae00';
					}
				} else {
					$list_battery = $list_battery . "\n" .discordlink::geticon("batterie_ok"). substr($eqLogic->getHumanName(), strrpos($eqLogic->getHumanName(), '[',-1) + 1, -1) . ' =>  __***' . eqLogic::byId($eqLogic->getId())->getStatus('battery') . "%***__";
				}

				if ($nb_ligne == 20) {
					$message = $list_battery;
					$message=str_replace("|","\n",$message);
					$_options = array('Titre'=>'Résumé Batteries : ', 'description'=> $message, 'colors'=> $colors, 'footer'=> 'By DiscordLink');
					$cmd->execCmd($_options);
					$nb_ligne = 0;
					$list_battery = '';
					$message = '';
				}
				$nb_ligne++;
			}
		}

		$message = $list_battery;
		$message=str_replace("|","\n",$message);
		$_options = array('Titre'=>'Résumé Batteries : ', 'description'=> $message, 'colors'=> $colors, 'footer'=> 'By DiscordLink');
		$cmd->execCmd($_options);

		$message2 = "Batterie en alerte : __***" . $nb_alert . "***__\n Batterie critique : __***".$nb_critique."***__";

		$message2=str_replace("|","\n",$message2);
		$_options2 = array('Titre'=>'Résumé Batterie', 'description'=> $message2, 'colors'=> $colors, 'footer'=> 'By DiscordLink');
		$cmd->execCmd($_options2);

		return 'truesendwithembed';
	}

	public function objectSummary($_options = array()) {

		$idobject = $_options['select'];
		log::add('discordlink', 'debug', 'idobject : '.$idobject);
		$object = jeeObject::byId($idobject);
		$def = config::byKey('object:summary');
		$message='';
		foreach ($def as $key => $value) {
			$result = '';
			$result = $object->getSummary($key);
			if ($result == '') continue;
			$message .='|'.discordlink::geticon($key).' *** '. $result.' '.$def[$key]['unit'] .' ***		('.$def[$key]['name'].')';
		}
		$message=str_replace("|","\n",$message);
		$cmd = $this->getEqLogic()->getCmd('action', 'sendEmbed');
		$_options = array('Titre'=>'Résumé : '.$object->getname(), 'description'=> $message, 'colors'=> '#0033ff', 'footer'=> 'By DiscordLink');
		$cmd->execCmd($_options);

		return 'truesendwithembed';
	}

	public function zwave($_options = array()) {

		if (discordlink::testplugin('openzwave')) {
			$message = '';
			$colors = '#00ff08';
			$maxTime = $this->getEqLogic()->getConfiguration('TempMax', 43200);
			$_format = 'Y-m-d H:M:S';
			$eqLogics = eqLogic::byType('openzwave');
			foreach($eqLogics as $eqLogic) {
				$zwaveexclude = $this->getEqLogic()->getConfiguration('zwaveIdExclude', '');
				if (!(strpos($zwaveexclude, $eqLogic->getLogicalId()) !== false)) {
					$maxDate = date($_format, "1970-1-1 00:00:00");
					$collectDate = strtotime($eqLogic->getStatus('lastCommunication', date($_format)));
					//$scenario->setLog( 'Commande ' . $cmd->getHumanName() . ' - ' . $collectDate);
					$maxDate = max($maxDate, $collectDate);
					$elapsedTime = time() - $maxDate;
					if ($elapsedTime >= $maxTime) {
						$message .= "|".discordlink::geticon("zwave_nok"). " ". $eqLogic->getName(). ' ('.$elapsedTime.')';
						if ($colors != '#ff0000') $colors = '#ff0000';
					} else {
						$message .= "|".discordlink::geticon("zwave_ok"). " ". $eqLogic->getName().' ('.$elapsedTime.')';
					}
				}
			}
			// log fin de traitement
			if (isset($_options['cron']) && $colors == '#00ff08') return 'truesendwithembed';
			$message=str_replace("|","\n",$message);
			$cmd = $this->getEqLogic()->getCmd('action', 'sendEmbed');
			$_options = array('Titre'=>'Zwave Info ', 'description'=> $message, 'colors'=> $colors, 'footer'=> 'By DiscordLink');
			$cmd->execCmd($_options);
		}
		return 'truesendwithembed';
	}

	public function centreMsg($_options = array()) {
	/*
		// Parcours de tous les Updates
		$listUpdate = "";
		$nbMaj = 0;
		$msgBloq = "";
		$nbMajBloq = 0;
		foreach (update::all() as $update) {
			$monUpdate = $update->getName();
			$statusUpdate = strtolower($update->getStatus());
			$configUpdate = $update->getConfiguration('doNotUpdate');
			if ($configUpdate == 1) {
				$configUpdate = " **(MaJ bloquée)**";
				$nbMajBloq++;
			}else{$configUpdate = "";}
			if ($statusUpdate == "update") {
				$nbMaj++;
				if ($listUpdate == ""){ $listUpdate = $nbMaj."- ".$monUpdate.$configUpdate; } else {$listUpdate .= "\n".$nbMaj."- ".$monUpdate.$configUpdate;}
			}
		}
		if ($nbMajBloq == 0) {
			$msgBloq = "";
		} elseif ($nbMajBloq == 1) {
			$msgBloq = " (dont **".$nbMajBloq."** bloquée)";
		} else {
			$msgBloq = " (dont **".$nbMajBloq."** bloquées)";
		}

		// Message selon le nombre de mises à jours
		if ($nbMaj == 0) {
			$msg = "*Vous n'avez pas de mises à jour en attente !*";
		}elseif  ($nbMaj == 1) {
			$msg = "*Vous avez **".$nbMaj."** mise à jour en attente".$msgBloq." :*"."\n".$listUpdate;
		} else {
			$msg = "*Vous avez **".$nbMaj."** mises à jour en attente".$msgBloq." :*"."\n".$listUpdate;
		}

		$cmd = $this->getEqLogic()->getCmd('action', 'sendEmbed');
		$_options = array('Titre'=>':gear: CENTRE DE MISES A JOUR :gear:', 'description'=> $msg, 'colors'=> '#ff0000', 'footer'=> 'By jcamus86');
		$cmd->execCmd($_options);

		// -------------------------------------------------------------------------------------- //
		$msg = "";
		$nbMsg = 0;
		$nbMsgMax = 5;		//Nombre de messages par bloc de notification
		$MsgBloc = 1;
		$listMessage = message::all();
		foreach ($listMessage as $message){
			$nbMsg++;
			if (!($nbMsg <= $nbMsgMax)){
				$nbMsg = 1;
				$MsgBloc = $MsgBloc + 1;
			}

			$msg[$MsgBloc] .= "[".$message->getDate()."]";
			$msg[$MsgBloc] .= " (".$message->getPlugin().") :";
			$msg[$MsgBloc] .= "\n" ;
			($message->getAction() != "") ? $msg[$MsgBloc] .= " (Action : ".$message->getAction().")" : null;
			$msg[$MsgBloc] .= " ".$message->getMessage()."\n";
			$msg[$MsgBloc] .= "\n" ;
			$msg[$MsgBloc] = html_entity_decode($msg[$MsgBloc], ENT_QUOTES | ENT_HTML5);
		}

		// Message selon le nombre de messages
		if ($nbMsg == 0){
			$i=1;
			$msg = "*Le centre de message est vide !*";
			$cmd = $this->getEqLogic()->getCmd('action', 'sendEmbed');
			$_options = array('Titre'=>':clipboard: CENTRE DE MESSAGES :clipboard:', 'description'=> $msg, 'colors'=> '#ff8040', 'footer'=> 'By Jcamus86');
			$cmd->execCmd($_options);
		}else{
			$i=0;
			foreach ($msg as $value){
				$i++;
				$cmd = $this->getEqLogic()->getCmd('action', 'sendEmbed');
				$_options = array('Titre'=>':clipboard: CENTRE DE MESSAGES '.$i.'/'.count($msg).' :clipboard:', 'description'=> $value, 'colors'=> '#ff8040', 'footer'=> 'By Jcamus86');
				$cmd->execCmd($_options);
			}
		}

		return 'truesendwithembed';
	*/
	}

	public function lastUser2($_options = array()) {
		$result = discordMsg::LastUser();
		if (isset($_options['cron']) && !$result['cronOk']) return 'truesendwithembed';
		$message=str_replace("|","\n",$result['Message']);
		$cmd = $this->getEqLogic()->getCmd('action', 'sendEmbed');
		$_options = array('Titre'=>$result['Titre'], 'description'=> $message, 'colors'=> '#ff00ff', 'footer'=> 'By Yasu et Jcamus86');
		$cmd->execCmd($_options);
		return 'truesendwithembed';
	}


	public function test() {
		$message = new discordlink_message();

		// Embed
		$embed = new discordlink_embed();
		$embed->setColor('#ff5648');
		$embed->setFooter('Demo Noodom');
		$embed->setDescription('On fait un super embed pour les utilisateurs de discordlink');
		$embed->setUrl('https://jeemate.ft');
		$embed->setTitle('Demon Embed Code');
		$embed->setThumbnail('https://st.depositphotos.com/1428083/2946/i/600/depositphotos_29460297-stock-photo-bird-cage.jpg');

		// Field 1
		$field1 = new discordlink_field();
		$field1->setName('Jeemate');
		$field1->setInline(TRUE);
		$field1->setValue('Is Cool');

		// Field2
		$field2 = new discordlink_field();
		$field2->setName('Jeedom Connect');
		$field2->setInline(TRUE);
		$field2->setValue('Heuuuu, Je doit repondre ??');

		// File 1
		$file1 = new discordlink_files();
		$file1->setDescription('apple-touch-icon.png');
		$file1->setName('apple-touch-icon.png');
		$file1->setAttachment('/var/www/html/apple-touch-icon.png');

		// Build Message
		$embed->addFields($field1);
		$embed->addFields($field2);
		$message->addEmbed($embed);
		//$message->addFiles($file1);

		$data = $message->build(eqLogic::byId(298)->getConfiguration('channelID'));
		$result = discordlink_deamon::request($data);

		$scenario->setlog(json_encode($result));
}
}