
/**
 * Javascript Bindings that apply to changing of passwords
 * in the admin tools
 */
 
(function( yourcode ) {

	yourcode( window.jQuery, window, document );

} (function( $, window, document ) {
	
	var baseURL = $("head base").attr( "href" );

	$(function( ) {
		initializeManagePermissionsTable( ); 
	});

	/**
	 * Setup the basic datatable functionality for the Manage Permissions
	 * table with the ability to load data as required
	 */
	
	function initializeManagePermissionsTable( ) {
		
		var table = "#managePermissionsTable";
		
		if( !$.fn.DataTable.isDataTable( table )) {
			
			var submitSet = { 'adminTool' : "managePermissionsHeader" };
			submitSet = JSON.stringify( submitSet );
			
			$.ajax({
				
				url: baseURL + "/scripts/adminTools.php",
				data: {"expData" : submitSet},
				method: "POST",
				dataType: "json"
				
			}).done( function( results ) {
				
				var datatable = $(table).DataTable({
					processing: true,
					serverSide: true,
					columns: results,
					pageLength: 100,
					deferRender: true,
					order: [[1,'asc']],
					language: {
						processing: "Loading Data... <i class='fa fa-spinner fa-pulse fa-lg'></i>"
					},
					ajax : {
						url: baseURL + "/scripts/adminTools.php",
						type: 'POST',
						data: function( d ) {  
							d.adminTool = "managePermissionsRows";
							d.totalRecords = $("#permissionsCount").val( );
							d.expData = JSON.stringify( d );
						}
					},
					infoCallback: function( settings, start, end, max, total, pre ) {
						$("#managePermissionsFilterData").html( pre );
					},
					dom : "<'row'<'col-sm-12'rt>><'row'<'col-sm-5'i><'col-sm-7'p>>"
						
				});
				
				initializeManagePermissionsTools( datatable );
				
			});
				
		}
		
		
		
	}
	
	/**
	 * Setup the functionality of several tools that only
	 * apply when a datatable has been instantiated.
	 */
	
	function initializeManagePermissionsTools( datatable ) {
		
		// SETUP Global Filter
		// By Button Click
		$("#managePermissionsFilterSubmit").click( function( ) {
			datatableFilterGlobal( datatable, $("#manageUsersFilter").val( ), true, false ); 
		});
		
		// By Pressing the Enter Key
		$("#managePermissionsFilter").keyup( function( e ) {
			if( e.keyCode == 13 ) {
				datatableFilterGlobal( datatable, $(this).val( ), true, false ); 
			}
		});
		
		initializePermissionChangeOptions( datatable );
	
	}
	
	/**
	 * Search the table via the global filter
	 */
	
	function datatableFilterGlobal( datatable, filterVal, isRegex, isSmartSearch ) {
		datatable.search( filterVal, isRegex, isSmartSearch, true ).draw( );
	}
	
	/**
	 * Setup the functionality of the class change icons
	 */
	 
	function initializePermissionChangeOptions( datatable ) {
		
		$("#managerPermissionsWrap").on( "change", ".permissionChange", function( ) {
			
			var currentClick = $(this);
			var submitSet = { };
			
			submitSet['permission'] = $(this).attr( "data-permission" );
			submitSet['level'] = $(this).val( );
			submitSet['adminTool'] = "permissionLevelChange";
			
			//Convert to JSON
			submitSet = JSON.stringify( submitSet );
		
			$.ajax({
				url: 'scripts/adminTools.php',
				type: 'POST',
				data: { 'expData': submitSet}, 
				dataType: 'json'
			}).done( function( results ) {
				
				console.log( results );
				
				if( results['STATUS'] == "SUCCESS" ) {
					alertify.success( results['MESSAGE'] );
					datatable.draw( false );
				} else {
					alertify.error( results['MESSAGE'] );
				}
			});
		});
		
	}
	
}));