<?php

if (!defined('_ECRIRE_INC_VERSION')) return;
$GLOBALS['indy_version'] = "0.1.1";

/*
 * on utilise nospam
*/
function indy_nospam ($values, $erreurs, $id_article)
{
include_spip('inc/texte');
	
    // si nospam est present on traite les spams
    if (include_spip('inc/nospam')) {
            $caracteres = compter_caracteres_utiles($values['texte']);
            //$erreurs['texte_message'] = "nospam";
            // moins de 10 caracteres sans les liens = spam !
            if ($caracteres < 10){
                    $erreurs['texte_message'] = _T('nospam:erreur_spam');
            }
            // on analyse le sujet
            $infos_sujet = analyser_spams($values['titre']);
            // si un lien dans le sujet = spam !
            if ($infos_sujet['nombre_liens'] > 0)
                    $erreurs['sujet_message'] = _T('nospam:erreur_spam');
     
            // on analyse le texte
            $infos_texte = analyser_spams($values['texte']);
            if ($infos_texte['nombre_liens'] > 0) {
                    // si un lien a un titre de moins de 3 caracteres = spam !
                    if ($infos_texte['caracteres_texte_lien_min'] < 3) {
                           ;// $erreurs['texte_message'] = _T('nospam:erreur_spam');
                    }
                    // si le texte contient plus de trois lien = spam !
                    if ($infos_texte['nombre_liens'] >= 10)
                            ;
                            // $erreurs['texte_message'] = _T('nospam:erreur_spam');
            }
			// verifier qu'un article identique n'a pas ete publie il y a peu
			if (sql_countsel('spip_articles','texte='.sql_quote($values['texte'])." AND id_article!=$id_article AND statut IN ('publie','prop','prepa')")>0)
				 {
						$erreurs['texte_message'] = _T('nospam:erreur_spam')." article identique";
						//$values['statut']='prop';
				}
    }
	return $erreurs;
}
/*
 * le filtre lettreMajuscule permet de mettre la première lettre d'un mot en majuscule
 */
function lettreMajuscule($texte)
{

  if ($texte !== '')
  {
    $texte[0] = strtoupper($texte[0]);
  }
  return $texte;
}
 
function moderation_notification($values)
{
	// Envoyer la notification a la mailling list de moderation
	$liste_moderation = lire_config('indymedia/liste_moderation');
	$envoyeur = lire_config('indymedia/liste_envoyeur');

	$lien_article = '<'.$GLOBALS['meta']['adresse_site'].'/spip.php?article'.$values['id_article'].'>';

	// on recupere des infos sur l'article	
	$row = sql_fetsel(
		array('titre'),
		array('spip_articles'),
		array('id_article='.$values['id_article'])
	);

	$body = "
	"._T('notification_lien_article')." : $lien_article
	";


	if ($values['debat'])
	{
		$sujet = '[Notification de mise en debat] '.$values['id_article'].' "'.$row['titre'].'"';
		$body .= "
		Raison : ".$values['explication']."
		";
	}
	if ($values['refus'])
	{
		$sujet = '[Notification de refus] '.$values['id_article'].' "'.$row['titre'].'"';
		$body .= "
		Raison : ".$values['explication']."
		";
	}
	if ($values['ajouter_debat'])
	{
		$sujet = '[Notification de debat] '.$values['id_article'].' "'.$row['titre'].'"';
		$body .= "
		Message : ".$values['texte_debat']."
		";

	}

	$envoyer_mail = charger_fonction('envoyer_mail', 'inc/');
	
	$ok=$envoyer_mail($liste_moderation, $sujet, $body, $envoyeur);
	return $liste_moderation;
}
/*
 * Notification de publication
 */

