
/**
 * Core Javascript Bindings that apply
 * to the entirety of the site.
 */
 
(function( yourcode ) {

	yourcode( window.jQuery, window, document );

} (function( $, window, document ) {

	$(function( ) {
		
		var isRunning = $("#isRunning").val( );
		
		if( isRunning == "true" ) {
		
			setTimeout(function () { 
				location.reload();
			}, 30 * 1000);
			
		}
		
	});

}));