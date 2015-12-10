var cct = $.cookie('csrf_cookie_aware');

$(document).ready(function(){   
	if ($('input[type=checkbox].activated-status').length) {
		$(function() {
			$('input[type=checkbox].activated-status').switchButton({
				labels_placement: 'right',
				on_label: 'Active',
				off_label: 'Deactive'
			});
		})
	}
	

	$("table#user-management").on("click", "td input.developer-status, input.researcher-status, input.manager-status", function(event) {
		var id = $(this).attr("id").split("_")[1];
		var field = $(this).attr("class").split("-")[0];
		var button = $(this);
		var c;

		if (this.checked) {
			c = 1;
		} else {
			c = 0;
		}

		$.ajax({
			url: "update_user_level",
			type: "POST",
			data: { user_id : id, field: field, value : c, 'csrf_token_aware' : cct},
			dataType: "json",
			success: function() {
				// Success
			},
			error: function(error) {
				console.debug(error);
				button.prop('checked', !c);
			} 
		});
	});
	

	$('.more').click(function(event) {
		$.ajax({
			url: "get_users_data",
			type: "POST",
			data: { 'csrf_token_aware' : cct},
			dataType: "json",
			success: function() {
				console.debug(data);
			}
		});
	});

	$("table#user-management").on("click", "td input.switchbutton", function(event) {
		var status = $(this).nextAll(".toggle-status:first");
		var id = $(this).attr("id").split("_")[1];
		var button = $(this);
		var c;

		if (this.checked) {
			c = 1;
			status.html("Active");
			status.addClass("on");
			status.removeClass("off");
		} else {
			c = 0;
			status.html("Deactive");
			status.addClass("off");
			status.removeClass("on");
		}

		$.ajax({
			url: "update_user_status",
			type: "POST",
			data: { user_id : id, value : c, 'csrf_token_aware' : cct},
			dataType: "json",
			success: function() {
				// Success
			},
			error: function(error) {
				console.debug(error);
				button.prop('checked', !c);
			} 
		});
	});

});