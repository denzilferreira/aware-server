$(document).ready(function(){
	$('.less').hide();
	
	var $table_researcher_data = $('table#study-data')
	.tablesorter({
		sortList: [[1,1]],
		widgetOptions : {
			// if true overrides default find rows behaviours and if any column matches query it returns that row
			filter_columnFilters: false,
			filter_anyMatch : true,
			filter_reset: '.reset'
		}
	});
	
	var max = $('#visualization-data .pager option:contains("all")').val();
	if(max < 11){
		$('#visualization-data .more').hide();
	}
	if(max == 0){
		$('table#study-data').hide();
		$('#visualization-data .no-data').show();
	}
	$('#visualization-data .more-link').click(function(){
		$('#visualization-data .pager option:contains("all")').prop('selected', true).change();
		$('#visualization-data .more').hide();
		$('#visualization-data .less').show();
	});
	
	$('#visualization-data .less-link').click(function(){
		$('#visualization-data .pager option:contains("10")').prop('selected', true).change();
		$('#visualization-data .less').hide();
		$('#visualization-data .more').show();
	});

});