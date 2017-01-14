
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
			sortCol: 5, 
			sortDir: "desc", 
			pageLength: 200,
			colTool: "filesHeader", 
			rowTool: "filesRows", 
			hasToolbar: false,
			optionsCallback: function( datatable ) {
				// initializeViewFilesButton( );
				// initializeDisableCheckedExperimentsButton( datatable );
			}
		});
	});
	
}));