function initESMQ() {
	$("#content").append("<div id='esmq-questionnaires' style='display: none;'> <ul> <li><a href='#esmq-0' id='add-new'>Add new</a></li> </ul> </div>");

	$("#esmq-questionnaires").tabs().dialog({
		autoOpen: false,
		modal: true,
		width: 800,
		minHeight: 500,
		draggable: false, // disable the dialog's drag we're using the tabs titlebar instead
		buttons: {
			'Save': function() {
				var fields = $(".input-field");
				fields.removeClass("invalid");
				$(".esmq-weekday").removeClass("invalid");
				var valid = true;
				var invalid_tab = -1;
				for (var i = 0; i < fields.length; i++) {
					if ($(fields[i]).val().length == 0) {
						$(fields[i]).addClass("invalid");
						valid = false;
						invalid_tab = $(fields[i]).closest(".esm-questionnaire").attr("id").split("esm-questionnaire")[1];
					}
				}
				var weekdays = $(".esm-questionnaire-item.value.weekdays");
				for (var w=0; w < weekdays.length; w++) {
				    if($(weekdays[w]).children(".esmq-weekday").children("input:checked").length == 0) {
				        $(weekdays[w]).children(".esmq-weekday").addClass("invalid");
				        valid = false;
				        invalid_tab = $(weekdays[w]).closest(".esm-questionnaire").attr("id").split("esm-questionnaire")[1];
				    }
				}
				if (valid) {
					$("#questionnaires_plugin_esm_questionnaire").val(getESMQConfig($(this)));
					$("#questionnaires_plugin_esm_questionnaire").addClass("enabled");
					$(this).dialog('destroy').remove();
					$('#sensors-buttons .save-button').click();
				} else {
					$("#esmq-questionnaires").tabs({
						"active": invalid_tab
					});
				}
			},
			'Cancel': function() {
				$(this).dialog('destroy').remove();
				$('#sensors-buttons .cancel-button').click();
			}
		},
		create: function() {
			var tabTitle = $("#tab_title"),
				tabContent = $("#tab_content"),
				tabTemplate = "<li><a href='#{href}'>#{label}</a> <span class='ui-icon ui-icon-close' role='presentation'>Remove Tab</span></li>",
				tabCounter = 1;

			var tabs = $("#esmq-questionnaires").tabs({
				disabled: [0]
			});

			// define the elements we're dealing with
			$dlg = $(this).parent();
			// clone close button from dialog title and put it in the tabs area
			//$dlg.find('.ui-dialog-titlebar-close').appendTo($tabs);
			// make the tabs draggable, give it a class that gracefully adds the move cursor and remove the dialog's original titlebar completely
			$dlg.draggable({
					handle: ".ui-tabs-nav"
				})
				.addClass('ui-draggable')
				.find('.ui-dialog-titlebar').remove();
			// give dialog styles to the tabs (would like to do this without adding CSS, but couldn't)
			$dlg.find('.ui-tabs').css('padding', '0px');

			// close icon: removing the tab on click
			tabs.delegate("span.ui-icon-close", "click", function() {
				var panelId = $(this).closest("li").remove().attr("aria-controls");
				$("#" + panelId).remove();
				tabs.tabs("refresh");
				var tabCount = $("#esmq-questionnaires ul li").length;
				if (tabCount == 1) {
					$("#esmq-questionnaires").append("<p class='esmq-info'>Add a new questionnaire by clicking 'Add new' -button</p>");
				}

			});

			tabs.bind("keyup", function(event) {
				if (event.altKey && event.keyCode === $.ui.keyCode.BACKSPACE) {
					var panelId = tabs.find(".ui-tabs-active").remove().attr("aria-controls");
					$("#" + panelId).remove();
					tabs.tabs("refresh");
				}
			});

			if ($("#questionnaires_plugin_esm_questionnaire").val().length > 1) {
				config = parseESMQConfig();
				for (c in config) {
					initQuestionnaire(config[c]);
				}
			}

			// addTab button: just opens the dialog
			$("#add-new").click(function(event) {
				event.preventDefault();

				var data = "<div class=\"esm-questionnaire\" id=\"esm-questionnaire{count}\">\r\n\r\n    <div class=\"esm-questionnaire-item\">\r\n      <div class=\"esm-questionnaire-item option\">\r\n        <label>Start date<\/label>\r\n      <\/div>\r\n\r\n      <div class=\"esm-questionnaire-item value\">\r\n        <input type=\"text\" class=\"input-field esmq-startdate\" id=\"esmq{count}-startdate\">\r\n      <\/div>\r\n    <\/div>\r\n\r\n    <div class=\"esm-questionnaire-item\">\r\n      <div class=\"esm-questionnaire-item option\">\r\n        <label>End date<\/label>\r\n      <\/div>\r\n      \r\n      <div class=\"esm-questionnaire-item value\">\r\n        <input type=\"text\" class=\"input-field esmq-enddate\" id=\"esmq{count}-enddate\">\r\n      <\/div>\r\n    <\/div>\r\n\r\n    <div class=\"esm-questionnaire-item\">\r\n      <div class=\"esm-questionnaire-item option\">\r\n        Days\r\n      <\/div>\r\n      \r\n      <div class=\"esm-questionnaire-item value weekdays\">\r\n        <div class=\"esmq-weekday\">\r\n          <input type=\"checkbox\" name=\"esmq-days-sun\" class=\"esmq-days-sun\" id=\"esmq{count}-day-sun\">\r\n          <label for=\"esmq{count}-day-sun\"><\/label>\r\n        <\/div>\r\n\r\n        <div class=\"esmq-weekday\">\r\n          <input type=\"checkbox\" name=\"esmq-days-mon\" class=\"esmq-days-mon\" id=\"esmq{count}-day-mon\">\r\n          <label for=\"esmq{count}-day-mon\"><\/label>\r\n        <\/div>\r\n\r\n        <div class=\"esmq-weekday\">\r\n          <input type=\"checkbox\" name=\"esmq-days-tue\" class=\"esmq-days-tue\" id=\"esmq{count}-day-tue\">\r\n          <label for=\"esmq{count}-day-tue\"><\/label>\r\n        <\/div>\r\n\r\n        <div class=\"esmq-weekday\">\r\n          <input type=\"checkbox\" name=\"esmq-days-wed\" class=\"esmq-days-wed\" id=\"esmq{count}-day-wed\">\r\n          <label for=\"esmq{count}-day-wed\"><\/label>\r\n        <\/div>\r\n\r\n        <div class=\"esmq-weekday\">\r\n          <input type=\"checkbox\" name=\"esmq-days-thu\" class=\"esmq-days-thu\" id=\"esmq{count}-day-thu\">\r\n          <label for=\"esmq{count}-day-thu\"><\/label>\r\n        <\/div>\r\n\r\n        <div class=\"esmq-weekday\">\r\n          <input type=\"checkbox\" name=\"esmq-days-fri\" class=\"esmq-days-fri\" id=\"esmq{count}-day-fri\">\r\n          <label for=\"esmq{count}-day-fri\"><\/label>\r\n        <\/div>\r\n\r\n        <div class=\"esmq-weekday\">\r\n          <input type=\"checkbox\" name=\"esmq-days-sat\" class=\"esmq-days-sat\" id=\"esmq{count}-day-sat\">\r\n          <label for=\"esmq{count}-day-sat\"><\/label>\r\n        <\/div>\r\n\r\n      <\/div>\r\n      \r\n    <\/div>\r\n\r\n    <div class=\"esm-questionnaire-item\">\r\n      <div class=\"esm-questionnaire-item option\">\r\n        <label>Start time<\/label>\r\n      <\/div>\r\n      \r\n      <div class=\"esm-questionnaire-item value\">\r\n        <input type=\"text\" id=\"esmq{count}-starttime\" class=\"input-field esmq-starttime\">\r\n      <\/div>\r\n    <\/div>\r\n\r\n    <div class=\"esm-questionnaire-item\">\r\n      <div class=\"esm-questionnaire-item option\">\r\n        <label>End time<\/label>\r\n      <\/div>\r\n      \r\n      <div class=\"esm-questionnaire-item value\">\r\n        <input type=\"text\" id=\"esmq{count}-endtime\" class=\"input-field esmq-endtime\">\r\n      <\/div>\r\n    <\/div>\r\n\r\n    <div class=\"esm-questionnaire-item\" style=\"padding-bottom: 5px;\">\r\n      <div class=\"esm-questionnaire-item option\">\r\n        <label for=\"esmq{count}-amount\">Amount<\/label>\r\n      <\/div>\r\n\r\n      <div class=\"esm-questionnaire-item value\">\r\n        <input type=\"text\" id=\"esmq{count}-amount\" class=\"input-field esmq-amount\">\r\n        <!-- <div id=\"esmq{count}-amount\" class=\"esmq-amount\"><\/div> -->\r\n      <\/div>\r\n    <\/div>\r\n\r\n    <div class=\"esm-questionnaire-item\">\r\n      <div class=\"esm-questionnaire-item option\">\r\n        <label for=\"esmq{count}-interval\">Interval<\/label>\r\n      <\/div>\r\n      \r\n      <div class=\"esm-questionnaire-item value radio\">\r\n          <input type=\"radio\" name=\"esmq{count}-interval\" id=\"esmq{count}-interval-spaced\" class=\"esmq-interval\" value=\"spaced\" checked>\r\n          <label for=\"esmq{count}-interval-spaced\">Spaced<\/label>\r\n          <input type=\"radio\" name=\"esmq{count}-interval\" id=\"esmq{count}-interval-random\" class=\"esmq-interval\" value=\"random\">\r\n          <label for=\"esmq{count}-interval-random\">Random<\/label>\r\n      <\/div>\r\n    <\/div>\r\n\r\n\r\n    <div class=\"esmq-questions\">\r\n      \r\n      <div class=\"esmq-add-question\">\r\n        <a href=\"#\">Add a new question<\/a>\r\n      <\/div>\r\n\r\n    <\/div>\r\n\r\n<\/div>";
				initQuestionnaire(data);
			});

			function initQuestionnaire(data) {
				$(".esmq-info").remove();
				var label = "Questionnaire " + tabCounter,
					id = "esmq-" + tabCounter,
					li = $(tabTemplate.replace(/#\{href\}/g, "#" + id).replace(/#\{label\}/g, label));


				tabs.find(".ui-tabs-nav").append(li);
				// Replace {count} tags for ID (for checkbox customization)
				tabs.append("<div id='" + id + "'>" + data.replace(/{count}/g, tabCounter) + "</div>");
				tabs.tabs("refresh");

				tabs.tabs({
					"active": tabCounter
				});


				// Init stuff
				$("#esmq" + tabCounter + "-startdate").datepicker({
					dateFormat: "yy-mm-dd",
					minDate: new Date(),
					onSelect: function(selected) {
						var selector = this.id.split("-")[0];
						$("#" + selector + "-enddate").datepicker("option", "minDate", new Date(selected));
					}
				});

				// Init
				$("#esmq" + tabCounter + "-enddate").datepicker({
					dateFormat: "yy-mm-dd",
					minDate: new Date(),
					onSelect: function(selected) {
						var selector = this.id.split("-")[0];
						$("#" + selector + "-startdate").datepicker("option", "maxDate", new Date(selected));
					}
				});

				$("#esmq" + tabCounter + "-starttime").timepicker();
				$("#esmq" + tabCounter + "-endtime").timepicker();

				$("#esmq" + tabCounter + "-starttime").on('click', function() {
					if ($(this).val().length == 0) {
						$(this).val("00:00");
					}
				});

				$("#esmq" + tabCounter + "-endtime").on('click', function() {
					if ($(this).val().length == 0) {
						$(this).val("00:00");
					}
				});

				$("#esmq" + tabCounter + "-amount").slider({
						value: 1,
						min: 1,
						max: 10,
						step: 1
					})
					.each(function() {
						// Get the options for this slider
						var opt = $(this).data().uiSlider.options;
						// Get the number of possible values
						var vals = opt.max - opt.min;

						// Space out values
						for (var i = 0; i <= vals; i++) {
							var el = $('<label>' + (i + 1) + '</label>').css('left', (i / vals * 100) + '%');
							$("#esmq" + tabCounter + "-amount").append(el);
						}
					});

				$(".input-field.esmq-amount").numeric();

				// Add new ESM-Q option
				$('#esm-questionnaire' + tabCounter).on('click', '.esmq-option-add', function(event) {
					event.preventDefault();

					var this_id = $(this).closest(".esmq-question").find(".esmq-question-id").val();
					var q_ids = $(this).closest(".esm-questionnaire").find(".esmq-question-id").map(function() {
						if (this.value != this_id) return "<option value='" + this.value + "'>" + this.value + "</option>";
					}).get().join("");

					var esm_type = $(this).parent().parent().children(".esm-questionnaire-item.value").children("select.esmq-question-type").val();

					var q_option_count = $(this).parent().find(".esmq-option").length + 1;
					var p = $(this).parent();

					var esmq_option;

					if (esm_type == 3) {
						esmq_option = '<div class="esmq-option">' +
							'<div class="esmq-option-del">' +
							'<a href="#">-</a>' +
							'</div>' +
							'<div class="esmq-option-value long">' +
							'<input type="text" placeholder="Option" class="input-field">' +
							'</div>' +
							'</div>';
					} else {
						esmq_option = '<div class="esmq-option">' +
							'<div class="esmq-option-del">' +
							'<a href="#">-</a>' +
							'</div>' +
							'<div class="esmq-option-value">' +
							'<input type="text" placeholder="Option" class="input-field">' +
							'</div>' +
							'<div class="esmq-option-followup" id="fup_' + (tabCounter - 1) + '-' + parseInt(this_id) + '-' + q_option_count + '">' +
							'<select class="esmq-followup" type="hidden">' +
							'<option value="false">End</option>' +
							q_ids +
							'</select>' +
							'</div>' +
							'</div>';
					}
					p.children('.esmq-option-add').before($(esmq_option).fadeIn('fast'));
				});

				// Delete existing ESM-Q option
				$('#esm-questionnaire' + tabCounter).on('click', '.esmq-option-del', function(event) {
					event.preventDefault();

					var p = $(this).parent();
					var children = p.parent().children('.esmq-option');
					if (children.length > 1) {
						p.remove().fadeOut('fast');
					}
				});

				// Bind question type change
				$('#esm-questionnaire' + tabCounter).closest(".esm-questionnaire").find('select.esmq-question-type').on("change", function() {
					var this_id = $(this).closest(".esmq-question").find(".esmq-question-id").val();
					var q_ids = $(this).closest(".esm-questionnaire").find(".esmq-question-id").map(function() {
						if (this.value != this_id) return "<option value='" + this.value + "'>" + this.value + "</option>";
					}).get().join("");

					var q_type_sel = $(this).val();
					var q_choices = $(this).parent().parent().children(".esmq-options");
					var q_body = $(this).parent().parent();

					// Delete question choices
					q_body.children(".esmq-options").remove();
					q_body.children(".esm-questionnaire-item.extra").remove();

					var elem;
					if (q_type_sel == 1) {
						elem = '<div class="esm-questionnaire-item option extra">Follow up</div><div class="esm-questionnaire-item value extra"> <select class="esmq-followup"><option value="false">End</option>' + q_ids + '</select> </div>';
						q_body.append($(elem).hide().fadeIn('fast'));
					} else if (q_type_sel == 2) {
						elem = '<div class="esmq-options"> <div class="esmq-legends"> <span style="margin-left: 12px">Choice</span> <span style="margin-left: 115px">To question #</span> </div> <div class="esmq-option"> <div class="esmq-option-del"> <a href="#">-</a> </div> <div class="esmq-option-value"> <input type="text" placeholder="Option" class="input-field"> </div> <div class="esmq-option-followup"> <select class="esmq-followup"><option value="false">End</option>' + q_ids + '</select> </div> </div> <div class="esmq-option-add"> <a href="#">+</a> </div> </div>';
						q_body.append($(elem).hide().fadeIn('fast'));
					} else if (q_type_sel == 3) {
						elem = '<div class="esm-questionnaire-item option extra">Follow up</div><div class="esm-questionnaire-item value extra"> <select class="esmq-followup"><option value="false">End</option>' + q_ids + '</select> </div>';
						q_body.append($(elem).hide().fadeIn('fast'));
						elem = '<div class="esmq-options"> <div class="esmq-legends"> <span style="margin-left: 12px">Choice</span> </div> <div class="esmq-option"> <div class="esmq-option-del"> <a href="#">-</a> </div> <div class="esmq-option-value long"> <input type="text" placeholder="Option" class="input-field"> </div> </div> <div class="esmq-option-add"> <a href="#">+</a> </div> </div>';
						q_body.append($(elem).hide().fadeIn('fast'));
					} else if (q_type_sel == 4) {
						elem = '<div class="esm-questionnaire-item option extra"> Max label </div> <div class="esm-questionnaire-item value extra"> <input type="text" class="input-field likert-max-label"> </div> <div class="esm-questionnaire-item option extra"> Min label </div> <div class="esm-questionnaire-item value extra"> <input type="text" class="input-field likert-min-label"> </div><div class="esm-questionnaire-item option extra">Follow up</div><div class="esm-questionnaire-item value extra"> <select class="esmq-followup"><option value="false">End</option>' + q_ids + '</select> </div>';
						q_body.append($(elem).hide().fadeIn('fast'));
					} else if (q_type_sel == 5) {
						elem = '<div class="esmq-options"> <div class="esmq-legends"> <span style="margin-left: 12px">Choice</span> <span style="margin-left: 115px">To question #</span> </div> <div class="esmq-option"> <div class="esmq-option-del"> <a href="#">-</a> </div> <div class="esmq-option-value"> <input type="text" placeholder="Option" class="input-field"> </div> <div class="esmq-option-followup"> <select class="esmq-followup"><option value="false">End</option>' + q_ids + '</select> </div> </div> <div class="esmq-option-add"> <a href="#">+</a> </div> </div>';
						q_body.append($(elem).hide().fadeIn('fast'));
					}
				});



				// Add new ESM-Q question
				$('#esm-questionnaire' + tabCounter).on('click', '.esmq-add-question', function(event) {
					event.preventDefault();

					var p = $(this);

					var c;
					var c_elem = $(this).closest(".esmq-questions").find(".esmq-question").last().find(".esmq-question-id");
					if (c_elem.length == 0) {
						c = 1;
					} else {
						c = parseInt(c_elem.val()) + 1;
					}
					var q_ids = $(this).closest(".esm-questionnaire").find(".esmq-question-id").map(function() {
						return "<option value='" + this.value + "'>" + this.value + "</option>";
					}).get().join("");
					var question = '<div class="esmq-question"> <input type="hidden" value="' + c + '" class="esmq-question-id"> <div class="esmq-question-head"> <div class="esmq-question-head-left"> #' + c + '</div> <div class="esmq-question-head-right"> <a href="#">X</a> </div> </div> <div class="esmq-question-body"> <div class="esm-questionnaire-item option"> Title </div> <div class="esm-questionnaire-item value"> <input type="text" class="input-field esmq-question-title"> </div> <div class="esm-questionnaire-item option"> Type </div> <div class="esm-questionnaire-item value"> <select class="esmq-question-type"> <option value="1">Free text</option> <option value="2">Radio</option> <option value="3">Checkbox</option> <option value="4">Likert</option> <option value="5">Quick answer</option> </select> </div> <div class="esm-questionnaire-item option extra" style="display: block;">Follow up</div><div class="esm-questionnaire-item value extra" style="display: block;"> <select class="esmq-followup"><option value="false">End</option>' + q_ids + '</select> </div></div> </div>';
					$(this).closest(".esm-questionnaire").find(".esmq-followup").append($("<option/>", {
						value: c,
						text: c
					}));


					p.before($(question).fadeIn('fast'));

					var q_type = $(this).closest(".esm-questionnaire").find('select.esmq-question-type');
					q_type.on("change", function() {
						var this_id = $(this).closest(".esmq-question").find(".esmq-question-id").val();
						var q_ids = $(this).closest(".esm-questionnaire").find(".esmq-question-id").map(function() {
							if (this.value != this_id) return "<option value='" + this.value + "'>" + this.value + "</option>";
						}).get().join("");

						var q_type_sel = $(this).val();
						var q_choices = $(this).parent().parent().children(".esmq-options");
						var q_body = $(this).parent().parent();

						// Delete question choices
						q_body.children(".esmq-options").remove();
						q_body.children(".esm-questionnaire-item.extra").remove();

						var elem;
						if (q_type_sel == 1) {
							elem = '<div class="esm-questionnaire-item option extra">Follow up</div><div class="esm-questionnaire-item value extra"> <select class="esmq-followup"><option value="false">End</option>' + q_ids + '</select> </div>';
							q_body.append($(elem).hide().fadeIn('fast'));
						} else if (q_type_sel == 2) {
							elem = '<div class="esmq-options"> <div class="esmq-legends"> <span style="margin-left: 12px">Choice</span> <span style="margin-left: 115px">To question #</span> </div> <div class="esmq-option"> <div class="esmq-option-del"> <a href="#">-</a> </div> <div class="esmq-option-value"> <input type="text" placeholder="Option" class="input-field"> </div> <div class="esmq-option-followup"> <select class="esmq-followup"><option value="false">End</option>' + q_ids + '</select> </div> </div> <div class="esmq-option-add"> <a href="#">+</a> </div> </div>';
							q_body.append($(elem).hide().fadeIn('fast'));
						} else if (q_type_sel == 3) {
							elem = '<div class="esm-questionnaire-item option extra">Follow up</div><div class="esm-questionnaire-item value extra"> <select class="esmq-followup"><option value="false">End</option>' + q_ids + '</select> </div>';
							q_body.append($(elem).hide().fadeIn('fast'));
							elem = '<div class="esmq-options"> <div class="esmq-legends"> <span style="margin-left: 12px">Choice</span> </div> <div class="esmq-option"> <div class="esmq-option-del"> <a href="#">-</a> </div> <div class="esmq-option-value long"> <input type="text" placeholder="Option" class="input-field"> </div> </div> <div class="esmq-option-add"> <a href="#">+</a> </div> </div>';
							q_body.append($(elem).hide().fadeIn('fast'));
						} else if (q_type_sel == 4) {
							elem = '<div class="esm-questionnaire-item option extra"> Max label </div> <div class="esm-questionnaire-item value extra"> <input type="text" class="input-field likert-max-label"> </div> <div class="esm-questionnaire-item option extra"> Min label </div> <div class="esm-questionnaire-item value extra"> <input type="text" class="input-field likert-min-label"> </div><div class="esm-questionnaire-item option extra">Follow up</div><div class="esm-questionnaire-item value extra"> <select class="esmq-followup"><option value="false">End</option>' + q_ids + '</select> </div>';
							q_body.append($(elem).hide().fadeIn('fast'));
						} else if (q_type_sel == 5) {
							elem = '<div class="esmq-options"> <div class="esmq-legends"> <span style="margin-left: 12px">Choice</span> <span style="margin-left: 115px">To question #</span> </div> <div class="esmq-option"> <div class="esmq-option-del"> <a href="#">-</a> </div> <div class="esmq-option-value"> <input type="text" placeholder="Option" class="input-field"> </div> <div class="esmq-option-followup"> <select class="esmq-followup"><option value="false">End</option>' + q_ids + '</select> </div> </div> <div class="esmq-option-add"> <a href="#">+</a> </div> </div>';
							q_body.append($(elem).hide().fadeIn('fast'));
						}
					});

				});

				// Delete existing ESM-Q question
				$('#esm-questionnaire' + tabCounter).on('click', '.esmq-question-head-right', function(event) {
					event.preventDefault();

					var p = $(this).parent().parent().parent();
					var q = $(this).parent().parent();
					var q_id = q.find(".esmq-question-id").val();

					$(this).closest(".esm-questionnaire").find(".esmq-followup option[value='" + q_id + "']").remove();

					var children = p.children('.esmq-question');
					if (children.length > 1) {
						q.remove().fadeOut('fast');
					}
				});
				tabCounter++;
			}
		}
	})
}


function parseESMQConfig() {
	var esmq = $("#questionnaires_plugin_esm_questionnaire").val();
	var j_esmq = JSON.parse(esmq);

	var htmls = [];
	//var html = "";

	// Foreach questionnaire
	for (var i = 0; i < j_esmq.length; i++) {
		var html = "";
		var id, startDate, endDate, startTime, endTime, numberOfESM, interval, startTimeHours, startTimeMinutes, endTimeHours, endTimeMinutes, weekDays;
		// Get ID
		id = j_esmq[i]["id"];
		// Get general
		startDate = new Date(j_esmq[i]["general"]["startDate"] * 1000);
		startDate = startDate.getFullYear() + "-" + (startDate.getMonth() + 1) + "-" + startDate.getDate();
		endDate = new Date(j_esmq[i]["general"]["endDate"] * 1000);
		endDate = endDate.getFullYear() + "-" + (endDate.getMonth() + 1) + "-" + endDate.getDate();
		startTime = j_esmq[i]["general"]["startTime"];
		endTime = j_esmq[i]["general"]["endTime"];
		weekDays = j_esmq[i]["general"]["weekDays"];
		numberOfESM = j_esmq[i]["general"]["numberOfESM"];
		interval = j_esmq[i]["general"]["interval"];

		// State of the art time convertions
		var startTimeHours = Math.floor((parseInt(startTime) % 86400) / 3600);
		startTimeHours = (startTimeHours.toString().length == 1) ? "0" + startTimeHours : startTimeHours
		var startTimeMinutes = Math.floor((parseInt(startTime) % 3600) / 60);
		startTimeMinutes = (startTimeMinutes.toString().length == 1) ? "0" + startTimeMinutes : startTimeMinutes
		startTime = startTimeHours + ":" + startTimeMinutes;

		var endTimeHours = Math.floor((parseInt(endTime) % 86400) / 3600);
		endTimeHours = (endTimeHours.toString().length == 1) ? "0" + endTimeHours : endTimeHours
		var endTimeMinutes = Math.floor((parseInt(endTime) % 3600) / 60);
		endTimeMinutes = (endTimeMinutes.toString().length == 1) ? "0" + endTimeMinutes : endTimeMinutes
		endTime = endTimeHours + ":" + endTimeMinutes;

		// Open questionnaire
		html += '<div class="esm-questionnaire" id="esm-questionnaire' + id + '">';
		html += '<div class="esm-questionnaire-item"> <div class="esm-questionnaire-item option"> <label>Start date</label> </div> <div class="esm-questionnaire-item value"> <input type="text" class="input-field esmq-startdate" id="esmq' + id + '-startdate" value="' + startDate + '"> </div> </div> <div class="esm-questionnaire-item"> <div class="esm-questionnaire-item option"> <label>End date</label> </div> <div class="esm-questionnaire-item value"> <input type="text" class="input-field esmq-enddate" id="esmq' + id + '-enddate" value="' + endDate + '"> </div> </div> <div class="esm-questionnaire-item"> <div class="esm-questionnaire-item option"> Days </div> <div class="esm-questionnaire-item value weekdays"> <div class="esmq-weekday"> <input type="checkbox" name="esmq-days-sun" class="esmq-days-sun" id="esmq' + id + '-day-sun"' + ((weekDays[0] == 1) ? " checked" : "") + '> <label for="esmq' + id + '-day-sun"></label> </div> <div class="esmq-weekday"> <input type="checkbox" name="esmq-days-mon" class="esmq-days-mon" id="esmq' + id + '-day-mon"' + ((weekDays[1] == 1) ? " checked" : "") + '> <label for="esmq' + id + '-day-mon"></label> </div> <div class="esmq-weekday"> <input type="checkbox" name="esmq-days-tue" class="esmq-days-tue" id="esmq' + id + '-day-tue"' + ((weekDays[2] == 1) ? " checked" : "") + '> <label for="esmq' + id + '-day-tue"></label> </div> <div class="esmq-weekday"> <input type="checkbox" name="esmq-days-wed" class="esmq-days-wed" id="esmq' + id + '-day-wed"' + ((weekDays[3] == 1) ? " checked" : "") + '> <label for="esmq' + id + '-day-wed"></label> </div> <div class="esmq-weekday"> <input type="checkbox" name="esmq-days-thu" class="esmq-days-thu" id="esmq' + id + '-day-thu"' + ((weekDays[4] == 1) ? " checked" : "") + '> <label for="esmq' + id + '-day-thu"></label> </div> <div class="esmq-weekday"> <input type="checkbox" name="esmq-days-fri" class="esmq-days-fri" id="esmq' + id + '-day-fri"' + ((weekDays[5] == 1) ? " checked" : "") + '> <label for="esmq' + id + '-day-fri"></label> </div> <div class="esmq-weekday"> <input type="checkbox" name="esmq-days-sat" class="esmq-days-sat" id="esmq' + id + '-day-sat"' + ((weekDays[6] == 1) ? " checked" : "") + '> <label for="esmq' + id + '-day-sat"></label> </div> </div> </div> <div class="esm-questionnaire-item"> <div class="esm-questionnaire-item option"> <label>Start time</label> </div> <div class="esm-questionnaire-item value"> <input type="text" id="esmq' + id + '-starttime" class="input-field esmq-starttime" value="' + startTime + '"> </div> </div> <div class="esm-questionnaire-item"> <div class="esm-questionnaire-item option"> <label>End time</label> </div> <div class="esm-questionnaire-item value"> <input type="text" id="esmq' + id + '-endtime" class="input-field esmq-endtime" value="' + endTime + '"> </div> </div> <div class="esm-questionnaire-item" style="padding-bottom: 5px;"> <div class="esm-questionnaire-item option"> <label for="esmq' + id + '-amount">Amount</label> </div> <div class="esm-questionnaire-item value"> <input type="text" id="esmq' + id + '-amount" class="input-field esmq-amount" value="' + numberOfESM + '"> </div> </div> <div class="esm-questionnaire-item"> <div class="esm-questionnaire-item option"> <label for="esmq' + id + '-interval">Interval</label> </div> <div class="esm-questionnaire-item value radio"> <input type="radio" name="esmq' + id + '-interval" id="esmq' + id + '-interval-spaced" class="esmq-interval" value="spaced"' + ((interval == "spaced") ? " checked" : "") + '> <label for="esmq' + id + '-interval-spaced">Spaced</label> <input type="radio" name="esmq' + id + '-interval" id="esmq' + id + '-interval-random" class="esmq-interval" value="random"' + ((interval == "random") ? " checked" : "") + '> <label for="esmq' + id + '-interval-random">Random</label> </div> </div> <div class="esmq-questions">';

		// Get questions
		for (var q = 0; q < j_esmq[i]["questions"].length; q++) {
			var q_id, q_type;

			q_id = j_esmq[i]["questions"][q]["id"];
			q_type = j_esmq[i]["questions"][q]["type"];

			var q_esm_title = j_esmq[i]["questions"][q]["data"]["esm"][0]["esm"]["esm_title"];
			var q_esm_type = j_esmq[i]["questions"][q]["data"]["esm"][0]["esm"]["esm_type"];

			var q_ids = "<option value='false'>End</option>";
			for (var qid = 1; qid < j_esmq[i]["questions"].length + 1; qid++) {
				if (qid != q_id) {
					q_ids += ("<option value='" + qid + "'>" + qid + "</option>");
				}
			}

			var elem;
			if (q_esm_type == 1) {
				var esmq_options = "";
				for (var qid = 1; qid < j_esmq[i]["questions"].length + 1; qid++) {
					if (qid != q_id) {
						esmq_options += ("<option value='" + qid + "'" + (j_esmq[i]["questions"][q]["data"]["conditions"]["_any"] == qid ? " selected" : "") + ">" + qid + "</option>");
					}
				}
				elem = '<div class="esm-questionnaire-item option extra">Follow up</div><div class="esm-questionnaire-item value extra"> <select class="esmq-followup"><option value="false">End</option>' + esmq_options + '</select> </div>';
			} else if (q_esm_type == 2) {
				var esmq_options = "";
				for (var cond in j_esmq[i]["questions"][q]["data"]["conditions"]) {
					esmq_options += '<div class="esmq-option">' +
						'<div class="esmq-option-del">' +
						'<a href="#">-</a>' +
						'</div>' +
						'<div class="esmq-option-value">' +
						'<input type="text" placeholder="Option" class="input-field" value="' + cond + '">' +
						'</div>' +
						'<div class="esmq-option-followup">' +
						'<select class="esmq-followup" type="hidden">' +
						'<option value="false">End</option>';
					for (var qid = 1; qid < j_esmq[i]["questions"].length + 1; qid++) {
						if (qid != q_id) {
							esmq_options += ("<option value='" + qid + "'" + (j_esmq[i]["questions"][q]["data"]["conditions"][cond] == qid ? " selected" : "") + ">" + qid + "</option>");
						}
					}
					esmq_options += '</select>' +
						'</div>' +
						'</div>';
				}

				elem = '<div class="esmq-options"> <div class="esmq-legends"> <span style="margin-left: 12px">Choice</span> <span style="margin-left: 115px">To question #</span> </div>' + esmq_options + '<div class="esmq-option-add"> <a href="#">+</a> </div> </div>';
			} else if (q_esm_type == 3) {
				var esmq_options = "";
				for (var cond in j_esmq[i]["questions"][q]["data"]["conditions"]) {
					esmq_options = '<div class="esmq-option">' +
						'<div class="esmq-option-del">' +
						'<a href="#">-</a>' +
						'</div>' +
						'<div class="esmq-option-value long">' +
						'<input type="text" placeholder="Option" class="input-field" value="' + cond + '">' +
						'</div>' +
						'</div>';
				}

				elem = '<div class="esm-questionnaire-item option extra">Follow up</div><div class="esm-questionnaire-item value extra"> <select class="esmq-followup"><option value="false">End</option>' + q_ids + '</select> </div>';
				elem += '<div class="esmq-options"> <div class="esmq-legends"> <span style="margin-left: 12px">Choice</span> </div> <div class="esmq-option"> <div class="esmq-option-del"> <a href="#">-</a> </div> <div class="esmq-option-value long"> <input type="text" placeholder="Option" class="input-field"> </div> </div> <div class="esmq-option-add"> <a href="#">+</a> </div> </div>';
			} else if (q_esm_type == 4) {
				var esm_likert_max_label = j_esmq[i]["questions"][q]["data"]["esm"][0]["esm"]["esm_likert_max_label"];
				var esm_likert_min_label = j_esmq[i]["questions"][q]["data"]["esm"][0]["esm"]["esm_likert_min_label"];
				var esmq_options = "";
				for (var qid = 1; qid < j_esmq[i]["questions"].length + 1; qid++) {
					if (qid != q_id) {
						esmq_options += ("<option value='" + qid + "'" + (j_esmq[i]["questions"][q]["data"]["conditions"]["_any"] == qid ? " selected" : "") + ">" + qid + "</option>");
					}
				}
				elem = '<div class="esm-questionnaire-item option extra"> Max label </div> <div class="esm-questionnaire-item value extra"> <input type="text" class="input-field likert-max-label" value="' + esm_likert_max_label + '"> </div> <div class="esm-questionnaire-item option extra"> Min label </div> <div class="esm-questionnaire-item value extra"> <input type="text" class="input-field likert-min-label" value="' + esm_likert_min_label + '"> </div><div class="esm-questionnaire-item option extra">Follow up</div><div class="esm-questionnaire-item value extra"> <select class="esmq-followup"><option value="false">End</option>' + esmq_options + '</select> </div>';
			} else if (q_esm_type == 5) {
				var esmq_options = "";
				for (var cond in j_esmq[i]["questions"][q]["data"]["conditions"]) {
					esmq_options += '<div class="esmq-option">' +
						'<div class="esmq-option-del">' +
						'<a href="#">-</a>' +
						'</div>' +
						'<div class="esmq-option-value">' +
						'<input type="text" placeholder="Option" class="input-field" value="' + cond + '">' +
						'</div>' +
						'<div class="esmq-option-followup">' +
						'<select class="esmq-followup" type="hidden">' +
						'<option value="false">End</option>';
					for (var qid = 1; qid < j_esmq[i]["questions"].length + 1; qid++) {
						if (qid != q_id) {
							esmq_options += ("<option value='" + qid + "'" + (j_esmq[i]["questions"][q]["data"]["conditions"][cond] == qid ? " selected" : "") + ">" + qid + "</option>");
						}
					}
					esmq_options += '</select>' +
						'</div>' +
						'</div>';
				}
				elem = '<div class="esmq-options"> <div class="esmq-legends"> <span style="margin-left: 12px">Choice</span> <span style="margin-left: 115px">To question #</span> </div>' + esmq_options + ' <div class="esmq-option-add"> <a href="#">+</a> </div> </div>';
			}
			var html_q = '<div class="esmq-question" style="display: block;"> <input type="hidden" value="' + q_id + '" class="esmq-question-id"> <div class="esmq-question-head"> <div class="esmq-question-head-left">#' + q_id + '</div> <div class="esmq-question-head-right"> <a href="#">X</a> </div> </div> <div class="esmq-question-body"> <div class="esm-questionnaire-item option">Title</div> <div class="esm-questionnaire-item value"> <input type="text" class="input-field esmq-question-title" value=' + q_esm_title + '> </div> <div class="esm-questionnaire-item option"> Type </div> <div class="esm-questionnaire-item value"> <select class="esmq-question-type"> <option value="1"' + ((q_esm_type == 1) ? " selected" : "") + '>Free text</option> <option value="2"' + ((q_esm_type == 2) ? " selected" : "") + '>Radio</option> <option value="3"' + ((q_esm_type == 3) ? " selected" : "") + '>Checkbox</option> <option value="4"' + ((q_esm_type == 4) ? " selected" : "") + '>Likert</option> <option value="5"' + ((q_esm_type == 5) ? " selected" : "") + '>Quick answer</option> </select> </div> ' + elem + ' </div> </div>';
			html += html_q
		}
		// Close questionnaire
		html += '<div class="esmq-add-question"> <a href="#">Add a new question</a> </div> </div> </div>';
		htmls.push(html);
	}
	return htmls;
}

function getESMQConfig(questionnaires) {
	var ESMQ = [];
	var qns = questionnaires.find(".esm-questionnaire");

	for (var i = 0; i < qns.length; i++) {
		var startDate, endDate, days, starTime, endTime, interval, activeWeekdays = [];
		var qns_id = $(qns[i]).attr("id").split("-questionnaire")[1];
		var questionnaire = {
			"id": qns_id
		};

		startDate = new Date($(qns[i]).find(".esmq-startdate").val());
		endDate = new Date($(qns[i]).find(".esmq-enddate").val());

		startTime = $(qns[i]).find(".esmq-starttime").val().split(":");
		startTime = startTime[0] * 3600 + startTime[1] * 60;
		endTime = $(qns[i]).find(".esmq-endtime").val().split(":");;
		endTime = endTime[0] * 3600 + endTime[1] * 60;

		numberOfESM = $(qns[i]).find(".esmq-amount").val();
		interval = $(qns[i]).find("input.esmq-interval").filter(":checked").val();

		var esmq_weekdays = $(qns[i]).find(".esmq-weekday input");
		for (var m = 0; m < 7; m++) {
			activeWeekdays.push($(esmq_weekdays[m]).prop("checked"));
		}

		var step, sTime;
		var dayOfWeek = startDate.getDay() - 1;

		questionnaire["general"] = {
			"startDate": startDate.getTime() / 1000,
			"endDate": endDate.getTime() / 1000,
			"startTime": startTime,
			"endTime": endTime,
			"weekDays": activeWeekdays,
			"numberOfESM": numberOfESM,
			"interval": interval
		};

		// Timezone fix
		startDate.setTime(startDate.getTime() + startDate.getTimezoneOffset() * 60 * 1000);

		var trigger = {
			"operand": "AND",
			"triggers": []
		};

		for (var t = startDate.getTime() / 1000; t <= endDate.getTime() / 1000; t += 24 * 3600) {
			dayOfWeek = (dayOfWeek + 1) % 7;

			if (activeWeekdays[dayOfWeek]) {
				if (interval == "spaced") {
					step = (endTime - startTime) / numberOfESM;
					for (var x = 1; x <= numberOfESM; x++) {
						sTime = t + startTime + (step * (x - 1));
						trigger["triggers"].push({
							"type": "alarm",
							"data": {
								"timestamp": sTime * 1000
							}
						});
					}
				} else if (interval == "random") {
					for (var x = 1; x <= numberOfESM; x++) {
						var duration = endTime - startTime;
						step = Math.floor(Math.random() * (endTime - startTime) + 1);
						sTime = t + startTime + step;
						trigger["triggers"].push({
							"type": "alarm",
							"data": {
								"timestamp": sTime * 1000
							}
						});
					}
				}
			}
		}
		questionnaire["trigger"] = trigger;

		// Get questions
		var q = $(qns[i]).find(".esmq-question");
		var questions_data = [];

		for (var j = 0; j < q.length; j++) {
			var q_id = parseInt($(q[j]).find(".esmq-question-head-left").last().text().trim().split("#")[1]);
			var q_title = $(q[j]).find(".esmq-question-title").val();
			var q_choices = $(q[j]).find(".esmq-option");
			var q_choice_val, q_choice_fup;
			var question_conditions = {};
			var q_options = [];

			for (var k = 0; k < q_choices.length; k++) {
				q_choice_val = $(q_choices[k]).find(".esmq-option-value input").val();
				q_choice_fup = $(q_choices[k]).find(".esmq-followup").children("option").filter(":selected").val();
				question_conditions[q_choice_val] = q_choice_fup;
				q_options.push(q_choice_val);
			}

			// No conditions e.g., freetext
			if (!question_conditions.length && !q_options.length) {
				// Last question, quit in any case
				if (j == q.length-1) {
					question_conditions["_any"] = "false";
				} else {
					question_conditions["_any"] = "" + (q_id+1) + "";
				}
			}

			// Construct ESM
			var q_esm;
			var q_type = $(q[j]).find(".esmq-question-type").val();
			if (q_type == 1) {
				q_esm = {
					"esm_type": "1",
					"esm_title": q_title,
					"esm_instructions": "",
					"esm_submit": "Submit",
					"esm_expiration_threashold": "0",
					"esm_trigger": "esm-questionnaire_" + qns_id
				};
			} else if (q_type == 2) {
				q_esm = {
					"esm_type": "2",
					"esm_title": q_title,
					"esm_instructions": "",
					"esm_radios": q_options,
					"esm_submit": "Submit",
					"esm_expiration_threashold": "0",
					"esm_trigger": "esm-questionnaire_" + qns_id
				};

			} else if (q_type == 3) {
				q_esm = {
					"esm_type": "3",
					"esm_title": q_title,
					"esm_instructions": "",
					"esm_checkboxes": q_options,
					"esm_submit": "Submit",
					"esm_expiration_threashold": "0",
					"esm_trigger": "esm-questionnaire_" + qns_id
				};

			} else if (q_type == 4) {
				var q_likert_max_label = $(q[j]).find(".likert-max-label").val();
				var q_likert_min_label = $(q[j]).find(".likert-min-label").val();
				q_esm = {
					"esm_type": "4",
					"esm_title": q_title,
					"esm_instructions": "",
					"esm_likert_max": "5",
					"esm_likert_max_label": q_likert_max_label,
					"esm_likert_min_label": q_likert_min_label,
					"esm_likert_step": "1",
					"esm_submit": "Submit",
					"esm_expiration_threashold": "0",
					"esm_trigger": "esm-questionnaire_" + qns_id
				};
			} else if (q_type == 5) {
				q_esm = {
					"esm_type": "5",
					"esm_title": q_title,
					"esm_instructions": "",
					"esm_quick_answers": q_options,
					"esm_expiration_threashold": "0",
					"esm_trigger": "esm-questionnaire_" + qns_id
				};
			}

			questions_data.push({
				"id": q_id,
				"type": "esm",
				"data": {
					"esm": new Array ({
						"esm": q_esm
					}),
					"conditions": question_conditions
				}
			});
		}
		questionnaire["questions"] = questions_data;
		ESMQ.push(questionnaire);
	}
	return JSON.stringify(ESMQ);
}