<?php

function outside_dial(&$call)
{
	global $db;

	// 1°) trouver à quel réseau appartient l'extension
	$net = outside_getNetworkId($call);
	agi_log(DEBUG_DEBUG, 'outside_dial() : '.$call->get_extension()." -> $net");
	
	if ($net < 0)
		return agi_notFound($call);
	
	// 2°) faut-il annoncer le prix à l'utilisateur ?
	//	faut-il continuer plus cher ?
        $query =        "select P.Announce, P.AskHigherCost ".
			"  from People_PrePay_Settings as P  ".
			" where People_Extension = ".$call->get_responsable();
	$query = $db->query($query);
	check_db($query);

	if (!($row = $query->fetchRow(DB_FETCHMODE_ORDERED)))
	{
		// not found : paramètre par défaut : 
		$askHigher = false;
		$announce = true;
		agi_log(DEBUG_INFO, "outside_dial() : account not found");
	}
	else
	{
		$askHigher = $row[1];
		$announce = $row[0];
	}
	
	agi_log(DEBUG_DEBUG, "outside_dial() : $announce - $askHigher");
	
	// 3°) récupérer la liste des providers et leur prix 
	$query = "select N.Name, N.channel, P.ConnectionPrice, P.Price, ".
		 "       P.RmDigits, P.AddDigits, N.DialOpts ".
		 " from  NetworkProvider as N, Price as P, ".
		 "	 NetworkTimeZone_details as T ".
		 " where P.Network_ID = $net ".
		 "   and P.NetworkProvider_ID = N.ID ".
		 "   and P.NetworkTimeZone_ID = T.NetworkTimeZone_ID ".
		 "   and (".date("i")." between start_min and end_min )".
		 "   and (".date("G")." between start_hour and end_hour )".
		 "   and (".date("n")." between start_month and end_month )".
		 "   and (".date("j")." between start_dom and end_dom )".
		 "   and (".date("w")." between start_dow and end_dow )".
		 " order by P.Price, P.ConnectionPrice ";
		 
	$query = $db->query($query);
	check_db($query);
	
	/*	0 : name	
	 *	1 : channel
	 *	2 : priceConn
	 *	3 : price/min
	 *	4 : RmDigits
	 *	5 : AddDigits
	 *	6 : DialOpts
	 */
	if ($query->numRows() == 0)
		return agi_notFound($call);


	$provider =  $query->fetchRow(DB_FETCHMODE_ORDERED);
	// 4°) on tente d'appeler
	$isFirst = true; // première tentative au prix le plus bas
	while (($isFirst || $askHigher) && $provider != NULL) 
	{
		$price = $provider[3];
		// 5°) on annonce au besoin : 
		if ($announce)
		{
			agi_play_soundSet(SOUNDSET_PRICE_ANNOUNCE, "");
			agi_sayNumber(ceil($price*100));
			agi_play_soundSet(SOUNDSET_CURRENCY, "");
		}

		// on teste l'un à la suite de l'autre tout les providers
		// qui ont le même prix d'appel
		while ($provider[3] == $price && $provider != NULL)
		{
			$callStr = $provider[1].$provider[5].
				substr($call->get_extension(), $provider[4]);
			agi_log(DEBUG_DEBUG, "outside_dial() : trying : $callStr");

			$result = agi_call($call, $callStr,
				$provider[6], 
				$provider[2], $provider[3], false);

			if ($result >= 0 ||
				$call->get_lastTryStatus() != "CHANUNAVIL")
				// succeed
				return $result;
			
			$provider =  $query->fetchRow(DB_FETCHMODE_ORDERED);
		}

		// annonce qu'il est impossible d'effectuer l'appel au pris
		// nnoncé précédement
		agi_play_soundSet(SOUNDSET_UNAVAIL_AT_PRICE, "");

		$first = false;

		// téléchargement du provider suivant
		$provider =  $query->fetchRow(DB_FETCHMODE_ORDERED);
	}

		
	agi_logCall($call, 0, 0, "NOT ROUTABLE");
	return -1;
}

/**
 * outside_getNetworkId - return the networkId for an extension
 *
 * PRE: $call is a valid CallObj where $extension is defined
 *
 * POST: retrun a positive number which is the network Id or a negative number
 *	which means 'unknown'
 *
 * I/O: dowsnload the primary networkId and execute the phpfunction in order to
 *	get the secondary network ID.
 */
function outside_getNetworkId(&$call)
{
	global $db;

	$query = "select N.ID, N.SubNetwork_function ".
		 " from  Network as N, NetworkMask as M ".
		 " where N.ID = M.Network_ID ".
		 "  and  '".$call->get_extension()."' LIKE M.Mask";
	
	$query = $db->query($query);
	check_db($query);

	if (!($row = $query->fetchRow(DB_FETCHMODE_ORDERED)))
		return -1;
	
	if ($row[1] != '')
	{
		// fonction de sous-séledtion
		$fct = "outside_select_".$row[1];
		return $fct();
	}
	
	return  $row[0];
}


?>
