$(document).ready(function(){   
	
	var $table_developer_plugins = $('table#developer-plugins').tablesorter({
		theme: 'dropbox',
		widgets: ["zebra", "filter"],
		dateFormat : "ddmmyyyy",
		sortList: [[0,0]],
		widgetOptions : {
			// if true overrides default find rows behaviours and if any column matches query it returns that row
			filter_columnFilters: false,
			filter_anyMatch : true,
			filter_reset: '.reset'
		}
	});

	// Target the $('.search') input using built in functioning
	// this binds to the search using "search" and "keyup"
	// Allows using filter_liveSearch or delayed search &
	// pressing escape to cancel the search
	if ($table_developer_plugins.length > 0) {
		$.tablesorter.filter.bindSearch( $table_developer_plugins, $('.search') );
	}

});