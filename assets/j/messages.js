/**
 * "The contents of this file are subject to the Mozilla Public License
 *  Version 1.1 (the "License"); you may not use this file except in
 *  compliance with the License. You may obtain a copy of the License at
 *  http://www.mozilla.org/MPL/

 *  Software distributed under the License is distributed on an "AS IS"
 *  basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 *  License for the specific language governing rights and limitations
 *  under the License.

 *  The Original Code is OpenVBX, released June 15, 2010.

 *  The Initial Developer of the Original Code is Twilio Inc.
 *  Portions created by Twilio Inc. are Copyright (C) 2010.
 *  All Rights Reserved.

 * Contributor(s):
 **/

var Message = {};

Message.Select = {
	select_all : function(list) {
		$('input[type="checkbox"]', list).prop('checked', true).trigger('checked');
	},
	select_none : function(list) {
		$('input[type="checkbox"]', list).prop('checked', false).trigger('unchecked');
	},
	select_unread : function(list) {
		$('input[type="checkbox"]', list.filter('.read')).prop('checked', false).trigger('unchecked');
		$('input[type="checkbox"]', list.filter('.unread')).prop('checked', true).trigger('checked');
	},
	select_read : function(list) {
		$('input[type="checkbox"]', list.filter('.unread')).prop('checked', flase).trigger('unchecked');
		$('input[type="checkbox"]', list.filter('.read')).prop('checked', true).trigger('checked');
	}
};

Message.Detail = {
	update : function(id, data, success)
	{
		var detail_id = typeof(id) == 'object' ? id.join(',') : id;
		$.ajax({
			url : OpenVBX.home + '/messages/details/' + detail_id,
			success : function(data) {
				if(!data.error) {
					success(data);

					/* Update the counts */
					$.ajax({
						url : OpenVBX.home + '/messages/inbox',
						success : function(data) {
							if(!data.error) {
								$.each(data.folders, function(i) {
									$('.count[rel="'+this['id']+'"]').text(this['new']);
								});
							}
						},
						type: 'GET',
						dataType: 'json'
					});

				}
			},
			error : function() {
			},
			data : data,
			dataType : 'json',
			type : 'POST'
		});
	},
	archive : function(id, success) {
		var data = {
			archived : true
		};
		this.update(id, data, success);
	},
	assign : function(id, user_id, success) {
		var data = {
			assigned : user_id
		};
		this.update(id, data, success);
	}
};

