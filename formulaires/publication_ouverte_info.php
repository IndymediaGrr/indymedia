<?php

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('action/ajouter_documents');
include_spip('inc/documents');
include_spip('inc/fonctions_publication');
/*
 * chargement des valeurs par defaut des champs du formulaire
 */
function formulaires_publication_ouverte_info_charger_dist()
{
	// session_set('cookie','oui');
	$values = array();

	$values['etape'] 		= _request('etape');			// etape actuelle
	$values['id_article']	 	= _request('id_article');		// id de l'article temporaire
	$values['type']			= "article";			// article ou evenement ?
	$values['id_rubrique'] 		= _request('id_rubrique');		// rubrique de la contrib
	$values['titre'] 		= _request('titre');			// titre de la contrib
	$values['texte'] 		= _request('texte');			// texte de la contrib
	$values['pseudo'] 		= _request('pseudo');			// pseudo ou nom de l'auteur
	$values['email'] 		= _request('email');			// email de l'auteur
	$values['mots'] 		= _request('mots');			// les mots-clésdate
	$values['date'] = _request('date');
	$values['date_modif'] = _request('date_modif');
	$values['supprimer_documents'] = _request('supprimer_documents');
	// editos
	$values['mise_en_edito'] = _request('mise_en_edito');
	$values['edito_complet'] = _request('edito_complet');
	// focus
	$values['mot_focus'] = _request('mot_focus');
	// si c'est pas un debut d'article
	if (_request('titre'))
		$values['ancre'] = '#etape_4';
	
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
			array('texte','extra','id_rubrique','titre','date'),
			array('spip_articles'),
			array('id_article='.$id_article)
		);
		$values['texte'] = $row['texte'] ;
		$values['titre'] = $row['titre'] ;
		$values['id_rubrique'] = $row['id_rubrique'] ;
		$extra = unserialize($row['extra']);
		$values['explication'] = $extra['OP_moderation'];
		$values['pseudo'] = $extra['OP_pseudo'];
		$values['email'] = $extra['OP_email'];
		$values['date'] = $row['date'];
		
		$values = indymedia_ajout_valeurs_mots ($values, $id_article);
	}

	include_spip('inc/securiser_action');
	$values['cle_ajouter_document'] = calculer_cle_action('ajouter-document-' . 'article' . '-' . $id_article);
	return $values;
}

/*
 * Vérification des champs du formulaire
 */
function formulaires_publication_ouverte_info_verifier_dist()
{
	$erreurs = array();
	$values = array();	
	$values['titre'] 		= _request('titre');			// titre de la contrib
	$values['texte'] 		= _request('texte');			// texte de la contrib
	$values['pseudo'] 		= _request('pseudo');			// pseudo ou nom de l'auteur
	$values['email'] 		= _request('email');			// email de l'auteur
	$values['mots'] 		= _request('mots');			// les mots-clés
	$values['fichier'] 		= _request('fichier');			// un fichier a 
	$values['etape'] 		= _request('etape');			// etape actuelle
	$values['id_article']	 	= _request('id_article');		// id de l'article temporaire
	$values['type']			= _request('type');			// article ou evenement ?
	$values['id_rubrique'] 		= _request('id_rubrique');		// rubrique de la contrib
	// editos
	$values['mise_en_edito'] = _request('mise_en_edito');
	$values['edito_complet'] = _request('edito_complet');
	// focus
	$values['mot_focus'] = _request('mot_focus');
	$values['date'] = _request('date');
	$supprimer_documents = _request('supprimer_documents');
	// si c'est pas un debut d'article
	if (_request('titre'))
		set_request('ancre', '#etape_4');

	if (!$values['type']) 
		$erreurs['type'] = _T('publication_erreur_type');

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
	if (count($erreurs) > 0){
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
					//'date_debut_indy' => $values['date_debut_indy'],
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
function formulaires_publication_ouverte_info_traiter_dist()
{
	$message = array();
	$values = array();
	$values['etape'] 		= _request('etape');			// etape actuelle
	$values['id_article']	 	= _request('id_article');		// id de l'article temporaire
	$values['type']			= _request('type');			// article ou evenement ?
	$values['id_rubrique'] 		= _request('id_rubrique');		// rubrique de la contrib
	$values['titre'] 		= _request('titre');			// titre de la contrib
	$values['texte'] 		= _request('texte');			// texte de la contrib
	$values['pseudo'] 		= _request('pseudo');			// pseudo ou nom de l'auteur
	$values['email'] 		= _request('email');			// email de l'auteur
	$values['mots'] 		= _request('mots');			// les mots-clés
	// editos
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
		/*
		 * tester si rubrique en attente activée
		 */
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
