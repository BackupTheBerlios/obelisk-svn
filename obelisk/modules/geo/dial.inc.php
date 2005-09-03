<?php

function geo_dial(&$call)
{
	global $db;


        $extension = $call->get_extension();
	$callerId = $call->get_cid();
	$callerIdFull = $call->get_cidFull();

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
		return agi_notFound($call);
	}
	
	agi_log(DEBUG_INFO, "geo/dial.inc.php: $extension -> ".
			"People : ".$row[0]);

	$call->set_extension($row[0]);
	
	include_once ('modules/people/dial.inc.php');
	return people_dial($call);
}

?>
