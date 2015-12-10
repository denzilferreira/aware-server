$(document).ready(function(){
	$('.less').hide();
	
	var $table_mqtt_history = $('table#mqtt-history')
	.tablesorter({
		sortList: [[0,1]],
		widgets: ["filter"],
		widgetOptions : {
			// if true overrides default find rows behaviours and if any column matches query it returns that row
			filter_columnFilters: false,
			filter_anyMatch : true,
			filter_reset: '.reset'
		}
	}).tablesorterPager({
		container: $("#pager-mqtt-history")
	});
	var max = $('#mqtt-history-wrapper .pager option:contains("all")').val();
	if(max < 11){
		$('#mqtt-history-wrapper .more').hide();
	}
	if(max == 0){
		$('table#mqtt-history').hide();
		$('#mqtt-history-wrapper .no-data').show();
	}
	$('#mqtt-history-wrapper .more-link').click(function(){
		$('#mqtt-history-wrapper .pager option:contains("all")').prop('selected', true).change();
		$('#mqtt-history-wrapper .more').hide();
		$('#mqtt-history-wrapper .less').show();
	});
	
	$('#mqtt-history-wrapper .less-link').click(function(){
		$('#mqtt-history-wrapper .pager option:contains("10")').prop('selected', true).change();
		$('#mqtt-history-wrapper .less').hide();
		$('#mqtt-history-wrapper .more').show();
	});
	
	if ($table_mqtt_history.length > 0) {
		$.tablesorter.filter.bindSearch( $table_mqtt_history, $('#mqtt-history-search') );
	}


});