function indymedia_notifier($values)
{

	// Envoyer la notification à la mailling list de modération
	$liste_moderation = lire_config('indymedia/liste_moderation');
	$envoyeur = lire_config('indymedia/liste_envoyeur');

	$sujet = '[Notification de publication] '.$values['id_article'].' "'.$values['titre'].'"';
	$lien_moderation = $GLOBALS['meta']['adresse_site'].'/spip.php?article'.$values['id_article'];
	
	include_spip('public/balises');	
	//$header = balise_HTTP_HEADER('Content-Type:text/html');
	$head = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml" dir="#LANG_DIR" xml:lang="#LANG" lang="#LANG">
		<head>
		<meta http-equiv="content-type" content="text/html; charset=#CHARSET" />
		<meta name="language" content="#LANG" />
		</head>
		<body>';
	$bottom = "</body>
		</html>";
	$body .=_T('notification_lien_article')." : ".$lien_moderation." - ";
	$body .= "
	
	"._T('titre')." : ".couper($values['titre'],200)."
	
	".couper($values['texte'],200);
	include_spip('inc/mail');
	
	envoyer_mail($liste_moderation, $sujet, $body, $envoyeur);
}

/*
 * Nettoyage d'articles non termines
 */

function indymedia_nettoyage()
{	
	$auj_php  = mktime(0, 0, 0, date("m"),   date("d"),   date("Y"));
	$hier_php  = mktime(0, 0, 0, date("m"),   date("d")-1,   date("Y"));
	$hier_sql = date ("Y-m-d H:i:s", $hier_php);
	
	//ecrire_config('indymedia/date_nettoyage', $hier_php);
	// initialiser
	if (!lire_config('indymedia/date_nettoyage')){
		ecrire_config('indymedia/date_nettoyage', $hier_sql);
	}
	//$bool = (lire_config('indymedia/date_nettoyage') < $auj_php)?'oui':'non';
	// dernier nettoyage + d'un jour, on nettoie
	if (lire_config('indymedia/date_nettoyage') < $auj_php){
		// maj date dernier nettoyage
		ecrire_config('indymedia/date_nettoyage', $auj_php);
		sql_delete('spip_articles', " texte='' AND titre='' AND id_rubrique=0 AND date<'".$hier_sql."'");
	}
}

/*
 * Insertion définitive des mots-cles à l'article
 */
function indymedia_ajout_mots($id_article,$mots)
{
	$id_article = (int) $id_article;
	
	// nettoyage, on vire tous les liens avec mots cles sauf "edito"
	sql_delete("spip_mots_liens",'id_objet='.$id_article." AND objet = 'article' AND id_mot != ".lire_config('indymedia_accueil/mot_edito'));
	
	if (is_array($mots))
	{
		foreach($mots as $id_mot)
		{
			$id_mot = (int) $id_mot;
			if ($id_mot != 0)
			{
				sql_insertq(
					'spip_mots_liens',
					array(
						'id_mot' => $id_mot,
						'id_objet' => $id_article,
						'objet' => 'article'
					)
				);
			}
		}
	}
}

/*
 * Traite les champs en vue de leur insertion dans la base de données
 */
