<?php
	include 'source.php';
	include 'accesso-db.php';

	switch ($text) {
		case "/start":
			send($cid, "Benvenuto $name,\nin 📀 MusicLyricBot 📀");

			$keyboard = [
            				["Artista 🎤", "Genere 🎵"],
                        	["Nuove uscite 🕒", "Testo canzone 📜"],
                        	["Ascolta musica 🎶"],
                        ];

            $key = array(
            				"resize_keyboard" => true,
            				"one_time_keyboard" => true,
                            "keyboard" => $keyboard,
                        );

            keyboard($key, "Tastiera interattiva attivata !\nChe cosa vuoi cercare ?", $cid);
			break;
        case "/tastiera":
			$keyboard = [
            				["Artista 🎤", "Genere 🎵"],
                        	["Nuove uscite 🕒", "Testo canzone 📜"],
                        	["Ascolta musica 🎶"],
                        ];

            $key = array(
            				"resize_keyboard" => true,
            				"one_time_keyboard" => true,
                            "keyboard" => $keyboard,
                        );

            keyboard($key, "Che cosa vuoi cercare ?", $cid);
			break;
		case "Artista 🎤":
			$keyboard = [
            				["Canzoni più popolari 🔝", "Info 📰"],
            				["Indietro 🔙"],
                        ];

            $key = array(
            				"resize_keyboard" => true,
            				"one_time_keyboard" => true,
                            "keyboard" => $keyboard,
                        );

            keyboard($key, "Cosa vuoi sapere\ndi un artista ?",$cid);
			break;
		case "Canzoni più popolari 🔝":
			send($cid, "Di che artista vuoi\ntrovare le canzoni\npiù popolari ?");
            update_state($cid, 1);
			break;
		case "Info 📰":
			send($cid, "Di quale artista\nvuoi informazioni ?");
			break;
		case "Genere 🎵":
			send($cid, "Che genere musicale\nstai cercando ?");
			break;
		case "Nuove uscite 🕒":
			send($cid, "Queste sono le nuove\ncanzoni uscite:");
			break;
		case "Testo canzone 📜":
			send($cid, "Di che canzone vuoi\ntrovare il testo ?");
			break;
		case "Ascolta musica 🎶":
			$keyboard = [
            				["Anteprima 💾", "Canzone completa 💽"],
            				["Indietro 🔙"],
                        ];

            $key = array(
            				"resize_keyboard" => true,
            				"one_time_keyboard" => true,
                            "keyboard" => $keyboard,
                        );

            keyboard($key, "Seleziona una\nmodalità di ascolto !",$cid);
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

            $key = array(
            				"resize_keyboard" => true,
            				"one_time_keyboard" => true,
                            "keyboard" => $keyboard,
                        );

            keyboard($key, "Che cosa vuoi cercare ?",$cid);
			break;
		case "/help":
			send($cid, "Elenco comandi:\n1) /tastiera ⌨");
			break;
		default:
            $state = getState($cid);
            switch($state)
            {
                case 0:
                    send($cid, "Elemento non trovato ❌\nDigita /help per aprire i comnadi.");
                case 1:
                    $bool = topTracks($cid, $text);
                    if($bool == true)
                        update_state($cid, 0);
                    break;
            }
			break;
	}
?>