
/**
 * Javascript Bindings that apply to changing of permissions
 * in the admin tools
 */
 
(function( yourcode ) {

	yourcode( window.jQuery, window, document );

} (function( $, window, document ) {
	
	var baseURL = $("head base").attr( "href" );

	$(function( ) {
		
		initializePermissionTools( );
		
		var viewState = $("#viewState").val( );
		if( viewState == "building" ) {
			
			setInterval( function( ) {
				window.location.reload( );
			}, 15000 );
			
		} else {
		
			var viewID = $("#viewID").val( );
			
			$(".datatableBlock").orcaDataTableBlock({ 
				sortCol: 2, 
				sortDir: "desc", 
				pageLength: 1000,
				colTool: "rawReadsHeader", 
				rowTool: "rawReadsRows", 
				hasToolbar: false,
				hasAdvanced: true,
				addonParams: { "viewID" : viewID },
				optionsCallback: function( datatable ) {
					initializeGroupClickPopups( );
				}
			});
			
		}
	});
	
	/**
	 * Initialize Permission Setting Tools
	 */
	 
	function initializePermissionTools( ) {
		
		$("#permissionDetailsWrap").on( "change", "#filePermission", function( ) {
			
			var selectVal = $(this).val( );
			if( selectVal == "private" ) {
				$("#fileGroupsBox").show( );
			} else {
				$("#fileGroupsBox").hide( );
			}
			
		});
		
		$("#permissionDetailsWrap").on( "click", "#permissionChangeBtn", function( ) {
			
			var submitSet = { 
				"tool" : "changePermission",
				"fileID" : $("#fileID").val( ),
				"filePermission" : $("#filePermission").val( )
			};
		
			// Get permitted groups select
			submitSet['fileGroups'] = [];
			$("#fileGroups option:selected").each( function( ) {
				submitSet['fileGroups'].push( $(this).val( ) );
			});
		
			// Convert to JSON
			submitSet = JSON.stringify( submitSet );
		
			// Send via AJAX for submission to
			// database and placement of files
			$.ajax({
				url: baseURL + "/scripts/fileTools.php",
				type: "POST",
				data: {"data" : submitSet},
				dataType: 'json',
				beforeSend: function( ) {
					$("#messages").html( "" );
				}
				
			}).done( function( data, textStatus, jqXHR ) {
			
				var alertType = "success";
				var alertIcon = "fa-check";
				if( data["STATUS"] == "error" ) {
					alertType = "danger";
					alertIcon = "fa-warning";
				} 
			
				$("#messages").html( '<div class="alert alert-' + alertType + '" role="alert"><i class="fa ' + alertIcon + ' fa-lg"></i> ' + data['MESSAGE'] + '</div></div>');
			
			}).fail( function( jqXHR, textStatus, errorThrown ) {
				console.log( jqXHR );
				console.log( textStatus );
			});
			
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
	
}));