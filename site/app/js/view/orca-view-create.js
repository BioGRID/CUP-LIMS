
/**
 * Javascript Bindings that apply to changing of permissions
 * in the admin tools
 */
 
(function( yourcode ) {

	yourcode( window.jQuery, window, document );

} (function( $, window, document ) {
	
	var baseURL = $("head base").attr( "href" );

	$(function( ) {
		
		initializeFormValidation( );
		
		var expIDs = $("#expIDs").val( );
		
		$(".datatableBlock").orcaDataTableBlock({ 
			sortCol: 4, 
			sortDir: "desc", 
			pageLength: 1000,
			colTool: "filesHeader", 
			rowTool: "filesRows", 
			hasToolbar: true,
			addonParams: { "ids" : expIDs, "showBGSelect" : "true" },
			optionsCallback: function( datatable ) {
				initializeOptionPopups( );
				initializeGlobalBGSelect( );
				initializeCheckboxCheck( );
			}
		});
	});
	
	/**
	 * Setup the functionality for creating a view from selected files 
	 */
	 
	function initializeGlobalBGSelect( ) {
		
		$(".datatableBlock").on( "change", ".orcaToolbarSelect", function( ) {
			
			var selectedVal = $("option:selected", this).text( );
			$(".orcaSelect").each( function( ) {
				var matchingOption = $(this).find( 'option:contains(' + selectedVal + ')' );
				if( matchingOption.length > 0 ) {
					matchingOption.prop( "selected", "selected" );
				}
			});
			
		});
		
	}
	
	/**
	 * Set a check for a selected files
	 */
	 
	function initializeCheckboxCheck( ) {
		$(".datatableBlock").on( "change", ".orcaDataTableRowCheck", function( ) {
			var table = $(this).closest( ".orcaDataTableTools" ).find( ".orcaDataTable" );
			selectedFileTest( table );
		});
		
		$(".datatableBlock").on( "click", ".orcaDataTableCheckAll", function( ) {
			var table = $(this).closest( ".orcaDataTableTools" ).find( ".orcaDataTable" );
			selectedFileTest( table );
		});
		
		resetCheckboxCheckAfterFiltering( );
		
	}
	
	/**
	 * Reset checkbox check whenever filtering
	 */
	 
	function resetCheckboxCheckAfterFiltering( ) {
		
		$(".orcaDataTableFilterSubmit").click( function( ) {
			$("#viewChecked").val( "" );
			$("#addViewForm").formValidation( 'revalidateField', 'viewChecked' );
		});
			
		// By Pressing the Enter Key
		$(".orcaDataTableFilterText").keyup( function( e ) {
			if( e.keyCode == 13 ) {
				$("#viewChecked").val( "" );
				$("#addViewForm").formValidation( 'revalidateField', 'viewChecked' );
			}
		});
	}
	
	/**
	 * Test for selected files
	 */
	 
	function selectedFileTest( table ) {
		if( table.find( ".orcaDataTableRowCheck:checked" ).length > 0 ) {
			$("#viewChecked").val( "1" );
		} else {
			$("#viewChecked").val( "" );
		}
		
		$("#addViewForm").formValidation( 'revalidateField', 'viewChecked' );
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
	 
	/**
	 * Setup the validation for the add new view form
	 */
	
	function initializeFormValidation( ) {
		
		var fieldVals = { };
		
		fieldVals['viewName'] = {
			validators: {
				notEmpty: {
					message: 'You must enter a view name'
				}
			}
		};
		
		fieldVals['viewDesc'] = {
			validators: {
				notEmpty: {
					message: 'You must enter a view description'
				}
			}
		};
		
		fieldVals['viewChecked'] = {
			excluded: false,
			validators: {
				notEmpty: {
					message: 'You must select one or more files listed below'
				}
			}
		};
			
		$("#addViewForm").formValidation({
			framework: 'bootstrap',
			fields: fieldVals
		}).on( 'success.form.fv', function( e ) {
			e.preventDefault( );
			
			var $form = $(e.target),
				fv = $(e.target).data( 'formValidation' );
			
			submitNewView( );
				
		});
	}
	
	function submitNewView( ) {
		
		var formData = $("#addViewForm").serializeArray( );
		var submitSet = { };
		
		// Get main form data
		$.each( formData, function( ) {
			submitSet[this.name] = this.value;
		});
		
		// Add type of tool
		submitSet['tool'] = "addView";
		
		// Get checked files and background settings
		submitSet['viewFiles'] = [];
		var table = $(".orcaDataTableTools" ).find( ".orcaDataTable" );
		var files = [];
		table.find( ".orcaDataTableRowCheck:checked" ).each( function( ) {
			var background = $(this).parent( ).parent( ).find( ".orcaSelect" );
			files.push( { "fileID" : $(this).val( ), "backgroundID" : background.val( ) } );
		});
		submitSet['viewFiles'] = files;
				
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
			if( data["STATUS"] == "ERROR" ) {
				alertType = "danger";
				alertIcon = "fa-warning";
				$("#addViewForm").formValidation( 'disableSubmitButtons', false );
			} else if( data["STATUS"] == "SUCCESS" ) {
				window.location = baseURL + "View?viewID=" + data['ID'];
			}
			
			$("#messages").html( '<div class="alert alert-' + alertType + '" role="alert"><i class="fa ' + alertIcon + ' fa-lg"></i> ' + data['MESSAGE'] + '</div></div>' );
			
		}).fail( function( jqXHR, textStatus, errorThrown ) {
			console.log( jqXHR );
			console.log( textStatus );
			$("#addViewForm").formValidation( 'disableSubmitButtons', false );
		});
		
	}
	
}));