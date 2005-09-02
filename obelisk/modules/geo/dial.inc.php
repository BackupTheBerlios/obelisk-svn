<?php

function geo_dial($extension, $callerId, $callerIdFull)
{
	global $db;

	$query = "select destination.People_Extension ".
		 "from Geographical_Alias as caller, ".
		 " 	Geographical_Alias as destination, ".
		 "	Geographical_Group as grp ".
		 "where caller.People_Extension = '".$callerId."' ".
		 "  and caller.Geographical_Group_ID = grp.ID ".
		 "  and grp.ID = destination.Geographical_Group_ID ".
		 "  and destination.Extension = '".$extension."' ";
	
	$query = $db->query($query);
	check_db($query);

	if (!($row = $query->fetchRow(DB_FETCHMODE_ORDERED)))
	{
		agi_log(DEBUG_ERR, "geo/dial.inc.php: extension : ".$extension.
					" not found");
		return agi_notFound($extension, $callerId, $callerIdFull);
	}
	
	agi_log(DEBUG_INFO, "geo/dial.inc.php: $extension -> ".
			"People : ".$row[0]);

	include_once ('modules/people/dial.inc.php');
	return people_dial($row[0], $callerId, $callerIdFull);
}

?>
