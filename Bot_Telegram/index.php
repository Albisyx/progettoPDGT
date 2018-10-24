<?php
	include 'source.php';

	switch ($text) {
		case "/start":
			if (checkIfUserExists($cid)) 
				send($cid, "Bentornato $name in\n📀 MusicLyricBot 📀");
			else
				send($cid, "Benvenuto $name in\n📀 MusicLyricBot 📀\nSe serve aiuto digita <b>/help</b>");
				
			tastieraPrincipale("Ecco le funzioni del bot");
			break;
        case "/tastiera":
			tastieraPrincipale("Ecco le funzioni del bot");
			break;
		case "Artista 🎤":
			$keyboard = [
            				["Canzoni più popolari 🔝", "Info 📰"],
            				["Indietro 🔙"],
                        ];

            markupKeyboard("Cosa vuoi sapere\ndi un artista?", $keyboard);
			break;
		case "Canzoni più popolari 🔝":
			send($cid, "Di quale artista vuoi\ntrovare le canzoni\npiù popolari?");
            update_state($cid, 1);
			break;
		case "Info 📰":
			send($cid, "Di quale artista\nvuoi informazioni?");
            update_state($cid, 2);
			break;
		case "Nuove uscite 🕒":
            getNewReleases();
            tastieraPrincipale("Serve altro?");
			break;
		case "Testo canzone 📜":
			$messaggio = "Per trovare il taesto di una canzone\npuoi procedere in due modi:\n"
						  ."<b>1)</b> inserendo solo il nome della <i>canzone</i>\n"
						  ."<b>2)</b> inserendo sia il nome della <i>canzone</i> che quello dell'<i>artista</i>\n\n"
						  ."Per l'opzione 2, è necessario attenersi a questo formato" 
						  ." -> <b>nome_artista:nome_canzone</b>";
			send($cid, $messaggio);
			update_state($cid, 3);
			break;
		case "Ascolta musica 🎶":
			send($cid, "Quale canzone vuoi ascoltare?");
			update_state($cid, 4);
			break;
		case "Indietro 🔙":
			tastieraPrincipale("Pagina iniziale");
			if(getState($cid) != 0)
				\update_state($cid, 0);
			break;
		case "Si ✅":
			if(getState($cid) == 3 || getState($cid) == 4)
			{
				update_state($cid, 0);
				tastieraPrincipale("Perfetto!\nEcco altre cose che poi fare:");
			}
			else
				tastieraPrincipale("Comando non disponibile\nin questa situazione");
			break;
		case "No ❌":
			if(getState($cid) == 3 || getState($cid) == 4)
				send($cid, "Ok, riproviamo allora!");
			else
				tastieraPrincipale("Comando non disponibile\nin questa situazione");
			break;
		default:
            $state = getState($cid);
            $esito = false;
            switch($state)
            {
                case 1:
                    $esito = topTracks($cid, $text);
                    break;
                case 2:
                    $esito = getArtistInfo($cid, $text);
                    break;
                case 3:
               		$esito = getLyrics($text);
               		break;
               	case 4:
               		$esito = listenTrack($text);
               		break;
                default:
                    send($cid, "❌ Elemento non trovato ❌\nPremi su /tastiera per accedere alle funzioni del bot");
            }
            if(($state == 3 || $state == 4) && $esito)
            {
            	$keyboard = [
            					["Si ✅", "No ❌"],
            					["Indietro 🔙"],
                        	];

               	markupKeyboard("La canzone trovata,\nè quella che stavi cercando?", $keyboard);
            }
            else if($esito){
            	tastieraPrincipale("Serve altro?");
            	update_state($cid, 0);
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
	
	function tastieraPrincipale($messaggio)
	{
		$keyboard = [
            			["Artista 🎤", "Nuove uscite 🕒"],
                       	["Ascolta musica 🎶", "Testo canzone 📜"],
                    ];

        markupKeyboard($messaggio, $keyboard);
	}
?>