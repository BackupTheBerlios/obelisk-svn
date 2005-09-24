<?php

function people_dial(&$call)
{
	global $db;

        $extension = $call->get_extension();
	$callerId = $call->get_cid();
	$callerIdFull = $call->get_cidFull();
			

	$query = "select Context ".
		 "  from AsteriskGoto ".
		 " where Extension = $extension ";
	
	$query = $db->query($query);
	check_db($query);
	
	if ($row = $query->fetchRow(DB_FETCHMODE_ORDERED))
	{
		// ceci boycott complètement le fonctionnement d'obelisk
		agi_goto($row[0]);
	}
			
	return agi_notFound($call);
}

?>
