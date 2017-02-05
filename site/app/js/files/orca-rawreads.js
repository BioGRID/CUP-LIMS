
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
		
			var IDs = $("#fileID").val( );
			
			$(".datatableBlock").orcaDataTableBlock({ 
				sortCol: 1, 
				sortDir: "desc", 
				pageLength: 1000,
				colTool: "rawReadsHeader", 
				rowTool: "rawReadsRows", 
				hasToolbar: false,
				addonParams: { "fileID" : IDs },
				optionsCallback: function( datatable ) {
					// initializeViewFilesButton( );
					// initializeCreateViewButton( );
					// initializeDisableCheckedExperimentsButton( datatable );
				}
			});
			
		}
	});
	
}));