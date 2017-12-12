<?php

if (!defined("_ECRIRE_INC_VERSION")) return;


/*
 * Déclaration des tables :
 *	- lieux : liste des lieux
 *	- lieux_objets : les liens entre les lieux et d'autres objets
 *	- debats : liste des debats
 *	- debats_objets : les liens entre les débats et d'autres objets
 */
 
function indymedia_declarer_tables_objets_sql($tables){
	$tables['spip_articles']['field']["extra"] = "LONGTEXT";
	$tables['spip_articles']['field']["date_debut_indy"] = "datetime DEFAULT  NULL";
	$tables['spip_articles']['field']["date_fin_indy"] = "datetime DEFAULT  NULL";

	$tables['spip_debats'] = array(
		'table_objet' => 'debats',
		'table_objet_surnoms' => array(),
		'type' => 'debats',
		'type_surnoms' => array('debats'),
		'editable'=>'oui',
		'principale' => 'oui',
		'titre' => "titre, '' AS lang",
		'field'=> array(
			"id_debat" => "BIGINT(21) NOT NULL",
			"id_auteur" => "BIGINT(21) NOT NULL",
			"id_article" => "BIGINT(21) NOT NULL",
			"date" => "DATETIME NOT NULL",
			"texte" => "TEXT NOT NULL"
		),
		'key' => array(
			"PRIMARY KEY" => "id_debat"
		),
		'rechercher_champs' => array(
			'texte' => 8,
		),
		'champs_versionnes' => array('id_debat')
	);
	
	$tables['spip_lieux'] = array(
		'table_objet'=>'lieux', # ??? hum hum redevient spip_forum par table_objet_sql mais casse par un bete 
		'table_objet_surnoms' => array('lieux'),
		'type'=>'lieux',
		'url_edit' => 'lieux_edit',
		'editable'=>'oui',
		'page' => '', // pas de page publique
		'champs_editables' => array('nom','ville','adresse','tel','site_web','email'),
		'principale' => 'oui',
		'titre' => "titre, '' AS lang",
		'field'=> array(
			"id_lieu" => "BIGINT(21) NOT NULL",
			"nom" => "VARCHAR(64) NOT NULL",
			"ville" => "VARCHAR(32) NOT NULL",
			"adresse" => "TEXT NOT NULL",
			"tel" => "VARCHAR(32)",
			"site_web" => "VARCHAR(64)",
			"email" => "VARCHAR(32)",
			"approuve" => "VARCHAR(10) DEFAULT 'non'"
		),
		'key' => array(
			"PRIMARY KEY" => "id_lieu"
		),
		'rechercher_champs' => array(
			'nom' => 8,
		),
		'champs_versionnes' => array('nom')
	);

	$lieux_objets = array(
		"id_lieu" => "BIGINT(21) NOT NULL",
		"objet" => "VARCHAR(25) DEFAULT '' NOT NULL",
		"id_objet" => "BIGINT(21) NOT NULL"
	);

	$cles_lieux_objets = array(
		"PRIMARY KEY" => "id_lieu, id_objet, objet"
	);
	

	$tables['spip_lieux_objets'] = array(
		'field' => &$lieux_objets,
		'key' => &$cles_lieux_objets
        );

	return $tables;
}

function indymedia_declarer_tables_auxiliaires($tables_auxiliaires)
{
	$lieux_objets = array(
		"id_lieu" => "BIGINT(21) NOT NULL",
		"objet" => "VARCHAR(25) DEFAULT '' NOT NULL",
		"id_objet" => "BIGINT(21) NOT NULL"
	);

	$cles_lieux_objets = array(
		"PRIMARY KEY" => "id_lieu, id_objet, objet"
	);
	

	$tables_auxiliaires['spip_lieux_objets'] = array(
		'field' => &$lieux_objets,
		'key' => &$cles_lieux_objets
        );

	return $tables_auxiliaires;
}

function indymedia_declarer_tables_interfaces($tables_interfaces)
{
	$tables_interfaces['tables_jointures']['spip_lieux'][] = 'lieux_objets';
	$tables_interfaces['tables_jointures']['spip_lieux_objets'][] = 'lieux';
	$tables_interfaces['tables_jointures']['spip_articles'][] = 'lieux_objets';
	$tables_interfaces['tables_jointures']['spip_articles'][] = 'debats';
	$tables_interfaces['tables_jointures']['spip_auteurs'][] = 'debats';

	$tables_interfaces['table_des_tables']['lieux'] = 'lieux';
	$tables_interfaces['table_des_tables']['lieux_objets'] = 'lieux_objets';	
	$tables_interfaces['table_des_tables']['debats'] = 'debats';

	$tables_interfaces['table_date']['articles']='date_debut_indy';
	$tables_interfaces['DATE_DEBUT_INDY'][]= 'normaliser_date(%s)';
	$tables_interfaces['table_date']['articles']='date_fin_indy';
	$tables_interfaces['DATE_FIN_INDY'][]= 'normaliser_date(%s)';
	
	return $tables_interfaces;
}

?>
