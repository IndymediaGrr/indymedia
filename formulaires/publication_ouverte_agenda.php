<?php

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('action/ajouter_documents');
include_spip('inc/documents');
include_spip('inc/fonctions_publication');
/*
 * chargement des valeurs par defaut des champs du formulaire
 */
function formulaires_publication_ouverte_agenda_charger_dist()
{
	$values = array();

	$values['etape'] 		= _request('etape');			// etape actuelle
	$values['id_article']	 	= _request('id_article');		// id de l'article temporaire
	$values['type']			= "evenement";			// article ou evenement ?
	$values['id_rubrique'] 		= _request('id_rubrique');		// rubrique de la contrib
	$values['jour_evenement'] 	= _request('jour_evenement'); 		// jour de l'événement
	$values['mois_evenement'] 	= _request('mois_evenement'); 		// mois de l'événement
	$values['annee_evenement'] 	= _request('annee_evenement'); 		// annee de l'événement
	$values['heure_evenement'] 	= _request('heure_evenement'); 		// heure de l'événement
	$values['lieu_evenement']	= _request('lieu_evenement');		// lieu de l'evenement
	$values['ville_evenement']	= _request('ville_evenement');		// lieu de l'evenement
	$values['adresse_evenement']	= _request('adresse_evenement');	// lieu de l'evenement
	$values['tel_evenement']	= _request('tel_evenement');		// lieu de l'evenement
	$values['web_evenement']	= _request('web_evenement');		// lieu de l'evenement
	$values['email_evenement']	= _request('email_evenement');		// lieu de l'evenement
	$values['lieu_evenement_existe']= _request('lieu_evenement_existe');	// lieu de l'evenement déjà existant
	$values['titre'] 		= _request('titre');			// titre de la contrib
	$values['texte'] 		= _request('texte');			// texte de la contrib
	$values['pseudo'] 		= _request('pseudo');			// pseudo ou nom de l'auteur
	$values['email'] 		= _request('email');			// email de l'auteur
	$values['mots'] 		= _request('mots');			// les mots-clés
	$values['date'] = _request('date');
	$values['date_modif'] = _request('date_modif');
	$values['supprimer_documents'] = _request('supprimer_documents');
	// editos
	$values['mise_en_edito'] = _request('mise_en_edito');
	$values['edito_complet'] = _request('edito_complet');
	// focus
	$values['mot_focus'] = _request('mot_focus');
	// si c'est pas un debut d'article
	if (_request('titre') or _request('previsu'))
		$values['ancre'] = '#etape_4';
	else
		$values['ancre'] = '.page-article';
	
	/*
	 * s'il s'agit du premier passage, on créé l'article
	 */
	if (!$values['id_article'])
	{
		$id_article = indymedia_creer_article();
		$values['id_article'] = $id_article;
	}
	/*
	 * sinon, si admin, on peut l'éditer
	 */
	else if ($values['id_article'] && est_admin())
	{
		$id_article = (int) $values['id_article'];
		// on recupere des infos sur l'article	
		$row = sql_fetsel(
			array('texte','extra','id_rubrique','titre','date_debut_indy','statut','date'),
			array('spip_articles'),
			array('id_article='.$id_article)
		);
		$values['texte'] = $row['texte'] ;
		$values['titre'] = $row['titre'] ;
		$values['id_rubrique'] = $row['id_rubrique'] ;
		//$values['statut'] = $row['statut'] ;
		$extra = unserialize($row['extra']);
		$values['explication'] = $extra['OP_moderation'];
		$values['pseudo'] = $extra['OP_pseudo'];
		$values['email'] = $extra['OP_email'];
		$values['date'] = $row['date'];
		
		// on recupere la date, l'heure et le lieu de l'evenement
		$values = indymedia_ajout_valeurs_date ($values, $row['date_debut_indy']);
		$values = indymedia_ajout_valeurs_lieu ($values, $id_article);
		$values = indymedia_ajout_valeurs_mots ($values, $id_article);
	}
	include_spip('inc/securiser_action');
	$values['cle_ajouter_document'] = calculer_cle_action('ajouter-document-' . 'article' . '-' . $id_article);

	return $values;
}

/*
 * Vérification des champs du formulaire
 */
