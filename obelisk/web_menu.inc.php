<?php

include('config/config.inc.php');

require_once('DB.php'); // PEAR DB

/* prepare the connection to the database */
$db = DB::connect($dsn, true); // persistent

function do_webMenu($menuID)
{
	sessionStart();
	
	// where ?
	if (!isset($_REQUEST["lastID"]))
	{	// ouverture du menu passe en parametre
		
		// si le module requiert un login : draw_login();

		// sinon dessiner le menu principal
	}
	else
	{
		$lastID = $_REQUEST["lastID"];
		// recuperation de l'entree du menu

		// verification des droit d'executions

		// dessiner le path

		// executer l'action
		////inclure le module lie proprement

		// dessiner le sous menu si il y a lieu sinon 

		// dessiner un lien vers le menu parent
	}
}

?>
