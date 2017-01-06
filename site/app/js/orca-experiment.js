
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
			sortCol: 0, 
			sortDir: "ASC", 
			pageLength: 100,
			colTool: "manageExperimentHeader", 
			rowTool: "manageExperimentRows", 
			optionsCallback: function( datatable ) {
				//initializePermissionChangeOptions( datatable );
			}
		});
	});
	
}));