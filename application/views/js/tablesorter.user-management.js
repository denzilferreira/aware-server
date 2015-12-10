var cct = $.cookie('csrf_cookie_aware');

$(document).ready(function(){

	var $table = $('table#user-management').tablesorter({
		theme: 'dropbox',
		widgets: ["zebra", "filter"],
		sortList: [[0,0]],
		headers: {
					2: { 
						sorter: false
					},
					3: { 
						sorter: false
					},
					4: { 
						sorter: false
					},
					5: { 
						sorter: false
					}
				},
		widgetOptions : {
			// if true overrides default find rows behaviours and if any column matches query it returns that row
			filter_columnFilters: false,
			filter_anyMatch : true,
			filter_reset: '.reset',
			filter_childRows: true
		}
	})
    // initialize the pager plugin
    // ****************************
    .tablesorterPager({

      // target the pager markup - see the HTML block below
      container: $(".pager"),

      ajaxUrl : 'get_users_data?page={page}&{filterList:filter}&{sortList:column}',

      // modify the url after all processing has been applied
      customAjaxUrl: function(table, url) {
          $(table).trigger('changingUrl', url);
          return url;
      },
      
      ajaxObject: {
        dataType: 'json'
      },

      ajaxProcessing: function(data){
        if (data && data.hasOwnProperty('rows')) {
          var r, row, c, d = data.rows,
          // total number of rows (required)
          total = data.total_rows,
          // array of header names (optional)
          headers = data.headers,
          // all rows: array of arrays; each internal array has the table cell data for that row
          rows = [],
          // len should match pager set size (c.size)
          len = d.length;
          // this will depend on how the json is set up - see City0.json
          // rows
          var ids = [];
          for ( r=0; r < len; r++ ) {
            row = []; // new row array
            // cells
            for ( c in d[r] ) {
            	if (c == "ID") {
            		ids.push(d[r][c]);
            	}
            	if (typeof(c) === "string") {
					if (["Developer", "Researcher", "Manager"].indexOf(c) > -1) {
						var checked = (d[r][c] == 1) ? " checked" : "";
						row.push("<input type='checkbox' id='" + c.toLowerCase()[0] + "_" + d[r]["ID"] + "' class='" + c.toLowerCase() + "-status basic' value='" + d[r][c] + "'" + checked + "><label for='" + c.toLowerCase()[0] + "_" + d[r]["ID"] + "'></label>")
					} else if (c == "Status") {
						var checked = (d[r][c] == 1) ? " checked" : "";
						row.push("<input type='checkbox' id='" + "s_" + d[r]["ID"] + "' class='status-activated switchbutton'" + checked + "><label for='" + c.toLowerCase()[0] + "_" + d[r]["ID"] + "'></label><span class='toggle-status " + ((d[r][c] == 1) ? "on" : "off") + "'>" + ((d[r][c] == 1) ? "Active" : "Deactive") + "</span>");
					} else if (c != "ID") {
						row.push(d[r][c]); // add each table cell data to row array
					}
				}
            }

            rows.push(row); // add new row array to rows array
          }
          // in version 2.10, you can optionally return $(rows) a set of table rows within a jQuery object
          return [ total, rows, headers ];
        }
      },

      // output string - default is '{page}/{totalPages}'; possible variables: {page}, {totalPages}, {startRow}, {endRow} and {totalRows}
      output: '{startRow} to {endRow} ({totalRows})',

      // apply disabled classname to the pager arrows when the rows at either extreme is visible - default is true
      updateArrows: true,

      // starting page of the pager (zero based index)
      page: 0,

      // Number of visible rows - default is 10
      size: 50,

      // if true, the table will remain the same height no matter how many records are displayed. The space is made up by an empty
      // table row set to a height to compensate; default is false
      fixedHeight: false,

      // remove rows from the table to speed up the sort of large tables.
      // setting this to false, only hides the non-visible rows; needed if you plan to add/remove rows with the pager enabled.
      removeRows: false,

      // css class names of pager arrows
      cssNext        : '.next',  // next page arrow
      cssPrev        : '.prev',  // previous page arrow
      cssFirst       : '.first', // go to first page arrow
      cssLast        : '.last',  // go to last page arrow
      cssPageDisplay : '.pagedisplay', // location of where the "output" is displayed
      cssPageSize    : '.pagesize', // page size selector - select dropdown that sets the "size" option
      cssErrorRow    : 'tablesorter-errorRow', // error information row

      // class added to arrows when at the extremes (i.e. prev/first arrows are "disabled" when on the first page)
      cssDisabled    : 'disabled' // Note there is no period "." in front of this class name

    }).bind("updateComplete",function(e, table) {
   		var status = $("input.activated-status", table);

   		for (var i=0; i < status.length; i++) {
   			console.debug(status[i]);
   			$(status[i]).switchButton({
				labels_placement: 'right',
				on_label: 'Active',
				off_label: 'Deactive'
			});
   		}

    });


	// Target the $('.search') input using built in functioning
	// this binds to the search using "search" and "keyup"
	// Allows using filter_liveSearch or delayed search &
	// pressing escape to cancel the search
	if ($table.length > 0) {
		$.tablesorter.filter.bindSearch( $table, $('.search') );
	}

		
});