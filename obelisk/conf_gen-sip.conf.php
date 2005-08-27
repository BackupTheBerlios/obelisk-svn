<?php

// téléchargement dans la base de données des comptes SIP + reconstitution
// du fichier de config
conf_log(DEBUG_DEBUG, "Sip.conf...");

$query = "Select VoIPAccountID, Name, FirstName, username, pwd, ".
	 	"VoIPAccount_People_Extension, canreinvite, host, port ".
	 "from People, Sip ".
	 "where extension = People_extension and enable = true";

$query = $db->query($query);
check_db();

while ($row = $query->fetchRow(DB_FETCHMODE_ORDERRED)) 
{
	conf_log(DEBUG_DEBUG, '['.$row[3].'S'.$row[0].']');
	
	echo '['.$row[3].'-'.$row[0]."]\n";
	echo "context=obelisk-sip-pep\ntype=friend\n";
	if (is_null($row[7]))
		echo "host=dynamic\n";
	else
	{
		echo "host=".$row[7]."\n";
		if (!is_null($row[8]))
			echo "port=".$row[8]."\n";
	}
	echo 'callerid='.$row[2].' '.$row[1].' <'.$row[3]."> \n";
	echo 'canreinvite='.($row[6] ? "yes\n" : "no\n");
	echo 'secret='.$row[4]."\n\n";
}

conf_log(DEBUG_INF, "DONE");

?>
