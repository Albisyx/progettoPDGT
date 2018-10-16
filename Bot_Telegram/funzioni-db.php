<?php
	//funzioni per interaagire col database

	function checkIfUserExists($chat_id)
	{
		$result = mysql_fetch_assoc(mysql_query("SELECT * FROM comando_eseguito WHERE cid='$chat_id'"));
    	if(empty($result['cid']))
    		return false;
    	else
			return true;
	}

	function update_state($cid, $state)
	{
		$dati_utente_db = mysql_query("SELECT * FROM comando_eseguito WHERE cid = '$cid'");
		$array = mysql_fetch_array($dati_utente_db);

		if($array[cid] == $cid)
			mysql_query("UPDATE comando_eseguito SET comando = '$state' WHERE cid = '$cid'");
		else
			mysql_query("INSERT INTO comando_eseguito (cid, comando) VALUES ('$cid', '$state')");
	}

	function getState($cid)
	{
		$risultato = mysql_query("SELECT comando FROM comando_eseguito WHERE cid='$cid'");
		$row = mysql_fetch_assoc($risultato);
		
		$stato = $row['comando'];
		
		return $stato;
	}
?>