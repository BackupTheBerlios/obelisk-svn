<?php

function people_dial($extension, $callerId, $callerIdFull)
{
	global $db;

	$query = "select ChanType, username, VoIPAccount.ID ".
		 "  from People, VoIPAccount, VoIPChannel ".
		 " where People.Extension = $extension ".
		 "   and VoIPAccount.People_Extension = $extension ".
		 "   and VoIPAccount.VoIPChannel_ID = VoIPChannel.ID ".
		 "   and VoIPAccount.Enable = true ";
	
	if ($row = $query->fetchRow(DB_FETCHMODE_ORDERRED))
	{
		$dialStr = $row[0]."/".$row[1]."-".$row[2];

		while ($row = $query->fetchRow(DB_FETCHMODE_ORDERED))
		{
			$dialStr .= ",";
			$dialStr .= $row[0]."/".$row[1]."-".$row[2];
		}

		agi_log(DEBUG_DEBUG, "people_dial(): $callerId : ".
			"$extension -> '$dialStr'");
		return agi_call($extension, $dialStr, "r", 0, 0, $callerId, 
				$callerIdFull);
	}
			
	return agi_notFound($extension, $callerId, $callerIdFull);
}

?>
