
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
				//initializePermissionChangeOptions( datatable );
			}
		});
	});
	
}));