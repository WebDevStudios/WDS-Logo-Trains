/**
 * WDS Loud Noises
 * http://webdevstudios.com
 *
 * Licensed under the GPLv2+ license.
 */

(function ( $ ) {

	$( document ).ready( function () {
		var audioElement = document.createElement( 'audio' );
		audioElement.setAttribute( 'src', audioFile );
		$.get();
		audioElement.addEventListener( "load", function () {
			audioElement.play();
		}, true );

		$( '#publish' ).on( 'click', function () {
			audioElement.play();
		} );

	} );

})( jQuery );