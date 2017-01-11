
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
				initializeDisableCheckedExperimentsButton( datatable );
			}
		});
	});
	
	/**
	 * Setup the functionality for viewing files 
	 */
	 
	function initializeViewFilesButton( ) {
		
		$(".datatableBlock").on( "click", ".experimentViewFilesBtn", function( ) {
			
			var table = $(this).closest( ".orcaDataTableTools" ).find( ".orcaDataTable" );
			var expIDs = [];
			table.find( ".orcaDataTableRowCheck:checked" ).each( function( ) {
				expIDs.push( $(this).val( ) );
			});

			if( expIDs.length ) {
				console.log( baseURL + "/Files?exps=" + expIDs.join( "," ) );
				window.location = baseURL + "/Files?exps=" + expIDs.join( "," );
			} else {
				alertify.alert( "No Experiments Selected", "Please check the box next to one or more experiments before clicking view files" );
			}
			
		});
		
	}
	
	/**
	 * Setup the functionality for the disable checked experiments
	 */
	 
	function initializeDisableCheckedExperimentsButton( datatable ) {
		
		$(".datatableBlock").on( "click", ".experimentDisableChecked", function( ) {
			
			console.log( "HERE I BE" );
			
			var table = $(this).closest( ".orcaDataTableTools" ).find( ".orcaDataTable" );
			
			var submitSet = { };
			submitSet['exps'] = [];
			submitSet['tool'] = "disableExperiment";
			
			table.find( ".orcaDataTableRowCheck:checked" ).each( function( ) {
				submitSet['exps'].push( $(this).val( ) );
			});
			
			//Convert to JSON
			submitSet = JSON.stringify( submitSet );

			$.ajax({
				url: baseURL + 'scripts/experimentTools.php',
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