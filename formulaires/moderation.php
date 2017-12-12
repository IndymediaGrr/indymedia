<?php

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/config');
// chargement des valeurs par defaut des champs du formulaire
// Le formulaire doit etre charge de la sorte :
// #FORMULAIRE_MODERATION{#ID_ARTICLE}

function formulaires_moderation_charger_dist($id_article)
{
	$id_article = (int) $id_article;

	$valeurs = array();

	// on recupere des infos sur l'article	
	$row = sql_fetsel(
		array('id_rubrique','extra','date_debut_indy','accepter_forum'),
		array('spip_articles'),
		array('id_article='.$id_article)
	);

	$valeurs['id_article'] = $id_article;
	$valeurs['id_rubrique_change'] = $row['id_rubrique'];
	$valeurs['accepter_forum'] = $row['accepter_forum'];
	// si il n'y a rien, cela veux dire que l'on est en postriori
	if (!$valeurs['accepter_forum']) $valeurs['accepter_forum'] = 'pos';

	$extra = unserialize($row['extra']);
	$valeurs['explication'] = $extra['OP_moderation'];

	// on recupere la date, l'heure et le lieu de l'evenement
	$valeurs = indymedia_ajout_valeurs_date ($valeurs, $row['date_debut_indy']);
	$valeurs = indymedia_ajout_valeurs_lieu ($valeurs, $id_article);
	$valeurs = indymedia_ajout_valeurs_mots ($valeurs, $id_article);

	return $valeurs;
}

// verification des valeurs du formulaire
function formulaires_moderation_verifier_dist()
{
	$erreurs = array();

	$values = array();

	$values['id_article'] = (int) _request('id_article');
	$values['id_rubrique'] = (int) _request('id_rubrique_change');
	$values['id_lieu'] = (int) _request('id_lieu_change');
	
	// Action demande
	$values['publie'] = _request('publie');
	$values['refus'] = _request('refus');
	$values['debat'] = _request('debat');
	$values['attente'] = _request('attente');
	$values['modifier_rubrique'] = _request('modifier_rubrique');
	$values['modifier_evenement'] = _request('modifier_evenement');
	$values['modifier_mots'] = _request('modifier_mots');
	$values['modifier_forum'] = _request('modifier_forum');
	$values['ajouter_debat'] = _request('ajouter_debat');
	
	$values['explication'] = _request('explication');
	$values['jour_evenement'] 	= _request('jour_evenement'); 		// jour de l'événement
	$values['mois_evenement'] 	= _request('mois_evenement'); 		// mois de l'événement
	$values['annee_evenement'] 	= _request('annee_evenement'); 		// annee de l'événement
	$values['date_evenement'] = _request('date_evenement');
	$values['heure_evenement'] = _request('heure_evenement');
	$values['lieu_evenement']	= _request('lieu_evenement');		// lieu de l'evenement
	$values['mots'] = _request('mots');
	$values['texte_debat'] = _request('texte_debat');
	$values['accepter_forum'] = _request('accepter_forum');
	$values['mise_en_edito'] = _request('mise_en_edito');
	$values['edito_complet'] = _request('edito_complet');
	// focus
	$values['mot_focus'] = _request('mot_focus');
	
	if (!$values['id_article'])
	{
		$erreurs['id_article'] = _T("erreur_moderation_interne");
	}

	// si deplacement en debat ou en refus, on a besoin d'une explication
	if ($values['debat'] || $values['refus'])
	{
		if ($values['explication']=='')
		{
			$erreurs['explication'] = _T('erreur_moderation_explication_oublie');
		}
	}

	// si modification d'un evenement // changement de evenement	
	if (($values['modifier_rubrique']) && (lire_config('indymedia/rubrique_agenda')==$values['id_rubrique']))
	{	
		/* Vérification jour_evenement */	
		if (!$values['jour_evenement'])	$erreurs['jour_evenement'] = "jour_evenement";
		if ($values['jour_evenement'] && (preg_match("@^([0-3]{1}[0-9]{1})$@",$values['jour_evenement']) !== 1))	
			$erreurs['jour_evenement'] = "jour_evenement";
		/* Vérification mois_evenement */	
		if (!$values['mois_evenement'])	$erreurs['mois_evenement'] = "mois_evenement";
		if ($values['mois_evenement'] && (preg_match("@^([0-1]{1}[0-9]{1})$@",$values['mois_evenement']) !== 1))	
			$erreurs['mois_evenement'] = "mois_evenement";
		/* Vérification annee_evenement */	
		if (!$values['annee_evenement'])	$erreurs['annee_evenement'] = "annee_evenement";
		if ($values['annee_evenement'] && (preg_match("@^([0-9]{4})$@",$values['annee_evenement']) !== 1))	
			$erreurs['annee_evenement'] = "annee_evenement";
		/* Vérification heure_evenement */			
		if (!$values['heure_evenement'])$erreurs['heure_evenement'] = "heure_evenement";
		if ($values['heure_evenement'] && (preg_match("@^(([0-1]{0,1}[0-9]{1})|(2[0-3]{1}))(:[0-5]{0,1}[0-9]{1}){1,2}$@",$values['heure_evenement']) !== 1) )
			$erreurs['heure_evenement'] = "heure_evenement";
		/* Vérification lieu_evenement */
		if ((!$values['id_lieu'] && !$values['lieu_evenement']) ) 
			$erreurs['lieu_evenement'] = _T("lieu_evenement");
	}

	// si modification mots-cles	
	if ($values['modifier_mots'])
	{
		if (count($values['mots']) == 1 && ($values['mots'][0] == ''))
		{
			$erreurs['mots'] = _T("erreur_moderation_mots_minimum");
		}
		else if (count($values['mots']) < 1)  $erreurs['mots'] = _T("erreur_moderation_mots_minimum");
	}
	
	// si ajout au debat
	if ($values['ajouter_debat'])
	{
		if ($values['texte_debat'] == '')
		{
			$erreurs['texte_debat'] = _T("erreur_moderation_debat_vide");
		}
	}

	// si modification forum
	if ($values['modifier_forum'])
	{
		if (($values['accepter_forum'] != 'non') && ($values['accepter_forum'] != 'pri') && ($values['accepter_forum'] != 'pos') )
		{
			$erreurs['accepter_forum'] = _T("erreur_moderation_accepter_forum");
		}
	}
	return $erreurs;
}

