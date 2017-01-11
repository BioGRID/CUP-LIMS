
/**
 * Javascript Bindings that apply to changing of permissions
 * in the admin tools
 */
 
(function( yourcode ) {

	yourcode( window.jQuery, window, document );

} (function( $, window, document ) {
	
	var baseURL = $("head base").attr( "href" );

	$(function( ) {
		$(".datatableBlock").orcaDataTableBlock({ 
			sortCol: 4, 
			sortDir: "desc", 
			pageLength: 100,
			colTool: "experimentHeader", 
			rowTool: "experimentRows", 
			hasToolbar: true,
			optionsCallback: function( datatable ) {
				initializeViewFilesButton( );
			}
		});
	});
	
	/**
	 * Setup the functionality for viewing files 
	 */
	 
	function initializeViewFilesButton( ) {
		
		$(".datatableBlock").on( "click", ".experimentViewFilesBtn", function( ) {
			
			var table = $(this).closest( ".orcaDataTableTools" ).find( ".orcaDataTable" );
			var fileIDs = [];
			table.find( ".orcaDataTableRowCheck:checked" ).each( function( ) {
				fileIDs.push( $(this).val( ) );
			});

			if( fileIDs.length ) {
				console.log( baseURL + "/Files?files=" + fileIDs.join( "," ) );
				window.location = baseURL + "/Files?files=" + fileIDs.join( "," );
			} else {
				alertify.alert( "No Experiments Selected", "Please check the box next to one or more experiments before clicking view files" );
			}
			
		});
		
	}
	

	
}));