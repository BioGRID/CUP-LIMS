
/**
 * Core Javascript Bindings that apply
 * to the entirety of the site.
 */
 
(function( yourcode ) {

	yourcode( window.jQuery, window, document );

} (function( $, window, document ) {
	
	Dropzone.autoDiscover = false;
	var baseURL = $("head base").attr( "href" );

	$(function( ) {
		
		initializeDropzone( );
		initializeDatePicker( );
		initializeUploadFormValidation( );
		
	});
	
	function initializeDatePicker( ) {
		
		$("input#datasetDate").datepicker({
			format: 'yyyy-mm-dd',
			todayHighlight: true,
			forceParse: true
		}).on( 'changeDate', function( e ) {
			$("#uploadForm").formValidation( 'revalidateField', 'datasetDate' );
		});
		
	}
	
	function initializeDropzone( ) {
		
		$("div#dropzoneBox").dropzone({ 
			url: baseURL + "/scripts/uploadDataset.php",
			maxFiles: 1,
			maxfilesexceeded: function( file ) {
				this.removeAllFiles( );
				this.addFile( file );
			},
			sending: function( file, xhr, formData ) {
				formData.append( 'datasetcode', $("#datasetCode").val( ) );
				$("#datasetFile").val( "" );
				$("#uploadForm").formValidation( 'revalidateField', 'datasetFile' );
			},
			success: function( file, response ) {
				console.log( response );
				var obj = JSON.parse( response );
				$("#datasetFile").val( obj.filename );
				$("#uploadForm").formValidation( 'revalidateField', 'datasetFile' );
			},
			canceled: function( file ) {
				$("#uploadForm").formValidation( 'revalidateField', 'datasetFile' );
			},
			error: function( file, message ) {
				$("#uploadForm").formValidation( 'revalidateField', 'datasetFile' );
			}
		});
		
	}
	
	function initializeUploadFormValidation( ) {
		
		var fieldVals = { };
		
		fieldVals['datasetName'] = {
			validators: {
				notEmpty: {
					message: 'A Dataset Name is Required'
				}
			}
		};
		
		fieldVals['datasetDate'] = {
			validators: {
				notEmpty: {
					message: 'A Dataset Date is Required'
				},
				date: {
					format: 'YYYY-MM-DD',
					message: 'The Dataset Date is not formatted correctly. Should be YYYY-MM-DD'
				}
			}
		};
		
		fieldVals['datasetDesc'] = {
			validators: {
				notEmpty: {
					message: 'A Dataset Description is Required'
				}
			}
		};
		
		fieldVals['datasetFile'] = {
			excluded: false,
			validators: {
				notEmpty: {
					message: 'An Uploaded Dataset File is Required'
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
				
			submitDataset( );
				
		});
	}
	
	function submitDataset( ) {
		alert( "DATASET SUBMITTED" );
	}

}));