
/**
 * Core Javascript Bindings that apply
 * to the entirety of the site.
 */
 
(function( yourcode ) {

	yourcode( window.jQuery, window, document );

} (function( $, window, document ) {
	
	Dropzone.autoDiscover = false;

	$(function( ) {
		
		$("div#dropzoneBox").dropzone({ 
			url: "test",
			addRemoveLinks: true,
			autoProcessQueue: false,
			maxFiles: 1,
			maxfilesexceeded: function( file ) {
				this.removeAllFiles( );
				this.addFile( file );
			}
		});
		
		$("input#datasetDate").datepicker({
			format: 'yyyy-mm-dd',
			todayHighlight: true,
			forceParse: true
		});
		
	});

}));