function formulaires_publication_ouverte_agenda_verifier_dist()
{
	$erreurs = array();
	$values = array();
	$values['etape'] 		= _request('etape');			// etape actuelle
	$values['id_article']	 	= _request('id_article');		// id de l'article temporaire
	$values['type']			= _request('type');			// article ou evenement ?
	$values['id_rubrique'] 		= _request('id_rubrique');		// rubrique de la contrib
	$values['jour_evenement'] 	= _request('jour_evenement'); 		// jour de l'événement
	$values['mois_evenement'] 	= _request('mois_evenement'); 		// mois de l'événement
	$values['annee_evenement'] 	= _request('annee_evenement'); 		// annee de l'événement
	$values['heure_evenement'] 	= _request('heure_evenement'); 		// heure de l'événement
	$values['lieu_evenement']	= _request('lieu_evenement');		// lieu de l'evenement
	$values['ville_evenement']	= _request('ville_evenement');		// ville_evenement
	$values['adresse_evenement']	= _request('adresse_evenement');	// adresse_evenement
	$values['tel_evenement']	= _request('tel_evenement');		// tel_evenement
	$values['web_evenement']	= _request('web_evenement');		// web_evenement
	$values['email_evenement']	= _request('email_evenement');		// email_evenement
	$values['lieu_evenement_existe']= _request('lieu_evenement_existe');	// lieu de l'evenement déjà existant
	$values['titre'] 		= _request('titre');			// titre de la contrib
	$values['texte'] 		= _request('texte');			// texte de la contrib
	$values['pseudo'] 		= _request('pseudo');			// pseudo ou nom de l'auteur
	$values['email'] 		= _request('email');			// email de l'auteur
	$values['mots'] 		= _request('mots');			// les mots-clés
	$values['fichier'] 		= _request('fichier');			// un fichier a attacher
	// editos
	$values['mise_en_edito'] = _request('mise_en_edito');
	$values['edito_complet'] = _request('edito_complet');
	// focus
	$values['mot_focus'] = _request('mot_focus');
	$values['date'] = _request('date');
	$supprimer_documents = _request('supprimer_documents');
	// si c'est pas un debut d'article
	if (_request('titre') or _request('changer_chaussette') or _request('previsu'))
		set_request('ancre', '#etape_4');
	else
		set_request('ancre', '.page-article');
	// spip_log('ancre : '._request('ancre'),	'test_'._LOG_AVERTISSEMENT);	
		
	if (!$values['type']) 
		$erreurs['type'] = _T('publication_erreur_type');

	if ($values['type'] == 'evenement')
	{
		/* Vérification jour_evenement */	
		if (!$values['jour_evenement'])	$erreurs['jour_evenement'] = _T('publication_erreur_jour_evenement');
		if ($values['jour_evenement'] && (preg_match("@^([0-3]{1}[0-9]{1})$@",$values['jour_evenement']) !== 1))	
			$erreurs['jour_evenement'] = _T('publication_erreur_jour_evenement');
		/* Vérification mois_evenement */	
		if (!$values['mois_evenement'])	$erreurs['mois_evenement'] = _T('publication_erreur_mois_evenement');
		if ($values['mois_evenement'] && (preg_match("@^([0-1]{1}[0-9]{1})$@",$values['mois_evenement']) !== 1))	
			$erreurs['mois_evenement'] = _T('publication_erreur_mois_evenement');
		/* Vérification annee_evenement */	
		if (!$values['annee_evenement'])	$erreurs['annee_evenement'] = _T('publication_erreur_annee_evenement');
		if ($values['annee_evenement'] && (preg_match("@^([0-9]{4})$@",$values['annee_evenement']) !== 1))	
			$erreurs['annee_evenement'] = _T('publication_erreur_annee_evenement');
		/* Vérification heure_evenement */			
		if (!$values['heure_evenement'])$erreurs['heure_evenement'] = _T('publication_erreur_heure_evenement');
		if ($values['heure_evenement'] && (preg_match("@^(([0-1]{0,1}[0-9]{1})|(2[0-3]{1}))(:[0-5]{0,1}[0-9]{1}){1,2}$@",$values['heure_evenement']) !== 1) )
			$erreurs['heure_evenement'] = _T('publication_erreur_heure_evenement');
		/* Vérification lieu_evenement */	
		if ((!$values['lieu_evenement']) && (!$values['lieu_evenement_existe'])) $erreurs['lieu_evenement'] = _T('publication_erreur_lieu_evenement');
	}

	if (($values['type'] == 'article') && (!$values['id_rubrique']))
		$erreurs['id_rubrique'] = _T('publication_erreur_rubrique');

	if (!$values['titre']) 
		$erreurs['titre'] = _T('publication_erreur_titre');

	if (!$values['texte'])
		$erreurs['texte'] = _T('publication_erreur_texte_1');
	else if (strlen($values['texte']) < 50) 
		$erreurs['texte'] = _T('publication_erreur_texte_2');

	// verif des mots
	if($message_erreur_mot = verifier_mots($values))
		$erreurs['mots'] = $message_erreur_mot;
	
	if ($values['nobot'])
		$erreurs['nobot'] = _T('publication_erreur_nobot');

	// controle de l'email
	if ($values['email'] && (preg_match("/^[[:alnum:]]([-_.]?[[:alnum:]_]?)*@[[:alnum:]]([-.]?[[:alnum:]])+\.([a-z]{2,6})$/",$values['email']) !== 1) )
	{
		$erreurs['email'] = _T('publication_erreur_email_format');
	}	
	
	$values['statut'] = indymedia_statut_article($values['id_article']);

	/*
	 * suppression de documents
	*/
	if (is_array($supprimer_documents)){
		include_spip('action/supprimer_document_indy');
		foreach($supprimer_documents as $val)
		{
			if (!action_supprimer_document_indy_dist($val))
				$erreurs['titre'] .= $val;
		}
	}
	/*
	 * Ajout des documents, on le fait içi car cette fonction peut générer des erreurs
	*/
	if (_request('joindre_upload') and _request('titre') and _request('texte')){	
		indymedia_traiter_document($values['id_article'],$erreurs);
	}
	else if (_request('joindre_upload')){	
		$erreurs['joindre_upload'] = _T('publication_erreur_doc');
	}
	if (_request('joindre_upload') or is_array($supprimer_documents)){	
		set_request('ancre', '#doc');
	}

	/*
	 * Si y'a des erreurs, on ne passera pas par la fonction "traiter"
	 * Il faut donc alimenter l'article maintenant, sinon la prévisualisation ne fonctionnera pas
	*/
	if (count($erreurs) > 0)
	{
		/*
		 * Traitement des valeurs en vue de leur utilisation en base de données
		*/		
		$values = indymedia_traiter_article($values);
		if ( indymedia_verifier_statut($values['statut']))
		{
			sql_updateq(
				'spip_articles',
				array(
					'titre' => $values['titre'],
					'id_rubrique' => $values['id_rubrique'],
					'texte' => $values['texte'],
					'statut' => $values['statut'],
					'lang' => $values['langue'],
					'id_secteur' => $values['id_secteur'],
					'date' => $values['date'],
					'date_debut_indy' => $values['date_debut_indy'],
					'date_modif' => $values['date_modif'],
					'extra' => $values['extra']
				),
				array(
					'id_article = '.$values['id_article']
				)
			);
		}
		/*
		 * un non-admin essaye de  modifier un article déjà publié
		*/
		else
		{			
			$erreurs['redac'] = _T('impossible_modifier_article_publie').$values['statut'];
		}
	}  
		/*
		 * un non-admin essaye de  modifier un article déjà publié
		*/
	else if ($values['id_article'] && ! indymedia_verifier_statut($values['statut']) )
	{  
			$erreurs['redac'] = _T('impossible_modifier_article_publie').$values['statut'];
	}
		/*
		 * verif si plugin nospam et si spam
		*/
	else if ($values['id_article'])
		$erreurs = indy_nospam ($values, $erreurs, $values['id_article']);

	// si un modo modifie les reglages editos
	indymedia_traiter_valeurs_edito($values);
		
	return $erreurs;
}

