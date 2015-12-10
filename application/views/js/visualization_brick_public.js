//Function for creating new brick
function Brick(chart_id, study_id, placement, pub, type, desc, path, img, timestamp) {
           
    //Attributes
    var img = img;
    var chart_id = chart_id;
    var placement = placement;
    var comment = desc;
    var study_id = study_id;
    var public_ = pub;
    var type = type;
    var path = path;
    //replaces "-" to "/".
    var timestamp = timestamp.replace(/-/g,"/");
    console.log("time:" + timestamp);
    //Timezone fix
    timestamp = new Date(Date.parse(timestamp));
    timestamp.setTime( timestamp.getTime() - timestamp.getTimezoneOffset()*60*1000); 
     //Timezone fix
    timestamp = new Date(Date.parse(timestamp));
    timestamp.setTime( timestamp.getTime() - timestamp.getTimezoneOffset()*60*1000); 

    //brick elements    
    var divTag = document.createElement("div");
    var dlgTag = document.createElement("div");
    var createdTag = document.createElement("label");
 
    //CreateTag attributes
    createdTag.innerHTML = "Created:" + timestamp.toUTCString();
    createdTag.className = "created_label";
    divTag.appendChild(createdTag);
    //brick attributes
    divTag.addEventListener("click", function( event ) {
        $(dlgTag).dialog('open');
    }, false);
    divTag.className = "brick size30percent"; 
    divTag.style.backgroundImage = "url('" + img + "')";


    //Dialog attributes
    //Add img div
    var imgTag = document.createElement("div");
    imgTag.className = "brick-dialog img";
    imgTag.style.height = '100%';
    imgTag.style.backgroundImage = "url('" + img + "')";
    dlgTag.appendChild(imgTag);
    dlgTag.className = "brick-dialog";
    dlgTag.title = "View chart";

    //Create dialog
    createDialog();

    //Generate comment field
    var commentTag = document.createElement("label");
    commentTag.className = "comment";
    commentTag.innerHTML = comment;
    dlgTag.appendChild(commentTag); 

    //Prototypes
    Brick.prototype.toHtml = function() {
        return divTag;
    }
    //When user has pressed image div
    function createDialog() {
        //get image size and add it to dialog size.
        var image = document.createElement('img');
        image.src = img;
        var w = image.width;
        var h = image.height;
        if(w > screen.width) w = screen.width;
        if(h > screen.height) h = screen.height;
  
        $(dlgTag).dialog({

            autoOpen: false,
            //position: 'center',  
            modal: true,
            width: w-180,
            height: h-180,
            buttons: 
            [  
                {
                    text: "Download",
                    click: function() {
                        //Does not work on ie.
                        var a = document.createElement("a");
                        a.setAttribute("download", "chart.png");
                        a.setAttribute("href", image.src);
                        a.appendChild(image);
                        document.body.appendChild(a);
                        a.click();
                        $(a).remove();
                    }
                },
                {
                    text: "Close",
                    click: function() {
                        $( this ).dialog( "close" );
                    }
                }
            ]
        });
     
    }
}