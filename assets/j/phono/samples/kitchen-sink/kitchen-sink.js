/* Kitchen Sink */
$(document).ready(function() {
	
	var phonos={}, calls={}, chats={};

	function createNewPhono(){
		//Clone a phono div
		var phonoCtr = ($(".phono").size() + 1) - 1;
		var newPhonoID = "Phono" + phonoCtr;
		var firstPhono = $('.phono').first();
		var newPhonoDiv = firstPhono.clone();
		newPhonoDiv.attr("id",newPhonoID).appendTo('.phonoHldr').show();
		newPhonoDiv.find(".phonoID").text(newPhonoID);
		
		var newPhonoDiv = $("#"+newPhonoID);
		phonos[newPhonoID] = $.phono({
			apiKey: "C17D167F-09C6-4E4C-A3DD-2025D48BA243",
        	onReady: function(event) {
        	    newPhonoDiv.find(".sessionId").text(this.sessionId);
			    newPhonoDiv.find(".phoneControl").show();
        	    log.info("["+newPhonoID+"] Phono loaded"); 
        	    
        	    if( ! this.audio.permission() ){
        	       this.audio.showPermissionBox();
        	    }
        	},
        	onUnready: function(event) {
        	    newPhonoDiv.find(".sessionId").text("disconnected");
        	    log.info("["+newPhonoID+"] Phono disconnected");  
        	},		   
        	onError: function(event) {
        	    log.error(event.reason);  
        	},		   
	     	audio: {
         		onPermissionBoxShow: function(event) {
         		    log.info("["+newPhonoID+"] Flash permission box loaded"); 
         		},
         		onPermissionBoxHide: function(event) {
         		    log.info("["+newPhonoID+"] Flash permission box closed");
         		    if( ! this.permission() ){
       	 		  	this.showPermissionBox();
       	 		   }
         		}
	     	},
	      
	     	phone: {
         		onIncomingCall: function(event) {
         	    	// was push to talk enabled for calls?
		 			var pttEnabled;
		 			($("#"+newPhonoID).find(".push-to-talk").is(":checked")) ? pttEnabled = true : pttEnabled = false;
         	       
         	       	var newCallID = createCallDiv(newPhonoID,"incoming",pttEnabled);
         	       	var newCallDiv = $("#"+newCallID);
         	       	newCallDiv.find(".callHeader .callDetail").html("<strong>Incoming call</strong>");
         	       	newCallDiv.find(".callHeader .callID").html(newCallID);
         	       	calls[newCallID] = event.call;
         	   	   	log.info("["+newPhonoID+"] New incoming call");
         	       
         	   		//Bind events from this call
         	   		Phono.events.bind(calls[newCallID], {
         	    		   onHangup: function(event) {
         	    		       newCallDiv.slideUp();
         	    		       calls[newCallID] = null;
         	    		       log.info("["+newPhonoID+"] ["+newCallID+"] Call hungup");
         	    		   },
         	    		   onError: function(event) {
         	    		       log.error("["+newPhonoID+"] ["+newCallID+"] Error: [" + event.reason + "]");
         	    		   }
         	   		});
         	   },
         	   onError: function(event) {
         	       log.error("["+newPhonoID+"] Error: [" + event.reason + "]");
         	   }
	     	},
	      
	     	messaging: {
         		onMessage: function(event, message) {
         		 var JID = message.from.split("/");
         		 log.info("["+newPhonoID+"] Message from " + JID[0] + " [" + message.body + "]");
         		 routeMessage(newPhonoID,"incoming",JID[0],message.body);
         		}
	     	}
      	});
	}
	
	//Creates a new call
	function createNewCall(phonoID, to){
		//clone a call box
		var phonoDiv = $("#"+phonoID);
		
		// was push to talk enabled for calls?
		var pttEnabled;
		(phonoDiv.find(".push-to-talk").is(":checked")) ? pttEnabled = true : pttEnabled = false;
		
		var newCallID = createCallDiv(phonoID,"outgoing",pttEnabled);
		var callDiv = $("#"+newCallID);
		callDiv.find(".callID").text(newCallID);
		callDiv.find(".callDetail").html("<strong>Outgoing call to:</strong> " + to);
		log.info("["+phonoDiv.attr('id')+"] ["+newCallID+"] Calling "+to);
		
		calls[newCallID] = phonos[phonoID].phone.dial(to, {
		    tones: true,
		    pushToTalk: pttEnabled,
            onAnswer: function(event) {
            	log.info("["+phonoDiv.attr('id')+"] ["+newCallID+"] Call answered");
            },
            onHangup: function() {
            	calls[newCallID] = null;
            	$("#"+newCallID).slideUp();
            	log.info("["+phonoDiv.attr('id')+"] ["+newCallID+"] Call hungup");
            }
        });
	}
	
	//Routes a message to the appropriate chat or creates a new chat
	function routeMessage(phonoID, type, jid, message){
		var theChat = "";
		$.each(chats, function(key, value) {
			if( value == jid ){
				theChat = key;
				var newMsg = chatMessage(jid,message,"inbound");
				var chatDiv = $("#"+theChat);
				renderNewMessage(chatDiv, newMsg);
				chatDiv.find(".chatTxtInput").focus();
				return;
			}
   		});
   		
 		//Did we find a chat?
		if(! theChat.length){
		    createNewChat(phonoID,jid,message);
		}
	}
	
	//Outputs an IM message
	function renderNewMessage(chat, msg){
		chat.find(".chatBox").append(msg);
		chat.find(".chatBox").attr({ scrollTop: chat.find(".chatBox").attr("scrollHeight") });
	}
	
	//Creates a new div to hold a call. Returns the id of the new div
	function createCallDiv(phonoID,callType,pttEnabled){
		//clone a call box
		var phonoDiv = $("#"+phonoID);
		var callBox = phonoDiv.find(".calls");
		var newCallCtr = ($(".callHldr").size() + 1) - 2;
		newCallID = "Call" + newCallCtr;
		var firstCall = $(".callHldr").first()
		var newCallDiv = firstCall.clone()
		newCallDiv.attr("id",newCallID).appendTo(callBox).show();
		
		if( callType == "outgoing"){
			newCallDiv.find(".callInputs").show();
		}else{
			newCallDiv.find(".callControls").show();
		}
		
		pttEnabled ? newCallDiv.find(".talkStart").show() : newCallDiv.find(".talkStart").hide();
		
		return newCallID;
	}
	
	//Creates a new chat
	function createNewChat(phonoID, to, message){
		//clone a chat box
		var phonoDiv = $("#"+phonoID);
		var newChatID = createChatDiv(phonoID);
		var chatDiv = $("#"+newChatID);
		chatDiv.find(".chatID").text(newChatID);
		chatDiv.find(".chatDetail").html(to);
		
		if( message.length ){
			var newMsg = chatMessage(to,message,"inbound");
			renderNewMessage(chatDiv, newMsg);
		}
		
		log.info("["+phonoDiv.attr('id')+"] ["+newChatID+"] Chat started with " + to);
		
		chats[newChatID] = to;
	}
	
	//Creates a new div to hold a chat. Returns the id of the new div
	function createChatDiv(phonoID){
		//clone a chat box
		var phonoDiv = $("#"+phonoID);
		var chatBox = phonoDiv.find(".chats");
		var newChatCtr = ($(".chatHldr").size() + 1) - 2;
		newChatID = "Chat" + newChatCtr;
		var firstChat = $(".chatHldr").first();
		var newChatDiv = firstChat.clone();
		newChatDiv.attr("id",newChatID).appendTo(chatBox).show();
		newChatDiv.find(".chatTxtInput").focus();
		
		return newChatID;
	}
	
	// Create and return a chat message bubble
	var chatMessage = function(from,body,type){
		var result;
		
		if( type == 'inbound'){
			var borderStyle = "inbound";
			var fromStyle = "fromInbound";
		}else{
			var borderStyle = "outbound";
			var fromStyle = "fromOutbound";
		}
		
		result = $("<div>")
			.addClass("chatEntry")
			.addClass(borderStyle);
		var fromHeader = $("<div>")
			.addClass("from")
			.addClass(fromStyle)
			.html(from)
			.appendTo(result);
		var msgBody = $("<div>")
			.addClass("body")
			.html(body)
			.appendTo(result);
			
		return result;
	}
	
	//DOM Event Handlers
	$(".addPhono").click(function(){
		createNewPhono();
		return false;
	});
	
	$('.closePhono').live('click', function() {
		var thisPhono = $(this).closest(".phono");
		var thisPhonoID = $(this).closest(".phono").attr("id");
		thisPhono.slideUp();
		phonos[thisPhono.attr("id")] = null;
		
		thisPhono.find(".callHldr").each(function(){
			calls[$(this).attr("id")] = null;
		});
		log.info("["+thisPhonoID+"] Phone removed");
		return false;
	});
	
	$('.closeChat').live('click', function() {
		var thisChat = $(this).closest(".chatHldr");
		var thisChatID = $(this).closest(".chatHldr").attr("id");
		thisChat.slideUp();
		chats[thisChatID] = null;
		log.info("["+thisChatID+"] Chat closed");
		return false;
	});
	
	$('.call').live('click', function() {
		var thisPhono = $(this).closest(".phono").attr("id");
		var callTo = $.trim($("#"+thisPhono).find(".callTo").val());
		createNewCall(thisPhono, callTo);
	});
	
	$('.headset').live($.browser.msie ? 'click': 'change', function() {
		var phonoId = $(this).closest(".phono").attr("id");
		var headsetEnabled = $("#"+phonoId).find(".headset").is(":checked");
		log.info("["+phonoId+"] Headset: " + headsetEnabled);
    	phonos[phonoId].phone.headset(headsetEnabled);		
	});
	
	$('.ring-tone, .ringback-tone').live($.browser.msie ? 'click': 'change', function() {
		var phonoId = $(this).closest(".phono").attr("id");
		var tone = $(this).val();
		if($(this).hasClass("ring-tone")){
			if(phonos[phonoId].phone.ringTone() !== tone){
				phonos[phonoId].phone.ringTone(tone);
				log.info("["+phonoId+"] Ring tone set: " + tone);
			}
		}else{
			if(phonos[phonoId].phone.ringbackTone() !== tone){
				phonos[phonoId].phone.ringbackTone(tone);
				log.info("["+phonoId+"] Ringback tone set: " + tone);
			}
		}	
	});
	
	$('.flashHelp a').live('click', function() {
		var thisPhono = $(this).closest(".phono");
		$(".flash-hldr").css("left","250.5px");
		thisPhono.find(".flashHelp").text("Try again");
		return false;
	});
	
	$('.chat').live('click', function() {
		var thisPhono = $(this).closest(".phono").attr("id");
		var chatTo = $.trim($("#"+thisPhono).find(".chatTo").val());
		createNewChat(thisPhono, chatTo, "");
	});
	
	$('.sendMsg').live('click', function() {
		var thisPhono = $(this).closest(".phono");
		var thisChat = $(this).closest(".chatHldr");
		var msgText = thisChat.find(".chatTxtInput").val();
		var newMsg = chatMessage("You",msgText,"outgoing");
		renderNewMessage(thisChat,newMsg);
		phonos[thisPhono.attr("id")].messaging.send(chats[thisChat.attr("id")],msgText);
		thisChat.find(".chatTxtInput").val("");
		log.info("["+thisPhono.attr('id')+"] Sending message to: " + chats[thisChat.attr('id')] + " [" + msgText + "]");
	});
	
	$('.hangup').live('click', function() {
		var thisCall = $(this).closest(".callHldr").attr("id");
		calls[thisCall].hangup();
		
		$("#"+thisCall).slideUp("fast", function(){
				$(this).remove();	
			});
	});
		
	$('.answer').live('click', function() {
		var callDiv = $(this).closest(".callHldr");
		calls[callDiv.attr("id")].answer();
		callDiv.find(".callControls").hide();
		callDiv.find(".callInputs").show();
		log.info("Call answered");
	});
	
	$('.mute').live('click', function() {
		var thisCall = $(this).closest(".callHldr").attr("id");
		calls[thisCall].mute(true);
	});
	
	$('.unMute').live('click', function() {
		var thisCall = $(this).closest(".callHldr").attr("id");
		calls[thisCall].mute(false);
	});
	
	$('.digit').live('click', function() {
		var thisCall = $(this).closest(".callHldr").attr("id");
		var theDigit = $(this).attr("value");
		calls[thisCall].digit(theDigit);
	});
	
	$('.talkStart').live('mousedown', function() {
		var thisCall = $(this).closest(".callHldr").attr("id");
		calls[thisCall].talking(true);
	});
	
	$('.talkStart').live('mouseup', function() {
		var thisCall = $(this).closest(".callHldr").attr("id");
		calls[thisCall].talking(false);
	});
	
	$('.showCall').live('click', function() {
		var thisPhono = $(this).closest(".phono");
		thisPhono.find(".phoneInput").show();
		thisPhono.find(".chatInput").hide();
	});
	
	$('.text').live('click', function() {
		var thisPhono = $(this).closest(".phono");
		var to = thisPhono.find(".jid").val();
		var msg = thisPhono.find(".msgBody").val();
		sendMessage(thisPhono.attr("id"),to,msg);
		thisPhono.find(".msgBody").val("");
	});
	
	$(".logToggler").click(function(){
		if( $("#logConsole").css("height") == "25px" ){
			$("#logConsole").css("height","245px");
			$("body").css("margin-bottom","285px");
			$(this).text("Hide Log Viewer");
		}else{
			$("#logConsole").css("height","25px");
			$("body").css("margin-bottom","25px");
			$(this).text("Show Log Viewer");
		}
		
		return false;
	});

	if(window.location.protocol != "http:"){
		var errorText = "Looks like you are running this sample locally and not on a web server. To run this example, either load it from a web server or <a href='http://www.macromedia.com/support/documentation/en/flashplayer/help/settings_manager04.html' target='_blank'>edit your Flash security settings</a>.";
		errorText += "<br/>Select \"Edit locations\" > \"Add location\" > \"Browse for folder\" and select the \"/js\" folder in the root of your download.";
		errorText += " <a href='#' onclick='$(this).parent().slideUp();'>Close this</a>";
		var errorBox = $("<div>")
			.addClass("error")
			.html(errorText)
			.prependTo("body");
	}
	
	//Create a phono when the sample is loaded
	createNewPhono();
});

//Log4Javascript Logging
var log = log4javascript.getLogger("main");
var appender = new log4javascript.InPageAppender("logHldr",[lazyInit = false,initiallyMinimized = false]);
log.addAppender(appender);
appender.setShowCommandLine(false);
appender.setHeight("245px");

Strophe.log = function(level, msg) {
	log.info(msg);
};