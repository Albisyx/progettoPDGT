<?php
	require_once(dirname(__FILE__).'/curl-lib.php');
	require_once(dirname(__FILE__).'/token.php');
    include 'accesso-db.php';
	define('api', 'https://api.telegram.org/bot'.$token.'/');

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

	function keyboard($tasti, $text, $id){
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
        	$informazioni = "🔝 Ecco le canzoni più popolari di " . $dati['nome_artista'] . " 🔝\n";

			for($i = 0; $i < count($dati['tracks']); $i++)
				$nomiCanzoni .= "<b>".($i + 1).")</b> ".$dati['tracks'][$i]."\n";

			$informazioni .= $nomiCanzoni;

			send($chat_id, $informazioni);
			$esito = true;
		}
		else
		{
			send($chat_id, 'Artista non trovato, riprovare');
			$esito = false;
		}
		return $esito;
	}

	//metodo per ottenere alcune informazioni riguardo ad un determinato artista
	function getArtistInfo($chat_id, $nomeArtista)
	{
		$url = 'https://progetto-pdgt.herokuapp.com/artist/'.urlencode($nomeArtista).'?type=info';
		$dati = http_request($url);

		if(!empty($dati))
		{
			// stringa da inviare all'utente contenente tutte le info di un'artista
			$informazioni = "Ecco alcune informazioni su <b>\"" . $dati['Nome'] . "\"</b>:\n";

			//variabili ausiliarie per comporre la stringa finale da restituire all'utente
			$followers = "💙 Followers -> <b>" . $dati['Followers'] . "</b>\n";
			$popolarita = "📊 Popolarità -> <b>" . $dati['Popolarità'] . "</b>\n";
			$link = "📍 Link a Spotify -> <b>" . $dati['Link'] . "</b>\n";
			$generi = "💽 Generi: \n";
			for($i = 0; $i < count($dati['Generi']); $i++)
				$generi .= "<b>       - ".$dati['Generi'][$i]."</b>\n";

			// composizione della risposta
			$informazioni .= $followers;
			$informazioni .= $popolarita;
			$informazioni .= $link;
			$informazioni .= $generi;

			send($chat_id, $informazioni);
			return true;
		}
		else
		{
			send($chat_id, 'Artista non trovato, riprovare!');
			return false;
		}
	}

	// metodo che si interfaccia al percorso dell'API ?/new-releases
	function getNewReleases()
	{
		$url = 'https://progetto-pdgt.herokuapp.com/new-releases';
		$dati = http_request($url);

		$nuoveUscite = "💿 Ecco a te 5 nuovi album 💿\n";

		for($i = 0; $i < count($dati['albums']); $i++)
		{
			$item = $dati['albums'][$i];
			$nuoveUscite .= "<b>Tipo 🎶:</b> " . $item['Tipo album'] . "\n";
			$nuoveUscite .= "<b>Nome 📄:</b> " . $item['Nome'] . "\n";
			$nuoveUscite .= "<b>Data di rilascio 📅:</b> " . $item['Data di rilascio'] . "\n";
			$nuoveUscite .= "<b>Link 📍:</b> " . $item['Link'] . "\n";
			//separo con una linea vuota, un nuovo album dal successivo
			$nuoveUscite .= "\n";
		}

		send($GLOBALS['cid'], $nuoveUscite);
	}

	// funzione che si interfaccia al percorso dell'API /lyrics che restituisce il testo di una canzone
	function getLyrics($tipoRichiesta, $nomeCanzone, $nomeArtista = '')
	{
		$url = 'https://progetto-pdgt.herokuapp.com/lyrics';

		switch($tipoRichiesta)
		{
			case 1:
				$url .= '?track_name='.urlencode($nomeCanzone);
				$dati = http_request($url);
				if(!$dati['error'])
				{
					$artista = $dati['artist'];
					$testo = $dati['lyrics'];

					$messaggio = "Ecco il testo per ".$artista." - ".$nomeCanzone.":\n\n".$testo;
					send($GLOBALS['cid'], $messaggio);
					$esito =  true;
				}
				else
				{
					send($GLOBALS['cid'], "Canzone non trovata");
					$esito = false;
				}
				break;
			case 2:
				$url .= '?artist='.urlencode($nomeArtista).'&track_name='.urlencode($nomeCanzone);
				$dati = http_request($url);

				if(!$dati['error'])
				{
					$testo = $dati['lyrics'];

					$messaggio = "Ecco il testo per ".$nomeArtista." - ".$nomeCanzone.":\n\n".$testo;
					send($GLOBALS['cid'], $messaggio);
					$esito =  true;
				}
				else
				{
					send($GLOBALS['cid'], "Canzone non trovata");
					$esito = false;
				}				
				break;
		}
		return $esito;
	}
?>