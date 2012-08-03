/*
 *	Cross-Site Javascript Implementation
 *
 *	Copyright 2012 - s0600204
 */
 
// create new object
function crossShare_comms() {
	
	this.local = window.location.protocol +"//"+ window.location.host;
	this.remote = "http://127.0.0.1/";
	this.pid = '';
	 
	function msg(e) {
		var auth = 0;
		if (e.origin == this.remote) {
			var sourceFrame = xGetElementById(this.pid + "_crossShare_iframe_remote");
			var targetFrame = xGetElementById(this.pid + "_crossShare_iframe_local");
			auth = 1;
		} else if (e.origin == this.local) {
			if (this.remote != "http://127.0.0.1/") {
				if (on_lan(this.local) && !on_lan(this.remote)) {
					eyeMessageBoxShow("Unable to transfer file due to 'Local' running on LAN");
				} else {
					var sourceFrame = xGetElementById(this.pid + "_crossShare_iframe_local");
					var targetFrame = xGetElementById(this.pid + "_crossShare_iframe_remote");
					auth = 1;
				}
			} else {
				eyeMessageBoxShow("Please connect to a remote server before trying to transfer files.");
			}
		}
		
		if (auth == 1) {
			var recData = e.data.split(",");
			if (recData[0] == "msg") {
				eyeMessageBoxShow(recData[1]);
			} else {
				sourceAddr = sourceFrame.src.substr(0,sourceFrame.src.indexOf("crossShare"))
				if (targetFrame.src.indexOf("?") > -1) {
					targetAddr = targetFrame.src.substr(0,targetFrame.src.indexOf("?"));
				} else {
					targetAddr = targetFrame.src;
				}
				
				recData[1] = sourceAddr + recData[1];
				targetFrame.src = targetAddr + "?receive=" + recData[0] + "&uri=" + recData[1];
			}
		}
	}
	this.msg = msg;
	
	function on_lan(addr) {
		addr = addr.substring(addr.indexOf(":")+3);
		if (addr.indexOf("/") > -1) {
			addr = addr.substring(0,addr.indexOf("/"));
		}
		if (addr.indexOf(":") > -1) {
			addr = addr.substring(0,addr.indexOf(":"));
		}
		if (addr.indexOf(".") > -1) {
			var fbyte = addr.substr(0, addr.indexOf("."));
			var sbyte = addr.substr(addr.indexOf(".")+1, addr.indexOf("."));
			if ((fbyte == 10)
				|| (fbyte == 176 && sbyte >= 16 && sbyte <= 31)
				|| (fbyte == 169 && sbyte == 254)
				|| (fbyte == 192 && sbyte == 168)) {
					return true;
			}
		}
		return false;
	}
}
var crossShareComm = new crossShare_comms();

// trigger function (required for use as listener reference)
function crossShare_msgTrigger(e) {
	crossShareComm.msg(e);
}

// finally, set up event listener
xAddEventListener(window, "message", crossShare_msgTrigger, false);