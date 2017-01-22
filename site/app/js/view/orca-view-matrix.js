
/**
 * Javascript Bindings that apply to changing of permissions
 * in the admin tools
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
		
		$(".datatableBlock").orcaDataTableBlock({ 
			sortCol: 4, 
			sortDir: "desc", 
			pageLength: 100,
			colTool: "experimentHeader", 
			rowTool: "experimentRows", 
			hasToolbar: true,
			optionsCallback: function( datatable ) {
				//initializeViewFilesButton( );
				//initializeDisableCheckedExperimentsButton( datatable );
				//progressBox.close( );
			}
		});
	});
	
}));