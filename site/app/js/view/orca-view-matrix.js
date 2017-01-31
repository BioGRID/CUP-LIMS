
/**
 * Javascript Bindings that apply to changing of permissions
 * in the admin tools
 */
 
(function( yourcode ) {

	yourcode( window.jQuery, window, document );

} (function( $, window, document ) {
	
	var baseURL = $("head base").attr( "href" );

	$(function( ) {
		
		var viewID = $("#viewID").val( );
		
		$(".datatableBlock").orcaDataTableBlock({ 
			sortCol: 0, 
			sortDir: "asc", 
			pageLength: 1000,
			colTool: "matrixViewHeader", 
			rowTool: "matrixViewRows", 
			hasToolbar: true,
			addonParams: { "viewID" : viewID },
			optionsCallback: function( datatable ) {
				// initializeViewFilesButton( );
				// initializeCreateViewButton( );
				// initializeDisableCheckedExperimentsButton( datatable );
			}
		});
	});
	
}));