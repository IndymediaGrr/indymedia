<?php

// maximum de mots cles autorises
function max_mots()
{
	return 8;
}

// verifier mots cles 
function verifier_mots($values)
{
	if (!$values['mots'])
		return _T('publication_erreur_mots_1');
	else if (is_array($values['mots']) && (count($values['mots']) > max_mots()) )
		return _T('publication_erreur_mots_2', array ("max_mots" => max_mots()));
	else 
		return false ;
}

?>
