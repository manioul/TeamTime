// index.js
//

function fConShow()
{
	$('#fConn').show();
	$('#fConn').addClass('largeConnexion');
	$('#nav2-text h1').one("click", fConHide);
}
function fConHide()
{
	$('#fConn').hide();
	$('#fConn').removeClass('largeConnexion');
	$('#nav2-text h1').one("click", fConShow);
}
function dSupShow()
{
	$('#dSupport ul').show();
	$('#nav3-text h1').one("click", dSuphide);
}
function dSuphide()
{
	$('#dSupport ul').hide();
	$('#nav3-text h1').one("click", dSupShow);
}
function fSigShow()
{
	$('#fSignup').show();
	$('#nav4-text h1').one("click", fSigHide);
}
function fSigHide()
{
	$('#fSignup').hide();
	$('#nav4-text h1').one("click", fSigShow);
}
$(function() {
	$('#nav2-text h1').one("click", fConShow);
	$('#nav3-text h1').one("click", dSupShow);
	$('#nav4-text h1').one("click", fSigShow);
});
