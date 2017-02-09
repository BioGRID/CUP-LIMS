
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
		initializeUploadFormValidation( );
		
	});
	
	function initializeDatePicker( ) {
		
		$("input#experimentDate").datepicker({
			format: 'yyyy-mm-dd',
			todayHighlight: true,
			forceParse: true
		}).on( 'changeDate', function( e ) {
			$("#uploadForm").formValidation( 'revalidateField', 'experimentDate' );
		});
		
	}
	
	function initializeDropzone( ) {
		
		expDropzone = new Dropzone( "div#dropzoneBox", { 
			url: baseURL + "/scripts/uploadExperiment.php",
			parallelUploads: 3,
			addRemoveLinks: true,
			sending: function( file, xhr, formData ) {
				formData.append( 'experimentCode', $("#experimentCode").val( ) );
			},
			success: function( file, response ) {
				
				// Set to a value, to allow for validation
				// of form submission
				$("#experimentHasFile").val( "true" );
				
				// Test to see if we are still validated
				$("#uploadForm").formValidation( 'revalidateField', 'experimentHasFile' );
				$("#experimentBG").append( "<option value='" + file.name + "'>" + file.name + "</option>" ).prop( 'disabled', false );
				
				
			},
			canceled: function( file ) {
				$("#uploadForm").formValidation( 'revalidateField', 'experimentHasFile' );
				removeBackgroundOption( file );
			},
			error: function( file, message ) {
				$("#uploadForm").formValidation( 'revalidateField', 'experimentHasFile' );
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
				$("#experimentHasFile").val( "" );
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
					$("#experimentHasFile").val( "" );
				}
			}
			
			$("#uploadForm").formValidation( 'revalidateField', 'experimentHasFile' );
			
		});
		
	}
	
	function removeBackgroundOption( file ) {
		
		$("#experimentBG option[value='" + file.name + "']").remove( );
		if( $("#experimentBG option").length <= 0 ) {
			$("#experimentBG").prop( "disabled", true );
		}
		
	}
	
	function initializeUploadFormValidation( ) {
		
		var fieldVals = { };
		
		fieldVals['experimentName'] = {
			validators: {
				notEmpty: {
					message: 'An Experiment Name is Required'
				}
			}
		};
		
		fieldVals['experimentDate'] = {
			validators: {
				notEmpty: {
					message: 'An Experiment Date is Required'
				},
				date: {
					format: 'YYYY-MM-DD',
					message: 'The Experiment Date is not formatted correctly. Should be YYYY-MM-DD'
				}
			}
		};
		
		fieldVals['experimentDesc'] = {
			validators: {
				notEmpty: {
					message: 'An Experiment Description is Required'
				}
			}
		};
		
		fieldVals['experimentHasFile'] = {
			excluded: false,
			validators: {
				notEmpty: {
					message: 'An Uploaded Experiment File is Required'
				},
				hasFiles: {
					message: 'You must upload at least one valid file AND have no files still processing...'
				}
			}
		};
		
		fieldVals['experimentBG'] = {
			validators: {
				notEmpty: {
					message: 'You must select at least one valid control file'
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
			
			submitExperiment( );
				
		});
	}
	
	function submitExperiment( ) {
		
		var formData = $("#uploadForm").serializeArray( );
		var submitSet = { };
		
		// Get main form data
		$.each( formData, function( ) {
			submitSet[this.name] = this.value;
		});
		
		// Get successful files only
		submitSet['experimentFiles'] = [];
		$.each( expDropzone.files, function( ) {
			if( this.status == "success" ) {
				submitSet['experimentFiles'].push( this.name );
			}
		});
		
		// Get multibackground select
		submitSet['experimentBG'] = [];
		$("#experimentBG option:selected").each( function( ) {
			submitSet['experimentBG'].push( $(this).val( ) );
		});
		
		// Convert to JSON
		submitSet = JSON.stringify( submitSet );
		
		// Send via AJAX for submission to
		// database and placement of files
		$.ajax({
			url: baseURL + "/scripts/submitExperiment.php",
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
				$("#uploadForm").formValidation( 'disableSubmitButtons', false );
			} 
			
			$("#messages").html( '<div class="alert alert-' + alertType + '" role="alert"><i class="fa ' + alertIcon + ' fa-lg"></i> ' + data['MESSAGE'] + '</div></div>' );
			
			if( data["STATUS"] == "success" ) {
				window.location = baseURL + "/FileProgress?expID=" + data["ID"];
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