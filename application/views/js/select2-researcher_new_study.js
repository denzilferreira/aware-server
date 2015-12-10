$(document).ready(function(){  

	$('#host-type').select2({
		minimumResultsForSearch: -1
	});
	
	$('#host-type').change(function(){
		var selected = $(this).find(':selected');
		$('.db-choice').hide();
		$('.'+selected.val()).fadeIn("medium"); 
	}).change();

});