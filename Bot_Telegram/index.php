<?php
	include 'source.php';

	switch ($text) {
		case "/start":
			if (checkIfUserExists($cid)) 
				send($cid, "Bentornato in 📀 MusicLyricBot 📀, $name");
			else
				send($cid, "Benvenuto $name \nin 📀 MusicLyricBot 📀\nSe serve aiuto digita /help");
				
			$keyboard = [
            				["Artista 🎤", "Genere 🎵"],
                        	["Nuove uscite 🕒", "Testo canzone 📜"],
                        	["Ascolta musica 🎶"],
                        ];

            markupKeyboard("Ecco le funzioni del bot", $keyboard);
			break;
        case "/tastiera":
			$keyboard = [
            				["Artista 🎤", "Genere 🎵"],
                        	["Nuove uscite 🕒", "Testo canzone 📜"],
                        	["Ascolta musica 🎶"],
                        ];

            markupKeyboard("Ecco le funzioni del bot", $keyboard);
			break;
		case "Artista 🎤":
			$keyboard = [
            				["Canzoni più popolari 🔝", "Info 📰"],
            				["Indietro 🔙"],
                        ];

            markupKeyboard("Cosa vuoi sapere\ndi un artista?", $keyboard);
			break;
		case "Canzoni più popolari 🔝":
			send($cid, "Di che artista vuoi\ntrovare le canzoni\npiù popolari ?");
            update_state($cid, 1);
			break;
		case "Info 📰":
			send($cid, "Di quale artista\nvuoi informazioni ?");
            update_state($cid, 2);
			break;
		case "Genere 🎵":
			send($cid, "Che genere musicale\nstai cercando ?");
			break;
		case "Nuove uscite 🕒":
            getNewReleases();
			break;
		case "Testo canzone 📜":
			$keyboard = [
            				["Digita il nome della canzone 🎼"],
            				["Digita il nome dell'artista 👱 e la canzone 🎼"],
            				["Indietro 🔙"],
                        ];

            markupKeyboard("Seleziona una\nmodalità di ricerca!", $keyboard);
			break;
		case "Digita il nome della canzone 🎼":
			send($cid, "Che canzone vuoi cercare ?");
			update_state($cid, 3);
			break;
		case "Digita il nome dell'artista 👱 e la canzone 🎼":
			send($cid, "Che artista e canzone vuoi cercare ?");
			// inserisci funzione di ricerca
			break;
		case "Ascolta musica 🎶":
			$keyboard = [
            				["Anteprima 💾", "Canzone completa 💽"],
            				["Indietro 🔙"],
                        ];

            markupKeyboard("Seleziona una\nmodalità di ascolto!", $keyboard);
			break;
		case "Anteprima 💾":
			send($cid, "Di che canzone vuoi\nascoltare l'anteprima ?");
			break;
		case "Canzone completa 💽":
			send($cid, "Che canzone vuoi ascoltare ?");
			break;
		case "Indietro 🔙":
			$keyboard = [
            				["Artista 🎤", "Genere 🎵"],
                        	["Nuove uscite 🕒", "Testo canzone 📜"],
                        	["Ascolta musica 🎶"],
                        ];

            markupKeyboard("Pagina iniziale", $keyboard);
			break;
		case "/help":
			send($cid, "Elenco comandi:\n1) /tastiera ⌨");
			break;
		default:
            $state = getState($cid);
            switch($state)
            {
                case 1:
                    $esito = topTracks($cid, $text);
                    if($esito)
                        update_state($cid, 0);
                    break;
                case 2:
                    $esito = getArtistInfo($cid, $text);
                    if($esito)
                        update_state($cid, 0);
                    break;
                case 3:
               		$esito = getLyrics(1, $text);
               		if($esito)
               			update_state($cid, 0);
               		break;
                default:
                    send($cid, "Elemento non trovato ❌\nDigita /help per aprire i comnadi.");
            }
            break;
	}

	function markupKeyboard($messaggio, $keyboard)
	{
		$key = array(
            			"resize_keyboard" => true,
            			"one_time_keyboard" => true,
                    	"keyboard" => $keyboard,
                    );

		keyboard($key, $messaggio, $GLOBALS['cid']);
	}
?>
