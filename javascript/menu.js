/*
 * Menu horizontal
 *
 */

$(document).ready( function () {

	$("#menu div.menuTitre").hover(
		function () {$("ul.menuItem",this).show();}
		,
		function () {$("ul.menuItem",this).hide();}
	);

});








