// Get CSRF cookie
var cct = $.cookie('csrf_cookie_aware');

jQuery(document).ready(function() {
	var selectedChart;
	var prevSelectedChart;
	var sendingChart;
	var filtering;
	var postData = {};
	//Used to determine devices.
	var devices = [];
	var act = "../get_chart_preview";
	//If we edit chart
	//console.log("chart_params: " + $(".chart_params").length);
	if($(".chart_params").length != 0) {
		act = "../../get_chart_preview";
	}
	var chart_id;
	//Default is for creating new chart. AJAX call.
	var chartAction = "../set_new_chart";
	//Retrieve id.
	var id = $(".id").html();
	//console.log(id);
	$(".id").remove();
	//Retrieve table data.
	var data = $(".visualizations-chart_creation-tables").html();
	$(".visualizations-chart_creation-tables").remove();
	var tablesData = JSON.parse(data);

	//Events
    jQuery('.nav_bar #nav_top a').on('click', function(e)  {
		//If chart is not selected.
		if(selectedChart == undefined) {
			alert("Select A chart.");
			e.preventDefault();
			return;
		}
		
		var state = jQuery(this).attr('href');
        // Show/Hide Tabs
		enableView(state);
  		//Stop propagation
        e.preventDefault();
    });
    $( "#next" ).click(function(e) {
		//If chart is not selected.
		if(selectedChart == undefined) {
			alert("Select A chart.");
			return;
		}
		//Check active tab
		var currentId;
		jQuery("#nav_top").children('li').each(function(){
			//Takes the latest <a class='active'>
     		if(this.className == 'active')
			{
				currentId = $(this).children('a').attr('id');
				this.className = "";
			}
     	});
		
		if(selectedChart != prevSelectedChart)
		{
			//change states for every tab
			var a = document.getElementById('nav_data');
			a.href = '#data_selection_' + selectedChart;
			a = document.getElementById('nav_fine');
			a.href = '#fine_tuning_' + selectedChart;
			prevSelectedChart = selectedChart;
			currentId = 'nav_chart'
			//Clear postData and devices 
			postData = {};
			devices = [];
			//insert chart type into postData.
			postData["chart_type"] = selectedChart;
			//add dropdown menu data to the postData
			triggerData(selectedChart);
			console.log(postData);
		}
		//For the first time.
		if(prevSelectedChart == undefined)
		{
			prevSelectedChart = selectedChart;
		}

		if(currentId == 'nav_chart')
		{
			var dom = jQuery('#nav_data');
			sendingChart = undefined;
			changeTopNavBar(dom);
			enableView(dom.attr("href"));
			$("#next").html("Save");
			//Enable fine tuning button.
			$("#ft").parent().removeClass("disabled");
		}
		else if (currentId == 'nav_data')
		{
			var dom = jQuery('#nav_fine');
			changeTopNavBar(dom);
			
			sendingChart = selectedChart;
			saveChart();
			//enableView(dom.attr("href"));
		}
		else if (currentId == 'nav_fine')
		{
			saveChart();
		}
		else {}
		e.preventDefault();
		
    });

	$(".window_middle_right").on('click', function() {
		//Add data to post
		
		if(chart_id != undefined) postData["chart_id"] = chart_id;
		if(devices.length != 0) postData["devices"] = devices.join();
		var div = $(this);
		var temp = JSON.stringify(postData);
		div[0].style.backgroundImage = 'none';
		div[0].innerHTML = '<div id="circularG" style="margin-left: auto; margin-right: auto;"><div id="circularG_1" class="circularG"></div><div id="circularG_2" class="circularG"></div><div id="circularG_3" class="circularG"></div><div id="circularG_4" class="circularG"></div><div id="circularG_5" class="circularG"></div><div id="circularG_6" class="circularG"></div><div id="circularG_7" class="circularG"></div><div id="circularG_8" class="circularG"></div></div>';
		//Disable events
		div.css('pointer-events', "none");
		//Send data..
		console.log("sendAjax");
		//console.log(temp);
		//AJAX call
		$.ajax({
		//Change to set_new_chart when ready
		url: act,
		type: "POST",
		data: { postData : temp, 'study_id' : id, 'csrf_token_aware' : cct},
		success: function(data) {
			var img = $('<div>' + data + '</div>').find(".i");//[0].html();
			//console.log(img);
			console.log("AjaxSUcces");
			div[0].innerHTML = '';
			div[0].style.backgroundImage = "url('" + img.html() + "')";
			//Enable events
			div.css('pointer-events', "auto");
		},
		// Alert status code and error if fail
	    error: function (xhr, ajaxOptions, thrownError){
	        console.log("xhr.status: " + xhr.status);
	        console.log("error: " + thrownError);
	        div[0].innerHTML = '';
	        alert("Error during creating preview...");
	    	div.css('pointer-events', "auto");
	    }
		});
	});


    $( "#cancel" ).click(function() {
        var form = document.createElement("form");
        var input = document.createElement("input");
        input.type = "submit";
        input.style.display = "none";
        form.appendChild(input);
        $(document.body).append(form);
        form.action = '/index.php/visualizations/study/' + id;
        form.submit();
    });
    
    $( "#ft" ).click(function() {
    	var dom = jQuery('#nav_fine');
		changeTopNavBar(dom);
		$("#next").html("Save");
		sendingChart = selectedChart;
		enableView(dom.attr("href"));
    });
    $( ".img_clicable" ).click(function(e) {
    	//Make img borders white
     	$(".img_clicable").each(function(){
     		this.style.border = "2px solid white";
     	});
     	this.style.border = "2px solid #5B9BD5";
		//save selected chart id
		selectedChart = this.id;
		if(selectedChart != sendingChart) {
			$("#next").html("Next");
			//Disable fine tuning button.
			$("#ft").parent().addClass("disabled");
		}
		else {
			$("#next").html("Save");
			//Disable fine tuning button.
		$("#ft").parent().removeClass("disabled");
		}
    });
	//Make imgs not draggable
	$(".img_clicable").each(function(){
		this.draggable = false;
    });
	//Make a tags not draggable
	$("a").each(function(){
		this.draggable = false;
    });
    //update y-axis and x-axis dropdown
    $(".table_name").change( function() {
		
		var selectedVal = $(this).find(':selected').val();
		//console.log("sVal: "+ selectedVal);
	 	var selectedObject = findJsonObject(tablesData, selectedVal);

	 	//Get options for the dropdownmenus.
	 	var options = getAxisOptions(selectedObject);
	 	//filter possibility
	 	var list = transform(options);
	 	filtering = filter(selectedVal,list,selectedChart);
	 	var none = "<option></option>";
	 	
	 	//Put options into dropdowns.
	 	if (selectedChart != 'scatter' && selectedChart != 'line' && selectedChart != 'box') {
		$(".y").html(none + filtering);
		}else {
		$(".y").html(filtering);
		}

		$(".x").html(filtering);
		$(".colour").html(none + filtering);
		$(".slicing").html(filtering);
		$(".angle").html(filtering);
		$(".clustering").html(none + filtering);
		$(".fill").html(none + filtering);
		$(".y, .x, .colour, .slicing, .angle, .clustering, .fill").trigger("change");

    });
    //filter
    function filter(val,object ,chartType){
    	var option = [];
    	if (chartType == 'column') {
    		//console.log('column chart');
    		switch(val){
    			case 'gravity': option = nocontain(object,'double');
    			break;
    			case 'gyroscope': option = nocontain(object,'axis');
    			break;
    			case 'accelerometer': option = nocontain(object,'double');
    			break;
    			case 'linear_accelerometer': option = nocontain(object,'double');
    			break;
    			case 'barometer': option = nocontain(object,'double');
    			break;
    			case 'processor': option = nocontain(object,'double');
    			break;
    			case 'network_traffic': option = nocontain(object,'double');
    									option = nocontain(option,'type');
    			break;
    			case 'battery': option = nocontain(object,'status');
    							option = nocontain(option,'level');
    							option = nocontain(option,'voltage');
    							option = nocontain(option,'temperature');
    							option = nocontain(option,'adaptor');
    			break;
    			case 'light': option = nocontain(object,'lux');
    			break;
    			case 'screen': option = nocontain(object,'status');
    			break;
    			case 'plugin_device_usage': option = nocontain(object,'elapsed');
    			break;
    			case 'network': option = nocontain(object,'state');
    			break;
    			case 'battery_charges': option = nocontain(object,'battery');
    									option = nocontain(option,'double');
    			break;
    			case 'battery_discharges': option = nocontain(object,'battery');
    									   option = nocontain(option,'double');
    			break;
    			default: option = object;
    			break;
    		}
    	};
    	if (chartType == 'pie') {
			//console.log('pie chart');
    		switch(val){
    			case 'gravity': option = nocontain(object,'double');
    			break;
    			case 'gyroscope': option = nocontain(object,'axis');
    			break;
    			case 'accelerometer': option = nocontain(object,'double');
    			break;
    			case 'linear_accelerometer': option = nocontain(object,'double');
    			break;
    			case 'barometer': option = nocontain(object,'double');
    			break;
    			case 'processor': option = nocontain(object,'double');
    			break;
    			case 'network_traffic': option = nocontain(object,'double');
    									option = nocontain(option,'type');
    			break;
    			case 'battery': option = nocontain(object,'status');
    							option = nocontain(option,'level');
    							option = nocontain(option,'voltage');
    							option = nocontain(option,'temperature');
    							option = nocontain(option,'adaptor');
    			break;
    			case 'light': option = nocontain(object,'lux');
    			break;
    			case 'screen': option = nocontain(object,'status');
    			break;
    			case 'plugin_device_usage': option = nocontain(object,'elapsed');
    			break;
    			case 'network': option = nocontain(object,'state');
    			break;
    			case 'battery_charges': option = nocontain(object,'battery');
    									option = nocontain(option,'double');
    			break;
    			case 'battery_discharges': option = nocontain(object,'battery');
    									   option = nocontain(option,'double');
    			break;
    			default: option = object;
    			break;
    		}
    	};
		if (chartType == 'histogram') {
			//console.log('histogram chart');
			switch(val){
    			case 'gravity': option = object; option.push('axis_combination');
    			break;
    			case 'gyroscope': option = object; option.push('axis_combination');
    			break;
    			case 'accelerometer': option = object; option.push('axis_combination');
    			break;
    			case 'linear_accelerometer': option = object; option.push('axis_combination');
    			break;
    			default: option = object;
    			break;
    		}
		};
		if (chartType == 'scatter') {
			//console.log('scatter chart');
			switch(val){
    			case 'gravity': option = object; option.push('axis_combination');
    			break;
    			case 'gyroscope': option = object; option.push('axis_combination');
    			break;
    			case 'accelerometer': option = object; option.push('axis_combination');
    			break;
    			case 'linear_accelerometer': option = object; option.push('axis_combination');
    			break;
    			default: option = object;
    			break;
    		}
		};
		if (chartType == 'line') {
			//console.log('line chart');
			switch(val){
    			case 'gravity': option = object; option.push('axis_combination');
    			break;
    			case 'gyroscope': option = object; option.push('axis_combination');
    			break;
    			case 'accelerometer': option = object; option.push('axis_combination');
    			break;
    			case 'linear_accelerometer': option = object; option.push('axis_combination');
    			break;
    			default: option = object;
    			break;
    		}
		};
		if (chartType == 'box') {
			//console.log('box chart');
			switch(val){
    			case 'gravity': option = object; option.push('axis_combination');
    			break;
    			case 'gyroscope': option = object; option.push('axis_combination');
    			break;
    			case 'accelerometer': option = object; option.push('axis_combination');
    			break;
    			case 'linear_accelerometer': option = object; option.push('axis_combination');
    			break;
    			default: option = object;
    			break;
    		}
		};
    	
    	var size = option.length;
    	var txt = "";
    	for (var i = 0; i < size; i++) {
    		txt += "<option>"+option[i]+"</option>\n";
    	}
    	return txt;
    }
    //function contain in string
    function nocontain(selected, strings){
    	var max = selected.length;
    	var option = [];
    	for (var i = 0; i < max; i++) {
    		if (selected[i].indexOf(strings) > -1) {
    			
    		}else option.push(selected[i]);
    	}
    	return option;
    }
    function transform(select){
    	var list = select.split("<option>");
    	var size = list.length;
    	var txt = "";
    	for (var i = 0; i < size; i++) {
    		txt += list[i];
    	}
    	list = txt.split("</option>");
    	list.pop();
    	size = list.length;
    	for (var i = 1; i < size; i++) {
    		list[i] = list[i].slice(1);
    	}
    	//console.log(list);
    	return list;
    }
	//Dropdown menu listeners Dataselection
	$(".y").change(function() {
    var selectedVal = this.value;
    postData[this.className] = selectedVal;
	});
	$(".x").change(function() {
    var selectedVal = this.value;
    postData[this.className] = selectedVal;
	});
	$(".clustering").change(function() {
    var selectedVal = this.value;
	postData[this.className] = selectedVal;
	});
	$(".fill").change(function() {
    var selectedVal = this.value;
    postData[this.className] = selectedVal;
	});
	$(".table_name").change(function() {
    var selectedVal = this.value;
    postData[this.className] = selectedVal;
	});
	$(".calculations").change(function() {
    var selectedVal = this.value;
    postData[this.className] = selectedVal;
	});
	$(".labels").change(function() {
    var selectedVal = this.value;
    postData[this.getAttribute("name")] = selectedVal;
	});
	$(".colour").change(function() {
    var selectedVal = this.value;
    postData[this.className] = selectedVal;
	});
	$(".slicing").change(function() {
    var selectedVal = this.value;
    postData[this.className] = selectedVal;
	});
	$(".angle").change(function() {
    var selectedVal = this.value;
    postData[this.className] = selectedVal;
	});

	//Fine tuning
	$(".limit").change(function() {
    var selectedVal = this.value;
    postData[this.getAttribute("name")] = selectedVal;
	});

	//Checkbox events
	$(".select_all").click(function() {
		var cbx = $(this).parent().siblings().children("input");
		if(this.checked) {
			cbx.each(function() {
				if(!this.checked) {
					$(this).trigger("click");
				}
			});
		} 
		else {
			cbx.each(function() {
				if(this.checked) {
					$(this).trigger("click");
				}
			});
	 	}
	});

	$(".device_checkbox").click(function() {
		if(this.checked) {
			//Add new value
			devices[devices.length] = this.value;
		} 
		else {
			//Remove device from devices.
			devices.splice(devices.indexOf(this.value), 1);
	 	}
	});

	$(".stat_cbx").click(function() {
		var dropdown = $(this).parent().children("select")[0];
		if(this.checked) {
			postData[dropdown.name] = dropdown.value;
		} 
		else {
			delete postData[dropdown.name];
	 	}
	});

	//Dropdown stat
	$(".stat_select").change(function() {
		var cbx = $(this).parent().children("input[type='checkbox']")[0];
		if(cbx.checked) {
			postData[this.name] = this.value;
		} 
		else {
			delete postData[this.name];
	 	}
	});

    // Initalize accordion
    $('.accordion').accordion({
    	heightStyle: "content",
    	collapsible: 'true'
    });
    //Make select all checkboxes to checked.
    $(".select_all").attr("checked", "true");
    // Populate tables
    $('.table_name').trigger('change');

    //Initialize Datetimepickers...
    var startDateTextBox = $('.startTime');
	var endDateTextBox = $('.endTime');

	startDateTextBox.datetimepicker({ 
		dateFormat: 'MM dd, yy',
		timeFormat: 'HH:mm:ss',
		separator: " ",
		changeMonth: true,
     	changeYear: true,
		onClose: function(dateText, inst) {
	 		var d = new Date(dateText);
	 		d.setTime( d.getTime() - d.getTimezoneOffset()*60*1000 );
	 		if(d != "Invalid Date") 
	 		{	//IN SECONDS
	 			postData["startTime"] = d.getTime() / 1000;
	 		}
		}
		
	});
	endDateTextBox.datetimepicker({ 
		dateFormat: 'MM dd, yy',
		timeFormat: 'HH:mm:ss',
		separator: " ",
		changeMonth: true,
      	changeYear: true,
		onClose: function(dateText, inst) {
			var d = new Date(dateText);
			d.setTime( d.getTime() - d.getTimezoneOffset()*60*1000 );
	 		if(d != "Invalid Date") 
	 		{	//IN SECONDS
	 			postData["endTime"] = d.getTime() / 1000;
	 		}
		},
		onSelect: function (selectedDateTime){
			startDateTextBox.datetimepicker('option', 'maxDate', endDateTextBox.datetimepicker('getDate') );
		}

	});
	//Set datetime settings
/*	startDateTextBox.datetimepicker('setDate', (new Date()) );
	endDateTextBox.datetimepicker('setDate', (new Date()) );
	endDateTextBox.datetimepicker('option', 'maxDate', endDateTextBox.datetimepicker('getDate'));
	startDateTextBox.datetimepicker('option', 'maxDate', endDateTextBox.datetimepicker('getDate') );
	*/
	
	/*$(".colorpicker").spectrum({
	    color: "#ECC",
	    showInput: true,
	    className: "full-spectrum",
	    showInitial: true,
	    showPalette: true,
	    showSelectionPalette: true,
	    maxPaletteSize: 10,
	    preferredFormat: "hex",
	    clickoutFiresChange: true,

	    move: function (color) {
	        
	    },
	    show: function () {
	    
	    },
	    beforeShow: function () {
	    
	    },
	    hide: function () {

	    },
	    change: function(color) {
    	postData[this.getAttribute("name")] = $(this).val();
	    },
	    palette: [
	        ["rgb(0, 0, 0)", "rgb(67, 67, 67)", "rgb(102, 102, 102)",
	        "rgb(204, 204, 204)", "rgb(217, 217, 217)","rgb(255, 255, 255)"],
	        ["rgb(152, 0, 0)", "rgb(255, 0, 0)", "rgb(255, 153, 0)", "rgb(255, 255, 0)", "rgb(0, 255, 0)",
	        "rgb(0, 255, 255)", "rgb(74, 134, 232)", "rgb(0, 0, 255)", "rgb(153, 0, 255)", "rgb(255, 0, 255)"], 
	        ["rgb(230, 184, 175)", "rgb(244, 204, 204)", "rgb(252, 229, 205)", "rgb(255, 242, 204)", "rgb(217, 234, 211)", 
	        "rgb(208, 224, 227)", "rgb(201, 218, 248)", "rgb(207, 226, 243)", "rgb(217, 210, 233)", "rgb(234, 209, 220)", 
	        "rgb(221, 126, 107)", "rgb(234, 153, 153)", "rgb(249, 203, 156)", "rgb(255, 229, 153)", "rgb(182, 215, 168)", 
	        "rgb(162, 196, 201)", "rgb(164, 194, 244)", "rgb(159, 197, 232)", "rgb(180, 167, 214)", "rgb(213, 166, 189)", 
	        "rgb(204, 65, 37)", "rgb(224, 102, 102)", "rgb(246, 178, 107)", "rgb(255, 217, 102)", "rgb(147, 196, 125)", 
	        "rgb(118, 165, 175)", "rgb(109, 158, 235)", "rgb(111, 168, 220)", "rgb(142, 124, 195)", "rgb(194, 123, 160)",
	        "rgb(166, 28, 0)", "rgb(204, 0, 0)", "rgb(230, 145, 56)", "rgb(241, 194, 50)", "rgb(106, 168, 79)",
	        "rgb(69, 129, 142)", "rgb(60, 120, 216)", "rgb(61, 133, 198)", "rgb(103, 78, 167)", "rgb(166, 77, 121)",
	        "rgb(91, 15, 0)", "rgb(102, 0, 0)", "rgb(120, 63, 4)", "rgb(127, 96, 0)", "rgb(39, 78, 19)", 
	        "rgb(12, 52, 61)", "rgb(28, 69, 135)", "rgb(7, 55, 99)", "rgb(32, 18, 77)", "rgb(76, 17, 48)"]
	    ]
	});*/


	//If we dont edit chart
	if($(".chart_params").length == 0)
	{
		//Set only Chart creation visible.
		changeTopNavBar(jQuery('#nav_chart'));
		//Disable fine tuning button.
		$("#ft").parent().addClass("disabled");
	}
	else
	{
		var chart_types =  {
			"geom_line()" : "line",
			"geom_histogram()" : "histogram",
			"geom_bar()" : "column",
			"geom_point()" : "scatter",
			"geom_boxplot()" : "box",
			"geom_bar() + coord_polar(\"y\")" : "pie"

		};
		var dropdowns = ["x", "y", "colour", "fill", "angle"];
		var accordions = ["limit", "ggtitle", "xlab", "ylab", "width", "height", "dpi"];
		var colors = ["color_y_axis", "color_x_axis", "color_grouping"];
		var times = ["startTime", "endTime"];
		var stats = ["stat_mean", "stat_min", "stat_max", "stat_median"];
		//Get server data.
		var data = $(".chart_params").html();
		$(".chart_params").remove();
		var chart_params = JSON.parse(data);
		//console.log("server data: " + data);
		
		var selected = {};
		$(chart_params).each(function( index, item ){
			selected[item["r_key"]] = item["r_value"];	
		});
		chart_id = chart_params[0]["chart_id"];

		//Get chart type.
		var chart_type = chart_types[selected["chart_type"]];
		//Put on the post data.
		postData["chart_type"] = chart_type;

		//Choose this chart as a sending chart and a selected chart
		selectedChart = chart_type;
		prevSelectedChart = chart_type;
		sendingChart = chart_type;

		//console.log("selected: " + selected["table_name"]);
		$(".table_name").val(selected["table_name"]);
		$(".table_name").trigger('change');

		//Make items selected... loops through dropdowns and limit
		$.each(selected,  function( index, value ) {
  			//$.inArray(value, array) returns -1 if not in array
  			if($.inArray(index, dropdowns) != -1)
  			{
  				$("."+index).each( function () {
  					//console.log("index: " + index +  " value: "+ value);
  					//console.log(this);
	  				$(this).val(value);
	  				$(this).trigger("change");
  				});
  			}
  			else if($.inArray(index, accordions) != -1)
  			{
  				$("input[name="+index+"]").each( function () {
	  				$(this).val(value);
	  				$(this).trigger("change");
  				});
  			}
  			else if ($.inArray(index, colors) != -1) {
  				$("input[name="+index+"]").each( function () {
	  				$(this).spectrum("set", value);
	  				$(this).trigger("change");
	  				postData[index] = value;
  				});
  			}
  			else if ($.inArray(index, times) != -1) {
  				$("input[name="+index+"]").each( function () {
  					//Timezone fix
  					var d = new Date(value * 1000);
  					d.setTime( d.getTime() + d.getTimezoneOffset()*60*1000); 
  					//Multiply 1000 because javascript wants milliseconds.
  					$(this).datetimepicker('setDate', d );
	  				postData[index] = value;
  				});
  			}
  			else if (index == "devices") {
  				//Get array of selected devices.
  				var de = value.split(",");
  				for (var i = de.length - 1; i >= 0; i--) {
  					$("input[value=" + de[i] + "]").attr("checked", true);
  				};
  				devices = de;
  			}
  			else if ($.inArray(index, stats) != -1) {
  				$("select[name=" + index + "]").each(function () {
  					$(this).val(value);
  				});
  				$("input[name=" + index +"]").each(function() {
  					$(this).trigger("click");
  				});
  			}
		});
		//console.log(postData);
		//console.log("devices:" + devices);
		//Enable navigation. Go to data selection first. Then trigger next button.
		changeTopNavBar(jQuery('#nav_data'));

		//change states for every tab 
		var a = document.getElementById('nav_data');
		a.href = '#data_selection_' + chart_type;
		a = document.getElementById('nav_fine');
		a.href = '#fine_tuning_' + chart_type;

		//Select correct chart at 1st view.
		$("#"+chart_type).trigger("click");

		//Set view for the fine tuning by clicking fine tuning button
		$("#ft").trigger("click");

		//Change AJAX call for the next button.
		chartAction = "../../update_chart";
	}

	//For saving chart
	function saveChart() {
		//Add data to post
		if(chart_id != undefined) postData["chart_id"] = chart_id;
		if(devices.length != 0) postData["devices"] = devices.join();

		var temp = JSON.stringify(postData);
		//Send data..
		console.log("sendAjax");
		console.log(temp);
		//Block UI
		$.blockUI( 
	    { 
	        css: { 
	           // border: '5px solid #5B9BD5'
	        },
	        overlayCSS:  { 
	            backgroundColor: '#5B9BD5', 
	            opacity:         0.6, 
	            cursor:          'wait' 
	        }, 
	        //theme: true, If theme is on, our css modifications wont work
	        message: "<p> Please wait... </p>"
	    });
		$.ajax({
		//Change to set_new_chart when ready
		url: chartAction,
		type: "POST",
		data: { postData : temp, 'study_id' : id, 'csrf_token_aware' : cct},
		success: function(data) {
			console.log("AjaxSUcces");
			console.log(data);
			$.unblockUI();
			//Redirect to visualizations
			$( "#cancel" ).trigger('click');
		},
		// Alert status code and error if fail
	    error: function (xhr, ajaxOptions, thrownError){
	        console.log("xhr.status: " + xhr.status);
	        console.log("error: " + thrownError);
	        alert("Error during creating chart...");
	    }
	});
}
}); //END ON PAGE READY

