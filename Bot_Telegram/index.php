<?php
	define('token', 'token');

	include 'sourc.php';

	switch ($text) {
		case "/start":
			send($cid, "📀 Benvenuto in MusicBot ! 📀");
			break;
		case "/help":
			send($cid, "Elenco comandi");
			break;
		default:
			send($cid, "Elemento non trovato ❌\nDigita /help per aprire i comnadi.");
			break;
	}
?>