<?php

function people_dial(&$call)
{
	global $db;

        $extension = $call->get_extension();
	$callerId = $call->get_cid();
	$callerIdFull = $call->get_cidFull();
			

	$query = "select ChanType, username, VoIPAccount.ID ".
		 "  from People, VoIPAccount, VoIPChannel ".
		 " where People.Extension = $extension ".
		 "   and VoIPAccount.People_Extension = $extension ".
		 "   and VoIPAccount.VoIPChannel_ID = VoIPChannel.ID ".
		 "   and VoIPAccount.Enable = true ";
	
	$query = $db->query($query);
	check_db($query);
	
	if ($row = $query->fetchRow(DB_FETCHMODE_ORDERED))
	{
		$dialStr = $row[0]."/".$row[1]."-".$row[2];

		while ($row = $query->fetchRow(DB_FETCHMODE_ORDERED))
		{
			$dialStr .= "&";
			$dialStr .= $row[0]."/".$row[1]."-".$row[2];
		}

		agi_log(DEBUG_DEBUG, "people_dial(): $callerId : ".
			"$extension -> '$dialStr'");
		return agi_call($call, $dialStr, "r", 0, 0);
	}
			
	return agi_notFound($call);
}

?>
