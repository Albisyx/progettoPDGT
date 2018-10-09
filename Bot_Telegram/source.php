<?php
	require_once(dirname(__FILE__).'/curl-lib.php');
	require_once(dirname(__FILE__).'/token.php');
	require_once(dirname(__FILE__).'/meekrodb.2.3.class.php');
	//inizializzo parametri per la connessione al database
	DB::$user = 'albertospadoni';
	DB::$dbName = 'my_albertospadoni';

	define('api', 'https://api.telegram.org/bot'.token.'/');

	$data = file_get_contents('php://input');
	$update = json_decode($data, true);

	$message = $update["message"];
	$text = $message["text"];
	$cid = $update["message"]["from"]["id"];
	$from = $message["from"];
	$name = $from["first_name"];
	$username = $from['username'];

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

	function topTracks($chat_id, $nomeArtista)
	{
		$url = 'https://progetto-pdgt.herokuapp.com/artist/'.urlencode($nomeArtista).'?type=top-tracks';
		$dati = http_request($url);
		
		if(!empty($dati))
		{
			for($i = 0; $i < count($dati['tracks']); $i++)
				$nomiCanzoni .= "<b>".($i + 1).")</b> ".$dati['tracks'][$i]."\n";

			send($chat_id, $nomiCanzoni);
		}
		else
			send($chat_id, 'Artista non trovato, riprovare');
	}

	//==================================================
	// METODI PER INTERAGIRE COL DATABASE DI ALTERVISTA
	//==================================================

	// metodo che inserisce nel database la chat_id e l'username di un utente
	// se questi dati non esistono già
	function registerUserIfNot($chat_id)
	{
		//controllo se l'utente è già registrato
		$results = DB::query("SELECT username FROM utenti WHERE chatID=$chat_id");
		if(!$results)
			//registro l'utente
			DB::query("INSERT INTO utenti VALUES (%d, %s, %d);", $chat_id, $GLOBALS['username'], 0);
	}

	//metodo per aggiornare lo stato di un determinato utente
	function updateState()
?>