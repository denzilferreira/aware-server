$(document).ready(function() {

	var sensors_settings = [];

	// Check (e.g., when study creation failed) if we have any sensor values set
	var sensors = $("li.sensor ul.sensor-settings");
	for (var i = 0; i < sensors.length - 1; i++) {
		if ($(sensors[i]).children("li.sensor-setting").children("input.sensor.value").hasClass("enabled")) {
			// Show sensor settings
			$(sensors[i]).parent().children("ul.sensor-settings").fadeIn("fast");
			// Add sensor parent active class
			$(sensors[i]).parent().toggleClass("active");
			// Change allow to expand mode
			$(sensors[i]).parent().children(".sensor-arrow").removeClass("up").addClass("down");
			// Sensor has settings with values set, add hilight
			$(sensors[i]).parent().addClass("enabled");
		}
	}

	// Sensor parent clicked, show or hide settings
	$("p.sensor.label, p.plugin.label, div.sensor-arrow").click(function(e) {
		if (!$("#sensors-settings").hasClass("disabled")) {
			// Show child
			if ($(this).parent().hasClass("active")) {
				// Hide child
				$(this).parent().children("ul.sensor-settings").fadeOut("fast");
				$(this).parent().toggleClass("active");
				$(this).parent().children(".sensor-arrow").removeClass("down").addClass("up");
			} else {
				// Show child
				$(this).parent().children("ul.sensor-settings").fadeIn("fast");
				$(this).parent().toggleClass("active");
				$(this).parent().children(".sensor-arrow").removeClass("up").addClass("down");
			}
		}

	});

	// Click on the sensor child
	$("li.sensor-setting > input").change(function(e) {
		// If checkbox checked or input field has value, add enabled class to sensor value
		// Else remove enabled class for sensor value
		var c = this.checked;
		if ($(this).hasClass("sensor.value.boolean")) {
			if (c) {
				$(this).addClass("enabled");
			} else {
				$(this).removeClass("enabled");
			}
		} else {
			if ($(this).val().length > 0) {
				$(this).addClass("enabled");
			} else {
				$(this).removeClass("enabled");
			}
		}

		var empty = true;
		var sensor_settings = $(this).parent().parent();

		// Check if we got any values set (checkboxes checked or input fields values)
		$(sensor_settings).children().children("input.sensor.value").each(function(e) {
			if ((!$(this).hasClass("boolean") && $(this).hasClass("enabled")) || ($(this).hasClass("boolean") && $(this).prop("checked"))) {
				empty = false;
				return false;
			}
		});

		// If sensor values set, add enabled class to sensor parent class to hilight
		// Else, remove class
		if (empty == true) {
			$(this).parent().parent().parent().removeClass("enabled");
		} else {
			$(this).parent().parent().parent().addClass("enabled");
		}

		// ESM-Q
		var plugin_id = $(this).parent().parent().parent().children(".plugin_id").val();
		if (plugin_id == 53) {
			console.debug("ESM-Q");
			toggleESMQ(c, sensor_settings);
		}
	});

	// Only allow numeric input for certain sensor settings
	if ($("input.sensor.value.integer").length) {
		$("input.sensor.value.integer").numeric();
	}
});