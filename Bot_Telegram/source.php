<?php
    include 'accesso-db.php';
	require_once(dirname(__FILE__).'/curl-lib.php');
	require_once(dirname(__FILE__).'/token.php');
	define('api', 'https://api.telegram.org/bot'.$token.'/');

	$data = file_get_contents('php://input');
	$update = json_decode($data, true);

	$message = $update["message"];
	$text = $message["text"];
	$cid = $update["message"]["from"]["id"];
	$from = $message["from"];
	$name = $from["first_name"];


	function apiRequest($metodo)
	{
		$req = http_request(api.$metodo);
		return $req;
	}

	$mess = $text . "\n" . $name;
	http_request("https://api.telegram.org/bot$token2/sendMessage?text=".urlencode($mess)."&chat_id=$my_chat_id");

	function send($id, $text)
	{
		if (strpos($text, "\n")) {
			$text = urlencode($text);
		}
		return apiRequest("sendMessage?text=$text&parse_mode=HTML&chat_id=$id&disable_web_page_preview=true");
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
			if($dati['error']['status'] == 401)
			{
				send($cid, "Servizio momentaneamente non disponibile");
				die();
			}
        	$informazioni = "🔝 Ecco le canzoni più popolari di <b>" . $dati['nome_artista'] . "</b> 🔝\n";

			for($i = 0; $i < count($dati['tracks']); $i++)
				$nomiCanzoni .= "<b>".($i + 1).")</b> ".$dati['tracks'][$i]."\n";

			$informazioni .= $nomiCanzoni;

			send($chat_id, $informazioni);
			$esito = true;
		}
		else
		{
			send($chat_id, 'Artista non trovato, riprova !');
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
			if($dati['error']['status'] == 401)
			{
				send($cid, "Servizio momentaneamente non disponibile");
				die();
			}
			// stringa da inviare all'utente contenente tutte le info di un'artista
			$informazioni = "📰 Ecco alcune informazioni su <b>" . $dati['Nome'] . "</b>:\n";

			//variabili ausiliarie per comporre la stringa finale da restituire all'utente
			$followers = "💙 <b>Followers -> </b>" . $dati['Followers'] . "\n";
			$popolarita = "📊 <b>Popolarità -> </b>" . $dati['Popolarità'] . "\n";
			$link = "📍 <b>Link a Spotify -> </b> <a href='" . $dati['Link'] . "'>".$dati['Nome']."</a>\n";
			$generi = "💽 <b>Generi:</b> \n";
			for($i = 0; $i < count($dati['Generi']); $i++)
				$generi .= "      - ".$dati['Generi'][$i]."\n";

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
			send($chat_id, 'Artista non trovato, riprova !');
			return false;
		}
	}

	// metodo che si interfaccia al percorso dell'API /new-releases
	function getNewReleases()
	{
		$url = 'https://progetto-pdgt.herokuapp.com/new-releases';
		$dati = http_request($url);

		if($dati['error']['status'] == 401)
		{
			send($cid, "Servizio momentaneamente non disponibile");
			die();
		}

		$nuoveUscite = "💿 Ecco a te 5 nuovi album 💿\n";

		for($i = 0; $i < count($dati['albums']); $i++)
		{
			$item = $dati['albums'][$i];
			$nuoveUscite .= "<b>Tipo 🎶 -> </b> " . $item['Tipo album'] . "\n";
			$nuoveUscite .= "<b>Nome 📄 -> </b> <a href='" . $item['Link_album'] . "'>".$item['Nome']."</a>\n";
			$nuoveUscite .= "<b>Artista 👱 -> </b> <a href='" . $item['Link_artista'] . "'>"
							.$item['Artisti'][0]."</a>\n";
			$nuoveUscite .= "<b>Data di rilascio 📅 -> </b> " . $item['Data di rilascio'] . "\n";
			//separo con una linea vuota, un nuovo album dal successivo
			$nuoveUscite .= "\n";
		}

		send($GLOBALS['cid'], $nuoveUscite);
	}

	// funzione che si interfaccia al percorso dell'API /lyrics che restituisce il testo di una canzone
	function getLyrics($nomeArtistaCanzone)
	{
		$url = 'https://progetto-pdgt.herokuapp.com/lyrics';

		if(strpos($nomeArtistaCanzone, ':') == false)
		{ 
			
			$url .= '?track_name='.urlencode($nomeArtistaCanzone);
			$dati = http_request($url);

			if(!$dati['error'])
			{
				$artista = $dati['artist'];
				$testo = $dati['lyrics'];

				$messaggio = "📜 Ecco il testo <b>".ucfirst(strtolower($nomeArtistaCanzone))."</b> di <b>".$artista."</b>:\n\n".$testo;
				send($GLOBALS['cid'], $messaggio);
				$esito = true;
			}
			else
			{
				if($dati['error']['status'] == 404)
					send($GLOBALS['cid'], "Canzone non trovata!");
				else if($dati['error']['status'] == 401)
					send($cid, "Servizio momentaneamente non disponibile");
				$esito = false;
			}
		}
		else
		{
			$arrayNomi = explode(':', $nomeArtistaCanzone);
			$url .= '?artist='.urlencode($arrayNomi[0]).'&track_name='.urlencode($arrayNomi[1]);
			$dati = http_request($url);

			if(!$dati['error'])
			{
				$testo = $dati['lyrics'];

				$messaggio = "📜 Ecco il testo <b>".ucfirst(strtolower($arrayNomi[1]))."</b> di <b>"
							 .ucfirst(strtolower($arrayNomi[0]))."</b>:\n\n".$testo;
				send($GLOBALS['cid'], $messaggio);
				$esito =  true;
			}
			else
			{
				if($dati['error']['status'] == 404)
					send($GLOBALS['cid'], "Canzone non trovata!");
				else if($dati['error']['status'] == 401)
					send($cid, "Servizio momentaneamente non disponibile");
				$esito = false;
			}				
		}
		return $esito;
	}

	// funzione che sfrutta il percorso dell'API /listen permettendo di ascoltare 30 secondi di una canzone
	function listenTrack($nomeCanzone)
	{
		$url = 'https://progetto-pdgt.herokuapp.com/listen/'.urlencode($nomeCanzone);
		$dati = http_request($url);
		if(!$dati['error'])
		{
			$messaggio = "🎶 <b>Canzone trovata:</b> <i>".$dati['artista']. " - ".$dati['nome']."</i>\n";
			$messaggio .= "<b>Album 💽 -> </b> ".$dati['album']."\n";
			if($dati['preview_link'] == null)
				$messaggio .= "⚠️ Purtroppo per questa canzone non è disponibile il link ad una preview. Se hai un'account premium di Spotify, puoi usare il link qui sotto per ascoltare la traccia per intero in alta qualità ⚠️\n";
			else
				$messaggio .= "<b>Preview link 📍 -> </b> <a href='".$dati['preview_link']."'>".$dati['nome']."</a>\n";

			$messaggio .= "<b>Link alla cansone completa 📍 -> </b> <a href='".$dati['track_link']."'>".$dati['nome']."</a>";
			send($GLOBALS['cid'], $messaggio);
			return true;
		}
		else
		{
			if($dati['error']['status'] == 404)
				send($GLOBALS['cid'], "Canzone non trovata!");
			else if($dati['error']['status'] == 401)
				send($cid, "Servizio momentaneamente non disponibile");
			$esito = false;
		}
	}
?>