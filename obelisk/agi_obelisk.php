<?

include('agi_util.inc.php');

agi_log(DEBUG_DEBUG , "agi_obelisk.php $entension - $callerId - $fullcallerId ...");

obelisk_dial($extension, $callerId)


$query = "Select Action ".
	 "from Dialplan ".
	 "where extension = $extension ".
	 "  and source = $callerId ".
	 "order by priority";

$query = $db->query($query);
check_db();

if ($row = $query->fetchRow(DB_FETCHMODE_ORDERRED)) 
{
	agi_log(DEBUG_DEBUG, "agi_obelisk.php: FOUND : ".$row[0]);
	
	agi_write($row[0]);

	agi_log(DEBUG_INF, "agi_obelisk.php: DONE");
}
else
{

	if ($extension = DEFAULT_EXTENSION)
		// impossible de trouver l'extension par défaut
		agi_log(DEBUG_CRIT, "agi_obelisk.php: DEFAULT EXTENSION NOT FOUND");
	else 
	{
		agi_log(DEBUG_INFO, "agi_obelisk.php: NOT FOUND -> ".
			"switching to default extension");
		agi_write("SET EXTENSION ".DEFAULT_EXTENSION);
		agi_write("EXEC AGI(".AGI_PATH."/agi_obelisk.php");
	}
}

?>
