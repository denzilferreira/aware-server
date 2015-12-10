var cct = $.cookie('csrf_cookie_aware');

jQuery(document).ready(function(){

	$(document).on('change',function() {
		$('#reload').css('display','block');
	}
	);

	$(document).on('click','#reload',function() {
		location.reload();
	}
	);
	
	var url = window.location.protocol+'//'+window.location.host+'/';
	var addimg = '<img src="'+url+'application/views/images/developer/add.png" title="Add new" class="add_image" height="24" alt="">';
	var removeimg = '<img src="'+url+'application/views/images/delete_icon.png" title="Remove" class="remove_image" height="12" alt="">';
	var extraimg = '<img src="'+url+'application/views/images/developer/new_extra.png" title="Add a new extra to a broadcast" class="addextra_image" height="24" alt="">';
	var newsettingcount = 1;
	var newbroadcastcount = 1;
	var newcontextprovidercount = 1;
	var newtablefieldcount = 1;
	var newbroadcastextracount = 1;

	//$('.add-setting').click(function() {
	$(document).on('click','#add-setting', function() {
		
		var $newdiv = 
				["<div id='new_setting"+newsettingcount+"' class='new_setting'>",
				"<a id='remove-settings' class='remove-button'>"+removeimg+"</a>",
				"<select class='plugin_settings_type' name='new"+newsettingcount+":plugin_setting_type'>",
					"<option value='integer'>Integer</option>",
					"<option value='real'>Real</option>",
					"<option value='text'>Text</option>",
				"</select>",
				"<input class='plugin_settings' type='text' maxlength='400' name='new"+newsettingcount+":plugin_setting' placeholder='setting'>",
				"<input class='plugin_settings_desc' type='text' maxlength='400' name='new"+newsettingcount+":plugin_setting_desc' placeholder='description'>",
				"</div>",
				"<div class='add-setting-button' >",
				"<a id='add-setting'>"+addimg+"</a>",
				"</div>"
				].join('\n');
			$(this).parent().remove();
			$(".settings").append( $newdiv );
			newsettingcount += 1;
	}
	);
	
	
	$(document).on('click','#remove-settings', function() {
		$(this).parent().find('.plugin_settings').val('this_field_was_removed');
		$(this).parent().find('.plugin_settings_desc').val('this_field_was_removed');
		//var $count = $('.plugin_settings').length;
		var $count = $('.plugin_settings:visible').length; //count only visible elements
		var $newsetting = false;
		if($(this).parent().attr('id').substring(0,3)=="new"){
			$newsetting = true;
		}
		else $newsetting = false;
		//alert($newsetting+":"+$(this).parent().attr('id')+"count: "+$count);
		if ($count > 1) {
			if ($newsetting) $(this).parent().remove(); 
			else $(this).parent().toggle();
			//either hide or remove
			//if it's a new setting, then remove, old settings: toggle.
		}
		else {
			if ($newsetting) $(this).parent().remove(); 
			else $(this).parent().toggle();
			var $newsetting =
				["<div id='new_setting"+newsettingcount+"' class='new_setting'>",
				"<a id='remove-settings' class='remove-button'>"+removeimg+"</a>",
				"<select class='plugin_settings_type' name='new"+newsettingcount+":plugin_setting_type'>",
					"<option value='integer'>Integer</option>",
					"<option value='real'>Real</option>",
					"<option value='text'>Text</option>",
				"</select>",
				"<input class='plugin_settings' type='text' maxlength='400' name='new"+newsettingcount+":plugin_setting' placeholder='setting'>",
				"<input class='plugin_settings_desc' type='text' maxlength='400' name='new"+newsettingcount+":plugin_setting_desc' placeholder='description'>",
				"</div>",
				"<div class='add-setting-button' >",
				"<a id='add-setting'>"+addimg+"</a>",
				"</div>"
				].join('\n');
			$(".add-setting-button").remove();
			$(".settings").append( $newsetting );
		}
	}
	);
	
	$(document).on('click','#remove-broadcast', function() {
		var $parentdiv = $(this).parent();
		var $isnew = false;
		if($(this).parent().attr('id').substring(0,3)=="new") {
			$isnew = true;
		}
		var $count = $('.plugin_broadcast:visible').length;
		if ($count > 1) {
			if ($isnew) ($parentdiv).remove(); //remove new broadcasts
			else {
				($parentdiv).toggle(); //hide old ones
				($parentdiv).find('.plugin_broadcast').val('this_field_was_removed');
				($parentdiv).find('.plugin_broadcast_desc').val('this_field_was_removed');
				($parentdiv).find('.plugin_extras').val('this_field_was_removed');
				($parentdiv).find('.plugin_extras_desc').val('this_field_was_removed');
			}
		}
		else {
			if ($isnew) ($parentdiv).remove();
			else ($parentdiv).toggle();
			addbroadcast(); //function call that will add the new div including the inputs, and will recreate the add-button
		}
	}
	);
	
	$(document).on('click', '#remove-broadcastextra', function() {
		var $parentdiv = $(this).parent();
		var $isnew = false;
		if($(this).parent().attr('id').substring(0,3)=="new") {
			$isnew = true;
		}
		if ($isnew) ($parentdiv).remove();
		else {
			($parentdiv).toggle();
			($parentdiv).find('.plugin_extras').val('this_field_was_removed');
			($parentdiv).find('.plugin_extras_desc').val('this_field_was_removed');
		}
	}
	);
	
	function addbroadcast() {
		var $newdiv =
			['<div id="new_broadcast'+newbroadcastcount+'" class="new_broadcast">',
			'<a id="remove-broadcast" class="remove-button">'+removeimg+'</a>',
			'<input class="plugin_broadcast" type="text" maxlength="400" name="new'+newbroadcastcount+':plugin_broadcast" placeholder="broadcast">',
			'<input class="plugin_broadcast_desc" type="text" maxlength="400" name="new'+newbroadcastcount+':plugin_broadcast_desc" placeholder="description">',
			'<div class="add-newextra-button"><a class="add-newextra">'+extraimg+'</a></div>',
			'</div>',
			'<div class="add-broadcast-button" >',
			'<a class="add-broadcast">'+addimg+'</a>',
			'</div>'
			].join('\n');
		$('.add-broadcast-button').remove();
		$('#broadcasts').append( $newdiv );
		newbroadcastcount += 1;
	}
	
	$(document).on('click','.add-broadcast', addbroadcast);		
	
	$(document).on('click','.add-newextra', function() {
		var broadcastdiv = $(this).parent().parent();
		var broadcastid = $(broadcastdiv).attr('id').replace( /^\D+/g, ''); //get only the id number
		var isnew = "";
		if ( $(broadcastdiv).attr('id').substr(0,3)=="new" ) isnew = "new";
		var $newdiv = 
				["<div id='new_extra-"+broadcastid+"-"+newbroadcastextracount+"' class='new_broadcast_extras'>",
				"<a id='remove-broadcastextra' class='remove-button'>" +removeimg+ "</a>",
				'<input class="plugin_extras" type="text" maxlength="400" name="new'+newbroadcastextracount+':plugin_broadcastextra:'+isnew+broadcastid+'" placeholder="broadcast extra">',
				'<input class="plugin_extras_desc" type="text" maxlength="400" name="new'+newbroadcastextracount+':plugin_broadcastextra_desc:'+isnew+broadcastid+'" placeholder=description>',
				"</div>"
				].join('\n');
		$(this).parent().parent().append( $newdiv );
		newbroadcastextracount += 1;
	}
	);
	
	$(document).on('click', '.add-context', function() {
		var $newdiv =
			["<div id='new_contextprovider-"+newcontextprovidercount+"' class='new_contextprovider'>",
			"<a id='remove-contextprovider' class='remove-button'>" +removeimg+"</a>",
			"<input class='context_providers' type='text' maxlength='400' name='new"+newcontextprovidercount+":context_providers' placeholder='context provider'>",
			"<input class='context_providers_uri' type='text' maxlength='100' name='new"+newcontextprovidercount+":context_providers_uri' placeholder='uri'>",
			"<input class='context_provider_desc' type='text' maxlength='400' name='new"+newcontextprovidercount+":context_provider_desc' placeholder='description'>",
			"</div>",
			'<div class="add-context-button" >',
			'<a class="add-context">'+addimg+'</a>',
			'</div>'
			].join('\n');
		$(this).parent().remove();
		$('#context_providers').append( $newdiv );
		
		if ($('.tables-not-found').length){
			$('.tables-not-found').remove();
			$('#new_table-0').toggle();
		}
		var $newtable = 
			[			
			'<div id="new_table-'+newcontextprovidercount+'" class="table">',
			'<div id="new_table'+newcontextprovidercount+'" class="new_table_name">unnamed</div>',
			'<div id="newtablefield-'+newtablefieldcount+'" class="newtablefields">',
			'<a id="remove-tablefield" class="remove-button">'+removeimg+'</a>',
			'<input class="table_name" type="text" maxlength="40" name="new'+newtablefieldcount+':table_name:new'+newcontextprovidercount+'" placeholder="name">',
			'<div id="tableselect"><select class="table_type" name="new'+newtablefieldcount+':table_type:new'+newcontextprovidercount+'"><option value="NULL">NULL</option><option value="INTEGER">INTEGER</option><option value="REAL">REAL</option><option value="TEXT">TEXT</option><option value="BLOB">BLOB</option></select></div>',
			'<input class="table_desc" type="text" maxlength="400" size="50" name="new'+newtablefieldcount+':table_desc:new'+newcontextprovidercount+'" placeholder="description">',
			'</div>',
			'<div class="add-table-button">',
			'<a class="add-table">'+addimg+'</a>',
			'</div>',
			'</div>'
			].join('\n');
		$('#tables').append( $newtable );
		newcontextprovidercount += 1;
		newtablefieldcount += 1;
	}
	);
	
	$(document).on('click', '#remove-contextprovider', function() {
		var $parentdiv = $(this).parent();

		var $id = $parentdiv.attr('id').split('-');
		$tablename="";
		var $count = $('.context_providers:visible').length;
		if ($id[0] == "context_provider") {
			//$("#table-"+$id[1]).toggle();
			//$("#table-"+$id[1]).empty();
			//$("#table-"+$id[1]).remove();
			($parentdiv).find('.context_providers').val('this_field_was_removed');
			($parentdiv).find('.context_providers_uri').val('this_field_was_removed');
			($parentdiv).find('.context_provider_desc').val('this_field_was_removed');
			$tablename = "table-";
			($parentdiv).toggle();
			$("#"+$tablename+$id[1]).remove();
		}
		else if ($id[0] == "new_contextprovider") {
			$tablename = "new_table";
			($parentdiv).remove();
			$("#"+$tablename+$id[1]).parent().remove();
		}
		
		if ($count == 1) {
			//$("#tables").empty();
			var $newdiv =
				["<div id='new_contextprovider-"+newcontextprovidercount+"' class='new_contextprovider'>",
				"<a id='remove-contextprovider' class='remove-button'>" +removeimg+"</a>",
				"<input class='context_providers' type='text' maxlength='400' placeholder='table name'>",
				"<input class='context_providers_uri' type='text' maxlength='100' placeholder='uri'>",
				"<input class='context_provider_desc' type='text' maxlength='400' placeholder='description'>",
				"</div>",
				'<div class="add-context-button" >',
				'<a class="add-context">'+addimg+'</a>',
				'</div>'
				].join('\n');
			$('.add-context-button').remove();
			$('#context_providers').append( $newdiv );
			var $newtable = 
			[			
			'<div id="new_table-'+newcontextprovidercount+'" class="table">',
			'<div id="new_table'+newcontextprovidercount+'" class="new_table_name">unnamed</div>',
			'<div id="newtablefield-'+newtablefieldcount+'" class="newtablefields">',
			'<a id="remove-tablefield" class="remove-button">'+removeimg+'</a>',
			'<input class="table_name" type="text" maxlength="40" name="new'+newtablefieldcount+':table_name:'+newcontextprovidercount+'" placeholder="name">',
			'<div id="tableselect"><select class="table_type" name="new'+newtablefieldcount+':table_type:'+newcontextprovidercount+'"><option value="NULL">NULL</option><option value="INTEGER">INTEGER</option><option value="REAL">REAL</option><option value="TEXT">TEXT</option><option value="BLOB">BLOB</option></select></div>',
			'<input class="table_desc" type="text" maxlength="400" size="50" name="new'+newtablefieldcount+':table_desc:'+newcontextprovidercount+'" placeholder="description">',
			'</div>',
			'<div class="add-table-button">',
			'<a class="add-table">'+addimg+'</a>',
			'</div>',
			'</div>'
			].join('\n');
			$('#tables').append( $newtable );
			newcontextprovidercount += 1;
			newtablefieldcount += 1;
		}

	}
	);
	
	$(document).on('change', '.context_providers', function() {
		if ($('.tables-not-found').length){
			$('.tables-not-found').remove();
			$('#new_table-0').toggle();
		}
		var $id = $(this).parent().attr('id').split('-');
		if ($id[0] == "context_provider") {
			$("#table"+$id[1]).empty();
			var $value = $(this).val();
			if ($value == "") $("#table"+$id[1]).append( "unnamed" );
			else $("#table"+$id[1]).append( $value );
		}
		else if ($id[0] == "new_contextprovider") {
			$("#new_table"+$id[1]).empty();
			var $value = $(this).val();
			if ($value == "") $("#new_table"+$id[1]).append( "unnamed" );
			else $("#new_table"+$id[1]).append( $value );
		}

	}
	);
	
	$(document).on('click', '.add-table', function() {
		var contextproviderid = $(this).parent().parent().attr('id').split("-");
		/* new here -------------------------------------------------------------------------------------------------------------------------------------------------------*/
		var isnew = "";
		if(contextproviderid[0].substring(0,3)=="new") {
			isnew = "new";
		}
		var $newstuff=[
			'<div id="newtablefield-'+newtablefieldcount+'" class="newtablefields">',
			'<a id="remove-tablefield" class="remove-button">'+removeimg+'</a>',
			'<input class="table_name" type="text" maxlength="40" name="new'+newtablefieldcount+':table_name:'+isnew+contextproviderid[1]+'" placeholder="name">',
			'<div id="tableselect"><select class="table_type" name="new'+newtablefieldcount+':table_type:'+isnew+contextproviderid[1]+'"><option value="NULL">NULL</option><option value="INTEGER">INTEGER</option><option value="REAL">REAL</option><option value="TEXT">TEXT</option><option value="BLOB">BLOB</option></select></div>',
			'<input class="table_desc" type="text" maxlength="400" size="50" name="new'+newtablefieldcount+':table_desc:'+isnew+contextproviderid[1]+'" placeholder="description">',
			'</div>',
			'<div class="add-table-button" >',
			'<a class="add-table">'+addimg+'</a>',
			'</div>'
			].join('\n');
		$(this).parent().parent().append( $newstuff );
		$(this).parent().remove();
		newtablefieldcount += 1;
	}
	);
	
	$(document).on('click', '#remove-tablefield', function() {
		
		var $id = $(this).parent().parent().attr('id');
		$(this).parent().find('.table_name').val('');
		$(this).parent().find('.table_type').val('');
		$(this).parent().find('.table_desc').val('');
		var $isnew = false;
		if($(this).parent().attr('id').substring(0,3)=="new") {
			$isnew = true;
		}
		var $count = $('#'+$id+' div.tablefields:visible').length + $('#'+$id+' div.newtablefields').length;
		if ($count > 1) {
			if ($isnew) $(this).parent().remove();
			else {
				$(this).parent().find('.table_name').val('this_field_was_removed');
				$(this).parent().find('.table_type').val('this_field_was_removed');
				$(this).parent().find('.table_desc').val('this_field_was_removed');
				$(this).parent().toggle();
			}
		}
	}
	);
	
	document.getElementById("uploadBtn").onchange = function () {
		document.getElementById("uploadFile").value = this.files[0].name;
		$("#uploadFile2").val('new_file');
		if($("#remove-packagefile").length==0){
			$("#fileupload").append( '<a id="remove-packagefile" >'+removeimg+'</a>' );
		}
	};
	
	document.getElementById("iconuploadBtn").onchange = function () {
		document.getElementById("uploadIconFile").value = this.files[0].name;
		$("#uploadIconFile2").val('new_icon');
		if($("#remove-iconfile").length==0) {
			$("#iconupload").append( '<a id="remove-iconfile" >'+removeimg+'</a>' );
		}
	};
	
	$(document).on('click', '#remove-packagefile', function() {
		var file = document.getElementById("uploadFile").value;
		if(confirm('Would you like to remove the chosen file "'+file+'"?')){
			$("#uploadBtn")[0].value='';
			$("#uploadFile").val('');
			$("#uploadFile2").val('');
			$(this).remove();
		}
	}
	);
	
	$(document).on('click', '#remove-iconfile', function() {
		var file = document.getElementById("uploadIconFile").value;
		if(confirm('Would you like to remove the chosen file "'+file+'"?')){
			$("#iconuploadBtn")[0].value='';
			$("#uploadIconFile").val('');
			$("#uploadIconFile2").val('');
			$(this).remove();
		}
	}
	);
	

});