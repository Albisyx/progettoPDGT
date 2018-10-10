<?php
	include 'source.php';
	include 'accasso-db.php';

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
			send($cid, "Elemento non trovato ❌\nDigita /help per aprire i comnadi.");
			break;
	}

	if ($text == "Canzoni più popolari 🔝" || $text == "Info 📰" || $text == "Genere 🎵" || $text == "Nuove uscite 🕒" || $text == "Testo canzone 📜" || $text == "Anteprima 💾" || $text == "Canzone completa 💽")
	{
		$dati_utente_db = mysql_query("SELECT * FROM comando_eseguito WHERE cid = '$cid'");
		$array = mysql_fetch_array($dati_utente_db);

		if($array[cid] == $cid){
			mysql_query("UPDATE comando_eseguito SET comando = '$text' WHERE cid = '$cid'");
		}
		else{
			mysql_query("INSERT INTO comando_eseguito (cid, comando) VALUES ('$cid', '$text')");
		}

		$dati_utente_db = mysql_query("SELECT * FROM comando_eseguito WHERE cid = '$cid'");
		while ($array = mysql_fetch_array($dati_utente_db))
		{
			send($cid, "$array[comando]");
		}
	}
?>