function indymedia_traiter_article($values)
{
	$values['id_rubrique'] = (int) $values['id_rubrique'];
	$values['id_article'] = (int) $values['id_article'];
	// on nettie le html sauf si balise multi
	if (!strpos($values['titre'], '</multi>'))
		$values['titre'] = stripslashes($values['titre']);
	else
		$values['titre'] = stripslashes($values['titre']);
	$values['texte'] = stripslashes($values['texte']);
	$values['pseudo'] = stripslashes($values['pseudo']); 
	$values['email'] = stripslashes($values['email']);
	$values['langue'] = '';
	$values['id_secteur'] = '';

	$values['statut'] = entites_html(stripslashes($values['statut']));
	// si deja publie, on touche pas a la date
	if ($values['statut'] == 'prepa')
		$values['date'] = date('Y-m-d H:i:s');
	else
		$values['date'] = _request('date');
	// on maj date modif
	$values['date_modif'] = date('Y-m-d H:i:s');
	$values['date_debut_indy'] = date('Y-m-d H:i:s');
	include_spip('inc/config');
	// pour les évenements :
	if ($values['type'] == 'evenement')
	{
		$values['id_rubrique'] = lire_config('indymedia/rubrique_agenda');

		if ($values['jour_evenement'] && $values['mois_evenement'] && $values['annee_evenement'] && $values['heure_evenement'])
		{
			$values['date_debut_indy'] = $values['annee_evenement'].'-'.$values['mois_evenement'].'-'.$values['jour_evenement'].' '.$values['heure_evenement'];
		}
		if ($values['lieu_evenement_existe'])
		{
			$values['id_lieu'] = (int) $values['lieu_evenement_existe'];
		}
		if ($values['lieu_evenement'])
		{
			$values['lieu_evenement'] = entites_html(stripslashes($values['lieu_evenement']));
			$values['ville_evenement'] = entites_html(stripslashes($values['ville_evenement']));
			$values['adresse_evenement'] = entites_html(stripslashes($values['adresse_evenement']));
			$values['tel_evenement'] = entites_html(stripslashes($values['tel_evenement']));
			$values['web_evenement'] = entites_html(stripslashes($values['web_evenement']));
			$values['email_evenement'] = entites_html(stripslashes($values['email_evenement']));
		}
	}

	if (($values['id_rubrique']) && is_int($values['id_rubrique']))
	{
		$row = sql_fetsel(
			array('lang, id_secteur'),
			array('spip_rubriques'),
			array('id_rubrique='.sql_quote($values['id_rubrique']))
			);
		$values['id_secteur'] = $row['id_secteur'];
		$values['langue'] = $row['lang'];
	}

	$extra = array(
		"OP_pseudo"=>$values['pseudo'],
		"OP_email"=>$values['email'],
	);
	$values['extra'] = serialize($extra);

	return $values;

}
function extra($extra,$arg){
	$values = unserialize($extra);
	return $values[$arg];
}

/*
 * Vérifie si le statut de l'article en cours de rédaction est bien "prepa"
 * Sinon, cela veux dire que l'article existe déjà ...
 * si auteur=admin alors ok pour modififier l'article
 */
function indymedia_verifier_statut($statut)
{
	if (($statut!= 'prepa') && ($GLOBALS['auteur_session'] && ($GLOBALS['auteur_session']['statut']!="0minirezo")))
		return false;
	return true;
}

function indymedia_statut_article($id_article)
{
	$id_article = (int) $id_article;
	$row = sql_fetsel(
		array('statut'),
		array('spip_articles'),
		array('id_article = '.$id_article)
	);
	$statut = $row['statut'];
	return $statut;
}

/*
 * Création de l'article s'il n'existe pas encore
 * Renvoi l'id de l'article
 */
function indymedia_creer_article()
{
	sql_insertq(
		'spip_articles',
		array(
			'statut' => 'prepa',
			'date' => date('Y-m-d H:i:s'),
			'date_modif' => date('Y-m-d H:i:s')
			)
		);
	$row = sql_fetsel(
		array('MAX(id_article) as max'),
		array('spip_articles')
	);

	return $row['max'];
}

/*
 * Ajout des documents
 */
