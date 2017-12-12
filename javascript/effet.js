/*
 * Système "flip" entre les input de type radio
 *
 */

jQuery.fn.set_panneau = function()
{
	$("ul.panneau").hide();
	$("ul.panneau").addClass('toHide');
	$("a.showPanneau").click(
		function()
		{
			var panneau = $(this).attr('panneau');

			if ($("ul."+panneau).hasClass('toHide'))
			{
				$("ul."+panneau).show();
				$("ul."+panneau).removeClass('toHide');
			}
			else
			{
				$("ul."+panneau).hide();
				$("ul."+panneau).addClass('toHide');
			}
			return false;
		}
	);
}

jQuery.fn.set_flip = function()
{

	var nom = $("input[type=radio][checked]").attr('value');
	$("label[for="+nom+"]").addClass('selected');

	$("input[type=radio]",this).click(
		function()
		{
			var ancetre = $(this).parents("div.contenu");
			$("label",ancetre).removeClass('selected');

			var nom = $(this).attr('value');
			$("label[for="+nom+"]").addClass('selected');

		}
	);
}

/*
 * Système d'onglets
 *
 */

jQuery.fn.set_onglets = function()
{
	// effet "onglet" pour le formulaire de publication ouverte
	var etape = $("input[name=etape]",this).val();

	if (etape) {
		$(".actif").removeClass("actif");
		$("#" + etape).show();
	}
	else $(".contenu:first",this).show();

	$("#onglets div.tabs span",this).click(
		function()
		{
			$(".actif").removeClass("actif");
			$(this).addClass("actif");

			$(".contenu").hide();

			var contenu_aff = $('a',this).attr("onglet");
			$("#" + contenu_aff).show();
			$("input[name=etape]").val(contenu_aff);
		}
	);
}

jQuery(function(){
	jQuery('#formulaire_publication_ouverte').set_onglets();
	jQuery('#formulaire_publication_ouverte').set_flip();
	jQuery('#formulaire_publication_ouverte').set_panneau();
	jQuery("input.datePicker").datePicker();
});

// on restaure à chaque changement du DOM.

onAjaxLoad(function() {
	if (jQuery){
		jQuery('#formulaire_publication_ouverte').set_onglets();
		jQuery('#formulaire_publication_ouverte').set_flip();
		jQuery('#formulaire_publication_ouverte').set_panneau();
		jQuery("input.datePicker").datePicker();
	}
});





$(document).ready( function () {

	// un effet lorsque l'on passe la sourie sur le lien publiez
	$("#colonneCentre a.lien_publier").hover(
		function () {
			$(this).css({
				"backgroundColor":"#f5eded",
				"color":"#443046"
				});

		}
		,
		function () {
			$(this).css({
				"backgroundColor":"#443046",
				"color":"#fff"
				});
		}
	);

	$("#colonneCentre div.lien_publier a").bind('click',function() {
		alert('coucou');
	});

	$("#colonneCentre div.lien_publier").click(
		function() {
			$('a',this).trigger('click');
		}
	);
	// un effet lorsque l'on passe la sourie sur un cadre edito
	$("div.edito").hover(
		function () {
			$(this).css({"backgroundColor":"#f5eded"});
		}
		,
		function () {
			$(this).css({"backgroundColor":"#fff"}); 
		}
	);



	


	// lorsque l'on passe la sourie sur agendaSuivant, la ligne agenda doit partir sur la gauche
	// on avance de 3 case soit 3 * 87 = 261
	$("#agenda img.agendaSuivant").click(
		function () {
			var position = $("#agenda div.ligneAgenda").position();
			position.left = position.left - 264
			$("#agenda div.ligneAgenda").animate({'left':position.left});
			return false;
		}
	);
	// lorsque l'on passe la sourie sur agendaPrecedent, la ligne agenda doit partir sur la droite
	// on recule de 3 case soit 3 * 87 = 261
	// sauf si la position est 0 ou <, dans ce cas on ne fait rien
	$("#agenda img.agendaPrecedent").click(
		function () {
			var position = $("#agenda div.ligneAgenda").position();
			position.left = position.left + 264;
			$("#agenda div.ligneAgenda").animate({'left':position.left});

			return false;
		}
	);
});








