
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
				initializeOptionPopups( );
				initializeFilesButtons( );
				initializePrivacyPopups( );
			}
		});
	});
	
	/**
	 * Setup Buttons on the files Toolbar
	 */
	 
	function initializeFilesButtons( ) {
		initializeViewFilesButtons( );
	}
	
	/**
	 * Setup the functionality for creating a view from selected files 
	 */
	 
	function initializeViewFilesButtons( ) {
		
		$(".datatableBlock").on( "click", ".viewClick", function( ) {
			
			var type = $(this).data( "type" );
			var values = $(this).data( "values" );
			var table = $(this).closest( ".orcaDataTableTools" ).find( ".orcaDataTable" );
			var fileIDs = [];
			table.find( ".orcaDataTableRowCheck:checked" ).each( function( ) {
				fileIDs.push( $(this).val( ) );
			});

			if( fileIDs.length ) {
				console.log( baseURL + "View?fileIDs=" + fileIDs.join( "|" ) + "&type=" + type + "&values=" + values );
				window.location = baseURL + "View?fileIDs=" + fileIDs.join( "|" ) + "&type=" + type + "&values=" + values;
			} else {
				alertify.alert( "No Files Selected", "Please check the box next to one or more files before clicking on an available view option" );
			}
			
		});
		
	}
	
	/**
	 * Setup Privacy Popup
	 */
	 
	function initializePrivacyPopups( ) {
		
		$(".datatableBlock").on( 'click', '.permissionView', function( event ) {
			
			var annPopup = $(this).qtip({
				overwrite: false,
				content: {
					text: function( event, api ) {
						
						var submitSet = { };
						submitSet['tool'] = "fetchFilePrivacyDetails";
						submitSet['fileID'] = $(this).data( "fileid" );
						
						// Convert to JSON
						submitSet = JSON.stringify( submitSet );
						
						$.ajax({
							url: baseURL + 'scripts/fileTools.php',
							type: 'POST',
							data: { 'data': submitSet }, 
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
	 * Setup tooltips for the options in the options column
	 */
	 
	 function initializeOptionPopups( ) {
		 
		$(".datatableBlock").on( 'mouseover', '.popoverData', function( event ) {
	 
			var optionPopup = $(this).qtip({
				overwrite: false,
				content: {
					title: $(this).data( "title" ),
					text: $(this).data( "content" )
				},
				style: {
					classes: 'qtip-bootstrap',
					width: '250px'
				},
				position: {
					my: 'bottom right',
					at: 'top left'
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