function indymedia_traiter_document($id_article,$erreurs)
{// Ajouter un document
		if (isset($_FILES['ajouter_document'])
		AND $_FILES['ajouter_document']['tmp_name']) {
			$files[] = array('tmp_name'=>$_FILES['ajouter_document']['tmp_name'],'name'=>$_FILES['ajouter_document']['name']);
			$ajouter_documents = charger_fonction('ajouter_documents','action');
			$ajouter_documents(
				'new',
				$files,
				'article',
				$id_article,
				'document');
			// supprimer le temporaire et ses meta donnees
			spip_unlink($_FILES['ajouter_document']['tmp_name']);
			spip_unlink(preg_replace(',\.bin$,',
				'.txt', $_FILES['ajouter_document']['tmp_name']));
		}
/*
include_spip('inc/forum');
	if (!isset($GLOBALS['visiteur_session']['tmp_forum_document']))
		session_set('tmp_forum_document',
			sous_repertoire(_DIR_TMP, 'documents_forum') . md5(uniqid(rand())));
	$tmp = $GLOBALS['visiteur_session']['tmp_forum_document'];
	$doc = &$_FILES['ajouter_document'];
	if (isset($_FILES['ajouter_document'])
		AND $_FILES['ajouter_document']['tmp_name']
	){
		// securite :
		// verifier si on possede la cle (ie on est autorise a poster)
		// (sinon tant pis) ; cf. charger.php pour la definition de la cle
		include_spip('inc/securiser_action');
		if (_request('cle_ajouter_document')!=calculer_cle_action($a = "ajouter-document-article-$id_article")){
			//$erreurs['document_indy'] = _T('forum:documents_interdits_forum');
			//unset($_FILES['ajouter_document']);
			;
		}
		//else {
			include_spip('inc/joindre_document');
			include_spip('action/ajouter_documents');
			list($extension, $doc['name']) = fixer_extension_document($doc);
			//$acceptes = forum_documents_acceptes();

			
				include_spip('inc/getdocument');
				if (!deplacer_fichier_upload($doc['tmp_name'], $tmp . '.bin'))
					$erreurs['document_indy'] = _T('copie_document_impossible');

				#		else if (...)
				#		verifier le type_document autorise
				#		retailler eventuellement les photos

			// si ok on stocke les meta donnees, sinon on efface
			if (isset($erreurs['document_indy'])){
				spip_unlink($tmp . '.bin');
				unset ($_FILES['ajouter_document']);
			}
			else {
				$doc['tmp_name'] = $tmp . '.bin';
				ecrire_fichier($tmp . '.txt', serialize($doc));
			}
		//}
	} // restaurer le document uploade au tour precedent
	elseif (file_exists($tmp . '.bin')){
		if (_request('supprimer_document_ajoute')){
			spip_unlink($tmp . '.bin');
			spip_unlink($tmp . '.txt');
		}
		elseif (lire_fichier($tmp . '.txt', $meta)){
			$doc = @unserialize($meta);
		}
	}
	
*/
	
}                                                                                     
                                                  
/*
 * le filtre no_space supprime tous les espaces d'un texte
 * necessaire lors de l'utilisation d'une balise dans un marqueur <a>
 * norme W3C
 */
function no_space($texte)
{
  $texte = preg_replace('/\s+/', '', $texte);
  return $texte;
}
                                                            
                                                            
/* 
 * Une balise qui affiche la version du plugin
 */
function balise_INDY_VERSION($p)
{
  $p->code ='$GLOBALS[\'indy_version\']';
  $p->statut = 'php';
  return $p;
}

// le critère {openKey} permet de restreindre la selection des mots clès
// à ceux selectionné dans l'interface de configuration
function critere_openKey($idb, &$boucles, $crit) {
        $boucle = &$boucles[$idb];
	$id_table = $boucle->id_table;

	$id_groupe = $id_table.'.id_groupe';

	$list = '';
	foreach (lire_config('indymedia') as $key => $val) {
	//echo $GLOBALS['meta'];
	//foreach ($GLOBALS['meta'] as $key => $val) {//echo $key." ";echo $val."<br /> ";
		if ((substr($key,0,7)) == "groupe_") {
			if ($val == "openPublishing") {
				$op_rub = substr($key,7);
				$list .= $op_rub .',';
			}
		}
	}
	if(strlen($list)){
		$list = substr($list,0,strlen($list)-1);
		$boucle->where[] = array("'IN'", "'$id_groupe'","'($list)'");
	}
    $boucle->modificateur["openKey"] = true;
}
// le critère {openKey} permet de restreindre la selection des mots clès
// à ceux selectionné dans l'interface de configuration
function critere_closeKey($idb, &$boucles, $crit) {
        $boucle = &$boucles[$idb];
	$id_table = $boucle->id_table;

	$id_groupe = $id_table.'.id_groupe';

	$list = '';
	foreach (lire_config('indymedia') as $key => $val) {
	//echo $GLOBALS['meta'];
	//foreach ($GLOBALS['meta'] as $key => $val) {//echo $key." ";echo $val."<br /> ";
		if ((substr($key,0,7)) == "groupe_") {
			if ($val == "openPublishing") {
				$op_rub = substr($key,7);
				$list .= $op_rub .',';
			}
		}
	}
	if(strlen($list)){
		$list = substr($list,0,strlen($list)-1);
		$boucle->where[] = array("'not IN'", "'$id_groupe'","'($list)'");
	}
    $boucle->modificateur["closeKey"] = true;
}

