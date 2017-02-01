
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
		var viewStyle = $("#viewStyle").val( );
		
		$(".datatableBlock").orcaDataTableBlock({ 
			sortCol: 0, 
			sortDir: "asc", 
			pageLength: 1000,
			colTool: "matrixViewHeader", 
			rowTool: "matrixViewRows", 
			hasToolbar: true,
			addonParams: { "viewID" : viewID, "viewStyle" : viewStyle },
			optionsCallback: function( datatable ) {
				initializeStyleSelect( datatable );
				initializeGroupClickPopups( );
				initializeFileHeaderMouseoverPopups( );
				// initializeDisableCheckedExperimentsButton( datatable );
			}
		});
		
		
		
	});
	
	/**
	 * Setup the functionality for changing the layout style
	 */
	 
	function initializeStyleSelect( datatable ) {
		$(".datatableBlock").on( "change", ".orcaToolbarSelect", function( ) {
			var viewID = $("#viewID").val( );
			var selectedVal = $("option:selected", this).val( );
			$("#viewStyle").val( selectedVal );
			
			var datatableBlock = $(".datatableBlock").data( "orcaDataTableBlock" );
			datatableBlock.updateOption( "addonParams", { "viewID" : viewID, "viewStyle" : selectedVal } );
			datatable.draw( false );
			
		});
	}
	
	/**
	 * Initialize Popups for when a person clicks on a group name
	 */
	 
	 function initializeGroupClickPopups( ) {
		 
		$(".datatableBlock").on( 'click', '.annotationPopup', function( event ) {
	 
			var annPopup = $(this).qtip({
				overwrite: false,
				content: {
					title: "<strong>" + $(this).text( ) + "</strong>",
					text: function( event, api ) {
						
						var submitSet = { };
						submitSet['tool'] = "fetchMatrixGroupAnnotation";
						submitSet['id'] = $(this).data( "id" );
						submitSet['viewID'] = $("#viewID").val( );
						
						// Convert to JSON
						submitSet = JSON.stringify( submitSet );
						
						$.ajax({
							url: baseURL + 'scripts/viewTools.php',
							type: 'POST',
							data: { 'expData': submitSet }, 
							dataType: 'json'
						}).done( function( results ) {
							api.set( 'content.text', results['DATA'] );
						});
						
						return "Loading... <i class='fa fa-lg fa-spin fa-spinner'></i>";
					}
				},
				style: {
					classes: 'qtip-bootstrap',
					width: '250px'
				},
				position: {
					my: 'middle left',
					at: 'middle right',
					viewport: $("#datatableBlock" )
				},
				show: {
					event: event.type,
					ready: true,
					solo: true
				},
				hide: {
					delay: 1000,
					fixed: true,
					event: 'mouseleave'
				}
			}, event);
			
		});
		
	 }
	 
	 /**
	 * Initialize Popups for when a person clicks on a condition header
	 */
	 
	 function initializeFileHeaderMouseoverPopups( ) {
		 
		$(".datatableBlock").on( 'mouseover', '.matrixHeaderPopup', function( event ) {
	 
			var annPopup = $(this).qtip({
				overwrite: false,
				content: {
					title: "<strong>Column " + $(this).text( ) + "</strong>",
					text: function( event, api ) {
						
						var submitSet = { };
						submitSet['tool'] = "fetchMatrixHeaderPopup";
						submitSet['fileID'] = $(this).data( "fileid" );
						submitSet['fileName'] = $(this).data( "file" );
						submitSet['bgID'] = $(this).data( "bgid" );
						submitSet['bgName'] = $(this).data( "bgfile" );
						submitSet['viewID'] = $("#viewID").val( );
						
						// Convert to JSON
						submitSet = JSON.stringify( submitSet );
						
						$.ajax({
							url: baseURL + 'scripts/viewTools.php',
							type: 'POST',
							data: { 'expData': submitSet }, 
							dataType: 'json'
						}).done( function( results ) {
							api.set( 'content.text', results['DATA'] );
						});
						
						return "Loading... <i class='fa fa-lg fa-spin fa-spinner'></i>";
					}
				},
				style: {
					classes: 'qtip-bootstrap',
					width: '350px'
				},
				position: {
					my: 'top center',
					at: 'bottom center',
					viewport: $("#datatableBlock" )
				},
				show: {
					event: event.type,
					ready: true,
					solo: true
				},
				hide: {
					delay: 1000,
					fixed: true,
					event: 'mouseleave'
				}
			}, event);
			
		});
		
	 }
	
}));