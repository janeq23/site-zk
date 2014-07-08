$(document).ready(function () {
	$('#gallery_thumbs a').click(function (e) {

		e.preventDefault();
		
		var photo_caption = $(this).attr('title');
		var photo_fullsize = $(this).attr('href');
		var photo_preview = photo_fullsize.replace("_fullsize", "_preview");

		$('#preview').fadeOut(500, function(){

			$('#gallery_preload_area').html('<img src="'+photo_preview+'" />');
			$('#gallery_preload_area img').imgpreload(function(){
				$('#preview').html('<a class="overlayLink" title="'+photo_caption+'" href="'+photo_fullsize+'"><img src="'+photo_preview+'"></a><figcaption><p>'+photo_caption+
					'<a class="overlayLink" title="'+photo_caption+'" href="'+photo_fullsize+'">Powiększ</a></p></figcaption>');
				$('#preview').fadeIn(500);
				setFancyBoxLinks();
				updateThumbnails();
			});
		});

	});
	updateThumbnails();
	setFancyBoxLinks();
});

function updateThumbnails(){
	$('#gallery_thumbs a').each(function(index){
		
		if ( $('#preview a').attr('href') == $(this).attr('href') ){
			$(this).addClass('selected');
		}else {
			$(this).removeClass('selected');
		}
	});
	
}
function setFancyBoxLinks(){
	// Konfiguracja Facnybox
	$("a.overlayLink").fancybox({
		'titlePosition' : 'over',
		'overlayColor' : '#000',
		'overlayOpacity' : 0.8,
		'autoScale' : true
	});
}