include_spip('inc/json');

function todate($t){return date('Y-m-d H:i:s',$t);}
/*
 * Insertion définitive du lien lieu-article lié à l'événement
 */
function indymedia_ajout_lieu_lien($id_article,$id_lieu)
{
	$id_article = (int) $id_article;
	$id_lieu = (int) $id_lieu;
	$row_idl = sql_fetsel(
		array('id_lieu'),
		array('spip_lieux_objets'),
		array("id_objet = $id_article AND objet = 'article'")
		);
	if(!isset($row_idl['id_lieu'])){
		sql_insertq(
			'spip_lieux_objets',
			array(
				'id_lieu' => $id_lieu,
				'objet' => 'article',
				'id_objet' => $id_article
			)
		);
	}
}

/*
 * Création d'un nouveau lieu et lien avec l'article
 */
function indymedia_ajout_nouveau_lieu($id_article,$values)
{
	$id_article = (int) $id_article;
	$row_idl = sql_fetsel(
		array('id_lieu'),
		array('spip_lieux_objets'),
		array('id_objet = '.$id_article)
		);
	// nouveau lieu ? 
	if(!isset($row_idl['id_lieu'])){
		$id_lieu = sql_insertq("spip_lieux");
	}
	// sinon un admin fait une maj
	else{
		$id_lieu = $row_idl['id_lieu'];
	}
	sql_updateq(
		'spip_lieux',
		array(
			'nom' =>  entites_html(stripslashes($values['lieu_evenement'])),
			'ville' =>  entites_html(stripslashes($values['ville_evenement'])),
			'adresse' =>  entites_html(stripslashes($values['adresse_evenement'])),
			'tel' =>  entites_html(stripslashes($values['tel_evenement'])),
			'site_web' =>  entites_html(stripslashes($values['web_evenement'])),
			'email' =>  entites_html(stripslashes($values['email_evenement']))

		),
		"id_lieu=".$id_lieu
	);
	// nouveau lieu ? 
	if(!isset($row_idl['id_lieu'])){
		indymedia_ajout_lieu_lien($id_article,$id_lieu);
	}
}

/*
 * inserer la date et heure dans valeurs formulaire
 */
function indymedia_ajout_valeurs_date($valeurs, $date_evenement)
{
	$valeurs['jour_evenement'] = substr($date_evenement,8,2);
	$valeurs['mois_evenement'] = substr($date_evenement,5,2);
	$valeurs['annee_evenement'] = substr($date_evenement,0,4);
	$valeurs['heure_evenement'] = substr($date_evenement,11,2).':'.substr($date_evenement,14,2);
	return $valeurs;
}

/*
 * inserer lieu dans valeurs formulaire
 */
function indymedia_ajout_valeurs_lieu($valeurs, $id_article)
{
	$row = sql_fetsel(
		array('id_lieu'),
		array('spip_lieux_objets'),
		array('id_objet = '.$id_article)
		);
	if (isset($row['id_lieu'])){
		$valeurs['id_lieu_change'] = $row['id_lieu'];
		$valeurs['lieu_evenement_existe'] = $row['id_lieu'];
		$row = sql_fetsel(
			array('nom, ville, adresse, tel, site_web, email'),
			array('spip_lieux'),
			array('id_lieu = '.$row['id_lieu'])
			);
			$valeurs['lieu_evenement'] = $row['nom'];
			$valeurs['ville_evenement'] = $row['ville'];
			$valeurs['adresse_evenement'] = $row['adresse'];
			$valeurs['tel_evenement'] = $row['tel'];
			$valeurs['web_evenement'] = $row['site_web'];
			$valeurs['email_evenement'] = $row['email'];
	}
	else{
			$valeurs['lieu_evenement'] = _request('lieu_evenement');
			$valeurs['ville_evenement'] = _request('ville_evenement');
			$valeurs['adresse_evenement'] = _request('adresse_evenement');
			$valeurs['tel_evenement'] = _request('tel_evenement');
			$valeurs['web_evenement'] = _request('web_evenement');
			$valeurs['email_evenement'] = _request('email_evenement');
	}
	//spip_log("lieu_evenement_existe--".$valeurs['lieu_evenement_existe'],	'test_'._LOG_AVERTISSEMENT);
	return $valeurs;
}

