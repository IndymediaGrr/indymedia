<?php

if (!defined("_ECRIRE_INC_VERSION")) return;

// chargement des valeurs par defaut des champs du formulaire
function formulaires_moderation_commentaire_charger_dist()
{
	$valeurs = array();

	return $valeurs;
}

function formulaires_moderation_commentaire_verifier_dist()
{
	$erreurs = array();
	
	$id_dernier_commentaire = _request('dernier_commentaire');
	$moderation_action = _request('moderation_action');

	if (!$id_dernier_commentaire)
	{
		$erreurs['dernier_commentaire'] = 'Vous devez indiquez le dernier commentaire modéré';
	}

	if ($moderation_action && 
		($moderation_action !== 'cacher') && ($moderation_action !== 'montrer'))
	{
		$erreurs['moderation_action'] = 'hum ... l\'action demandée me semble bizarre.';
	}

	return $erreurs;
}

function formulaires_moderation_commentaire_traiter_dist()
{
	$message = array();

	include_spip('base/abstract_sql');

	$moderation_action = _request('moderation_action');
	$id_dernier_commentaire = _request('dernier_commentaire');
	$selection = _request('selection');

	$first = true;
	$in = '';
	foreach($selection as $id_forum)
	{
		if (!$first) $in .=',';
		$first = false;
		$in .= $id_forum;
	}

	switch($moderation_action)
	{
		case 'cacher' :echo $in;
			sql_updateq(
				'spip_forum',
				array(
					'statut' => 'off',
				),
				"id_forum IN ($in)"
			);
			break;
		case 'montrer' :
			sql_updateq(
				'spip_forum',
				array(
					'statut' => 'publie',
				),
				"id_forum IN ($in)"
			);
			break;
	}

	if ($id_dernier_commentaire)
	{
		ecrire_config('indymedia/dernier_commentaire', $id_dernier_commentaire);
	}
	
	return $message;
}


?>
