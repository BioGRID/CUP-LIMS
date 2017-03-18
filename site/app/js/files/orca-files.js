
/**
 * Javascript Bindings that apply to changing of permissions
 * in the admin tools
 */
 
(function( yourcode ) {

	yourcode( window.jQuery, window, document );

} (function( $, window, document ) {
	
	var baseURL = $("head base").attr( "href" );

	$(function( ) {
		
		var IDs = $("#ids").val( );
		var incBG = $("#includeBG").val( );
		var type = $("#type").val( ); 
		
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
			hasToolbar: true,
			addonParams: { "ids" : IDs, "includeBG" : includeBG, "type" : type },
			optionsCallback: function( datatable ) {
				initializeFilesButtons( );
			}
		});
	});
	
	/**
	 * Setup Buttons on the files Toolbar
	 */
	 
	function initializeFilesButtons( ) {
		initializeCreateViewButton( );
	}
	
	/**
	 * Setup the Create View Button Functionality
	 */
	 
	function initializeCreateViewButton( ) {
		
		$(".datatableBlock").on( "click", ".fileCreateViewBtn", function( ) {
					
			var table = $(this).closest( ".orcaDataTableTools" ).find( ".orcaDataTable" );
			var fileIDs = [];
			table.find( ".orcaDataTableRowCheck:checked" ).each( function( ) {
				fileIDs.push( $(this).val( ) );
			});

			if( fileIDs.length ) {
				console.log( baseURL + "View/Create?fileIDs=" + fileIDs.join( "|" ) );
				window.location = baseURL + "View/Create?fileIDs=" + fileIDs.join( "|" );
			} else {
				alertify.alert( "No Files Selected", "Please check the box next to one or more files before clicking create view" );
			}
			
		});
		
	}
	
}));