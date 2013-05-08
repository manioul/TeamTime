// index.js
//

$(function() {
	$('#nav2-text h1').toggle(function() {
		$('#dConnexion').css('display', 'block');
		$('#dConnexion').addClass('largeConnexion');
		}
		,function() {
		$('#dConnexion').css('display', 'none');
		$('#dConnexion').removeClass('largeConnexion');
		});
});
