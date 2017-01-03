
/**
 * Javascript Bindings that apply to changing of passwords
 * in the admin tools
 */
 
(function( yourcode ) {

	yourcode( window.jQuery, window, document );

} (function( $, window, document ) {
	
	var baseURL = $("head base").attr( "href" );

	$(function( ) {
		initializeFormValidation( );
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
					order: [[0,'asc']],
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
	
	/**
	 * Setup the validation for the add new permission form
	 */
	
	function initializeFormValidation( ) {
		
		var fieldVals = { };
		
		fieldVals['permissionName'] = {
			validators: {
				notEmpty: {
					message: 'You must enter a permission name'
				}
			}
		};
		
		fieldVals['permissionDesc'] = {
			validators: {
				notEmpty: {
					message: 'You must enter a permission description'
				}
			}
		};
		
		fieldVals['permissionCategory'] = {
			validators: {
				notEmpty: {
					message: 'You must enter a permission category'
				}
			}
		};
			
		$("#addPermissionForm").formValidation({
			framework: 'bootstrap',
			fields: fieldVals
		}).on( 'success.form.fv', function( e ) {
			e.preventDefault( );
			
			var $form = $(e.target),
				fv = $(e.target).data( 'formValidation' );
			
			submitAddNewPermission( );
				
		});
	}
	
	function submitAddNewPermission( ) {
		
		var formData = $("#addPermissionForm").serializeArray( );
		var submitSet = { };
		
		// Get main form data
		$.each( formData, function( ) {
			submitSet[this.name] = this.value;
		});
		
		// Add type of tool
		submitSet['adminTool'] = "addPermission";
				
		// Convert to JSON
		submitSet = JSON.stringify( submitSet );
		
		// Send via AJAX for submission to
		// database and placement of files
		$.ajax({
			url: baseURL + "/scripts/adminTools.php",
			type: "POST",
			data: {"expData" : submitSet},
			dataType: 'json',
			beforeSend: function( ) {
				$("#messages").html( "" );
			}
		}).done( function( data, textStatus, jqXHR ) {
			
			var alertType = "success";
			var alertIcon = "fa-check";
			if( data["STATUS"] == "ERROR" ) {
				alertType = "danger";
				alertIcon = "fa-warning";
				$("#addPermissionForm").formValidation( 'disableSubmitButtons', false );
			} else if( data["STATUS"] == "SUCCESS" ) {
				$("#addPermissionForm").trigger( "reset" );
				$("#addPermissionForm").data('formValidation').resetForm( );
				$("#managePermissionsTable").DataTable( ).draw( false );
			}
			
			$("#messages").html( '<div class="alert alert-' + alertType + '" role="alert"><i class="fa ' + alertIcon + ' fa-lg"></i> ' + data['MESSAGE'] + '</div></div>' );
			
		}).fail( function( jqXHR, textStatus, errorThrown ) {
			console.log( jqXHR );
			console.log( textStatus );
			$("#addPermissionForm").formValidation( 'disableSubmitButtons', false );
		});
		
	}
	
}));