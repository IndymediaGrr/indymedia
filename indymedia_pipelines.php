<?php

if (!defined("_ECRIRE_INC_VERSION")) return;

/*
 * Met en place le Cron en fonction de la fréquence de génération de la configuration
 */

function indymedia_cleanit ($flux) 
{
	// spip_log("_DIR_CACHE--"._DIR_CACHE,	'test_'._LOG_AVERTISSEMENT);
	$flux['cleanit'] = 3600;
	return $flux;
}

?>
