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
    //console.log("time:" + timestamp);
    //Timezone fix
    timestamp = new Date(Date.parse(timestamp));
    timestamp.setTime( timestamp.getTime() - timestamp.getTimezoneOffset()*60*1000); 
    //brick elements	
    var divTag = document.createElement("div");
    var closeTag = document.createElement("div");
    var publishTag = document.createElement("div");
    var refreshTag = document.createElement("div");
    var dlgTag = document.createElement("div");
    var createdTag = document.createElement("label");

    //CreateTag attributes
    createdTag.innerHTML = "Created:" + timestamp.toUTCString();
    createdTag.className = "created_label";
   
    //brick attributes
    divTag.addEventListener("click", function( event ) {
        //Refresh comment
        commentTag.value = comment;
        $(dlgTag).dialog('open');
    }, false);
    divTag.className = "brick size30percent"; 
    divTag.style.backgroundImage = "url('" + img + "')";

    //closeButton attributes
    closeTag.className = "closeButton";
    closeTag.addEventListener("click", function( event ) {
        //To stop event propagation
        if (!event) 
        {
            event = window.event;
        }
        event.cancelBubble = true;
        if (event.stopPropagation)
        {
            event.stopPropagation(); 
        } 
        //Show confirmation dialog.
        $('<div></div>').appendTo('body')
            .html('<div><h5>Delete chart?</h5></div>')
            .dialog({
            modal: true, title: 'Confirm', zIndex: 10000, autoOpen: true,
            width: 'auto', resizable: false,
            buttons: 
            {
                Yes: function () {
                    $(this).dialog("close");
                    //Delete brick
                    console.debug("Deleting brick " + divTag.id);
                    var brick = document.getElementById(divTag.id);
                    brick.outerHTML = "";
                    delete brick;
                    // Rearrange layout
                    $(window).trigger("resize");
                    //AJAX call
                    $.ajax({
                        //Change to set_new_chart when ready
                        url: "../remove_chart",
                        type: "POST",
                        data: {'study_id' : study_id, 'placement' : placement, 'path' : path, 'chart_id' : chart_id, 'csrf_token_aware' : cct},
                        dataType: "json",
                        success: function(data) {
                            //Redirect to visualizations
                            console.log("AjaxSUcces");

                            console.log(data);
                        },
                        // Alert status code and error if fail
                        error: function (xhr, ajaxOptions, thrownError){
                            console.log("xhr.status: " + xhr.status);
                            console.log("error: " + thrownError);
                        }
                    });
                },
                No: function () {
                    $(this).dialog("close");
                }
            },
                close: function (event, ui) {
                    $(this).remove();
                }
        });

    	
      }, false);
      
    //publishButton attributes
    publishTag.className = "publishButton";
    publishTag.addEventListener("click", function( event ) {
        //To stop event propagation
        if (!event) 
        {
            event = window.event;
        }
        event.cancelBubble = true;
        if (event.stopPropagation)
        {
            event.stopPropagation(); 
        } 
        $("#"+chart_id).click();
      }, false);

    //If public at the start
    if(public_ == 1) {
        $(publishTag).addClass('public');
    }
    //refreshTag attributes
    refreshTag.className = "refreshButton";
    refreshTag.addEventListener("click", function( event ) {
        //To stop event propagation
        if (!event) 
        {
            event = window.event;
        }
        event.cancelBubble = true;
        if (event.stopPropagation)
        {
            event.stopPropagation(); 
        } 
        var temp = 'chart_id: ' + chart_id + ' placement: ' + placement + 
        ' comment: ' + comment + "\nstudy_id: " + this.study_id + 
        ' public_: ' + public_ + ' type: ' + type + 
        '\npath: ' + path + ' timestamp: ' + timestamp;
        console.log(temp);
        $(divTag).css('pointer-events', "none");
        divTag.style.backgroundImage = 'none';
        var waitingTag = document.createElement("div");
        waitingTag.style.position = 'absolute';
        waitingTag.style.width = '100%';
        waitingTag.style.height = '100%';
        waitingTag.style.zIndex = 1;
        waitingTag.innerHTML = '<div id="circularG" style="margin-left: auto; margin-right: auto;"><div id="circularG_1" class="circularG"></div><div id="circularG_2" class="circularG"></div><div id="circularG_3" class="circularG"></div><div id="circularG_4" class="circularG"></div><div id="circularG_5" class="circularG"></div><div id="circularG_6" class="circularG"></div><div id="circularG_7" class="circularG"></div><div id="circularG_8" class="circularG"></div></div>';
        divTag.appendChild(waitingTag);
        //alert(temp);
        $.ajax({
            //Change to set_new_chart when ready
            url: "../refresh_chart",
            type: "POST",
            data: {'study_id' : study_id, 'chart_id' : chart_id, 'csrf_token_aware' : cct},
            //dataType: "json",
            success: function(data) {
                //Redirect to visualizations
                console.log("AjaxSUcces");
                console.log(data);
                $(divTag).css('pointer-events', "auto");
                divTag.removeChild(waitingTag);
                divTag.style.backgroundImage = "url('" + data + "')";
                imgTag.style.backgroundImage = "url('" + data + "')";           
            },
               
            // Alert status code and error if fail
            error: function (xhr, ajaxOptions, thrownError){
                console.log("xhr.status: " + xhr.status);
                console.log("error: " + thrownError);
                $(divTag).css('pointer-events', "auto");
                divTag.removeChild(waitingTag);
            }
        });
      }, false);
    //Dialog attributes
    //Add img div
    var imgTag = document.createElement("div");
    imgTag.className = "brick-dialog img";
    //imgTag.appendChild(image);
    imgTag.style.height = '100%';
    imgTag.style.backgroundImage = "url('" + img + "')";
    dlgTag.appendChild(imgTag);
    dlgTag.className = "brick-dialog";
    dlgTag.title = "View chart";
    //Generate comment field
    var commentTag = document.createElement("textarea");
    commentTag.className = "comment";
    commentTag.placeholder = "Write a comment...";
    commentTag.innerHTML = comment;
    commentTag.style.position = "absolute";
    commentTag.style.left = '10%';
    commentTag.style.bottom = 0;
    commentTag.style.zIndex = 1;
    commentTag.style.display = "none";

    dlgTag.appendChild(commentTag);   

    //Add child divs to the parent div
    divTag.appendChild(closeTag);
    divTag.appendChild(publishTag);
    divTag.appendChild(refreshTag);
    divTag.appendChild(createdTag);

    //Create dialog
    createDialog();
    //Prototypes
    Brick.prototype.toHtml = function() {
        return divTag;
    }
    Brick.prototype.setComment = function(c) {
        comment = c;
    }
    Brick.prototype.getComment = function() {
        return comment;
    }
    //When user has pressed image div
    function createDialog() {
        //get image size and add it to dialog size.
        var image = document.createElement('img');
        image.src = img;
        /*
        var w = image.width;
        var h = image.height;
        console.log("w:" + w + "\n" + "h:" + h +"screen.w" + screen.width + "screen.h" + screen.height );
        if(w > screen.width) w = screen.width;
        if(h > screen.height) h = screen.height;
        */
        $(dlgTag).dialog({

            autoOpen: false,
            //position: 'center',  
            modal: true,
            width: screen.width / 2,
            height: screen.height / 2,
            buttons: 
            [
                {
                    text: "Public"
                },

                {
                    text: "Refresh",
                    click: function() {
                        var waitingTag = document.createElement("div");
                        waitingTag.style.position = 'absolute';
                        waitingTag.style.width = '90%';
                        waitingTag.style.height = '90%';
                        waitingTag.style.zIndex = 1;
                        waitingTag.innerHTML = '<div id="circularG" style="margin-left: auto; margin-right: auto;"><div id="circularG_1" class="circularG"></div><div id="circularG_2" class="circularG"></div><div id="circularG_3" class="circularG"></div><div id="circularG_4" class="circularG"></div><div id="circularG_5" class="circularG"></div><div id="circularG_6" class="circularG"></div><div id="circularG_7" class="circularG"></div><div id="circularG_8" class="circularG"></div></div>';
                        $(".ui-dialog-buttonpane button:contains('Refresh')").button("disable");
                        $(".ui-dialog-buttonpane button:contains('Download')").button("disable");
                        $(".ui-dialog-buttonpane button:contains('Edit')").button("disable");
                        imgTag.appendChild(waitingTag);
                        $.ajax({
                            //Change to set_new_chart when ready
                            url: "../refresh_chart",
                            type: "POST",
                            data: {'study_id' : study_id, 'chart_id' : chart_id, 'csrf_token_aware' : cct},
                            //dataType: "json",
                            success: function(data) {
                                console.log("AjaxSUcces");
                                console.log(data);
                                divTag.style.backgroundImage = "url('" + data + "')";
                                imgTag.style.backgroundImage = "url('" + data + "')";
                                imgTag.removeChild(waitingTag);
                                $(".ui-dialog-buttonpane button:contains('Refresh')").button("enable");
                                $(".ui-dialog-buttonpane button:contains('Download')").button("enable");
                                $(".ui-dialog-buttonpane button:contains('Edit')").button("enable");
                            },
                            // Alert status code and error if fail
                            error: function (xhr, ajaxOptions, thrownError){
                                console.log("xhr.status: " + xhr.status);
                                console.log("error: " + thrownError);
                                imgTag.removeChild(waitingTag);
                                $(".ui-dialog-buttonpane button:contains('Refresh')").button("enable");
                                $(".ui-dialog-buttonpane button:contains('Download')").button("enable");
                                $(".ui-dialog-buttonpane button:contains('Edit')").button("enable");
                            }
                        });
                    }
                },

                {
                    text: "Toggle comment",
                    click: function() {
                        if(commentTag.style.display == "none") {
                            commentTag.style.display = "block"; 
                        }
                        else {
                            commentTag.style.display = "none";
                        }
                    }
                },

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
                    text: "Save",
                    click: function() {
                        //Get txt from textarea.
                        comment = commentTag.value;
                        //ajax call for updating chart.
                        $.ajax({
                            //Change to set_new_chart when ready
                            url: "../save_comment",
                            type: "POST",
                            data: {'study_id' : study_id, 'comment' : comment, 'chart_id' : chart_id, 'csrf_token_aware' : cct},
                            dataType: "json",
                            success: function(data) {
                                //Redirect to visualizations
                                console.log("AjaxSUcces");

                                console.log(data);
                            },
                            // Alert status code and error if fail
                            error: function (xhr, ajaxOptions, thrownError){
                                console.log("xhr.status: " + xhr.status);
                                console.log("error: " + thrownError);
                            }
                        });
                        //Close dialog
                        $( this ).dialog( "close" );
                    }
                },

               {
                    class: 'showScript',
                    text: "Show Rscript",
                    click: function() {
                        var h = screen.height*60/100;
                        var w = screen.width*50/100;
                        //AJAX call
                        $.ajax({
                            //Change to set_new_chart when ready
                            url: "../get_rscript_text",
                            type: "POST",
                            data: {'study_id' : study_id, 'chart_id' : chart_id, 'csrf_token_aware' : cct},
                            //dataType: "json",
                            success: function(data) {
                                //Redirect to visualizations
                                console.log("AjaxSUcces");
                                console.log(data);
                                //Show dialog
                                var script = document.createElement("div");
                                script.title = "Rscript";
                                script.style.scroll = 'auto';
                                script.innerHTML = data;
                                $(script).dialog({
                                    modal: true,
                                    autoOpen: true,
                                    stack: true,
                                    width: w,
                                    height: h,
                                    buttons:
                                    {
                                        Close: function() {
                                            $( this ).dialog( "close" );
                                            $( this ).remove();
                                        }
                                    }
                                });
                            },
                            // Alert status code and error if fail
                            error: function (xhr, ajaxOptions, thrownError){
                                console.log("xhr.status: " + xhr.status);
                                console.log("error: " + thrownError);
                            }
                        });
                        
                    }
                },

                {
                    class: 'leftButton',
                    text: "Edit",
                    click: function() {
                        $.blockUI( 
                            { 
                                overlayCSS:  { 
                                    backgroundColor: '#5B9BD5', 
                                    opacity:         0.6, 
                                    cursor:          'wait' 
                                }, 
                                //theme: true, If theme is on, our css modifications wont work
                                message: "<p> Please wait... </p>"
                            });
                        //Redirect to edit chart page
                        var form = $( "#edit_chart" );
                        var action = form.attr('action');
                        form.attr('action', action + chart_id).submit();
                        form.attr('action', action);

                        $( this ).dialog( "close" );
                    }
                }

            ]
        });
        //Change publish button to checkbox.
        $('.ui-dialog-buttonpane button:eq(0)',$(dlgTag).parent()).replaceWith('<input id="'+ chart_id+'" type="checkbox" /><label for="'+
                    chart_id + '"style="margin-right: 5px">Public</label>');
        //Set initial value to checkbox
        $("#"+chart_id).attr('checked', public_ == 1);
        //Add event handler to it.
        $("#"+chart_id).click(function() {
            if(this.checked) {
                public_ = 1;
                $(publishTag).addClass('public');
            } 
            else {
                public_ = 0;
                $(publishTag).removeClass('public');
            }
            //ajax call for setting public value.
            $.ajax({
                url: "../set_publicity_value",
                type: "POST",
                data: {'study_id' : study_id, 'chart_id' : chart_id, 'public' : public_, 'csrf_token_aware' : cct},
                dataType: "json",
                success: function(data) {
                    //Redirect to visualizations
                    console.log("AjaxSUcces");
                    console.log(data);
                },
                // Alert status code and error if fail
                error: function (xhr, ajaxOptions, thrownError){
                    console.log("xhr.status: " + xhr.status);
                    console.log("error: " + thrownError);
                }
            });
        });
    }
}

