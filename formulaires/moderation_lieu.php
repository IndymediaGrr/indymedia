<?php

if (!defined("_ECRIRE_INC_VERSION")) return;


// chargement des valeurs par defaut des champs du formulaire
function formulaires_moderation_lieu_charger_dist($id_lieu)
{
	$valeurs = array();

	$valeurs['id_lieu'] = $id_lieu;

	$row = sql_fetsel(
		array('nom','ville','adresse','tel','email','site_web'),
		array('spip_lieux'),
		array('id_lieu = '.$valeurs['id_lieu'])
		);

	$valeurs['nom'] = $row['nom'];
	$valeurs['adresse'] = $row['adresse'];
	$valeurs['tel'] = $row['tel'];
	$valeurs['email'] = $row['email'];
	$valeurs['ville'] = $row['ville'];
	$valeurs['site_web'] = $row['site_web'];

	return $valeurs;
}



// vérification des données du formulaires
function formulaires_moderation_lieu_verifier_dist()
{
	$erreurs = array();
	$valeurs = array();

	$valeurs['id_lieu'] = _request('id_lieu');
	$valeurs['nom'] = _request('nom');
	$valeurs['adresse'] = _request('adresse');
	$valeurs['tel'] = _request('tel');
	$valeurs['email'] = _request('email');
	$valeurs['ville'] = _request('ville');
	$valeurs['site_web'] = _request('site_web');
	if (! _request('supprimer'))
		if (!$valeurs['nom']) $erreurs['nom'] = _T('erreur_nom_obligatoire');

	return $erreurs;
}


// chargement des valeurs par defaut des champs du formulaire
function formulaires_moderation_lieu_traiter_dist()
{
	$message = array();
	$valeurs = array();

	$valeurs['id_lieu'] = (int) _request('id_lieu');
	$valeurs['nom'] =  entites_html(stripslashes(_request('nom')));
	$valeurs['adresse'] =  entites_html(stripslashes(_request('adresse')));
	$valeurs['tel'] =  entites_html(stripslashes(_request('tel')));
	$valeurs['email'] =  entites_html(stripslashes(_request('email')));
	$valeurs['ville'] =  entites_html(stripslashes(_request('ville')));
	$valeurs['site_web'] =  _request('site_web');

	if (_request('enregistrer'))
	{
		sql_updateq(
			"spip_lieux",
			array(
				"nom" => $valeurs['nom'],
				"ville" => $valeurs['ville'],
				"adresse" => $valeurs['adresse'],
				"tel" => $valeurs['tel'],
				"email" => $valeurs['email'],
				"site_web" => $valeurs['site_web']

			),
			"id_lieu=".$valeurs['id_lieu']
		);

		$message['redirect'] = generer_url_ecrire("lieux","message_ok="._T('modification_lieu_ok'));
	}
	else if (_request('supprimer'))
	{
		sql_delete("spip_lieux","id_lieu=".$valeurs['id_lieu']);
		$message['redirect'] = generer_url_ecrire("lieux","message_ok="._T('suppression_lieu_ok'));
	}
	return $message;
}

