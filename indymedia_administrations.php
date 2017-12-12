<?php

if (!defined("_ECRIRE_INC_VERSION")) return;

function indymedia_upgrade($nom_meta_base_version,$version_cible)
{   
	$maj = array();
	
	// champs extra
	//cextras_api_upgrade(indymedia_declarer_champs_extras(), $maj['create']);
	
	$maj['create'] = array(
        array('maj_tables', array('spip_articles','spip_debats','spip_lieux','spip_lieux_objets')),
		array('sql_alter',  "TABLE spip_articles ADD COLUMN extra longtext"),
		array('sql_alter',  "TABLE spip_articles ADD COLUMN date_debut_indy datetime DEFAULT  NULL"),
		array('sql_alter',  "TABLE spip_articles ADD COLUMN date_fin_indy datetime DEFAULT  NULL"),
	// sql_alter("TABLE spip_articles DROP date_debut_indy");
	// sql_alter("TABLE spip_articles DROP date_fin_indy");
	);
	$maj['1.1.0'] = array(
    	);
	$maj['1.2.4']  = array(array('sql_alter',"TABLE spip_lieux ADD COLUMN approuve varchar(10) DEFAULT 'non' after email"));
	$maj['1.2.5']  = array(array('sql_alter',"TABLE spip_lieux CHANGE tel tel varchar(32)"));
	$maj['1.2.6']  = array(array('sql_alter',"TABLE spip_lieux CHANGE nom nom varchar(64)"));

	include_spip('base/upgrade');
	maj_plugin($nom_meta_base_version, $version_cible, $maj);
	
}

function indymedia_vider_tables($nom_meta_base_version)
{
	effacer_meta($nom_meta_base_version);
	// sql_alter("TABLE spip_articles DROP extra");
	// sql_alter("TABLE spip_articles DROP date_debut_indy");
	// sql_alter("TABLE spip_articles DROP date_fin_indy");
}
?>