/*
 * inserer mots dans valeurs formulaire
 */
function indymedia_ajout_valeurs_mots($valeurs, $id_article)
{
	include_spip('inc/config');
	// les mots-cles
	$row = sql_select(
		array('MOTS.id_mot'),
		array('MOTS' => 'spip_mots','LIEN'=>'spip_mots_liens'),
		array('MOTS.id_mot = LIEN.id_mot','LIEN.id_objet='.$id_article, "LIEN.objet = 'article'")
	);
	while($r = sql_fetch($row))
	{
		// mot cle edito ? complet ? focus ?
		if ($r['id_mot']==lire_config('indymedia_accueil/mot_edito'))
			$valeurs['mise_en_edito'] = 'mise_en_edito';
		else if ($r['id_mot']==lire_config('indymedia_accueil/mot_edito_complet'))
			$valeurs['edito_complet'] = 'edito_complet';
		else if ($r['id_mot']==lire_config('indymedia_accueil/mot_focus'))
			$valeurs['mot_focus'] = 'mot_focus';
		else 
		// ajout comme un tag normal
		$valeurs['mots'][] = $r['id_mot'];
		
	}
	return $valeurs;
}

/*
 * mettre ou supprimer les mot cle edito - edito_complet - focus
 */
function indymedia_traiter_valeurs_edito($valeurs)
{
	$id_article = (int) $valeurs['id_article'];
	include_spip('action/editer_liens');
	include_spip('inc/config');
	// si pas en edito, on vire si existe
	if ($valeurs['mise_en_edito']!='mise_en_edito')
		objet_dissocier(array("mot"=>lire_config('indymedia_accueil/mot_edito')), array("article"=>$id_article));
	// sinon si en edito, on ajoute
	else
		objet_associer(array("mot"=>lire_config('indymedia_accueil/mot_edito')), array("article"=>$id_article));
		
	// si pas en edito complet, on vire si existe
	if ($valeurs['edito_complet']!='edito_complet')
		objet_dissocier(array("mot"=>lire_config('indymedia_accueil/mot_edito_complet')), array("article"=>$id_article));
	// sinon si en edito complet, on ajoute le lien
	else
		objet_associer(array("mot"=>lire_config('indymedia_accueil/mot_edito_complet')), array("article"=>$id_article));
		
	// si pas en focu complet, on vire si existe
	if ($valeurs['mot_focus']!='mot_focus')
		objet_dissocier(array("mot"=>lire_config('indymedia_accueil/mot_focus')), array("article"=>$id_article));
	// sinon si en edito complet, on ajoute le lien
	else
		objet_associer(array("mot"=>lire_config('indymedia_accueil/mot_focus')), array("article"=>$id_article));
}

// SURCHARGE pit => moderation syndic par defaut !!!!
// http://doc.spip.org/@action_editer_site_dist
function action_editer_site($arg=null) {

	if (is_null($arg)){
		$securiser_action = charger_fonction('securiser_action', 'inc');
		$arg = $securiser_action();
	}

	if (!$id_syndic = intval($arg)){
		$id_syndic = site_inserer_hack(_request('id_parent'));
		if ($logo = _request('logo')
		  AND $format_logo = _request('format_logo')) {
			include_spip('inc/distant');
			$logo = _DIR_RACINE . copie_locale($logo);
			@rename($logo,_DIR_IMG . 'siteon'.$id_syndic.'.'.$format_logo);
		}
	}

	if (!$id_syndic)
		return array(0,'');

	include_spip('action/editer_site');
	$err = site_modifier($id_syndic);

	return array($id_syndic,$err);
}


/**
 * Inserer un nouveau site en base
 *
 * http://doc.spip.org/@insert_syndic
 *
 * @param  $id_rubrique
 * @return bool
 */
