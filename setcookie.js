// StateIt.com	
	
//function that checks if cookie exists
/*
function getCookie (name,value) {
    if(document.cookie.indexOf(name) == 0) //Match without a ';' if its the firs
        return -1<document.cookie.indexOf(value?name+"="+value+";":name+"=")
    else if(value && document.cookie.indexOf("; "+name+"="+value) + name.length + value.length + 3== document.cookie.length) //match without an ending ';' if its the last
        return true
    else { //match cookies in the middle with 2 ';' if you want to check for a value
        return -1<document.cookie.indexOf("; "+(value?name+"="+value + ";":name+"="))
    }
}
*/

// Safari doesn't let you set sessions from within an iFrame
// Check if the browser is Safari
if (navigator.userAgent.indexOf('Safari') != -1 && navigator.userAgent.indexOf('Chrome') == -1) {
	alert('Its Safari');
	
	//get studio ID
	var this_js_script = $('script[src*=//setcookie.js]'); // or better regexp to get the file name..

	var studioID = this_js_script.attr('data-id'); 
	alert(studioID);  
	if (typeof my_var_1 === "undefined" ) {
		window.parent.location = "https://my.slateit.com/student-login-universal.php";
	}

	//redirect parent window
	window.parent.location = "https://my.slateit.com/student-login.php?studioID="+studioID;
	
	//check if cookie exists
	/*
	var myCookie = getCookie("slateit");
	if (myCookie) {
		alert("cookie does not exist");	
		//get the parent url and build the redirect url
		var url = (window.location != window.parent.location)
				? document.referrer
				: document.location;
		url = 'https://my.slateit.com/setcookie.php?url='+url;
	} else {
		alert("cookie exists");
	}
	*/
} 