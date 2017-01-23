
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
			hasToolbar: true,
			addonParams: { "expIDs" : expIDs, "includeBG" : includeBG },
			optionsCallback: function( datatable ) {
				initializeOptionPopups( );
				initializeFilesButtons( );
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