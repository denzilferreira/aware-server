//Debug flag
var debug = false;

//AWARE framework JS functions
function hashcode(){
	var e=document.getElementById('devices').options[document.getElementById('devices').selectedIndex].text;
	if(e=='All') { 
		document.getElementById('device_id').value = 0;
		convertToHash();
		return;
	}
	for(var r=0,i=0;i<e.length;i++)r=(r<<5)-r+e.charCodeAt(i),r&=r;
	document.getElementById('device_id').value=r;
};

function convertToHash() {
	for(var h=1; h < document.getElementById('devices').options.length; h++) {
		var e = document.getElementById('devices').options[h].text;
		for(var r=0,i=0;i<e.length;i++)r=(r<<5)-r+e.charCodeAt(i),r&=r;
		document.getElementById('all').value += r + ",";
	}
}

