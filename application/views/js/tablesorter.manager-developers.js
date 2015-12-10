$(document).ready(function(){   

	// hide child rows
	$('.tablesorter-childRow td').hide();
	$('.tablesorter-childRow tr').hide();
	
	var $table = $('table#developers').tablesorter({
		theme: 'dropbox',
		widgets: ["zebra", "filter"],
		sortList: [[0,0]],
		widgetOptions : {
			// if true overrides default find rows behaviours and if any column matches query it returns that row
			filter_columnFilters: false,
			filter_anyMatch : true,
			filter_reset: '.reset',
			filter_childRows: true
		}
	});

	// Target the $('.search') input using built in functioning
	// this binds to the search using "search" and "keyup"
	// Allows using filter_liveSearch or delayed search &
	// pressing escape to cancel the search
	$.tablesorter.filter.bindSearch( $table, $('.search') );

	// Basic search binding, alternate to the above
	// bind to search - pressing enter and clicking on "x" to clear (Webkit)
	// keyup allows dynamic searching
	/*
	$(".search").bind('search keyup', function (e) {
		$('table').trigger('search', [ [this.value] ]);
	});
	*/
	
	// Toggle child row content (td), not hiding the row since we are using rowspan
	// Using delegate because the pager plugin rebuilds the table after each page change
	// "delegate" works in jQuery 1.4.2+; use "live" back to v1.3; for older jQuery - SOL
	$('.tablesorter').delegate('.toggle', 'click' ,function(){

		// use "nextUntil" to toggle multiple child rows
		// toggle table cells instead of the row
		$(this).closest('tr').nextUntil('tr:not(.tablesorter-childRow)').find('td').toggle();

		return false;
	});

	
	//$('table').bind('filterEnd', function(){
	$('.search').bind("keyup", function() {
		//$(this).find('tr.tablesorter-filter-row').removeClass('filtering');
		//$(this).find('tr.even').removeClass('filtered');
		//$('.tablesorter-childRow td').toggle();
		$('.tablesorter-childRow td').css("display", "table-cell");
		 if( !$(this).val() ) {
			$('.tablesorter-childRow td').css("display", "none");
		 }
	});
		
});