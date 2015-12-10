var study_id;

//Onready function
$(function() {
	var wall = new freewall("#freewall");
	wall.reset( {
		selector: '.brick',
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
    	var brick = new Brick(chart_id, s_id, placement, public_, type, comment, path, img, timestamp);
    	wall.appendBlock(brick.toHtml());
    });

	$(window).trigger("resize");
	
});

