
/**
 * Javascript Bindings that apply to management and creation of 
 * jquery datatables instances
 */
 
(function( yourcode ) {

	yourcode( window.jQuery, window, document );

} (function( $, window, document ) {
	
	$.orcaDataTableBlock = function( el, options ) {
	
		var base = this;
		base.$el = $(el);
		base.el = el;
		
		base.data = { 
			id: base.$el.attr( "id" ),
			baseURL: $("head base").attr( "href" ),
			checkedBoxes: { }
		};
		
		/**
		 * Common Components
		 */
		
		base.components = { 
			table: base.$el.find( ".orcaDataTable" ),
			filterOutput: base.$el.find( ".orcaDataTableFilterOutput" ),
			filterSubmit: base.$el.find( ".orcaDataTableFilterSubmit" ),
			filterText: base.$el.find( ".orcaDataTableFilterText" ),
			tableRowCount: base.$el.find( ".orcaRowCount" ),
			toolbar: base.$el.find( ".orcaDataTableToolbar" )
		};
		
		base.$el.data( "orcaDataTableBlock", base );
		
		/** 
		 * Setup basic structure and functionality of the 
		 * ORCA DataTable Block
		 */
		 
		base.init = function( ) {
			base.options = $.extend( {}, $.orcaDataTableBlock.defaultOptions, options );
			base.initializeTable( );
		};
		
		/**
		 * Grab the set of columns that will be displayed
		 * for this table
		 */
		
		base.fetchCols = function( ) {
			
			var submitSet = { 'tool' : base.options.colTool };
			$.extend( submitSet, base.options.addonParams );
			submitSet = JSON.stringify( submitSet );
			
			return $.ajax({
				
				url: base.data.baseURL + "/scripts/datatableTools.php",
				data: {"expData" : submitSet},
				method: "POST",
				dataType: "json"
				
			});
			
		};
		
		/**
		 * Setup the functionality of several tools that only
		 * apply when a datatable has been instantiated.
		 */
		
		base.initializeTools = function( ) {
			
			// SETUP Global Filter
			// By Button Click
			base.components.filterSubmit.click( function( ) {
				base.filterGlobal( base.components.filterText.val( ), true, false ); 
			});
			
			// By Pressing the Enter Key
			base.components.filterText.keyup( function( e ) {
				if( e.keyCode == 13 ) {
					base.filterGlobal( base.components.filterText.val( ), true, false ); 
				}
			});
			
			// Setup Check All Button on Toolbar
			if( base.options.hasToolbar ) {
				base.components.toolbar.find( ".orcaDataTableCheckAll" ).click( function( ) {
					var statusText = $(this).attr( "data-status" );
					
					if( statusText == "check" ) {
						base.setCheckAllStatus( "uncheck", true );
					} else if( statusText == "uncheck" ) {
						base.setCheckAllStatus( "check", false );
					}
					
				});
			}
			
			// Setup storage of checked boxes
			base.components.table.on( "change", ".orcaDataTableRowCheck", function( ) {
				if( $(this).prop( "checked" ) ) {
					base.data.checkedBoxes[$(this).val( )] = true;
				} else {
					base.data.checkedBoxes[$(this).val( )] = false;
				}
			});
			
		};
		
		/**
		 * Setup the basic datatable functionality 
		 * table with the ability to load data as required
		 */
		
		base.initializeTable = function( ) {
			
			$.when( base.fetchCols( ) ).then( function( data, textStatus, jqXHR ) {
				
				var datatable = base.components.table.DataTable({
					processing: true,
					serverSide: true,
					columns: data,
					pageLength: base.options.pageLength,
					deferRender: true,
					order: [base.options.sortCol,base.options.sortDir],
					language: {
						processing: "Loading Data... <i class='fa fa-spinner fa-pulse fa-lg'></i>"
					},
					ajax : {
						url: base.data.baseURL + "/scripts/datatableTools.php",
						type: 'POST',
						data: function( d ) {  
							d.tool = base.options.rowTool;
							d.totalRecords = base.components.tableRowCount.val( );
							d.checkedBoxes = base.data.checkedBoxes;
							$.extend( d, base.options.addonParams );
							d.expData = JSON.stringify( d );
						}
					},
					infoCallback: function( settings, start, end, max, total, pre ) {
						base.components.filterOutput.html( pre );
					},
					dom : "<'row'<'col-sm-12'rt>><'row'<'col-sm-5'i><'col-sm-7'p>>"
						
				});
				
				base.initializeTools( );
				base.options.optionsCallback( datatable );
				
			});
				
		};
		
		/**
		 * Search the table via the global filter
		 */
		
		base.filterGlobal = function( filterVal, isRegex, isSmartSearch ) {
			base.components.table.DataTable( ).search( filterVal, isRegex, isSmartSearch, true ).draw( );
		};
		
		/**
		 * Set the check all button status to the values passed in
		 */
		 
		base.setCheckAllStatus = function( statusText, propVal ) {
			base.components.table.find( ".orcaDataTableRowCheck:enabled" ).prop( "checked", propVal );
			base.components.toolbar.find( ".orcaDataTableCheckAll" ).attr( "data-status", statusText );
		};
		
		base.updateOption = function( optionName, optionValue ) {
			base.options[optionName] = optionValue;
		};
		
		base.init( );
	
	};

	$.orcaDataTableBlock.defaultOptions = { 
		sortCol: 0,
		sortDir: "ASC",
		pageLength: 100,
		colTool: "",
		rowTool: "",
		addonParams: { },
		hasToolbar: false
	};

	$.fn.orcaDataTableBlock = function( options ) {
		return this.each( function( ) {
			(new $.orcaDataTableBlock( this, options ));
		});
	};
	
}));