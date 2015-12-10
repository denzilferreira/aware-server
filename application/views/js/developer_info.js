var cct = $.cookie('csrf_cookie_aware');

jQuery(document).ready(function(){

	$(".add_api_confirm").dialog({
		autoOpen: false,
		minWidth: 200,
		title: "Insert study API key",
		buttons: {
			"Ok": function() {
				var id = $('.plugin_id').attr('id');
				var val = document.getElementById('api_key_text').value;
				console.log(val);
				$.ajax({
					url: "../plugin_give_studyaccess",
					type: "POST",
					data: { plugin_id : id, value : val, 'csrf_token_aware' : cct },
					dataType: "json",
					success: function() {
						window.location.reload(true);
					},
					error: function(error) {
					}
				});

			},

			"Cancel": function() {
				$(".add_api_confirm").dialog('close');
			}

		}
	});

	$('.delete-apikey').each(function() {  
		$.data(this, 'dialog', 
			$(this).next('.delete-dialog').dialog({
				autoOpen: false,
				modal: true,
				draggable: false,
				resizable: false
			})
		);  
		}).click(function(e) {
			e.preventDefault();
			var val = $(this).parent().attr('id');  
			var plugin_id = $('.plugin_id').attr('id');
			
			var data_array = { value : val, plugin_id : plugin_id, 'csrf_token_aware' : cct};
			var link = '../delete_apikey';
			
			$.data(this, 'dialog').dialog('option', 'title', 'Remove plugin from study');
			$.data(this, 'dialog').dialog({buttons: getButtonsDelCo(link, data_array)});
			$.data(this, 'dialog').dialog('open');    
	});

	$('#statebox').click(function(event) {
		var id = $('.plugin_id').attr('id');
		var val;
		if (this.checked) {
			val = 1;
		} else {
			val = 0;
		}
		$.ajax({
			url: "../update_plugin_state",
			type: "POST",
			data: { plugin_id : id, value : val, 'csrf_token_aware' : cct },
			dataType: "json",
			success: function() {
				// Success
			},
			error: function(error) {
				// error
			} 
		});
	});		

	$('#add_api').click(function(event) {
		var id = $('.plugin_id').attr('id');
		$(".add_api_confirm").dialog('open');
	});

	function getButtonsDelCo(link, data_array) {
	var dialog_buttons = {};
	dialog_buttons['Ok'] = {	text  : 'Ok', 
								click : function() {
											$.ajax({
												url: link,
												type: "POST",
												data: data_array,
												dataType: "json",
												success: function(data){
													window.location.reload(true);
												},
												error: function(data){
													window.location.reload(true);
												}
											});
										}, 
								 class : 'button-dialog-ok'
							   };
	dialog_buttons['Cancel'] =  {	text  : 'Cancel', 
									click : function() {
											$('#email').val('');
											$('.dialog-error').hide();
											$(this).dialog('close');
											}, 
									class : 'button-dialog-close'
							   };
	return dialog_buttons;	
	}

});