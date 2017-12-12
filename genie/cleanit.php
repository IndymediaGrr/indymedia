<?php

function genie_cleanit_dist($t) {
  
	include_spip('inc/invalideur');
	// spip_log("_DIR_CACHE--"._DIR_CACHE. 'skel/',	'test_'._LOG_AVERTISSEMENT);
	purger_repertoire(_DIR_CACHE . 'contextes/', array(
		'atime' => (time() - (1800)),
		));
	return 1;
}

?>
