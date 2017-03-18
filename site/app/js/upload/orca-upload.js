
/**
 * Core Javascript Bindings that apply
 * to the entirety of the site.
 */
 
(function( yourcode ) {

	yourcode( window.jQuery, window, document );

} (function( $, window, document ) {
	
	Dropzone.autoDiscover = false;
	var baseURL = $("head base").attr( "href" );
	var expDropzone;

	$(function( ) {
		
		addFileValidator( );
		initializeDropzone( );
		initializeDatePicker( );
		initializePermissionSwitch( );
		initializeUploadFormValidation( );
		
	});
	
	function initializePermissionSwitch( ) {
		
		$("#uploadForm").on( "change", "#filePermission", function( ) {
			
			var selectVal = $(this).val( );
			if( selectVal == "private" ) {
				$("#fileGroupsBox").show( );
			} else {
				$("#fileGroupsBox").hide( );
			}
			
		});
		
	}
	
	function initializeDatePicker( ) {
		
		$("input#fileDate").datepicker({
			format: 'yyyy-mm-dd',
			todayHighlight: true,
			forceParse: true
		}).on( 'changeDate', function( e ) {
			$("#uploadForm").formValidation( 'revalidateField', 'fileDate' );
		});
		
	}
	
	function initializeDropzone( ) {
		
		expDropzone = new Dropzone( "div#dropzoneBox", { 
			url: baseURL + "/scripts/uploadFiles.php",
			parallelUploads: 3,
			addRemoveLinks: true,
			sending: function( file, xhr, formData ) {
				formData.append( 'fileCode', $("#fileCode").val( ) );
			},
			success: function( file, response ) {
				
				// Set to a value, to allow for validation
				// of form submission
				$("#hasFile").val( "true" );
				
				// Test to see if we are still validated
				$("#uploadForm").formValidation( 'revalidateField', 'hasFile' );
				$("#fileBG").append( "<option value='" + file.name + "'>" + file.name + "</option>" ).prop( 'disabled', false );
				
				
			},
			canceled: function( file ) {
				$("#uploadForm").formValidation( 'revalidateField', 'hasFile' );
				removeBackgroundOption( file );
			},
			error: function( file, message ) {
				$("#uploadForm").formValidation( 'revalidateField', 'hasFile' );
				removeBackgroundOption( file );
			}
		});
		
		expDropzone.on( "addedfile", function( file ) {
			
			// Use a fancy text type icon to represent
			// non-image type files
			if (!file.type.match(/image.*/)) {
				expDropzone.emit( "thumbnail", file, baseURL + "/img/text-icon.png" );
			} 
			
			// Check for duplicates and ignore them if they
			// are already uploaded
			if( this.files.length ) {
				var i, len;
				for( i = 0, len = this.files.length; i < len - 1; i++ ) {
					if( this.files[i].name == file.name && this.files[i].size == file.size ) {
						this.removeFile( file );
					}
				}
			} 
			
		});
		
		expDropzone.on( "removedfile", function( file ) {
			
			removeBackgroundOption( file );
			
			if( !this.files.length ) {		
				// Empty out to prevent form submission
				$("#hasFile").val( "" );
			} else {
				
				// Check to see if there are any successfully
				// uploaded files remaining and set the validation
				// flag if not 
				var hasFile = false;
				var i, len;
				for( i = 0, len = this.files.length; i < len; i++ ) {
					if( this.files[i].status == "success" ) {
						hasFile = true;
						break;
					}
				}
				
				if( !hasFile ) {
					$("#hasFile").val( "" );
				}
			}
			
			$("#uploadForm").formValidation( 'revalidateField', 'hasFile' );
			
		});
		
	}
	
	function removeBackgroundOption( file ) {
		
		$("#fileBG option[value='" + file.name + "']").remove( );
		if( $("#fileBG option").length <= 0 ) {
			$("#fileBG").prop( "disabled", true );
		}
		
	}
	
	function initializeUploadFormValidation( ) {
		
		var fieldVals = { };
		
		fieldVals['fileDesc'] = {
			validators: {
				notEmpty: {
					message: 'A File Set Name is Required'
				}
			}
		};
		
		fieldVals['fileDate'] = {
			validators: {
				notEmpty: {
					message: 'An Run Date is Required'
				},
				date: {
					format: 'YYYY-MM-DD',
					message: 'The Run Date is not formatted correctly. Should be YYYY-MM-DD'
				}
			}
		};
		
		fieldVals['fileTags'] = {
			validators: {
				stringLength: {
					message: 'Tags must be less than 255 characters total',
					max: 255,
					min: 0
				}
			}
		};
		
		fieldVals['hasFile'] = {
			excluded: false,
			validators: {
				notEmpty: {
					message: 'An Uploaded File is Required'
				},
				hasFiles: {
					message: 'You must upload at least one valid file AND have no files still processing...'
				}
			}
		};
		
		fieldVals['fileBG'] = {
			validators: {
				notEmpty: {
					message: 'You must select at least one valid control file'
				}
			}
		}
		
		fieldVals['filePermission'] = {
			validators: {
				notEmpty: {
					message: 'A File Permission Setting is Required'
				}
			}
		}
			
		$("#uploadForm").formValidation({
			framework: 'bootstrap',
			fields: fieldVals
		}).on( 'success.form.fv', function( e ) {
			e.preventDefault( );
			
			var $form = $(e.target),
				fv = $(e.target).data( 'formValidation' );
			
			submitFiles( );
				
		});
	}
	
	function submitFiles( ) {
		
		var formData = $("#uploadForm").serializeArray( );
		var submitSet = { };
		
		// Get main form data
		$.each( formData, function( ) {
			submitSet[this.name] = this.value;
		});
		
		// Get successful files only
		submitSet['files'] = [];
		$.each( expDropzone.files, function( ) {
			if( this.status == "success" ) {
				submitSet['files'].push( this.name );
			}
		});
		
		// Get multibackground select
		submitSet['fileBG'] = [];
		$("#fileBG option:selected").each( function( ) {
			submitSet['fileBG'].push( $(this).val( ) );
		});
		
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
			url: baseURL + "/scripts/submitFiles.php",
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
				$("#uploadForm").formValidation( 'disableSubmitButtons', false );
			} 
			
			$("#messages").html( '<div class="alert alert-' + alertType + '" role="alert"><i class="fa ' + alertIcon + ' fa-lg"></i> ' + data['MESSAGE'] + '</div></div>' );
			
			if( data["STATUS"] == "success" ) {
				window.location = baseURL + "/FileProgress?files=" + data["IDS"];
			}
			
		}).fail( function( jqXHR, textStatus, errorThrown ) {
			console.log( jqXHR );
			console.log( textStatus );
			$("#uploadForm").formValidation( 'disableSubmitButtons', false );
		});
		
	}
	
	function addFileValidator( ) {
		
		FormValidation.Validator.hasFiles = {
			validate: function( validator, $field, options ) {
			
				var files = expDropzone.files;
				var filesLength = files.length;
				var hasSuccess = false;
				var stillProcessing = false;
				
				if( filesLength ) {
					var i, len;
					for( i = 0; i < filesLength; i++ ) {
						// Ensure at least 1 successful file
						if( files[i].status == "success" ) {
							hasSuccess = true;
						// Ensure no files are still queued or uploading
						} else if( files[i].status == "queued" || files[i].status == "uploading" ) {
							stillProcessing = true;
						}
					}
				}
				
				// If at least one successful and none
				// still processing, we are valid
				if( hasSuccess && !stillProcessing ) {
					return true;
				} 
				
				return false;
			
			}
		};
		
	}

}));