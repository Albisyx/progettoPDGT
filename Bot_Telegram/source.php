<?php
	define('api', 'https://api.telegram.org/bot'.token.'/');
	require_once(dirname(__FILE__).'/curl-lib.php');
	require_once(dirname(__FILE__).'./token.php');

	$data = file_get_contents('php://input');
	$update = json_decode($data, true);

	$message = $update["message"];
	$text = $message["text"];
	$cid = $update["message"]["from"]["id"];
	$from = $message["from"];
	$name = $from["first_name"];

	function apiRequest($metodo){
		$req = http_request(api.$metodo);
		return $req;
	}

	function send($id, $text){
		if (strpos($text, "\n")) {
			$text = urlencode($text);
		}
		return apiRequest("sendMessage?text=$text&parse_mode=HTML&chat_id=$id");
	}

	function keyboard($tasti, $text, $id)
	{
		$tasti_ric = $tasti;
		$decod_tasti = json_encode($tasti_ric);

		if (strpos($text, "\n")) {
			$text = urlencode($text);
		}
		apiRequest("sendMessage?text=$text&parse_mode=Markdown&chat_id=$id&reply_markup=$decod_tasti");
	}
?>