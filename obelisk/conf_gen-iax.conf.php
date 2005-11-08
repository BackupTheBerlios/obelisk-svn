#!/usr/bin/php

<?php

include ('conf_util.inc.php');

// téléchargement dans la base de donnÃ©es des comptes SIP + reconstitution
// du fichier de config
conf_log(DEBUG_DEBUG, "Iax.conf...");

$query = "Select VoIPAccount_ID, Name, FirstName, username, pwd, ".
	 	"VoIPAccount_People_Extension, notransfer, host, port ".
	 "from People, Iax ".
	 "where extension = VoIPAccount_People_extension and enable = true";

$query = $db->query($query);
check_db($query);

while ($row = $query->fetchRow(DB_FETCHMODE_ORDERRED)) 
{
	conf_log(DEBUG_DEBUG, '['.$row[3].'-'.$row[0].']');
	
	echo '['.$row[3].'-'.$row[0]."]\n";
	echo "context=obelisk-iax-pep\ntype=friend\n";
	echo 'username='.$row[3].'-'.$row[0]."\n";
	if (is_null($row[7]))
		echo "host=dynamic\n";
	else
	{
		echo "host=".$row[7]."\n";
		if (!is_null($row[8]))
			echo "port=".$row[8]."\n";
	}
	echo 'callerid='.$row[2].' '.$row[1].' <'.$row[5]."> \n";
	echo 'notransfer='.($row[6]=="t" ? "yes\n" : "no\n");
	echo 'secret='.$row[4]."\n\n";
}

conf_log(DEBUG_INFO, "DONE");

?>
