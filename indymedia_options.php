<?php
if (!defined("_ECRIRE_INC_VERSION")) return;

  
// si les extra de la table que l'on veut etendre sont vide faut creer le tableau
if (empty($GLOBALS['champs_extra']['articles']))
  $GLOBALS['champs_extra']['articles'] = Array();

// Création du champ extra contenant la raison de la modération d'un article
$GLOBALS['champs_extra']['articles']['OP_moderation'] = "propre|raison de la mod&eacute;ration";

// Création des champs extra contenant le pseudo et l'email du rédacteur
$GLOBALS['champs_extra']['articles']['OP_pseudo'] = "propre|pseudonyme du r&eacute;dacteur";
$GLOBALS['champs_extra']['articles']['OP_mail'] = "propre|mail du r&eacute;dacteur";
$table_des_traitements['DATE_DEBUT_INDY'][]= 'normaliser_date(%s)';     
// proteger le #FORMULAIRE_PUBLICATION_OUVERTE
$GLOBALS['formulaires_no_spam'][] = 'formulaire_publication_ouverte';
//$GLOBALS['date_nettoyage'];

$GLOBALS['forcer_lang'] = true;
// probleme cache css en https
// define('_INTERDIRE_COMPACTE_HEAD', true);
define('_LOG_FILTRE_GRAVITE', _LOG_DEBUG);
?>
