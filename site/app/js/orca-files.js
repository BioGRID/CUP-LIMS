
/**
 * Javascript Bindings that apply to changing of permissions
 * in the admin tools
 */
 
(function( yourcode ) {

	yourcode( window.jQuery, window, document );

} (function( $, window, document ) {
	
	var baseURL = $("head base").attr( "href" );

	$(function( ) {
		
		var expIDs = $("#expIDs").val( );
		var incBG = $("#includeBG").val( );
		
		var includeBG = false;
		if( incBG == "true" ) {
			includeBG = true;
		}
		
		$(".datatableBlock").orcaDataTableBlock({ 
			sortCol: 4, 
			sortDir: "desc", 
			pageLength: 200,
			colTool: "filesHeader", 
			rowTool: "filesRows", 
			hasToolbar: false,
			addonParams: { "expIDs" : expIDs, "includeBG" : includeBG },
			optionsCallback: function( datatable ) {
				// initializeViewFilesButton( );
				// initializeDisableCheckedExperimentsButton( datatable );
			}
		});
	});
	
}));