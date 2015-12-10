var browser_name, version, old=false;

function get_browser(){
    var ua= navigator.userAgent, tem, 
    M= ua.match(/(opera|chrome|safari|firefox|msie|trident(?=\/))\/?\s*([\d\.]+)/i) || [];
    if(/trident/i.test(M[1])){
        tem=  /\brv[ :]+(\d+(\.\d+)?)/g.exec(ua) || [];
		browser_name = 'MSIE';
		version = parseFloat(tem[1] || '');
        return;
    }
	if(M && (tem= ua.match(/version\/([\.\d]+)/i))!= null) M[2]= tem[1];
    M=M? [M[1], M[2]]: [navigator.appName, navigator.appVersion, '-?'];
    browser_name = M[0].toUpperCase().trim();
	version = parseFloat(M[1]);
}
get_browser();
$(document).ready(function(){
	
	if( browser_name=='OPERA' && version<=15){
		old = true;
	} else if (browser_name=='CHROME' && version<=16){
		old = true;
	} else if (browser_name=='SAFARI' && version<5.1){
		old = true;
	} else if (browser_name=='FIREFOX' && version<=15){
		old = true;
	} else if (browser_name=='MSIE' && version<9){
		old = true;
	}
	if(old==true){
		$('body').prepend('<div id="browser_aler">Unfortunately we do not support your browser. Please update your browser for better user experience.</div>');
	}
});