
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
			pageLength: 100,
			colTool: "viewHeader", 
			rowTool: "viewRows", 
			hasToolbar: true,
			optionsCallback: function( datatable ) {
				initializeDisableCheckedViewsButton( datatable );
			}
		});
	});
	
	/**
	 * Setup the functionality for the disable checked experiments
	 */
	 
	function initializeDisableCheckedViewsButton( datatable ) {
		
		$(".datatableBlock").on( "click", ".viewDisableChecked", function( ) {
			
			var table = $(this).closest( ".orcaDataTableTools" ).find( ".orcaDataTable" );
			
			var submitSet = { };
			submitSet['views'] = [];
			submitSet['tool'] = "disableView";
			
			table.find( ".orcaDataTableRowCheck:checked" ).each( function( ) {
				submitSet['views'].push( $(this).val( ) );
			});
			
			//Convert to JSON
			submitSet = JSON.stringify( submitSet );

			$.ajax({
				url: baseURL + 'scripts/viewTools.php',
				type: 'POST',
				data: { 'expData': submitSet}, 
				dataType: 'json'
			}).done( function( results ) {
				
				if( results['STATUS'] == "SUCCESS" ) {
					alertify.success( results['MESSAGE'] );
					datatable.draw( false );
				} else {
					alertify.alert( "An Error Occurred", results['MESSAGE'] );
				}
				
			});
			
		});
		
	}
	
}));