/*
 * on enregistre l'article
 */
function formulaires_publication_ouverte_agenda_traiter_dist()
{
	$message = array();
	$values = array();
	$values['etape'] 		= _request('etape');			// etape actuelle
	$values['id_article']	 	= _request('id_article');		// id de l'article temporaire
	$values['type']			= _request('type');			// article ou evenement ?
	$values['id_rubrique'] 		= _request('id_rubrique');		// rubrique de la contrib
	
	$values['jour_evenement'] 	= _request('jour_evenement'); 		// jour de l'événement
	$values['mois_evenement'] 	= _request('mois_evenement'); 		// mois de l'événement
	$values['annee_evenement'] 	= _request('annee_evenement'); 		// annee de l'événement
	$values['heure_evenement'] 	= _request('heure_evenement');	 	// heure de l'événement
	$values['date_evenement'] = _request('date_evenement');
	$values['lieu_evenement']	= _request('lieu_evenement');		// lieu de l'evenement
	$values['ville_evenement']	= _request('ville_evenement');		// ville du lieu
	$values['adresse_evenement']	= _request('adresse_evenement');	// adresse du lieu
	$values['tel_evenement']	= _request('tel_evenement');		// tel du lieu
	$values['web_evenement']	= _request('web_evenement');		// siteweb du lieu
	$values['email_evenement']	= _request('email_evenement');		// email du lieu
	$values['lieu_evenement_existe']= _request('lieu_evenement_existe');	// id du lieu de l'evenement
	
	$values['titre'] 		= _request('titre');			// titre de la contrib
	$values['texte'] 		= _request('texte');			// texte de la contrib
	$values['pseudo'] 		= _request('pseudo');			// pseudo ou nom de l'auteur
	$values['email'] 		= _request('email');			// email de l'auteur
	$values['mots'] 		= _request('mots');			// les mots-clés
	$values['mise_en_edito'] = _request('mise_en_edito');
	$values['edito_complet'] = _request('edito_complet');
	// focus
	$values['mot_focus'] = _request('mot_focus');
	$values['date'] = _request('date');

	$values = indymedia_traiter_article($values);

	/*
	 * Statut : si pré-visualisation, alors l'article est "en redaction" (prepa)
	 *	si publication alors l'article est "proposé" (prop) ou publié
	 */
	$values['statut'] = indymedia_statut_article($values['id_article']);	
	/*
	 * tester si nouvel article a publier
	 */	
	if (($values['statut']=="prepa") && _request('publier'))
	{			
		/* tester si rubrique en attente activée */
		$en_attente_defaut = lire_config('indymedia/en_attente_defaut');
		$en_attente_actif = lire_config('indymedia/en_attente_actif');
		if ($en_attente_defaut == 'en_attente_defaut' && $en_attente_actif == 'en_attente_actif' ){
			// si rubrique en attente activée
			$values['statut'] = "prop";
		}
		else{
			// sinon publication directe
			$values['statut'] = "publie";
		}
	}

	sql_updateq(
		'spip_articles',
		array(
			'titre' => $values['titre'],
			'id_rubrique' => $values['id_rubrique'],
			'texte' => $values['texte'],
			'statut' => $values['statut'],
			'lang' => $values['langue'],
			'id_secteur' => $values['id_secteur'],
			'date' => $values['date'],
			'date_debut_indy' => $values['date_debut_indy'],
			'date_modif' => $values['date_modif'],
			'extra' => $values['extra']
		),
		array(
			'id_article = '.$values['id_article']
		)
	);

	if (_request('publier'))
	{
		indymedia_ajout_mots($values['id_article'],$values['mots']);
		indymedia_traiter_valeurs_edito($values);

		if ($values['id_lieu'])	indymedia_ajout_lieu_lien($values['id_article'],$values['id_lieu']);
		else if ($values['lieu_evenement']) indymedia_ajout_nouveau_lieu($values['id_article'],$values);

		/* Nettoyage d'articles non termines */
		indymedia_nettoyage();			
		/* Notif admin	 */
		indymedia_notifier($values);
		
		if (lire_config('indymedia/en_attente_defaut') == 'en_attente_defaut' && lire_config('indymedia/en_attente_actif') == 'en_attente_actif' ){
			$message['redirect'] = generer_url_public("attente","message_publication=ok");
		}
		else{
			$message['redirect'] = generer_url_public("article","id_article=".$values['id_article']."&message_publication=ok&var_mode=recalcul");
		}
	}

	return $message;
}

?>