function site_inserer_hack($id_rubrique) {

	include_spip('inc/rubriques');

	// Si id_rubrique vaut 0 ou n'est pas definie, creer le site
	// dans la premiere rubrique racine
	if (!$id_rubrique = intval($id_rubrique)) {
		$id_rubrique = sql_getfetsel("id_rubrique", "spip_rubriques", "id_parent=0",'', '0+titre,titre', "1");
	}

	// Le secteur a la creation : c'est le secteur de la rubrique
	$id_secteur = sql_getfetsel("id_secteur", "spip_rubriques", "id_rubrique=".intval($id_rubrique));
	// eviter un null si la rubrique n'existe pas (rubrique -1 par exemple)
	if (!$id_secteur)
		$id_secteur = 0;

	$champs = array(
		'id_rubrique' => $id_rubrique,
		'id_secteur' => $id_secteur,
		'statut' => 'prop',
		'moderation' => 'oui',	// HACK
		'date' => date('Y-m-d H:i:s'));
	
	// Envoyer aux plugins
	$champs = pipeline('pre_insertion',
		array(
			'args' => array(
				'table' => 'spip_syndic',
			),
			'data' => $champs
		)
	);

	$id_syndic = sql_insertq("spip_syndic", $champs);
	pipeline('post_insertion',
		array(
			'args' => array(
				'table' => 'spip_syndic',
				'id_objet' => $id_syndic
			),
			'data' => $champs
		)
	);

	return $id_syndic;
}

/**
 * AJOUT https
 * enclosures
 * ajout d'un rel="enclosure" sur les liens mp3 absolus
 * appele en pipeline apres propre pour traiter les [mon son->http://monsite/mon_son.mp3]
 * peut etre appele dans un squelette apres |liens_absolus
 *
 * @param $texte
 * @return mixed
 */
function indymedia_post_propre($texte) {
	if (test_plugin_actif('player')){
		$reg_formats="mp3";
		// plus vite
		if (stripos($texte,".$reg_formats")!==false
		  AND stripos($texte,"<a")!==false){

			$cfg = unserialize($GLOBALS['meta']['player']);
			// insertion du mini-player inline
			if (isset($cfg['insertion_auto'])
				AND in_array('inline_mini',$cfg['insertion_auto'])){
				$texte = preg_replace_callback(
					",<a(\s[^>]*href=['\"]?(https://[a-zA-Z0-9\s()\/\:\._%\?+'=~-]*\.($reg_formats))['\"]?[^>]*)>,Uims",
					'player_enclose_link',
					$texte
					);
			}
			if (isset($cfg['insertion_auto'])
				AND in_array('player_end',$cfg['insertion_auto'])){

				preg_match_all(",<a(\s[^>]*href=['\"]?(https://[a-zA-Z0-9\s()\/\:\._%\?+'=~-]*\.($reg_formats))['\"]?[^>]*)>,Uims",$texte,$matches,PREG_SET_ORDER);
				if (count($matches)){
					foreach ($matches as $m){
						$url = $m[2];
						$texte .= recuperer_fond("modeles/player",array('url_document'=>$url,'titre'=>player_joli_titre($url)));
					}
				}
			}
		}
	}

	return $texte;
} 
/**
 * forcer isset
 * @return mixed
 */
function est_admin() {
	return (isset($GLOBALS['auteur_session']['statut']) && ($GLOBALS['auteur_session']['statut']=="0minirezo"));
} 
/***************************************************************************\
 *  SPIP, Systeme de publication pour l'internet                           *
 *          HACK SANS IP                                                               *
 *  Copyright (c) 2001-2014                                                *
 *  Arnaud Martin, Antoine Pitrou, Philippe Riviere, Emmanuel Saint-James  *
 *                                                                         *
 *  Ce programme est un logiciel libre distribue sous licence GNU/GPL.     *
 *  Pour plus de details voir le fichier COPYING.txt ou l'aide en ligne.   *
\***************************************************************************/

if (!defined('_ECRIRE_INC_VERSION')) return;

