
/**
 * Core Javascript Bindings that apply
 * to the entirety of the site.
 */
 
(function( yourcode ) {

	yourcode( window.jQuery, window, document );

} (function( $, window, document ) {
	
	Dropzone.autoDiscover = false;
	var baseURL = $("head base").attr( "href" );
	var annDropzone;

	$(function( ) {
		
		addFileValidator( );
		initializeDropzone( );
		initializeUploadFormValidation( );
		
	});
	
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
				
			},
			canceled: function( file ) {
				$("#uploadForm").formValidation( 'revalidateField', 'hasFile' );
			},
			error: function( file, message ) {
				$("#uploadForm").formValidation( 'revalidateField', 'hasFile' );
			}
		});
		
		expDropzone.on( "addedfile", function( file ) {
			
			// Use a fancy text type icon to represent
			// non-image type files
			if (!file.type.match(/image.*/)) {
				expDropzone.emit( "thumbnail", file, baseURL + "/img/text-icon.png" );
			} 
			
			// Only allow a single file to be added
			if( this.files[1] != null ) {
				this.removeFile( this.files[0] );
			}
			
		});
		
		expDropzone.on( "removedfile", function( file ) {
			
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
	
	function initializeUploadFormValidation( ) {
		
		var fieldVals = { };
		
		fieldVals['annotationDesc'] = {
			validators: {
				notEmpty: {
					message: 'A description for this Annotation File is Required'
				},
				stringLength: {
					message: 'Annotation description must be less than 255 characters total',
					max: 255,
					min: 0
				}
			}
		};
		
		fieldVals['hasFile'] = {
			excluded: false,
			validators: {
				notEmpty: {
					message: 'An Uploaded Annotation File is Required'
				},
				hasFiles: {
					message: 'You must upload one valid file AND it must not still be processing...'
				}
			}
		};
			
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
		
		// Convert to JSON
		submitSet = JSON.stringify( submitSet );
		
		// Send via AJAX for submission to
		// database and placement of files
		$.ajax({
			url: baseURL + "/scripts/submitAnnotation.php",
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
				window.location = baseURL + "/FileProgress/Annotation?files=" + data["IDS"];
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