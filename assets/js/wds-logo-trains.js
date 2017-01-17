(function($) {
	$( '.wds-logo-train a[href="#"]' ).on( 'click', function( e ) {
		e.preventDefault();
	} );

	$( document ).ready( function () {
		var audioElement = document.createElement( 'audio' );
		audioElement.setAttribute( 'src', audioFile );
		$.get();
		audioElement.addEventListener( "load", function () {
			audioElement.play();
		}, true );

		$( '.button-primary' ).on( 'click', function () {
			audioElement.play();
		} );

	} );

})(jQuery);