function inc_log($message, $logname=NULL, $logdir=NULL, $logsuf=NULL) {
	static $test_repertoire = array();
	static $compteur = array();
	static $debugverb = ""; // pour ne pas le recalculer au reappel
	global $nombre_de_logs, $taille_des_logs;

	if (is_null($logname) OR !is_string($logname))
		$logname = defined('_FILE_LOG') ? _FILE_LOG : 'spip';
	if (!isset($compteur[$logname])) $compteur[$logname] = 0;
	if ($logname != 'maj'
	AND defined('_MAX_LOG')
	AND (
		$compteur[$logname]++ > _MAX_LOG
		OR !$nombre_de_logs
		OR !$taille_des_logs
	))
		return;

	$logfile = ($logdir===NULL ? _DIR_LOG : $logdir)
	  . ($logname)
	  . ($logsuf===NULL ? _FILE_LOG_SUFFIX : $logsuf);

	if (!isset($test_repertoire[$d = dirname($logfile)])) {
		$test_repertoire[$d] = false; // eviter une recursivite en cas d'erreur de sous_repertoire
		$test_repertoire[$d] = (@is_dir($d)?true:(function_exists('sous_repertoire')?sous_repertoire($d, '', false, true):false));
	}

	// si spip_log() dans mes_options, ou repertoire log/ non present, poser dans tmp/
	if (!defined('_DIR_LOG') OR !$test_repertoire[$d])
		$logfile = _DIR_RACINE._NOM_TEMPORAIRES_INACCESSIBLES.$logname.'.log';

	$rotate = 0;
	$pid = '(pid '.@getmypid().')';

	// accepter spip_log( Array )
	if (!is_string($message)) $message = var_export($message, true);

	if (!$debugverb AND defined('_LOG_FILELINE') AND _LOG_FILELINE){
		$debug = debug_backtrace();
		$l = $debug[1]['line'];
		$fi = $debug[1]['file'];
		if (strncmp($fi,_ROOT_RACINE,strlen(_ROOT_RACINE))==0)
			$fi = substr($fi,strlen(_ROOT_RACINE));
		$fu = isset($debug[2]['function']) ? $debug[2]['function'] : '';
		$debugverb = "$fi:L$l:$fu"."():";
	}

	$m = date("Y-m-d H:i:s").' 0.0.0.0 '.$pid.' '
	  //distinguer les logs prives et publics dans les grep
		. $debugverb
		. (test_espace_prive()?':Pri:':':Pub:')
		.preg_replace("/\n*$/", "\n", $message);


	if (@is_readable($logfile)
	AND (!$s = @filesize($logfile) OR $s > $taille_des_logs * 1024)) {
		$rotate = $nombre_de_logs;
		$m .= "[-- rotate --]\n";
	}

	$f = @fopen($logfile, "ab");
	if ($f) {
		fputs($f, (defined('_LOG_BRUT') AND _LOG_BRUT) ? $m : str_replace('<','&lt;',$m));
		fclose($f);
	}

	if ($rotate-- > 0
	AND function_exists('spip_unlink')) {
		spip_unlink($logfile . '.' . $rotate);
		while ($rotate--) {
			@rename($logfile . ($rotate ? '.' . $rotate : ''), $logfile . '.' . ($rotate + 1));
		}
	}

	// Dupliquer les erreurs specifiques dans le log general
	if ($logname !== _FILE_LOG
	  AND defined('_FILE_LOG'))
		inc_log($logname=='maj' ? 'cf maj.log' : $message);
	$debugverb = "";
}   
// vider l'ip dans forum
function inc_forum_insert($objet, $id_objet, $id_forum, $force_statut = NULL) {
	include_spip('inc/forum_insert');
	$rep = inc_forum_insert_dist($objet, $id_objet, $id_forum, $force_statut);
	spip_log("repforum--".$rep,	'indy'._LOG_AVERTISSEMENT);
	if($rep>0){
		sql_updateq(
			'spip_forum',
			array(
				'ip' =>  "0.0.0.0",
				'id_auteur' =>  0
			),
			"id_forum=".$rep
		);
	}
	return $rep;
}
?>
