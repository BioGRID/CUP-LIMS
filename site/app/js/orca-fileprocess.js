
/**
 * Core Javascript Bindings that apply
 * to the entirety of the site.
 */
 
(function( yourcode ) {

	yourcode( window.jQuery, window, document );

} (function( $, window, document ) {
	
	var baseURL = $("head base").attr( "href" );

	$(function( ) {
		
		var expID = $("#experimentID").val( );
		var performFull = $("#performFull").val( );
			
		// Fetch list of files that need to be
		// processed based in fetched parameters
		$.ajax({
			url: baseURL + "/scripts/processFiles.php",
			type: 'POST',
			data: {'expID' : expID, 'performFull': performFull, 'script': "fetchFiles"},
			dataType: 'json',
			beforeSend: function( ) {
				$("#processingOutput").html( "" );
			}
		}).done( function( data, textStatus, jqXHR ) {
			
			// Send list of files to be processed one
			// by one and parsed into the database
			
			if( data.length > 0 ) {
				processFiles( data, 0, data.length );
			} else {
				$("#processingOutput").html( "<div class='alert alert-success' role='alert'><i class='fa fa-check fa-fw fa-lg'></i> All Files Processed!</div>" );
			}
			
		}).fail( function( jqXHR, textStatus, errorThrown ) {
			console.log( jqXHR );
			console.log( textStatus );
			$("#processingOutput").html( "<div class='alert alert-danger' role='alert'><i class='fa fa-warning fa-fw fa-lg'></i> Unable to fetch file list, please try again later!</div>" );
		});
		
	});
	
	function processFiles( files, fileCount, fileTotal ) {
		
		// Process a file into the database
		// and maintain it's status output to the user
		
		if( files.length > 0 ) {
			
			fileCount++;
			var fileCountDetails = "[" + fileCount + "/" + fileTotal + "]";
			
			var file = files.pop( );
			var processID = "process" + file["ID"];
		
			$.ajax({
				url: baseURL + "/scripts/processFiles.php",
				type: 'POST',
				data: {'fileID': file['ID'], 'script': "parseFile"},
				dataType: "json",
				beforeSend: function( ) {
					$("#processingOutput").prepend( "<div id='" + processID + "' class='alert alert-warning' role='alert'><i class='processIcon fa fa-spinner fa-spin fa-lg fa-fw'></i> <span class='processText'>Processing file " + fileCountDetails + " : <strong>" + file['NAME'] + "</strong> (" + file['SIZE'] + ")</span></div>" );
				}
			}).done( function( data, textStatus, jqXHR ) {
			
				if( data['STATUS'] == "SUCCESS" ) {
					$("#" + processID).removeClass( "alert-warning" ).addClass( "alert-success" );
					$("#" + processID + " .processIcon").removeClass( "fa-spinner fa-spin" ).addClass( "fa-check" );
					$("#" + processID + " .processText").html( "Successfully processed " + fileCountDetails + " : <strong>" + file['NAME'] + "</strong> (" + file['SIZE'] + ")" );
				} else if( data['STATUS'] == "ERROR" ) {
					$("#" + processID).removeClass( "alert-warning" ).addClass( "alert-danger" );
					$("#" + processID + " .processIcon").removeClass( "fa-spinner fa-spin" ).addClass( "fa-warning" );
					$("#" + processID + " .processText").html( "Error Processing " + fileCountDetails + " : <strong>" + file['NAME'] + "</strong> (" + file['SIZE'] + ") - " + data['MESSAGE'] );
				}
			
				processFiles( files, fileCount, fileTotal );
				
			}).fail( function( jqXHR, textStatus, errorThrown ) {
				console.log( jqXHR );
				console.log( textStatus );
				$("#" + processID).removeClass( "alert-warning" ).addClass( "alert-danger" );
				$("#" + processID + " .processIcon").removeClass( "fa-spinner fa-spin" ).addClass( "fa-warning" );
				$("#" + processID + " .processText").html( "Error Processing " + fileCountDetails + " : <strong>" + file['NAME'] + "</strong> (" + file['SIZE'] + ") - " + textStatus + " HALTING!!" );
			});
			
		} else {
			$("#processingOutput").prepend( "<div class='alert alert-info' role='alert'><i class='processIcon fa fa-check fa-lg fa-fw'></i> <strong>" + fileTotal + " Files Processed!</div>" );
		}
		
	}

}));