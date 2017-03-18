
/**
 * Javascript Bindings that apply to changing of permissions
 * in the admin tools
 */
 
(function( yourcode ) {

	yourcode( window.jQuery, window, document );

} (function( $, window, document ) {
	
	var baseURL = $("head base").attr( "href" );

	$(function( ) {
		initializeViewPrivacyPopups( );
	});
	
	/**
	 * Setup Privacy Popup
	 */
	 
	function initializeViewPrivacyPopups( ) {
		
		$("body").on( 'click', '.viewPermissionPopup', function( event ) {
			
			var annPopup = $(this).qtip({
				overwrite: false,
				content: {
					text: function( event, api ) {
						
						var submitSet = { };
						submitSet['tool'] = "fetchViewPrivacyDetails";
						submitSet['viewID'] = $(this).data( "viewid" );
						
						// Convert to JSON
						submitSet = JSON.stringify( submitSet );
						
						$.ajax({
							url: baseURL + 'scripts/viewTools.php',
							type: 'POST',
							data: { 'expData': submitSet }, 
							dataType: 'json'
						}).done( function( results ) {
							api.set( 'content.text', results['DATA'] );
						});
						
						return "Loading... <i class='fa fa-lg fa-spin fa-spinner'></i>";
					}
				},
				style: {
					classes: 'qtip-bootstrap',
					width: '250px'
				},
				position: {
					my: 'middle left',
					at: 'middle right',
					viewport: $("#datatableBlock" )
				},
				show: {
					event: event.type,
					ready: true,
					solo: true
				},
				hide: {
					delay: 1000,
					fixed: true,
					event: 'mouseleave'
				}
			}, event);
			
		});
		
	}
	
}));