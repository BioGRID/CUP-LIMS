
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
			}
		});
	});
	
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