//Helper functions
function changeTopNavBar(dom) {
	dom.parent('li').addClass('active');
	dom.parent('li').prevAll('li').addClass('active');
	dom.parent('li').css("display","inline");
	dom.parent('li').prevAll('li').css("display","inline");
	dom.parent('li').nextAll('li').css("display","none");
}

function enableView(selectedView) {
//Show/hide Tabs
jQuery('.window_container ' + selectedView).show().siblings().hide();

}
function findJsonObject(tablesData, selectedVal) {
	//console.log(tablesData);
	for (var i = 0; i<tablesData.length; i++) {
		//console.log(tablesData[i]);
		if(tablesData[i].TABLE_NAME == selectedVal) {
			//console.log("sval: " + selectedVal);
			//console.log("table_name: " + tablesData[i].TABLE_NAME);
			return tablesData[i];
		}
	}
	return undefined;
}

function getAxisOptions(selectedObject){
	var temp = "";
	for (var i = 0; i < selectedObject.TABLE_COLUMNS.length; i++) {
		temp +=	"<option>" + selectedObject.TABLE_COLUMNS[i].COLUMN_NAME + "</option>\n";
	};
	return temp;
}
//Sets data to postData variable
function triggerData(selectedChart) {
    var ds = "#data_selection_"+selectedChart + " ";
    var ft = "#fine_tuning_"+selectedChart + " ";
    // Populate tables
    $(ds + 'select').trigger('change');
    $(ft + 'input').trigger('change');
    $(ft + '.device_checkbox').trigger('click');
	//Continue..   
   /* $(ft + '.colorpicker').each(function() {
	//this.spectrum("option", "change");
	});*/
    //Range color and dont work
    //$(ft + '.hasDatepicker').trigger('onClose');
}
