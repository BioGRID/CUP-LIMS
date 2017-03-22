
/**
 * Javascript Bindings that apply to displaying created
 * views in ORCA
 */
 
(function( yourcode ) {

	yourcode( window.jQuery, window, document );

} (function( $, window, document ) {
	
	var baseURL = $("head base").attr( "href" );

	$(function( ) {
		
		var progressBox = alertify.alert( ).setting({
			'message': $(".orcaViewProgressWrap").html( ),
			'closable' : false,
			'basic' : true,
			'padding' : true,
			'movable' : false,
			'overflow' : true
		}).show( );
		
		setInterval( function( ) {
			window.location.reload( );
		}, 15000 );
		
	});
	
}));