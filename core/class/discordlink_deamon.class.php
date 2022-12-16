<?php

	class discordlink_deamon
	{

		private static function client() {
			//$url = 'http://' . config::byKey('internalAddr') . ':3466/api/request';
			$url = 'http://192.168.1.140:3466/api/request';
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

			$headers = array(
				"Accept: application/json",
				"Content-Type: application/json",
			);
			curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

			return $curl;
		}

		public static function request($data) {
			$curl = self::client();

			log::add('discordlink', 'error', 'request = '.json_encode($data));

			curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));

			$reponse = curl_exec($curl);
			curl_close($curl);

			return $reponse;
		}
	}