<?php
if (!class_exists('Backplane')) {
	class Backplane {
	  var $busname;
	  var $secret;
	
	  function __construct($busname, $secret) {
		$this->busname = $busname;
		$this->secret = $secret;
	  }
	
	  function send($config) {
		  $bp_message = '[{
				"source": "%0%",
				"type": "%1%",
				"payload": {
					"context": "%2%",
					"identities": {
						"startIndex": 0,
						"itemsPerPage": 1,
						 "totalResults": 1,
						"entry": {
							"id": "%3%",
							"displayName": "%4%",
							"accounts": [{
								"identityUrl": "%5%",
								"username": "%6%",
								"photos": [{
									"value": "%7%",
									"type": "avatar"
								}]
							}]
						}
					}
				}
			}]';
			$trans = array(
				"%0%" => $config['source'],
				"%1%" => $config['type'], // "identity/login" or "identity/logout"
				"%2%" => $config['source'],
				"%3%" => $config['user_id_url'],
				"%4%" => $config['display_name'],
				"%5%" => $config['user_id_url'],
				"%6%" => $config['display_name'],
				"%7%" => $config['photo']
			);
	
			$payload = strtr($bp_message, $trans);
			//
			$ch = curl_init($config["channel"]);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_HEADER, 0);  // DO NOT RETURN HTTP HEADERS
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  // RETURN THE CONTENTS OF THE CALL
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Authorization: Basic ' . self::urlsafe_b64encode($this->busname . ':' . $this->secret),
				'Content-Type: application/x-www-form-urlencoded'
			));
			$rsp = curl_exec($ch);
			return $rsp;
		}
		public static function urlsafe_b64encode($string) {
		$data = base64_encode($string);
		return $data;
		}
	}
}
?>