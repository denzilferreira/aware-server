$(document).ready(function(){  

	$('#esm-type').select2({
		minimumResultsForSearch: -1
	});
	
	$('.esm-threshold').select2({
		minimumResultsForSearch: -1
	});
	
	$('#esm-likertmax').select2({
		minimumResultsForSearch: -1
	});
	
	$('#broadcasts-select').select2({
		minimumResultsForSearch: -1
	});
	
	$('#broadcasts-type').select2({
		minimumResultsForSearch: -1
	});
	
	$('#esm-type').on('select2-selecting', function(e){ 
		$('.error-msg').hide();
		$('.mqtt-error').hide();
	});

	$('#esm-type').change(function(){
		$("tr.esm-history-message").css("font-weight", "normal");
		var selected = $(this).find(':selected');
		$('.esm-message').hide();
		$('.'+selected.val()).fadeIn("medium"); 
	}).change();
	
	$(".esm-options").select2({
		dropdownCssClass: 'select2-search',
		tags:[],
		minimumResultsForSearch: -1
	});
	
	$('#broadcasts-type').change(function(){
		var selected = $(this).find(':selected');
		$('.broadcasts-message').hide();
		$('.'+selected.val()).fadeIn("medium"); 
	}).change();
	
	$(".broadcasts-options").select2({
		dropdownCssClass: 'select2-search',
		tags:[],
		minimumResultsForSearch: -1
	});
	
	$(".configuration").select2({
		dropdownCssClass: 'select2-search',
		tags:[],
		minimumResultsForSearch: -1
	});
	
	$("#study-configuration").select2({
		dropdownCssClass: 'select2-search',
		tags:[],
		minimumResultsForSearch: -1,
		width: '725px',
		height: '100px',
		containerCssClass: 'study-config-container'
	});

	
	

});