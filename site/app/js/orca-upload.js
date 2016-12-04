
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
				
			},
			canceled: function( file ) {
				$("#uploadForm").formValidation( 'revalidateField', 'experimentHasFile' );
			},
			error: function( file, message ) {
				$("#uploadForm").formValidation( 'revalidateField', 'experimentHasFile' );
			}
		});
		
		expDropzone.on( "addedfile", function( file ) {
			
			console.log( this.files );
			
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
					message: 'You must upload at least one valid file'
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
				
			submitExperiment( );
				
		});
	}
	
	function submitExperiment( ) {
		console.log( "EXPERIMENT SUBMITTED" );
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
						if( files[i].status == "success" ) {
							hasSuccess = true;
						} else if( files[i].status == "queued" || files[i].status == "uploading" ) {
							stillProcessing = true;
						}
					}
				}
					
				if( hasSuccess && !stillProcessing ) {
					return true;
				} 
				
				return false;
			
			}
		};
		
	}

}));