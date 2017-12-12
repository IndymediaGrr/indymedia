<?php

/***************************************************************************\
 *  SPIP, Systeme de publication pour l'internet                           *
 *                                                                         *
 *  Copyright (c) 2001-2011                                                *
 *  Arnaud Martin, Antoine Pitrou, Philippe Riviere, Emmanuel Saint-James  *
 *                                                                         *
 *  Ce programme est un logiciel libre distribue sous licence GNU/GPL.     *
 *  Pour plus de details voir le fichier COPYING.txt ou l'aide en ligne.   *
\***************************************************************************/

if (!defined("_ECRIRE_INC_VERSION")) return;

function action_enlever_lieu_approuve_dist($arg=null) {

	$securiser_action = charger_fonction('securiser_action', 'inc');
	$id_lieu = $securiser_action();

	lieu_approuve_enlever($id_lieu);
}

/**
 * Supprimer une cotisation
 * @param int $id_registre_cotisation
 * @return void
 */
function lieu_approuve_enlever($id_lieu) {
/*
	autres vérifs ....
*/   
	// indiquer que form_reponse est deja traité
	sql_updateq("spip_lieux", array("approuve" => "non"), "id_lieu=$id_lieu");
	return $id_lieu;
}

?>
