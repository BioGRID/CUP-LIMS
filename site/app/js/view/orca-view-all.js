
/**
 * Javascript Bindings that apply to changing of permissions
 * in the admin tools
 */
 
(function( yourcode ) {

	yourcode( window.jQuery, window, document );

} (function( $, window, document ) {
	
	var baseURL = $("head base").attr( "href" );

	$(function( ) {
		initializeViewPermissionsLink( );
		initializePermissionTools( );
	});
	
	/**
	 * Setup the view permissions link
	 */
	 
	function initializeViewPermissionsLink( ) {
		$(".showViewPermissions").on( "click", function( ) {
			$("#permissionDetailsWrap").toggle( );
			if( $("#permissionDetailsWrap").is( ":visible" ) ){
				$(".showViewPermissions").html( "Hide Permissions <i class='fa fa-angle-double-up'></i>" );
			} else {
				$(".showViewPermissions").html( "Edit Permissions <i class='fa fa-angle-double-down'></i>" );
			}
		});
	}
	
	/**
	 * Initialize Permission Setting Tools
	 */
	 
	function initializePermissionTools( ) {
		
		$("#permissionDetailsWrap").on( "change", "#viewPermission", function( ) {
			
			var selectVal = $(this).val( );
			if( selectVal == "private" ) {
				$("#viewGroupsBox").show( );
			} else {
				$("#viewGroupsBox").hide( );
			}
			
		});
		
		$("#permissionDetailsWrap").on( "click", "#permissionChangeBtn", function( ) {
			
			var submitSet = { 
				"tool" : "changePermission",
				"viewID" : $("#viewID").val( ),
				"viewPermission" : $("#viewPermission").val( )
			};
		
			// Get permitted groups select
			submitSet['viewGroups'] = [];
			$("#viewGroups option:selected").each( function( ) {
				submitSet['viewGroups'].push( $(this).val( ) );
			});
		
			// Convert to JSON
			submitSet = JSON.stringify( submitSet );
		
			// Send via AJAX for submission to
			// database and placement of files
			$.ajax({
				url: baseURL + "/scripts/viewTools.php",
				type: "POST",
				data: {"expData" : submitSet},
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
	
}));