
/**
 * Javascript Bindings that apply to changing of passwords
 * in the admin tools
 */
 
(function( yourcode ) {

	yourcode( window.jQuery, window, document );

} (function( $, window, document ) {
	
	var baseURL = $("head base").attr( "href" );

	$(function( ) {
		initializeOptionPopups( );
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
					order: [[1,'asc']],
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
		
		initializeClassChangeOptions( datatable );
		initializeStatusChangeOptions( datatable );
	
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
	 
	function initializeClassChangeOptions( datatable ) {
		
		$("#managerUserWrap").on( "click", ".classChange", function( ) {
			
			var currentClick = $(this);
			var submitSet = { };
			
			submitSet['userID'] = $(this).attr( "data-userid" );
			submitSet['direction'] = $(this).attr( "data-direction" );
			submitSet['adminTool'] = "userClassChange";
			
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
					var cellIndex = datatable.cell( currentClick.closest( 'tr' ).find('.userClass') ).index( );
					datatable.cell( cellIndex ).data( results['NEWVAL'] );
					datatable.draw( false );
				} else {
					alertify.error( results['MESSAGE'] );
				}
			});
		});
		
	}
	
	/**
	 * Setup the functionality of the status change icons
	 */
	 
	function initializeStatusChangeOptions( datatable ) {
		
		$("#managerUserWrap").on( "click", ".statusChange", function( ) {
			
			var currentClick = $(this);
			var submitSet = { };
			
			submitSet['userID'] = $(this).attr( "data-userid" );
			submitSet['status'] = $(this).attr( "data-status" );
			submitSet['adminTool'] = "userStatusChange";
			
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
					var cellIndex = datatable.cell( currentClick.closest( 'tr' ).find('.userStatus') ).index( );
					datatable.cell( cellIndex ).data( results['NEWVAL'] );
					datatable.draw( false );
				} else {
					alertify.error( results['MESSAGE'] );
				}
			});
		});
		
	}
	
	/**
	 * Setup tooltips for the options in the options column
	 */
	 
	 function initializeOptionPopups( ) {
		 
		$("#managerUserWrap").on( 'mouseover', '.popoverData', function( event ) {
	 
			var optionPopup = $(this).qtip({
				overwrite: false,
				content: {
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