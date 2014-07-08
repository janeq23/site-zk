$(document).ready(function () {
	setFancyBoxLinks();
});

function setFancyBoxLinks(){
	// Konfiguracja Facnybox
	$("a[rel=photos]").fancybox({
		'titlePosition' : 'over',
		'overlayColor' : '#000',
		'overlayOpacity' : 0.8,
		'autoScale' : true,
		'titleFormat' : function(title, currentArray, currentIndex, currentOpts) {
			return '<span id="fancybox-title-over">Zdjęcie ' + (currentIndex + 1) + ' / ' + currentArray.length + (title.length ? ' &nbsp;&nbsp; ' + title : '') + '</span>';
			} 
	});
}
