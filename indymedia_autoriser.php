<?php

/***************************************************************************\
 *  SPIP, Systeme de publication pour l'internet                           *
 *                                                                         *
 *  Copyright (c) 2001-2014                                                *
 *  Arnaud Martin, Antoine Pitrou, Philippe Riviere, Emmanuel Saint-James  *
 *                                                                         *
 *  Ce programme est un logiciel libre distribue sous licence GNU/GPL.     *
 *  Pour plus de details voir le fichier COPYING.txt ou l'aide en ligne.   *
\***************************************************************************/

if (!defined('_ECRIRE_INC_VERSION')) return;

/**
 * Autoriser a associer des documents a un objet :
 * il faut avoir le droit de modifier cet objet
 * @param $faire
 * @param $type
 * @param $id
 * @param $qui
 * @param $opt
 * @return bool
 */
function autoriser_associerdocuments($faire, $type, $id, $qui, $opt){
	// spip_log("faire--".$faire,	'indy'._LOG_AVERTISSEMENT);
	return true;
	if ($type=='document') return false; // pas de document sur les documents
	return true;
}

/**
 * Autoriser a dissocier des documents a un objet :
 * il faut avoir le droit de modifier cet objet
 * @param $faire
 * @param $type
 * @param $id
 * @param $qui
 * @param $opt
 * @return bool
 */
function autoriser_dissocierdocuments($faire, $type, $id, $qui, $opt){
	// spip_log("faire--".$faire,	'indy'._LOG_AVERTISSEMENT);
		return true;
	if ($type=='document') return false; // pas de document sur les documents
	// cas particulier
	if (intval($id)<0 AND $id==-$qui['id_auteur']){
		return true;
	}
	return autoriser('modifier',$type,$id,$qui,$opt);
}
