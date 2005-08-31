<?

function geo_dial($extension, $callerId, $callerIdFull)
{
	global $db;

	// On commence par r�cup�rer le groupe o� pourrait se trouver 
	// l'extension que l'on veut appeler; c-a-d, le groupe dont l'extension
	// est directement inférieur
	$query = "select Extension, Name from Geographical_Group ".
		   "where Extension in ".
		     "(select MAX(Extension) from Geographical_Group ".
		       "where Extension <= $extension )";

	$query = $db->query($query);
	check_db($query);

	if (!($row = $query->fetchRow(DB_FETCHMODE_ORDERED)))
	{
		agi_log(DEBUG_ERR, "geo/dial.inc.php: No Group in DB");
		return agi_notFound($extension, $callerId, $callerIdFull);
	}

	
	$grp_name = $row[1]; 
	$grp_ext = $row[0];
	
	agi_log(DEBUG_DEBUG, "geo/dial.inc.php: Geo Group : ".$grp_name);
	
	$query = "select People_ID from Geographical_alias".
		   "where Extension = $extension ".
		   "  and Geographical_Group_Extension=".$grp_ext.
			
	$query = $db->query($query);
	check_db($query);

	if (!($row = $query->fetchRow(DB_FETCHMODE_ORDERRED)))
	{
		agi_log(DEBUG_ERR, "geo/dial.inc.php: extension : ".$extension.
					" not found in ".$grp_name);
		return agi_notFound($extension, $callerId, $callerIdFull);
	}
	
	agi_log(DEBUG_INFO, "geo/dial.inc.php: $extension in $grp_name -> ".
			"People : ".$row[0]);
	return agi_callPep($row[0], $callerId, $callerIdFull);
}

?>