// OK, on y va
function formulaires_moderation_traiter_dist()
{
	$message = array();
	$values = array();

	$values['id_article'] = (int) _request('id_article');
	$values['id_rubrique'] = (int) _request('id_rubrique_change');
	$values['id_lieu'] = (int) _request('id_lieu_change');
	
	// Action demande
	$values['publie'] = _request('publie');
	$values['refus'] = _request('refus');
	$values['debat'] = _request('debat');
	$values['attente'] = _request('attente');
	$values['modifier_rubrique'] = _request('modifier_rubrique');
	$values['modifier_evenement'] = _request('modifier_evenement');
	$values['modifier_mots'] = _request('modifier_mots');
	$values['ajouter_debat'] = _request('ajouter_debat');
	$values['modifier_forum'] = _request('modifier_forum');
	
	$values['explication'] = _request('explication');
	$values['jour_evenement'] 	= _request('jour_evenement'); 		// jour de l'événement
	$values['mois_evenement'] 	= _request('mois_evenement'); 		// mois de l'événement
	$values['annee_evenement'] 	= _request('annee_evenement'); 		// annee de l'événement
	$values['date_evenement'] = _request('date_evenement');
	$values['heure_evenement'] = _request('heure_evenement');
	$values['mots'] = _request('mots');
	$values['texte_debat'] = _request('texte_debat');
	$values['accepter_forum'] = _request('accepter_forum');	
	$values['lieu_evenement']	= _request('lieu_evenement');		// lieu de l'evenement
	$values['ville_evenement']	= _request('ville_evenement');		// lieu de l'evenement
	$values['adresse_evenement']	= _request('adresse_evenement');	// lieu de l'evenement
	$values['tel_evenement']	= _request('tel_evenement');		// lieu de l'evenement
	$values['web_evenement']	= _request('web_evenement');		// lieu de l'evenement
	$values['email_evenement']	= _request('email_evenement');		// lieu de l'evenement
	$values['lieu_evenement_existe']= _request('lieu_evenement_existe');	// lieu de l'evenement
	// editos
	$values['mise_en_edito'] = _request('mise_en_edito');
	$values['edito_complet'] = _request('edito_complet');
	// focus
	$values['mot_focus'] = _request('mot_focus');
	
	// construction du meta explication (champs "modération" dans la table article)
	// Attention de ne pas perdre les autres champs extra, faut aller les recuperer dans la table
	$row = sql_fetsel(
		array('extra'),
		array('spip_articles'),
		array('id_article = '.$values['id_article'])
		);

	$old_extra = unserialize($row['extra']);
        $extra = array(
              "OP_pseudo"=>$old_extra['OP_pseudo'],
              "OP_mail"=>$old_extra['OP_mail'],
              "OP_moderation"=>entites_html(stripslashes($values['explication']))
              );
              
        $extra=serialize($extra);
	if ($values['publie'])
	{
		sql_updateq (
			'spip_articles',
			array(
				'statut' => 'publie'
			),
			'id_article='.$values['id_article']
		);
		$message = _T("article_statut_publie");
	}
	
	else if ($values['refus'])
	{
		sql_updateq (
			'spip_articles',
			array(
				'statut' => 'refuse',
				'extra' => $extra
			),
			'id_article='.$values['id_article']
		);
		$message = _T("article_statut_refus");
		//moderation_notification($values);
		$ok=moderation_notification($values);
		if ($ok) $message = $message." - "._T("email_envoye");
		else $message = $message." - "._T("email_echoue");
	}
	
	else if ($values['debat'])
	{
		sql_updateq (
			'spip_articles',
			array(
				'statut' => 'debat',
				'extra' => $extra
			),
			'id_article='.$values['id_article']
		);
		$message = _T("article_statut_debat");
		$ok=moderation_notification($values);
		if ($ok) $message = $message." - "._T("email_envoye");
		else $message = $message." - "._T("email_echoue");
	}
	
	else if ($values['attente'])
	{
		sql_updateq (
			'spip_articles',
			array(
				'statut' => 'prop'
			),
			'id_article='.$values['id_article']
		);
		$message = _T("article_statut_attente");
	}
	
	if ($values['modifier_rubrique'])
	{
		sql_updateq (
			'spip_articles',
			array(
				'id_rubrique' => $values['id_rubrique']
			),
			'id_article='.$values['id_article']
		);
		// si changement en evenement		
		if (lire_config('indymedia/rubrique_agenda')==$values['id_rubrique'])
		{
			if ($values['jour_evenement'] && $values['mois_evenement'] && $values['annee_evenement'] && $values['heure_evenement'])
			{
				$values['date_debut_indy'] = $values['annee_evenement'].'-'.$values['mois_evenement'].'-'.$values['jour_evenement'].' '.$values['heure_evenement'];
			}
			//list($jour,$mois,$annee) = explode('/',$values['date_evenement']);
			$date_evenement = $values['date_debut_indy'];
			//$message = array('message_erreur' =>  $date_evenement);
			sql_updateq (
				'spip_articles',
				array(
					'date_debut_indy' => $date_evenement
				),
				'id_article='.$values['id_article']
			);
			
			// mise a jour du lieu
			if ($values['lieu_evenement'])
			{
				$values['lieu_evenement'] = entites_html(stripslashes($values['lieu_evenement']));
				$values['ville_evenement'] = entites_html(stripslashes($values['ville_evenement']));
				$values['adresse_evenement'] = entites_html(stripslashes($values['adresse_evenement']));
				$values['tel_evenement'] = entites_html(stripslashes($values['tel_evenement']));
				$values['web_evenement'] = entites_html(stripslashes($values['web_evenement']));
				$values['email_evenement'] = entites_html(stripslashes($values['email_evenement']));
			}
			sql_delete("spip_lieux_objets",array("objet='article'",'id_objet='.$values['id_article']));
			if ($values['lieu_evenement']) 
				indymedia_ajout_nouveau_lieu($values['id_article'],$values);
			else if ($values['id_lieu'])	
				indymedia_ajout_lieu_lien($values['id_article'],$values['id_lieu']);
			
			$message = array('message_ok' =>  _T('moderation_modifier_evenement'));
		}
		else{
			$message = array('message_ok' =>  _T('moderation_change_rubrique'));
		}
	}
	else{
		//$message = array('message_erreur' =>  "id_rubrique ".$values['id_rubrique']);
	}
	indymedia_traiter_valeurs_edito($values);
		
	if ($values['modifier_mots'])
	{
		// on vire tous les anciens mots cles sauf "edito"
		sql_delete("spip_mots_liens",'id_objet='.$values['id_article']." AND objet = 'article' AND id_mot != ".lire_config('indymedia_accueil/mot_edito'));
		// et on ajoute les nouveaux
		if (is_array($values['mots']))
		{
			foreach($values['mots'] as $id_mot)
			{
				$id_mot = (int) $id_mot;
				if ($id_mot != 0)
				{
					sql_insertq(
						'spip_mots_liens',
						array(
							'id_mot' => $id_mot,
							'id_objet' => $values['id_article'],
							'objet' => 'article'
						)
					);
				}
			}
		}
		$message = array('message_ok' =>  _T('moderation_modifier_mots'));
	}
	
	if ($values['ajouter_debat'])
	{
		sql_insertq(
			"spip_debats",
			array(
				'id_article' => $values['id_article'],
				'id_auteur' => $GLOBALS['auteur_session']['id_auteur'],
				'texte' => entites_html(stripslashes($values['texte_debat'])),
				'date' => date('Y-m-d H:i:s')
			)
		);
		$message = array('message_ok' =>  _T('moderation_ajouter_debat'));
		$ok=moderation_notification($values);
		if ($ok) $message = $message." - "._T("email_envoye");
		else $message = $message." - "._T("email_echoue");
		
	}
	
	if ($values['modifier_forum'])
	{
		sql_updateq(
			"spip_articles",
			array(
				'accepter_forum' => $values['accepter_forum']
			),
			"id_article=".$values['id_article']
		);
		$message = array('message_ok' =>  _T('moderation_modifier_forum'));
	}
	return $message;
}

?>
