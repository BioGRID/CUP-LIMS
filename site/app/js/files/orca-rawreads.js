
/**
 * Javascript Bindings that apply to changing of permissions
 * in the admin tools
 */
 
(function( yourcode ) {

	yourcode( window.jQuery, window, document );

} (function( $, window, document ) {
	
	var baseURL = $("head base").attr( "href" );

	$(function( ) {
		
		var viewState = $("#viewState").val( );
		if( viewState == "building" ) {
			
			setInterval( function( ) {
				window.location.reload( );
			}, 15000 );
			
		} else {
		
			var viewID = $("#viewID").val( );
			
			$(".datatableBlock").orcaDataTableBlock({ 
				sortCol: 2, 
				sortDir: "desc", 
				pageLength: 1000,
				colTool: "rawReadsHeader", 
				rowTool: "rawReadsRows", 
				hasToolbar: false,
				addonParams: { "viewID" : viewID },
				optionsCallback: function( datatable ) {
					initializeGroupClickPopups( );
				}
			});
			
		}
	});
	
	/**
	 * Initialize Popups for when a person clicks on a group name
	 */
	 
	 function initializeGroupClickPopups( ) {
		 
		$(".datatableBlock").on( 'click', '.annotationPopup', function( event ) {
	 
			var annPopup = $(this).qtip({
				overwrite: false,
				content: {
					title: "<strong>" + $(this).text( ) + "</strong>",
					text: function( event, api ) {
						
						var submitSet = { };
						submitSet['tool'] = "fetchGroupAnnotation";
						submitSet['id'] = $(this).data( "id" );
						submitSet['viewID'] = $("#viewID").val( );
						
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