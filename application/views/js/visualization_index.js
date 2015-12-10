// Get CSRF cookie
var cct = $.cookie('csrf_cookie_aware');
var study_id;

//Onready function
$(function() {
	var wall = new freewall("#freewall");
	wall.reset( {
		//draggable: true,
		selector: '.brick',
		
		//animate: true,
		//Only 3 bricks in a row.
	   // fixSize: null,
		gutterX: 15, // width spacing between blocks;
		gutterY: 15, // height spacing between blocks;
		cellW: function(width) {
		    console.log("freewall width:" + width);
			var cellWidth = width / 3.0;
			return cellWidth;
		},
		cellH: "auto",
		delay: 0,
		onResize: function() {
			wall.refresh();
		}
	});
	//Get study id
	study_id = $(".id").html();
	$(".id").remove();

	//unblock UI when user leaves the page. Does not work on IE 
	window.onunload = function() {
	    $.unblockUI();
	};
	//Get charts
	$('.visualizations-container').each(function() {
        var container = $(this);
        var json = JSON.parse(container.html());
        var img = container.attr('data-image');;
        var chart_id = json.id;
        var comment = json.description;
        var s_id = json.studies_id;
        var public_ = json.public;
        var type = json.type;
       	var path = json.path;
       	var timestamp = json.timestamp;
        var placement = json.placement;
 
    	container.remove();
    	// chart_id, study_id, placement, pub, type, desc, path, img, timestamp
    	var brick = new Brick(chart_id, s_id, placement, public_, type, comment, path, img, timestamp);
    	wall.appendBlock(brick.toHtml());
    });
	var addmore = makeAddMoreBrick(study_id);
	wall.appendBlock(addmore);
	$(".add-more").click(function() {

		document.forms["new_chart"].submit();
		//Block UI...
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
		wall.fitWidth();	
	});
	
	//Testing for the add-more brick because it didnt appear on the start.
	$(window).trigger("resize");
	
});
function makeAddMoreBrick(id) {
	var addmore = 
	"<div class='brick add-more size30percent' style='background-color: #5B9BD5'>" +
		"<div class='cover'>" + 
			"<form id='new_chart' action='/index.php/visualizations/new_chart/" + id + "'>"+
				"<h2>Create a new chart.</h2>"+
			"</form>" +
		"</div>" +
	"</div>";
	return addmore;

}