Message.Player = {
	getRecordingUrls : function(afterFetch) {
		if(Message.Player.messageIdsToRecordingURLs) {
			return afterFetch();
		}

		$.ajax({
			success : function(data) {
				if(data.error) {
					return;
				}

				Message.Player.messageIdsToRecordingURLs = {};

				if( 'recording_url' in data )
					Message.Player.messageIdsToRecordingURLs[data.id] = data.recording_url + '.mp3';
				if( 'messages' in data )
					for( var id in Message.Player.messages ) {
						var item = data.messages[id];
						Message.Player.messageIdsToRecordingURLs[item.id] = item.recording_url + '.mp3';
					}

				Message.Player.allowPause = true;
				return afterFetch();
			},
			type : 'GET',
			dataType : 'json'
		});
	},

	messageIdsToRecordingURLs : null,

	currentSoundObject : null,
	currentMessageAnchor : null,

	lastLoadBarHandler : null,

	// When 'allowPause' is true, the Play button will turn to a
	// Pause button after the message starts playing.
	allowPause : false,

	nextMessageToPlayAnchor : null,

 	togglePlayForMessage : function(anchor) {
		var _togglePlayForMessage = function(anchor) {
			if (Message.Player.currentMessageAnchor != null && Message.Player.currentMessageAnchor != anchor) {
				// A message is already playing but the user wants to play a new one.

				// Save a reference to the next message to play.  As soon as the stop
				// event function get's called, we'll start playing this next message.
				Message.Player.nextMessageToPlayAnchor = anchor;

				// We have to stop the current message before we can start a new one
				Message.Player.currentSoundObject.stop();

			} else if (Message.Player.currentMessageAnchor != null && Message.Player.currentMessageAnchor == anchor) {

				if (Message.Player.currentSoundObject.paused) {
					Message.Player.currentSoundObject.resume();
				} else if (Message.Player.allowPause) {
					Message.Player.currentSoundObject.pause();
				} else {
					// The user wants to stop play on the current message
					Message.Player.currentSoundObject.stop();
				}
			} else {
				// We're not playing anything right now, and everything is ready to go.
				Message.Player.playMessage(anchor);
			}
		};
		Message.Player.getRecordingUrls(function(){ _togglePlayForMessage(anchor)});

	},

	playMessage: function(anchor) {
		Message.Player.currentMessageAnchor = anchor;

		anchor = $(anchor);
		var id = anchor.attr('id').replace('play-', '');

		Message.Player.currentSoundId = "message-" + id;

		if (!(id in Message.Player.messageIdsToRecordingURLs)) {
			throw "Unable to find the recording URL for id '" + id + "'";
		}

		var recordingURL = Message.Player.messageIdsToRecordingURLs[id];

		var messageRow = anchor.closest('.message-row');

		var playPauseStopButton = messageRow.find('.playback-button');
		var player = messageRow.find('.player');
		var transcript = messageRow.find('.transcript');
		var player = messageRow.find('.player');
		var loadBar = messageRow.find('.load-bar');
		var playBar = messageRow.find('.play-bar');
		var playTime = messageRow.find('.play-time');

		var updatePlayBarAndTimeWithPercent = function(soundObject, percentPlayed) {
			// If the file was already loaded, then 'whileloading' never gets called and our
			// load bar might still be stuck at zero.  Let's make sure it's always 100% once
			// we start playing...
			loadBar.css('width', '100%');
			playBar.css('width', percentPlayed + '%');

			playTime.text(convertMsecsToMinutesAndSeconds(soundObject.position));
		};

		var finishOrStop = function() {
			playPauseStopButton.removeClass('pause').removeClass('stop').addClass('play');
			player.removeClass('current-player').hide();
			transcript.show();
			Message.Player.currentMessageAnchor = null;
			Message.Player.currentSoundObject.destruct();
			Message.Player.currentSoundObject = null;

			// If there's a message waiting to play, let's play it.
			if (Message.Player.nextMessageToPlayAnchor != null) {
				var nextMessage = Message.Player.nextMessageToPlayAnchor;
				Message.Player.nextMessageToPlayAnchor = null;
				Message.Player.togglePlayForMessage(nextMessage);
			}
		};

		Message.Player.currentSoundObject = soundManager.createSound({
			id: Message.Player.currentSoundId,
			url: recordingURL,
			onplay: function() {
				playPauseStopButton.removeClass('play');

				if (Message.Player.allowPause) {
					playPauseStopButton.addClass('pause');
				} else {
					playPauseStopButton.addClass('stop');
				}

				player.addClass('current-player').show();
				transcript.hide();
			},
			onresume: function() {
				playPauseStopButton.removeClass('play').addClass('pause');
			},
			onpause: function() {
				playPauseStopButton.removeClass('pause').addClass('play');
			},
			onstop: finishOrStop,
			onfinish: finishOrStop,
			onload: function () {
				// Get rid of the spinner
				playTime.html('');
				loadBar.css('width', '100%');
			},
			whileloading: function() {
				var percentLoaded = Math.round((this.bytesLoaded / this.bytesTotal) * 100);
				loadBar.css('width', percentLoaded + '%');
			},
			whileplaying: function() {
				var percentPlayed = Math.round((this.position / this.duration) * 100);
				updatePlayBarAndTimeWithPercent(this, percentPlayed);
			}
		});
		Message.Player.currentSoundObject.play();


		if (Message.Player.lastLoadBarHandler != null) {
			loadBar.unbind('click', Message.Player.lastLoadBarHandler);
		}

		Message.Player.lastLoadBarHandler = function(e) {
			e.stopPropagation();

			var soundObject = Message.Player.currentSoundObject;

			var offset = loadBar.offset();
			var xOffset = e.pageX - offset.left;
			var width = loadBar.width();
			var percent = (xOffset / width)

			var msecPosition = ((xOffset / width) * soundObject.durationEstimate);

			soundObject.setPosition(msecPosition);
			updatePlayBarAndTimeWithPercent(soundObject, Math.round((xOffset / width) * 100));
		};
		loadBar.click(Message.Player.lastLoadBarHandler);

	}
};

