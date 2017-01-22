
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
		
		initializeView( progressBox );
		
	});
	
	/**
	 * Initialize either the creation of the
	 * view, or quickly begin loading it
	 */
	
	function initializeView( progressBox ) {
		
		var submitSet = { };
		submitSet['viewID'] = $("#viewID").val( );
		submitSet['viewCode'] = $("#viewCode").val( );
		
		//Convert to JSON
		var submitData = JSON.stringify( submitSet );
		
		$.ajax({
			url: baseURL + 'scripts/processView.php',
			type: 'POST',
			data: { 'expData': submitData }, 
			dataType: 'json'
		}).done( function( results ) {
			console.log( results['MESSAGE'] );
			
			submitSet['tool'] = "checkViewBuildProgress";
			submitData = JSON.stringify( submitSet );
			
			var stateCheck = setInterval( function( ) {
				
				console.log( "RUNNING" );
				
				$.ajax({
					url: baseURL + 'scripts/viewTools.php',
					type: 'POST',
					data: { 'expData': submitData },
					dataType: 'json'
				}).done( function( results ) {
					console.log( results );
					
					if( results['STATE'] == "complete" ) {
						clearInterval( stateCheck );
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
								progressBox.close( );
							}
						});
					} else {
						console.log( "STILL BUILDING" );
					}
					
				});
				
			}, 5000 );
			
		});
	}
	
}));