<?php
	define('token', '649489910:AAEq5DspAvSmf349thfaBPhe_a68VI5X6EQ');

	include 'sourc.php';

	switch ($text) {
		case "/start":
			send($cid, "Benvenuto $name,\nin 📀 MusicLyricBot 📀");
			break;
        case "/tastiera":
			$keyboard = [
            				["Artista 🎤", "Genere 🎵"],
                        	["Nuove uscite 🕒", "Popolari 🔝"],
                        ];
            $key = array(
            				"resize_keyboard" => true,
                            "keyboard" => $keyboard,
                        );
            keyboard($key, "Tastiera interattiva attivata !",$cid);
			break;
		case "/help":
			send($cid, "Elenco comandi:\n1) /tastiera ⌨");
			break;
		default:
			send($cid, "Elemento non trovato ❌\nDigita /help per aprire i comnadi.");
			break;
	}
?>