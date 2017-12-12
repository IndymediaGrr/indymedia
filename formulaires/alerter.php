<?php

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/config');

// chargement des valeurs par defaut des champs du formulaire
function formulaires_alerter_charger_dist()
{
	$valeurs = array();

	$valeurs['nom'] = _request('nom');
	$valeurs['email'] = _request('email');
	$valeurs['texte'] = _request('texte');
	$valeurs['nobot'] = _request('nobot');
	$valeurs['id_article'] = _request('id_article');
	$valeurs['editable'] = 'editable';

	return $valeurs;
}

// vérification des valeurs du formulaire
function formulaires_alerter_verifier_dist()
{

	include_spip('inc/filtres');

	$erreurs = array();

	$id_article = _request('id_article');
	$nobot = _request('nobot');
	$nom = _request('nom');
	$email = _request('email');
	$texte = _request('texte');

	if (!$id_article)
	{
		$erreurs['id_article'] = _T("alerte_erreur_interne");
	}

	if ($nobot)
	{
		$erreurs['nobot'] = _T("alerte_nobot");
	}

	if (!$texte)
	{
		$erreurs['texte'] = _T("alerte_raison_obligatoire");
	}
	else if (strlen($texte) < 15)
	{
		$erreurs['texte'] = _T("alerte_raison_trop_court");
	}

	if (!$nom)
	{
		$erreurs['nom'] = _T("alerte_nom_obligatoire");
	}

	if (!$email)
	{
		$erreurs['email'] = _T("alerte_email_obligatoire");
	}
	else if (!email_valide($email))
	{
		$erreurs['email'] = _T("alerte_email_invalide");
	}

	return $erreurs;
}

// OK, on y va
function formulaires_alerter_traiter_dist()
{
	$id_article = _request('id_article');
	$nobot = _request('nobot');
	$nom = _request('nom');
	$email = _request('email');
	$texte = _request('texte');

	$message = array(
		'texte' => '',
		'fin' => ''
	);

	// récuperer quelques infos sur l'article
	include_spip('base/abstract_sql');
	
	$article = sql_fetsel(
		array('titre','date'),
		array('spip_articles'),
		array('id_article = '.$id_article)
	);


	// Envoyer la notification à la mailling list de modération
	$liste_moderation = lire_config('indymedia/liste_moderation');
	$envoyeur = lire_config('indymedia/liste_envoyeur');

	$sujet = '[Alerte de publication] '.$id_article.' "'.$article['titre'].'"';
	$lien_moderation = '<'.$GLOBALS['meta']['adresse_site'].'/ecrire/?exec=articles&id_article='.$id_article.'>';
	$body = "
	Lien interne : $lien_moderation
	Auteur de la notification : $nom <$email>
	----------------
	$texte
	
	";

	include_spip('inc/mail');
	
	if (envoyer_mail($liste_moderation, $sujet, $body, $envoyeur))
	{
		$message['texte'] = _T("alerte_reussite");
		$message['message_ok'] = _T("alerte_reussite");
		//$message['redirect'] = $GLOBALS['meta']['adresse_site'];
	}
	else
	{
		$message['texte'] = _T("alerte_erreur_mail");
		$message['message_ok'] = _T("alerte_reussite");
	}
		$message['redirect'] = generer_url_public("article","id_article=".$id_article."&message_alerte=ok&var_mode=recalcul");
	$valeurs['editable'] = null;

	return $message;
}
