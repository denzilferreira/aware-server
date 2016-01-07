// Get CSRF cookie
var cct = $.cookie('csrf_cookie_aware');


// Variables for device list
var device_search = null;
var order_by_column = 'device-id';
var order_by_type = 'ASC';
var offset = 0;
var limit = 50;

var selected_devices = [];
var visible_devices = [];
var total_devices;
var timeoutReference;
var currentConfig = [];

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

function getButtonsDelStudy(link, data_array) {
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


function getButtonsAddCo(link, study_id) {
	var dialog_buttons = {};
	dialog_buttons['Ok'] = {	text  : 'Ok', 
								click : function() {
											var email = $('#email').val();
											$.ajax({
												url: link,
												type: "POST",
												data: { email : email, study_id : study_id, 'csrf_token_aware' : cct},
												dataType: "json",
												success: function(data){
													if(data==0){
														$('.dialog-error').toggle();
													}else{
														$('.dialog-error').hide();
														window.location.reload(true);
													}
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

function setMQTT(){
	$(".mqtt-id").each(function() {
		var id = $(this).closest('td').prev('td').text();
		for(var r=0,i=0;i<id.length;i++)r=(r<<5)-r+id.charCodeAt(i),r&=r;
		$(this).text(r);
	});
}

function getMQTTHistory(study_id) {
	// Let's clear old data
	$("#mqtt-history > tbody > tr").remove();
	$.ajax({
		url: "../get_mqtt_history",
		type: "POST",
		data: { study_id : study_id, 'csrf_token_aware' : cct},
		dataType: "json",
		success: function(data) {
			if ($.isEmptyObject(data)) {
				$("#mqtt-history-wrapper").parent().append("No MQTT messages sent");
				$("#mqtt-history-wrapper").hide();
				return;
			}
			// Save device ID's to array
			for (var key in data) {
				// Date object from unix timestamp
				var date = new Date(data[key]["timestamp"] * 1000);
				// Convert to UTC
				date = new Date(date.setHours(date.getHours() + (date.getTimezoneOffset() / 60)))
				// Format
				date_long = $.formatDateTime('d MM yy hh:ii', date);
				date = $.formatDateTime('d MM yy', date);
				
				var topic = data[key]["topic"];
				
				if (data[key]["topic"] == "esm") {
					var msg = data[key]["message"];
					var obj = jQuery.parseJSON(msg);
					
					for (var i=0; i < obj.length; i++) {
						var item = obj[i]["esm"];
						var title = obj[i]["esm"]["esm_title"];
						$("#mqtt-history tbody").append("<tr class='mqtt-history-message'><td class='mqtt-history-date'>" + date + "</td><td class='mqtt-history-topic'>" + topic + "</td><td class='mqtt-history-title' title='" + date_long + " " + title + "'>" + title +"</td><td class='mqtt-history-data' style='display: none;'>" + "[" + JSON.stringify(obj[i]) + "]" + "</td><td class='mqtt-history-devices' style='display: none;'>" + data[key]["receivers"] + "</td></tr>");
					}
				} else {
					$("#mqtt-history tbody").append("<tr class='mqtt-history-message'><td class='mqtt-history-date'>" + date + "</td><td class='mqtt-history-topic'>" + topic + "</td><td class='mqtt-history-title'>" + data[key]["message"] +"</td><td class='mqtt-history-data' style='display: none;'>" + data[key]["message"] + "</td><td class='mqtt-history-devices' style='display: none;'>" + data[key]["receivers"] + "</td></tr>");
				}
				
				// Update table to allow sorting
				$("#mqtt-history").trigger("update");
				
			}
		}
	});
}

function updateDeviceLabels(){
	var data_array = { study_id : $('.study_id').attr('id'), device_id_list : visible_devices, 'csrf_token_aware' : cct};
	$.ajax({
			url: '../get_device_data',
			type: "POST",
			data: data_array,
			dataType: "json",
			success: function(data){
				$('#study-devices tr.device-row').each(function (i, row) {
					var result = $.grep(data, function(e){ return e.device_id == $(row).find('td.device-id').text(); });
					if (result.length == 1) {
						$(row).find('td.device-label input').val(result[0].label);
					}
				}); 
			},
			error: function(data){
				//console.log(data);
			}
		});
}

function getDevices(){
	var html_devices = [];
	var html_viz = [];
	visible_devices = [];

	// Show loader while we process ajax query
	$("#devices-loader.ajax-loader").show();
	disableDeviceList();
	$.ajax({
		url: '../get_study_devices',
		type: "POST",
		data: { study_id : $('.study_id').attr('id'), device_search : device_search, order_by_column : order_by_column, order_by_type : order_by_type, offset : offset, limit : limit, 'csrf_token_aware' : cct},
		dataType: "json",
		success: function(data){
			total_devices = data[data.length - 1]['total'];

			html_viz.push('<div id="visualization-loader" class="ajax-loader"></div>');
			for (var i = 0; i < Object.keys(data).length - 1; i++) {
				html_devices.push('<tr id="'+ data[i]['device_id'] +'" class="device-row">'+
				'<td class="checkbox-cell">'+
				'<input type="checkbox" class="select-device basic" name="devices[]" value="1" id="cb_' + data[i]['device_id'] + '"><label for="cb_' + data[i]['device_id'] + '" style="margin-top: -9px !important;"></label></td>'+
				'<td class="device-id">' + data[i]['device_id'] + '</td>'+
				'<td class="device-label"><input type="text" class="label_box" readonly value="' + (data[i]['label'] == null ? '' : data[i]['label']) + '">'+
				'<div class="label-buttons">'+
				'<a href="#" class="edit-description" title="Edit device label"><img src="'+document.base_url+'/application/views/images/edit.png" alt="Edit" height="11" width="11" class="edit_image"></a>'+
				'<a href="#" class="cancel-button save_cancel" title="Cancel changes"><img src="'+document.base_url+'/application/views/images/delete_icon.png" alt="Cancel" height="11" width="11" class="del_image"></a>'+
				'<a href="#" class="save-button save_cancel" title="Save changes"><img src="'+document.base_url+'/application/views/images/ok.png" alt="Save" height="13" width="13" class="ok_image"></a>' +
				'</div>'+
				'<div class="remove-device"><a href="#" class="cancel-button" title="Remove device"><img src="'+document.base_url+'/application/views/images/delete_icon.png" alt="Cancel" height="11" width="11" class="del_image"></a></div>'+
				'</td>'+
				'</tr>');

				// Keep list of all devices visible on page
				visible_devices.push(data[i]['device_id']);
				
				// If device doesn't have label, use device id instead
				html_viz.push('<div class="visualization-device" title="' + (data[i]['label'] == '' ? data[i]['device_id'] : data[i]['label']) + '" id="viz_' + data[i]['device_id'] + '"></div>');
			}	
			// Replace old stuff with new
			$('#study-devices tbody').html(html_devices);
			$('#visualization-devices').html(html_viz);
			bindLabelButtons();
			// Delete old stuff
			$('.table-navigation').empty();
			// Display info of selected items and total count
			$('.table-navigation').append('<div class="table-info">Displaying ' + (data[data.length - 1]['total'] == 0 ? '0' : ((offset+1) + '-' +  (offset + Object.keys(data).length-1))) + ' of total ' + data[data.length - 1]['total'] + ' devices. Total of <span class="devices-count">' + selected_devices.length + '</span> devices selected.</div>');
			
			
			// Display previous button if we can go back
			if (offset > 1) {
				$('.table-navigation').append("<a href='#' class='devices-previous'>&laquo; Previous</a>");
			}
			// Display next button if there's more to display
			if ((offset + Object.keys(data).length-1) < data[data.length - 1]['total']) {
				$('.table-navigation').append("<a href='#' class='devices-next'>Next &raquo;</a>");
			}
			
			// Bind checkboxes
			$('input.select-device').bind("click", function() {
				var dev_id = $(this).attr('id').split("cb_")[1];
				// Add device to list
				if ($(this).prop('checked')) {
					selected_devices.push(dev_id);
				// Remove device from list
				} else {
					selected_devices.splice( $.inArray(dev_id, selected_devices), 1 );
				}
				// Update selected devices count
				$('.devices-count').html(selected_devices.length);

				// Loop through all visible devices
				for (var i=0; i < visible_devices.length; i++) {
					// If any of the devices visible on the screen isn't on selected devices list
					// Break and deselect 'select all button', because, well we haven't selected all devices
					if (selected_devices.indexOf(visible_devices[i]) == -1) {
						$('#select-all:checkbox').prop('checked', false);
						$('#devices-values .success').hide();
						break;
					}
					// We are at the last visible device, all of them seem to be on selected device list
					// So select 'select all button'
					if (i == visible_devices.length - 1) {
						$('#select-all:checkbox').prop('checked', 'checked');
						
						// Form select all messsage
						var msg = "<div class='success'>" + visible_devices.length + " devices on this view selected.";
						if ((total_devices-selected_devices.length) > 0) {
							msg = msg + "<a href='#' class='select-rest'>Select rest " + (total_devices-selected_devices.length) + " devices</a>";
						} else {
							msg = msg + "</div>";
						}
						$('#devices-values').prepend($(msg).hide().fadeIn('medium'));
									
					}
				}
				
				// If we have devices selected, let user send message
				if (selected_devices.length > 0) {
					$('.sendbutton').prop("disabled", false);
				} else {
					$('.sendbutton').prop("disabled", true);
				}
			});
			
			// Check if we have already selected that device
			for (var i = 0; i < visible_devices.length; i++) {
				if ($.inArray(visible_devices[i], selected_devices) > -1) {
					$('#cb_' + visible_devices[i]).prop('checked', 'checked');
				}
			}
			
			//Deselect 'select all button' if not all visible devices selected
			if ($('.select-device:checked').length != visible_devices.length) {
				$('#select-all:checkbox').prop('checked', false);
			}
			
			// Bind click function to visualization boxes
			$('.visualization-device').bind("click", function() {
				id = $(this).attr('id').split('viz_')[1];
				
				var c = $('#cb_' + id).prop('checked');
				c = c == false ? true : false;
				$('#cb_' + id).prop('checked',c);
			});

			// Remove device
			$('.remove-device a').click(function(event) {
				event.preventDefault();

				var device_id = $(this).parent().parent().parent().attr("id");
				console.debug("Device ID:", device_id);
				$("#remove-device-dialog").data("device_id", device_id).dialog("open");
			});

			// Hide ajax loader, we got data
			$("#devices-loader.ajax-loader").hide();
			
			// Let's get visualization if we got results
			if (total_devices > 0) {
				$("#select-all").prop("disabled", false);
				var table = $("#study-data tr.plugin-data td.plugin-data-type").eq(0).text();
				$('.ui-datepicker-current-day').click();
			} else {
				enableDeviceList();
				$("#select-all").prop("disabled", true);
			}
			
		},
		error: function(data){
			console.log(data);
		}
	});
}

function bindLabelButtons() {
		//Device label buttons
	$('.label-buttons .save_cancel').hide();
	$(function(){
		$('.label-buttons .edit-description').click(function(e) {
			var check_box = $(this).parents().eq(2).find('.select-device');
			e.preventDefault();
			if(!check_box.is(':checked')){
				check_box.click();
			}
			$('.select-device').each(function (index, value) {
				$(this).attr("disabled", true);
			});
			$('#select-all').attr("disabled", true);
			$('.label-buttons').children('.save_cancel').hide();
			$('.device-label input').attr('readonly', true);
			$(this).parents().eq(1).children('input').attr('readonly', false).focus();
			var button_line = $(this).parents().eq(1).children('.label-buttons');
			$('.edit-description').fadeOut(500, function(){
				button_line.children('.save_cancel').fadeIn(500);
			});
		});
	});	
	
	$('.label-buttons .cancel-button').click(function(e){
		e.preventDefault();
		$(this).parents().eq(1).children('input').attr('readonly', true);
		$(this).parents().eq(1).children('.label-buttons').children('.save_cancel').fadeOut(500, function(){
			$('.edit-description').fadeIn(500);
		});
		$('.select-device').each(function (index, value) {
				$(this).attr('disabled', false);
		});
		$('#select-all').attr("disabled", false);
		updateDeviceLabels();
	});
	
	$('.label-buttons .save-button').click(function(e){
		e.preventDefault();
		$(this).parents().eq(1).children('input').attr('readonly', true);
		var label = $(this).parents().eq(1).children('input').val();
		var label_parent = $(this).parents().eq(1).children('input');
		var study_id = $('.study_id').attr('id');
		var link = '../edit_label';
		var data_array = { study_id : study_id, device_id_list : selected_devices, label : label, 'csrf_token_aware' : cct};
		$.ajax({
			url: link,
			type: "POST",
			data: data_array,
			dataType: "json",
			success: function(data){
				updateDeviceLabels();
			},
			error: function(data){
				//console.log(data);
			}
		});
		
		$(this).parents().eq(1).children('.label-buttons').children('.save_cancel').fadeOut(500, function(){
			$('.edit-description').fadeIn(500);
		});
		$('.select-device').each(function (index, value) {
				$(this).attr('disabled', false);
		});
		$('#select-all').attr("disabled", false);
	});

	$('td.device-label input').bind("change paste keyup", function() {
		var label = $(this).val();
		$('#study-devices tr.device-row').each(function (i, row) {
			if ($(row).find('td.checkbox-cell input').is(':checked')) {
				if ($(row).find('td.device-label input').val() != label) {
					$(row).find('td.device-label input').val(label);
				}
			}
		});
	});
}

function disableDeviceList() {
	$('#devices-values').addClass('disable_elements');
	$('#devices-values').fadeTo(100,.6);
	$('#devices-values').children().prop('disabled',true);
}

function enableDeviceList() {
	$('#devices-values').fadeTo('medium',1.0);
	$('#devices-values').removeClass('disable_elements');
	$('#devices-values').children().prop('disabled',false);
}

function populateMQTTMessage(mqtt_type, data) {
	// Type: ESM
	if (mqtt_type == "esm") {
		// Change to ESM tab
		$("a#tab-esm").click();
		
		data = jQuery.parseJSON(data)[0]["esm"];
		var type = data["esm_type"];
		
		// Change to correct ESM type
		$('#esm-type').select2('val', type);
		$('.esm-message').hide();
		$('.'+type).fadeIn('medium');
		
		// Populate selections based on ESM type
		switch (type) {
			case "1": // Freetext
				$('.esm-message.1 [name="esm-title"]').val(data['esm_title']);
				$('.esm-message.1 [name="esm-instructions"]').val(data['esm_instructions']);
				$('.esm-message.1 [name="esm-threshold"]').select2("val", data["esm_expiration_threshold"]);
			break;	
			case "2": // Radio
				$('.esm-message.2 [name="esm-title"]').val(data['esm_title']);
				$('.esm-message.2 [name="esm-instructions"]').val(data['esm_instructions']);
				$('.esm-message.2 [name="esm-options"]').select2("val", data["esm_radios"]);
				$('.esm-message.2 [name="esm-threshold"]').select2("val", data["esm_expiration_threshold"]);
			break;
			case "3": // Checkbox
				$('.esm-message.3 [name="esm-title"]').val(data['esm_title']);
				$('.esm-message.3 [name="esm-instructions"]').val(data['esm_instructions']);
				$('.esm-message.3 [name="esm-options"]').select2("val", data["esm_checkboxes"]);
				$('.esm-message.3 [name="esm-threshold"]').select2("val", data["esm_expiration_threshold"]);
			break;
			case "4": // Likert
				$('.esm-message.4 [name="esm-title"]').val(data['esm_title']);
				$('.esm-message.4 [name="esm-instructions"]').val(data['esm_instructions']);
				$('.esm-message.4 [name="esm-likertmax"]').select2("val", data['esm_likert_max']);
				$('.esm-message.4 [name="esm-likertmax-label"]').val(data['esm_likert_max_label']);
				$('.esm-message.4 [name="esm-likertmin-label"]').val(data['esm_likert_min_label']);
                $('.esm-message.4 [name="esm-likert-step"]').val(data['esm_likert_step']);
				$('.esm-message.4 [name="esm-threshold"]').select2("val", data["esm_expiration_threshold"]);
			break;
			case "5": // Quick answer
				$('.esm-message.5 [name="esm-title"]').val(data['esm_title']);
				$('.esm-message.5 [name="esm-instructions"]').val(data['esm_instructions']);
				$('.esm-message.5 [name="esm-options"]').select2("val", data["esm_quick_answers"]);
				$('.esm-message.5 [name="esm-threshold"]').select2("val", data["esm_expiration_threshold"]);
			break;
			case "6": // Scales
				$('.esm-message.6 [name="esm-title"]').val(data['esm_title']);
				$('.esm-message.6 [name="esm-instructions"]').val(data['esm_instructions']);
				$('.esm-message.6 [name="esm-scale-min"]').val(data["esm_scale_min"]);
				$('.esm-message.6 [name="esm-scale-max"]').val(data["esm_scale_max"]);
				$('.esm-message.6 [name="esm-scale-max-label"]').val(data["esm_scale_max_label"]);
				$('.esm-message.6 [name="esm-scale-min-label"]').val(data["esm_scale_min_label"]);
				$('.esm-message.6 [name="esm-scale-step"]').val(data["esm_scale_step"]);
				$('.esm-message.6 [name="esm-threshold"]').select2("val", data["esm_expiration_threshold"]);
			break;
		}
	} else if (mqtt_type == "broadcasts") {
		// Change to broadcasts tab
		$("a#tab-broadcasts").click();
		
		// Update broadcasts type
		$('div#broadcasts [name="broadcasts-type"]').select2("val", data);
	} else if (mqtt_type == "configuration") {
		// Change to configuration tab
		$("a#tab-configuration").click();

		// Update config
		var obj = $.parseJSON(data);

		var configs = [];
		$(obj).each(function(index, object) {
			configs.push(object["setting"] + "=" + object["value"]);
		});
		
		// Filter empty and null values
		configs = configs.filter(function(e){return e});
		$('#configuration [name="configuration"]').select2("val", configs);
	} else {
		// Change to custom message tab
		$("a#tab-custom").click();
		
		// Update topic and description
		$('.tab-content.value [name="custom-topic"]').val(mqtt_type);
		$('.tab-content.value [name="custom-description"]').val(data);
	} 
}

function sendMQTTMessage(type, msg, study_id) {
	$.ajax({
			url: "../../webservice/publish",
			type: "POST",
			data: { study_id : study_id, devices_list : selected_devices, mqtt_type : type, msg : msg, 'csrf_token_aware' : cct},
			dataType: "json",
			success: function(data){
				if (!data.errors) {
					var mqtt_type = $("div.tabs ul li.selected a").attr("id").split("-")[1];
					$("#" + mqtt_type + " div.buttons").before("<div class='success-msg'>MQTT message(s) pushed to all selected devices</div>");
					$('.success-msg').delay(5000).fadeOut("medium", function() { this.remove() });
					
					// Let's clear ESM queue
					$("#esm-queue > tbody > tr").remove();
					
					// Update MQTT history
					getMQTTHistory(study_id);
				}
			},
			error: function(data){
				//console.log(data);
			}
		});

}
function getBase64FromImageUrl(URL) {
    var img = new Image();
    img.src = URL;
    img.onload = function () {


    var canvas = document.createElement("canvas");
    canvas.width =this.width;
    canvas.height =this.height;

    var ctx = canvas.getContext("2d");
    ctx.drawImage(this, 0, 0);


    var dataURL = canvas.toDataURL("image/png");

    alert(  dataURL.replace(/^data:image\/(png|jpg);base64,/, ""));

    }
}

function getStudyConfiguration() {
	// Remove all enabled classes
	$("ul#sensors-settings").find("*").removeClass("enabled");
	// Remove all active classes
	$("ul#sensors-settings").find("*").removeClass("active");
	// Disable study sensor input fields
	$("ul#sensors-settings input").prop("disabled", true);
	// Uncheck all checkboxes
	$("ul#sensors-settings input[type=checkbox]").prop("checked", false);
	// Clear all input fields
	$("ul#sensors-settings input[type=text]").val("");
	// Disable expanding sensors
	$("ul#sensors-settings").addClass("disabled");

	// Hide parent sensors
	$("ul#sensors-settings li.sensor").hide();
	// Hide sensor settings
	$("ul.sensor-settings").hide();
	
	// Change sensor arrow
	$("div.sensor-arrow").removeClass("down").addClass("up");	
	// Disable sensor arrow
	$(".sensor-arrow").addClass("disabled");


	// Disable sensor settings
	$(".sensor-value").removeClass("enabled");

	$.ajax({
		dataType: "json",
		data: {'csrf_token_aware' : cct},
		url: "../get_study_configuration/" + $('.study_id').attr('id'),
		type: "POST",
		success: function(preloadedData) {
			if (preloadedData.length > 0) {
				
				var obj = $.parseJSON(preloadedData[0]["text"]);

				var set = [];
				// Get sensor setting
				if (typeof obj["sensors"] !== "undefined") {
					set = set.concat(obj["sensors"]);
				}
				// Get plugin settings
				for (p in obj["plugins"]) {
					for (s in obj["plugins"][p]["settings"]) {
						set.push(obj["plugins"][p]["settings"][s]);
					}
				}

				var confs = [];
				// Parse sensor and their settings
				if (typeof set[0] !== "undefined") {
					$(set).each(function(index, object) {
						var setting = object["setting"];
						var value = object["value"];

						if (typeof value === "object") {
							value = JSON.stringify(value);
						}

						if (value == "true") {
							$("#" + setting).prop("checked", true);
						} else {
							$("#" + setting).val(value);
						}

						$("#" + setting).addClass("enabled");
						// Show sensor parent
						$("#" + setting).parent().parent().parent().show();
						// Activate sensor parent
						$("#" + setting).parent().parent().parent().addClass("active");
						// Add hilight
						$("#" + setting).parent().parent().parent().addClass("enabled");
						// Show sensor settings
						$("#" + setting).parent().parent().show();
						$("#" + setting).parent().show();
						// Change arrow
						$("#" + setting).parent().parent().parent().children(".sensor-arrow").removeClass("up").addClass("down");
					});	
				} else {
					$("#sensors-settings-wrapper").prepend('<div class="no-data">There\'s no enabled sensors or plugins for this study.</div>');
					//
				}
			} else {
				$("#sensors-settings-wrapper").prepend("<div class='no-data sensors'>This study doesn't use any sensors.</div>");
			}
		}
	});
}

function generateStudyConfiguration() {
	var config = [];
	var plugins = [];
	var configuration = {};

	// Loop each sensor / plugin
	$("li.sensor").each(function(){
 		var sensor = $(this);
		var settings = sensor.find("input.value");
		var plugin = {};

		// Loop each setting under sensor / plugin
		for (var i=0; i < Object(settings).length; i++) {
			var setting = $(settings[i]);

			if (setting.prop("checked") && setting.hasClass("boolean")) {
			// Enable / disable value for setting

				// Plugin
				if (sensor.children("p.label").hasClass("plugin")) {
					var plugin_package = sensor.children("input.package-name").val();
					plugin["plugin"] = plugin_package;
					plugin["settings"] = [{"setting": setting.attr("id"), "value": "true" }];
				} else {
				// Sensor
					// If first sensor, init array
					if (configuration.sensors == undefined) {
						configuration["sensors"] = [];
					}
					//config.push({"setting": setting.attr("id"), "value": "true" });
					configuration["sensors"].push({"setting": setting.attr("id"), "value": "true" });
				}
			} else if (setting.hasClass("enabled") && (setting.hasClass("integer") || setting.hasClass("text") || setting.hasClass("real"))) {
				// Plugin
				if (sensor.children("p.label").hasClass("plugin")) {
					if (typeof plugin["settings"] !== "undefined") {
						plugin["settings"].push({"setting": setting.attr("id"), "value": setting.val() });
					}
				} else {
				// Sensor
					if (typeof configuration["sensors"] !== "undefined") {
						configuration["sensors"].push({"setting": setting.attr("id"), "value": setting.val()});
					}
				}

			}
		}

		// If we have plugin
		if (plugin.plugin != undefined) {
			// If first plugin, init array
			if (configuration.plugins == undefined) {
				configuration["plugins"] = [];
			}
			configuration["plugins"].push(plugin);
		}


	});

	return configuration;
	//return config;
}

function changeStudyStatus(study_id, val) {

	$.ajax({
		url: "../../webservice/update_study_status",
		type: "POST",
		data: { study_id : study_id, value : val, 'csrf_token_aware' : cct},
		dataType: "json",
		success: function(data) {
			$("input#close-confirm").prop("checked", Boolean(val));

			//$("div.switch-container > .toggle-status").toggle();
			$("span.toggle-status").toggleClass("active");
			//$("span.toggle-status open").toggleClass("active");
			var text = ((data == 1) ? "Active" : "Deactive");
			//$("div.switch-container > .toggle-status").html(text);
		}
	});	
}

function getConfigDifference(currentConfig, config) {
	var differenceConfig = [];
	for (i=0; i < currentConfig.length; i++) {
		for (j=0; j < config.length; j++) {
			if (config[j]["setting"] == currentConfig[i]["setting"]) {
				break;
			// User has disabled plugin / setting
			} else if (j == config.length-1) {
				if ("setting" in currentConfig[i]) {
					if (currentConfig[i]["setting"].indexOf("status_") > -1) {
						differenceConfig.push({"setting": currentConfig[i]["setting"], "value": "false" });
					}
				}
			}
		}
	}
	return differenceConfig;
}

$(document).ready(function(){
	
	var study_id = $('.study_id').attr('id');
	var table = $("#study-data tr.plugin-data td.plugin-data-type").eq(0).text();

	$("form").submit(function(e) {
		var config = generateStudyConfiguration();
		$("form").append("<input type='hidden' id='config' name='config' value='" + JSON.stringify(config) + "'>");
	});

	$('#status_plugin_esm_questionnaire').parent().parent().append("<li class='sensor-setting'><a href='#' id='manage_esmq' class='disabled'>Manage questionnaires</a></li>").fadeIn("fast");
	$("#manage_esmq").click(function(event) {
		event.preventDefault();
		$.when(initESMQ()).done(function() {
			$("#esmq-questionnaires").dialog("open");
		});
		
	});

	$("#status_plugin_esm_questionnaire").change(function(e) {
		$("#manage_esmq").toggleClass("disabled");
	});

	// Fetch MQTT history on load
	if ($('#mqtt-history-wrapper').length) {
		getMQTTHistory(study_id);
	}

	// Get devices & visualization
	if ($('table#study-devices').length > 0) {
		getDevices();
		enableDeviceList();
	}

	// If previous / next clicked under device list
	$('.table-navigation').on('click', 'a', function(e) {	
		e.preventDefault(); 
		// Hide possible messages
		$('#devices-values .success').hide();
		$('#devices-values .warning').hide();
		
		option = $(e.currentTarget).attr('class');
		
		if (option == 'devices-next') {
			offset = offset + 50;
		} else if (option == 'devices-previous') {
			offset = offset - 50;
		}
		// Get devices
		getDevices();
	});
	
	// Sorting device list by device id
	$('.device-id').click(function(e) {
		e.preventDefault();
		// Toggle between sort by ASC / DESC
		order_by_type = order_by_type == 'ASC' ? 'DESC' : 'ASC';
		order_by_column = 'device-id';
		var el = $(this);
		getDevices();
		$('#study-devices .sort_arrow').remove();
		$('<img src="'+document.base_url+'/application/views/images/'+order_by_type+'.png" height="10" width="10" style="margin-right:5px;" class="sort_arrow">').hide().prependTo(el).fadeIn(500);
	});
	
	// Sorting device list by device label
	$('.device-label').click(function(e) {
		e.preventDefault();
		// Toggle between sort by ASC / DESC
		order_by_type = order_by_type == 'ASC' ? 'DESC' : 'ASC';
		order_by_column = 'device-label';
		var el = $(this);
		getDevices();
		$('#study-devices .sort_arrow').remove();
		$('<img src="'+document.base_url+'/application/views/images/'+order_by_type+'.png" height="10" width="10" style="margin-right:5px;" class="sort_arrow">').hide().prependTo(el).fadeIn(500);
	});
	
	var last_search;
	// Sort device list by search item
	$("#devices-search").on("change paste keyup focusout", function(e) {
		search_item = $('input#devices-search').val();
		
		if (timeoutReference) clearTimeout(timeoutReference);
		timeoutReference = setTimeout(function() {
			device_search = search_item;
			
			if (e.type == 'keyup' || (e.type == 'focusout' && search_item != last_search)) {
				offset = 0;
				last_search = search_item;
				getDevices();
			}
        }, 1000);
	});

	// Get study configuration
	if ($("div#sensors-settings-wrapper > ul#sensors-settings").length) {
		getStudyConfiguration();
	}
	
	
	// Populate MQTT message data when clicking on MQTT history element
	$("#mqtt-history").on("click", ".mqtt-history-message", function(e){ 
		var mqtt_type = $(this).children(".mqtt-history-topic").text();
		
		// Change font style
		$("tr.mqtt-history-message").css("font-weight", "normal");
		$(this).css("font-weight", "bold");
		
		// Clear previous data
		$('.tab-content.value input').val('');
		$('.esm-message [name="esm-threshold"]').select2("val", 0);
		$('.esm-message input').select2('data', null);
		
		var data = $(this).children(".mqtt-history-data").text();
		populateMQTTMessage(mqtt_type, data);
	});
	

	// Change visualization data on click
	$(".plugin-data").click(function(e) {
		$(".plugin-data").css("font-weight", "normal");
		
		$(this).css("font-weight", "bold");
		table = $(this).children(".plugin-data-type").text();
		$('.ui-datepicker-current-day').click();
	});
	
	// Visualization calendar
	$('#datepicker').datepicker({
		inline : true,
		dateFormat: 'yy/mm/dd',
		altField : '#selectedDate',
		minDate: new Date($("#creation-date").text() * 1000),
		maxDate: 0,
		onSelect : function(dateText, inst){
			var this_date=$(this).val();
			var start = new Date(this_date).setUTCHours(00,00,00,000);
			var end = new Date(this_date).setUTCHours(23,59,59,999);
			start = start + 86400*1000;
			end = end + 86400*1000;
			var devices = new Array()
			$('.visualization-device').css('background-color', '#f46d43');
			
			// Show loader, hide devices visualization
			$(".visualization-device").hide();
			$("#visualization-loader.ajax-loader").show();

			// Delete day visualization
			$("table.visualization.day").remove();

			if (table == "esms") {
				//start = $("#creation-date").text();
				var date = new Date();
				var start = new Date(date.getFullYear(), new Date(this_date).getMonth(), 1).setUTCHours(00,00,00,000) + 86400*1000;
				var end = new Date(date.getFullYear(), new Date(this_date).getMonth() + 1, 0).setUTCHours(23,59,59,999) + 86400*1000;
				$.ajax({
					url: "../get_visualization_data_esms",
					type: "POST",
					data: { study_id : study_id, devices_list : visible_devices, start : start, end : end, table : table, 'csrf_token_aware' : cct},
					dataType: "json",
					success: function(data) {

						$("div#visualization-devices").before(data);

						$('.visualization.day.device').bind("click", function() {
							id = $(this).attr('title');
							
							var c = $('#cb_' + id).prop('checked');
							c = c == false ? true : false;
							$('#cb_' + id).prop('checked',c);
						});

						// Hide loader, show devices visualization
						$("#visualization-loader.ajax-loader").hide();
						enableDeviceList();
					},
					error: function(data) {
						enableDeviceList();
					}
				});
			} else {
				$.ajax({
					url: "../get_visualization_data",
					type: "POST",
					data: { study_id : study_id, devices_list : visible_devices, start : start, end : end, table : table, 'csrf_token_aware' : cct},
					dataType: "json",
					success: function(data) {
						for (i=0; i<data.length; i++) {
							$("#viz_" + data[i].device_id).css('background-color', '#a6d96a');
						}

						// Hide loader, show devices visualization
						$(".visualization-device").show();
						$("#visualization-loader.ajax-loader").hide();
						enableDeviceList();
					},
					error: function(data) {
						enableDeviceList();
					}
				});
			}

		}
	});
	
	// "Initialize" datepicker with current day and
	var table = $("#study-data tr.plugin-data td.plugin-data-type").eq(0).text();

	$('.sendbutton').prop("disabled",true);

	// Select all devices checkbox controlling
	$('#select-all').click(function() {
		// Hide previous messages
		$('#devices-values .success').hide();
		$('#devices-values .warning').hide();
		var c = this.checked;
		
		$(':checkbox').prop('checked', c);
		if (c) {
			// selected_devices = visible_devices;
			for (var i=0; i < visible_devices.length; i++) {
				if (selected_devices.indexOf(visible_devices[i]) == -1) {
					selected_devices.push(visible_devices[i]);
				}
			}
			
			// Form select all messsage
			var msg = "<div class='success'>" + visible_devices.length + " devices on this view selected.";
			if ((total_devices-selected_devices.length) > 0) {
				msg = msg + "<a href='#' class='select-rest'>Select rest " + (total_devices-selected_devices.length) + " devices</a>";
			} else {
				msg = msg + "</div>";
			}
			$('#devices-values').prepend($(msg).hide().fadeIn('medium'));
			
			$('.select-rest').bind("click", function(e) {
				e.preventDefault();
				
				
				// We are about the select all devices matching active filters, lets clear all selections first
				selected_devices = [];
				
				// Get data and push it to array
				var total;
				$("#devices-loader.ajax-loader").show();
				disableDeviceList();
				$.ajax({
					url: '../get_study_devices_all',
					type: "POST",
					data: { study_id : $('.study_id').attr('id'), device_search : device_search, 'csrf_token_aware' : cct},
					dataType: "json",
					success: function(data) {
						total = data['total'];

						for (var i = 0; i < Object.keys(data).length - 1; i++) {
							selected_devices.push(data[i]['device_id']);
						}
						
						// Change success message
						$('#devices-values .success').hide();
						var msg = "<div class='success'>All " + total + " devices matching active filters selected!</div>";
						$('#devices-values').prepend($(msg).hide().fadeIn('medium'));
						$("#devices-loader.ajax-loader").hide();
						enableDeviceList();
					},
					error: function(data) {
						enableDeviceList();
					}
					
				});
			});
			// Allow message sending
			$('.sendbutton').prop("disabled", false);
		// Where are deselecting all devices
		} else {
			// Remove all visible devices from selected devices list
			for (var i=0; i < visible_devices.length; i++) {
				selected_devices.splice( $.inArray(visible_devices[i], selected_devices), 1 );

			}
			
			// Form deselect all messsage
			var msg = "<div class='warning'>" + visible_devices.length + " devices on this view deselected.";
			if (selected_devices.length > 0) {
				msg = msg + "<a href='#' class='deselect-rest'>Deselect rest " + (selected_devices.length) + " devices</a>";
			} else {
				msg = msg + "</div>";
			}
			$('#devices-values').prepend($(msg).hide().fadeIn('medium'));
			
			$('.deselect-rest').bind("click", function(e) {
				e.preventDefault();
				
				// Change success message
				$('#devices-values .warning').hide();
				var msg = "<div class='warning'>All " + selected_devices.length + " devices matching active filters deselected!</div>";
				$('#devices-values').prepend($(msg).hide().fadeIn('medium'));
				
				// Let's clear all selected devices
				selected_devices = [];
				
				$("#devices-loader.ajax-loader").hide();
				
			});
			// Disable message sending
			$('.sendbutton').prop("disabled", true);
		}
		// Update device count
		$('.devices-count').html(selected_devices.length);
	});
	
	$('.sendbutton').click(function(e){
		e.preventDefault();
		
		var mqtt_type = $("div.tabs ul li.selected a").attr("id").split("-")[1];
		var form;
		if (mqtt_type == "esm") {
			form = $("#esm-" + $("#esm-type").val());
		} else {
			form = $("form#" + mqtt_type);
		}
		if (mqtt_type == "custom") {
			mqtt_type = $("input[name=custom-topic]").val();
		}

		// Remove old errors
		$('.error-msg').remove();
		$('.mqtt-error').remove();
		
		// Check if we have messages in ESM queue
		if ($("#esm-queue > tbody > tr").length > 0) {
			var messages = [];
			var json;
			
			$("#esm-queue tbody td.esm-queue-message-data").each(function(){
				messages.push($(this).text().substring(1, $(this).text().length - 1));
			});
			
			json = "[" + messages.join(",") + "]";
			sendMQTTMessage("esm", json, study_id);
			
		// We are sending one message only
		} else {
			var form_data = form.serialize()
			$.ajax({
				url: "../../webservice/construct_mqtt_message",
				type: "POST",
				data: {form_data : form_data, 'csrf_token_aware' : cct},
				dataType: "json",
				success: function(data) {
					// We got some empty fields, display error
					if (data.error) {
						$.each( data.errors, function( key, value ) {
							$("#" + form.attr("id") + " input[name='" + key + "']").after("<span class='error-msg'>*</span>");
						});
						$("#" + mqtt_type + " div.buttons").before("<div class='mqtt-error'>Please fill all the fields</div>");
					} else {
						// We are good to go
						sendMQTTMessage(mqtt_type, data, study_id);
					}
				},
				error: function(data) {
					//console.debug(data);
				}
			});
		}
	});
		
	$('#add-to-queue').click(function(e){
		e.preventDefault();
		var form = $("#esm-" + $("#esm-type").val());

		// Remove old errors
		$('.error-msg').remove();
		$('.mqtt-error').remove();
		
		var form_data = form.serialize()
		$.ajax({
			url: "../../webservice/construct_mqtt_message",
			type: "POST",
			data: {form_data : form_data, 'csrf_token_aware' : cct},
			dataType: "json",
			success: function(data) {
				// User did not fill all fields, display error
				if (data.error) {
					$.each( data.errors, function( key, value ) {
						$("#" + form.attr("id") + " input[name='" + key + "']").after("<span class='error-msg'>*</span>");
					});
					$("#esm div.buttons").before("<div class='mqtt-error'>Please fill all the fields</div>");
				// Everything ok, lets add message to queue
				} else {
					var obj = jQuery.parseJSON(data)[0];
					
					var type = obj["esm"]["esm_type"];
					var title = obj["esm"]["esm_title"];
					var esm_type = {1: "Free text", 2: "Radio", 3: "Checkbox", 4: "Likert", 5: "Quick answer", 6: "Scale"};
					$("table#esm-queue tbody").append("<tr class='esm-queue-message'><td class='esm-queue-message-type'>" + esm_type[type] + "</td><td class='esm-queue-message-title' title='" + title + "'>" + title + "</td><td class='esm-queue-message-data'>" + data + "</td><td class='esm-queue-message-remove'><img src='"+document.base_url+"/application/views/images/delete_icon.png' alt='Remove' height='11' width='11' class='del_image'></td></tr>");
					
					$('.esm-queue-message-remove').bind("click", function() {
						$(this).parent().remove();
					});
					
					// Let's clear fields
					$('.tab-content.value input').val('');
					$('.esm-message [name="esm-threshold"]').select2("val", 0);
					$('.esm-message input').select2('data', null);
				}
			}
		});
			
	});
	
	// Populate MQTT message when clicken on ESM queue item
	$("#esm-queue").on("click", ".esm-queue-message> td:nth-child(1), .esm-queue-message> td:nth-child(2)", function(e){ 
		var data = $(this).parent().children(".esm-queue-message-data").text();
		populateMQTTMessage("esm", data);
	});
	
	// Check if we got elements in ESM queue, display message if queue empty
	$("#esm-queue thead").append("<tr class='no-data'><td colspan='2'>Your ESM queue is empty.</td></tr>");
	$("#esm-queue-title").hide();
	$("table#esm-queue tbody").bind("pageinit DOMSubtreeModified", function() {
		if ($("#esm-queue > tbody > tr").length > 0) {
			$("#esm-queue thead tr.no-data").hide();
			$("#esm-queue-title").show();
		} else {
			$("#esm-queue-title").hide();
			$("#esm-queue thead").append("<tr class='no-data'><td colspan='2'>Your ESM queue is empty</td></tr>");
		}
	
	});
	
	// Prevent blinking when showing more/less
	$('.more-link').click(function(e){
		e.preventDefault();
	});
	
	$('.less-link').click(function(e){
		e.preventDefault();
	});
	
	//new study database selector
	$('.db_selection').change(function()
    {
		if($(this).val() == "My own") {
            $('.newstudy_hiddenstuff').show();
        } else {
            $('.newstudy_hiddenstuff').hide();
        }
	});
	
	var page_loaded = false;
	if (!page_loaded) {
		var element = $("#study-data tr.plugin-data").eq(0);
		element.css('font-weight', 'bold');
		//element.click();
	}


	// Close study dialog
	$("#close-study-dialog").dialog({
		width: 'auto',
        height: 'auto',
		autoOpen: false,  
		modal: true,  
		draggable: false,
		resizable: false,
		buttons: {
			"Close study": function() {
				var study_id = $('.study_id').attr('id');
				changeStudyStatus(study_id, 0);
				$("#close-study-dialog").dialog("close");
			},
			"Cancel": function() {
				$("#close-study-dialog").dialog("close");
			}
		}
	});

	// Remove device dialog
	$("#remove-device-dialog").dialog({
		width: 'auto',
        height: 'auto',
		autoOpen: false,  
		modal: true,  
		draggable: false,
		resizable: false,
		buttons: {
			"Remove device": function() {
				var study_id = $('.study_id').attr('id');
				var device_id = $(this).data("device_id");
				$.ajax({
					url: "../remove_device",
					type: "POST",
					data: { study_id : study_id, device_id : device_id, 'csrf_token_aware' : cct},
					dataType: "json",
					success: function(data) {
						if (data.success == true) {
							getDevices();
							$('#devices-values .success').hide();
						}
						
					}
				});
				$(".table-navigation:eq(1)").after("<div class='success-msg'>Device deleted succesfully!</div>");
				$('.success-msg').delay(3000).fadeOut("medium", function() { this.remove() });
				$(this).dialog("close");
			},
			"Cancel": function() {
				$(this).dialog("close");
			}
		}
	});


	// Change study status
	$('.study-status').click(function(e) {
    	if($(this).is(":checked")) {
				var val = true;
			} else {
				var val = false;

		}

		var study_id = $('.study_id').attr('id');

		if (val == false) {
			$("#close-confirm").prop("checked", true);
			$("#close-study-dialog").dialog("open");
		} else {
			changeStudyStatus(study_id, 1);
		}
	});
	
	
	//textarea value
	var description_txt = '';
	var description_height = 0;
	
	//Study description buttons
	$('#description-buttons .save-cancel').hide();

	$('#description-buttons .edit-description').click(function(e){
		e.preventDefault();
		description_txt = $('#expand_area').val();
		description_height = $('#expand_area')[0].scrollHeight-8;
		$('textarea#expand_area').attr('disabled', false).focus();
		$('#description-buttons .save-cancel').show();
	});

	$('#description-buttons .cancel-button').click(function(e){
		e.preventDefault();
		$('textarea#expand_area').attr('disabled', true);
		$('#description-buttons .save-cancel').hide();
		$('#expand_area').val(description_txt);
		$('#expand_area').height(description_height);
	});
	
	$('#description-buttons .save-button').click(function(e){
		e.preventDefault();
		$('textarea#expand_area').attr('disabled', true);
		var study_id = $('.study_id').attr('id');
		var description = $('textarea#expand_area').val();
		var description_parent = $('textarea#expand_area');
		var link = '../edit_description';
		var data_array = { study_id : study_id, description : description, 'csrf_token_aware' : cct};
		$.ajax({
			url: link,
			type: "POST",
			data: data_array,
			dataType: "json",
			success: function(data){
				if (data != 0) {
					description_parent.val(data);
					$('#description-buttons .save-cancel').hide();
					description_txt = $('#expand_area').val();
					description_height = $('#expand_area')[0].scrollHeight;
				} else {
					$('#expand_area').val(description_txt);
					$('#expand_area').height(description_height);
				}
			}
		});
		
	});

	// Sensor buttons
	$('#sensors-buttons .save-cancel').hide();

	$('#sensors-buttons .edit-description').click(function(e){
		e.preventDefault();
		// Set current config
		currentConfig = generateStudyConfiguration();
		// Enable clicking on sensors
		$("#sensors-settings").removeClass("disabled");
		// Show and enable all sensors
		$("ul#sensors-settings li.sensor").fadeIn("medium");
		$("ul#sensors-settings input").prop("disabled", false);
		// Show cancel and confirm buttons
		$('#sensors-buttons .save-cancel').show();
		// Enable arrow
		$(".sensor-arrow").removeClass("disabled");
		// Remove no-data notification
		$("dConfiv.no-data.sensors").remove();
		// Remove no sensors enabled message
		$('#sensors-settings-wrapper').children('.no-data').remove();

		if ($("#status_plugin_esm_questionnaire").prop("checked")) {
			$("#manage_esmq").toggleClass("disabled");
		}
	});
	
	$('#sensors-buttons .cancel-button').click(function(e){
		e.preventDefault();
		// Revert changes
		getStudyConfiguration();
		$('#sensors-buttons .save-cancel').hide();
		$("#manage_esmq").toggleClass("disabled");
	});

	$('#sensors-buttons .save-button').click(function(e){
		e.preventDefault();

		var config = generateStudyConfiguration();

		// Update study config to db
		$.ajax({
			async: false,
			url: "../update_study_sensors",
			type: "POST",
			data: { study_id : study_id, config : JSON.stringify(config), 'csrf_token_aware' : cct},
			dataType: "json"
		});

		// Send updated config to all devices
		$.ajax({
			async: false,
			url: "../../webservice/study_config_updated",
			type: "POST",
			data: { study_id : study_id, config : JSON.stringify(config), 'csrf_token_aware' : cct},
			dataType: "json"
		});

		$('#sensors-buttons .save-cancel').hide();
		$("#manage_esmq").toggleClass("disabled");
		getStudyConfiguration();

	});


	//Co-researcher buttons
	$('.delete-co').each(function() {  
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
			var user_id = $(this).parent().attr('id');  
			var study_id = $('.study_id').attr('id');
			
			var data_array = { user_id : user_id, study_id : study_id, 'csrf_token_aware' : cct};
			var link = '../delete_co';
			
			$.data(this, 'dialog').dialog('option', 'title', 'Delete co-researcher');
			$.data(this, 'dialog').dialog({buttons: getButtonsDelCo(link, data_array)});
			$.data(this, 'dialog').dialog('open');    
	});


	// Delete study dialog
	$("#delete-study-dialog").dialog({
		width: 'auto',
        height: 'auto',
		autoOpen: false,  
		modal: true,  
		draggable: false,
		resizable: false,
		buttons: {
			"Delete study": function() {
				$.ajax({
					url: "../delete_study",
					type: "POST",
					data: { study_id : study_id, 'csrf_token_aware' : cct},
					dataType: "json",
					success: function(data) {
						if (data == true) {
							window.location.replace("../studies");
						}
					}
				});	
			},
			"Cancel": function() {
				$(this).dialog('close');
			}
		}
	});

	// Delete study button
	$('#delete-study').click(function(e) {
		$("#delete-study-dialog").dialog("open");
	});
	
	$('.qr_dialog').dialog({
		width: 'auto',
        height: 'auto',
		autoOpen: false,  
		modal: true,  
		draggable: false,
		resizable: false,
		buttons: {
					"Download": function() {
						$(this).dialog('close');
					},
					"Close": function() {
						$(this).dialog('close');
					}
				}
	});
	$('.show_qr').click(function (e) {
		e.preventDefault();
		$('.qr_dialog').dialog('open');
    });
	
	$('button span:contains("Download")').parent().wrap('<a href="#" id="submit_qr_wrapper"></a>');
	
	$('#submit_qr, #submit_qr_wrapper').click(function (e) {
		e.preventDefault();
		$("#qr_form").submit();
		return false;
    });
	
	$('.add-co').each(function() {  
		$.data(this, 'dialog', 
			$(this).next('.add-dialog').dialog({
				autoOpen: false,  
				modal: true,  
				draggable: false,
				resizable: false
			})
		);  
		}).click(function(e) {
			e.preventDefault();
			
			var study_id = $('.study_id').attr('id');
			var link = '../add_co';
			
			$.data(this, 'dialog').dialog('option', 'title', 'Add co-researcher');
			$.data(this, 'dialog').dialog({buttons: getButtonsAddCo(link, study_id)});
			$.data(this, 'dialog').dialog('open');    
	});

	// Open study database credentials dialog
	$('#show-db-credentials').click(function(e) {
		e.preventDefault();
		$("#study-data-dialog").dialog("open");
	});

	// Delete study dialog
	$("#study-data-dialog").dialog({
		width: '500px',
        height: 'auto',
		autoOpen: false,  
		modal: true,  
		draggable: false,
		resizable: false,
		buttons: {
			"Close": function() {
				$(this).dialog('close');
			}
		}
	});

	
	$('#newstudy_testbutton').click(function(e){
		e.preventDefault();
		
		$('#connection-error').hide();
		$('#connection-success').hide();
		
		//var hostname = $('#db_hostname').val() +  ':' + $('#db_port').val();
		var hostname = $('#db_hostname').val();
		var port =  $('#db_port').val();
		var db_name =  $('#db_name').val();
		var username = $('#db_username').val();
		var password = $('#db_password').val();

		var link = '../webservice/check_database_connection';
		var data_array = { hostname : hostname, port: port, db_name: db_name, username : username, password : password, 'csrf_token_aware' : cct};
		$.ajax({
			url: link,
			type: "POST",
			data: data_array,
			dataType: "json",
			success: function(data){
				if (data == true) {
					$('#connection-success').show();
				} else if (data == false) {
					$('#connection-error').show();
				}
			}
		});
	});
	
	$('.error-state').delay(10000).fadeOut('slow');
	
});