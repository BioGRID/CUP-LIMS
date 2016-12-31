
/**
 * Javascript Bindings that apply to changing of passwords
 * in the admin tools
 */
 
(function( yourcode ) {

	yourcode( window.jQuery, window, document );

} (function( $, window, document ) {
	
	var baseURL = $("head base").attr( "href" );

	$(function( ) {
		initializeManageUserTable( );
	});

	/**
	 * Setup the basic datatable functionality for the Manage User
	 * table with the ability to load data as required
	 */
	
	function initializeManageUserTable( ) {
		
		var table = "#manageUsersTable";
		
		if( !$.fn.DataTable.isDataTable( table )) {
			
			var submitSet = { 'adminTool' : "manageUsersHeader" };
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
					order: [[1,'desc']],
					language: {
						processing: "Loading Data... <i class='fa fa-spinner fa-pulse fa-lg'></i>"
					},
					ajax : {
						url: baseURL + "/scripts/adminTools.php",
						type: 'POST',
						data: function( d ) {  
							d.adminTool = "manageUsersRows";
							d.totalRecords = $("#userCount").val( );
							d.expData = JSON.stringify( d );
						}
					},
					infoCallback: function( settings, start, end, max, total, pre ) {
						$("#manageUsersFilterData").html( pre );
					},
					dom : "<'row'<'col-sm-12'rt>><'row'<'col-sm-5'i><'col-sm-7'p>>"
						
				});
				
				initializeManageUserTools( datatable );
				
			});
				
		}
		
	}
	
	/**
	 * Setup the functionality of several tools that only
	 * apply when a datatable has been instantiated.
	 */
	
	function initializeManageUserTools( datatable ) {
		
		// SETUP Global Filter
		// By Button Click
		$("#manageUsersFilterSubmit").click( function( ) {
			datatableFilterGlobal( datatable, $("#manageUsersFilter").val( ), true, false ); 
		});
		
		// By Pressing the Enter Key
		$("#manageUsersFilter").keyup( function( e ) {
			if( e.keyCode == 13 ) {
				datatableFilterGlobal( datatable, $(this).val( ), true, false ); 
			}
		});
	
	}
	
	/**
	 * Search the table via the global filter
	 */
	
	function datatableFilterGlobal( datatable, filterVal, isRegex, isSmartSearch ) {
		datatable.search( filterVal, isRegex, isSmartSearch, true ).draw( );
	}
	
}));