$(document).ready(function() {

	(function(){

		var now = new Date();
		var midnightToday = new Date(now.getFullYear() , now.getMonth(), now.getDate(), 0, 0, 0).getTime();
		var firstOfTheYear = new Date(now.getFullYear(), 0, 1, 0, 0, 0).getTime();

		$(".unformatted-absolute-timestamp").each(function(index, element) {
			element = $(element);

			var timestamp = parseInt(element.text()) * 1000;;

			element.text(convertTimeToString(timestamp, midnightToday, firstOfTheYear));

			element.removeClass('hide');
		});

		var refreshRelativeTimes = function() {
			$(".unformatted-relative-timestamp").each(function(index, element) {
				element = $(element);

				var timestamp = element.data('timestamp');

				if (!timestamp) {
					// Store away the original time because we're about to
					// erase the body of this element
					timestamp = parseInt(element.text()) * 1000;
					element.data('timestamp', timestamp);
				}

				var nowTime = (new Date().getTime());
				var timeDiff = (nowTime - timestamp);

				var minsAgo = Math.floor(timeDiff / 1000 / 60);
				var hoursAgo = Math.floor(timeDiff / 1000 / 60 / 60);

				if (nowTime > timestamp && (nowTime - timestamp) < 60 * 60 * 1000) {
					// show relative time - it's less than an hour old AND
					// it's not in the future (it could happen!)

					var text;

					if (hoursAgo == 0) {
						text = minsAgo + " minutes ago";
					} else {
						text = hoursAgo + " hour" + (hoursAgo > 1 ? "s" : "") + " ago";
					}

					element.text(text);

				} else {
					// show absolute time
					element.text(convertTimeToString(timestamp));
				}

				element.removeClass('hide');
			});
		}

		refreshRelativeTimes();

		// And, refresh these relative times once a minute
		setInterval(refreshRelativeTimes, 60 * 1000);
	})();


	Message.Player.player = $('#audio-player');

	$('a.quick-play').click( function(event) {
		event.preventDefault();
		Message.Player.togglePlayForMessage(this);
	});

	$('.caller-id-phone a').click(function() {
		var anchor = $(this);
		if(anchor.parents('ul.caller-id-phone')) {
			anchor.hide()
				.parents('ul')
				.append('<li class="calling">Calling...</li>');

		} else {
			anchor.hide()
				.parent()
				.append('<li class="calling">Calling...</li>');
		}

		var call_params = {
			callerid : $('.callerid', this).text(),
			from : $('.from', this).text(),
			to: $('.to', this).text()
		};

		if ($('#vbx-client-status').hasClass('online')) {
			$.post(
				$(this).attr('href'),
				$.extend(call_params, { 'log_only': true }),
				function(data) {
					if (!data.error) {
						window.parent.Client.call($.extend(call_params, { 'Digits': 1 }));
						$('.quick-call-popup .calling').remove();
						$('.quick-call-popup.open').toggleClass('open');
					}
				},
				'json'
			);
			anchor.show();
		}
		else {
			$.ajax({
				url : $(this).attr('href'),
				data : call_params,
				dataType : 'json',
				type : 'POST',
				success : function(data) {
					if(data.error)
					{
						$('.error-dialog')
							.data('dialog.uiDialog')
							.uiDialogTitlebar.text('Twilio Sandbox');
						$('.error-dialog .error-code').text('');
						$('.error-dialog .error-message')
							.text(data.message);
						$('.error-dialog').dialog('open');
					}

					$('.quick-call-popup .calling').remove();
					$('.quick-call-popup.open').toggleClass('open');
					anchor.show();
				}
			});
		}
		return false;

	});

	// If someone clicks in the quick-call-popup, don't act as if
	// they wanted the click to go through and take them to the details page.
	// NOTE: The click for the call button still works, and is caught elsewhere.
	$('.quick-call-popup').click(function(){
		return false;
	});
	// Same goes for the sms popup
	$('.quick-sms-popup').click(function(){
		return false;
	});


	$('.quick-sms-popup .send-button').click(function(event) {
		var popup = $(this).parents('.quick-sms-popup');
		$('.sending-sms-loader').show();
		$.ajax({
			url : OpenVBX.home + '/messages/sms/' + $(this).attr('rel'),
			data : {
				from : $('.from-phone', popup).text(),
				to : $('.sms-to-phone', popup).text(),
				content : $('input[name="content"]', popup).val()
			},
			success : function(data) {
				$('.sending-sms-loader').hide();
				if(!data.error) {
					$('textarea', popup).val('');
					$(popup).hide();
				}
			},
			error : function() {
				$('.sending-sms-loader').hide();
			},
			type : 'POST',
			dataType : 'json'
		});
		event.preventDefault();
	});

	$('.message-row').hover(
		function() {
			$(this).addClass('hover');
		},
		function() {
			$(this).removeClass('hover');
		}
	);

	$('.message-row td').mousedown(function() {
		$('.message-row').removeClass('clicked');
		$(this).parent().addClass('mousedown');
	})


	$('body').mouseup(function() {
		$('.message-row').removeClass('mousedown')
	});


	$('.message-row td.message-details-link').click(function() {
		$(this).parent().addClass('clicked');
		document.location = OpenVBX.home + '/messages/details/' + $(this).parent().attr('rel');
	});

	$('.select').click(function() {
		var select_method = $(this).attr('class')
			.replace('select ','')
			.replace('-', '_');
		select_method = Message.Select[select_method] || function() {};
		select_method($('.message-row'));

		$('.dropdown-select-button').parent()
			.children('ul.open').toggleClass('open');

		return false;
	});

	$('input[type="checkbox"]').change(function() {
		if(this.checked) {
			$(this).trigger('checked');
		} else {
			$(this).trigger('unchecked');
		}
	});

	$('.message-select input[type="checkbox"]').bind('checked', function() {
		$(this).parent().parent().addClass('checked');
	}).bind('unchecked', function() {
		$(this).parent().parent().removeClass('checked');
	});

	$('.assign-button').buttonista({ menu : '.assign-to-popup' });

	// When the assign to popup comes up, want to position it so that
	// the little person icon in the popup appears directly over top of
	// the person icon in the message row.  If we can't have that, we just
	// try hard to make sure the popup doesn't go offscreen.
	$('.assign-button').click(function(event){
		var assignToPopup = $(this).parent().children('.assign-to-popup');

		// Offset by 1 so the icon stays in exactly the same place
		assignToPopup.top($(this).top() - 1);

		// Offset by 5 so the icon stays in exactly the same place
		assignToPopup.right($(this).right() + 5);

		var visibleTopOffset = $(document).scrollTop();
		var visibleBottomOffset = $(document).scrollTop() + $(window).height();

		if (assignToPopup.top() < visibleTopOffset) {
			assignToPopup.top(visibleTopOffset + 10);
		}

		if (assignToPopup.bottom() > visibleBottomOffset) {
			assignToPopup.bottom(visibleBottomOffset - 10);
		}

		event.preventDefault();
	});

	$('.quick-call-button').buttonista({ menu : '.quick-call-popup' });
	$('.quick-sms-button').buttonista({ menu : '.quick-sms-popup', toggler : '.sms-toggler', focus: '.sms-message' });
	var updateCount = function() {
		var length = $(this).val().length;
		$(this).parents('.quick-sms-popup, #reply-sms')
			.find('.count')
			.text(160 - length);
	};
	$('.quick-sms-popup input[name="content"]').live('keyup', updateCount);
	$('.quick-sms-popup input[name="content"]').keypress();
	$('#reply-sms textarea').live('keyup', updateCount);
	$('#reply-sms textarea').live('keyup', updateCount);
	$('#reply-sms textarea').keypress();
	$('#reply-sms .submit-button').click(function(event) {
		event.preventDefault();
		$('#reply-sms button').prop('disabled', true);
		$('#reply-sms .loader').show();
		$.ajax({
			url : $('#reply-sms').attr('action'),
			data : $('#reply-sms').serializeArray(),
			success : function(data) {
				$('#reply-sms .loader').hide();
				$.notify('SMS sent');
				$('#reply-sms textarea').val('');
				$('#reply-sms .submit-button').prop('disabled', false).flicker();
			},
			type : 'POST',
			dataType : 'json'
		});

		return false;
	});
	$('.dropdown-select-button').buttonista();


	$('.delete-button').click(function() {
		if($('.delete-button').attr('id') && $('.delete-button').attr('id').match('delete-')) {
			id = $(this).attr('id').replace('delete-','');
			Message.Detail.archive(id,
								   function() {
									   $('.delete-button span').text('Deleted');
								   });
		} else {
			var id = [];
			$('input[name^="message"]:checked').each(function(){
				id.push($(this).val());
			});

			if(!id.length) {
				return;
			}

			Message.Detail.archive(id,
								   function() {
									   for(var rel in id) {
										   $('tr[rel="'+id[rel]+'"]').remove();
									   }
									   $.notify('Deleted '+ id.length + ' message' + (id.length > 1 ? 's' : ''));
								   });
		}

		return false;
	});

	$('.assign-user-list .user a').live('click', function(event) {
		event.preventDefault();
		var anchor = $(this);

		var initials;

		var matches = anchor.text().match(/\((..)\)$/)

		if (matches) {
		    initials = matches[1];
		} else {
		    initials = "";
		}

		Message.Detail.assign($(this).parents('tr').attr('rel'),
							  $(this).attr('rel'),
							  function() {
								  anchor.parents('ul')
									  .find('li')
									  .removeClass('assigned');
								  anchor.parent()
									  .addClass('assigned');
								  anchor.parents('td')
									  .find('.owner-name')
									  .text(initials);
								  anchor.parents('.assign-to-popup').removeClass('open');
							  });
	});


	$('#save-details').click(function() {
		$('#message-details').submit();
	});

});
