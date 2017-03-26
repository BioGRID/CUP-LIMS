
/**
 * Javascript Bindings that apply to changing of permissions
 * in the admin tools
 */
 
(function( yourcode ) {

	yourcode( window.jQuery, window, document );

} (function( $, window, document ) {
	
	var baseURL = $("head base").attr( "href" );
	alertify.defaults.maintainFocus = false;

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
			hasAdvanced: true,
			addonParams: { "viewID" : viewID, "viewStyle" : viewStyle },
			optionsCallback: function( datatable ) {
				initializeStyleSelect( datatable );
				initializeGroupClickPopups( );
				initializeFileHeaderMouseoverPopups( );
				initializeViewFilesLink( );
				initializeColorOnlyPopup( );
				initializeRawReadsPopup( );
			}
		});
		
	});
	
	/**
	 * Setup a popup showing the raw read scores for a given field in the matrix
	 */
	 
	function initializeRawReadsPopup( ) {
		
		$(".datatableBlock").on( 'click', '.rawDetailsPopup', function( event ) {
			
			var currentCell = $(this);
			currentCell.addClass( "highlightCell" );
			
			// Setup a Progress Box Showing a Default Loading Script
			var progressBox = alertify.alert( ).setting({
				'message': "Loading Raw Reads... <i class='fa fa-lg fa-spin fa-spinner'></i>",
				'closable' : true,
				'basic' : true,
				'padding' : false,
				'movable' : false,
				'overflow' : false,
				'transition' : 'fade',
				'onclose' : function( ) {
					currentCell.removeClass( "highlightCell" );
				}
			}).show( );
			
			var submitSet = { };
			submitSet['tool'] = "fetchMatrixRawReads";
			submitSet['fileID'] = $(this).data( "fileid" );
			submitSet['fileName'] = $(this).data( "filename" );
			submitSet['groupID'] = $(this).data( "groupid" );
			submitSet['groupName'] = $(this).data( "groupname" );
			submitSet['viewID'] = $("#viewID").val( );
			submitSet['scoreVal'] = $(this).text( );
				
			// Convert to JSON
			submitSet = JSON.stringify( submitSet );
				
			$.ajax({
				url: baseURL + 'scripts/viewTools.php',
				type: 'POST',
				data: { 'expData': submitSet }, 
				dataType: 'json'
			}).done( function( results ) {
				
				// Only show the data if they didn't close
				// the progress box in the meantime
				if( progressBox.isOpen( ) ) {
					progressBox.setContent( results['DATA'] );
				}
				
			});
			
		});
		
	}
	
	/**
	 * Setup the view files link
	 */
	 
	function initializeViewFilesLink( ) {
		$(".showFileLegend").on( "click", function( ) {
			$("#fileList").toggle( );
			if( $("#fileList").is( ":visible" ) ){
				$(".showFileLegend").html( "Hide Files <i class='fa fa-angle-double-up'></i>" );
			} else {
				$(".showFileLegend").html( "View Files <i class='fa fa-angle-double-down'></i>" );
			}
		});
	}
	
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
	 * Initialize Popups for color boxes when only color is showing
	 */
	 
	 function initializeColorOnlyPopup( ) {
		 
		$(".datatableBlock").on( 'mouseover', '.colorOnlyPopup', function( event ) {
	 
			var annPopup = $(this).qtip({
				overwrite: false,
				content: {
					text: $(this).data( "value" )
				},
				style: {
					classes: 'qtip-bootstrap',
					width: '100px'
				},
				position: {
					my: 'bottom center',
					at: 'top center',
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
						submitSet['tool'] = "fetchGroupAnnotation";
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