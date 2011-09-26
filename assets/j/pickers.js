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

var swfu;

var Pickers = {
	timing : {
		setDisabled : function($widget, disabled) {
			$control = $widget.siblings('a');
			if (disabled) {
				$("<em>").text("Closed").insertBefore($widget);
				$widget.hide().find('input').val('');
				$control.html("add").attr({ "class" : "timing-add" });
			} else {
				$widget.show().siblings('em').remove();
				range = [new Date(0, 0, 0, 9, 0, 0), new Date(0, 0, 0, 17, 0, 0)];
				$widget.find('input').each(function() {
					if(!$(this).val())
						$.timePicker($(this)).setTime(range.shift());
				});
				$control.html("remove").attr({ "class" : "timing-remove" });
			}
		}
	},
	audio : {
		picker : null,

		saveValue : function(audioChoice, mode, sayValue, playValue) {
			audioChoice.find('input[name$="_mode"]').val(mode);
			audioChoice.find('input[name$="_say"]').val((sayValue == null) ? "" : sayValue);
			audioChoice.find('input[name$="_play"]').val((playValue == null) ? "" : playValue);

			var value;
			
			if (mode == 'say') {
				value = sayValue;
			} else if (mode == 'play') {
				value = playValue;
			} else {
				alert("Unexpected mode: " + mode);
			}

			audioChoice.trigger("save", [mode, value]);
		},
		
		cancelDeviceRecordingIfActive : function(audioChoice) {
			var cancelFunction = audioChoice.data('cancel_recording_function');
			
			if (cancelFunction) {
				cancelFunction();
			}
		},
		
		startDeviceRecording : function(event) {
			event.preventDefault();
			
			var audioChoice = $(this).closest('.audio-choice');
			
			// We'll tag the recording with this value.
			var tag = audioChoice.find('input[name$="_tag"]').val();

			// We steal our caller ID information from the Call Popup
			var callerIdAsSelect = $('select[name="callerid"] option:first');
			var callerIdAsSelectLast = $('select[name="callerid"] option:last');
			var callerIdAsInput = $('input[name="callerid"]');

			if (callerIdAsSelect.length > 0) {
			    callerId = callerIdAsSelect.val();
			    if (callerId == '') {
			    	callerId = callerIdAsSelectLast.val();
			    }
			} else {
		        callerId = callerIdAsInput.val();
			}

			console.log(callerId);

			var numberToCall = audioChoice.find('.audio-choice-record').find('input[name="number"]').val();

			var showInputView = function() {
				audioChoice.find('.audio-choice-editor .audio-choice-record .input').show();
				audioChoice.find('.audio-choice-editor .audio-choice-record .status.scheduling').hide();
				audioChoice.find('.audio-choice-editor .audio-choice-record .status.in-call').hide();
			}
			
			var showStatusForSchedulingCall = function() {
				audioChoice.find('.audio-choice-editor .audio-choice-record .input').hide();
				audioChoice.find('.audio-choice-editor .audio-choice-record .status.scheduling').show();
				audioChoice.find('.audio-choice-editor .audio-choice-record .status.in-call').hide();
			}

			var showStatusForInCall = function() {
				audioChoice.find('.audio-choice-editor .audio-choice-record .input').hide();
				audioChoice.find('.audio-choice-editor .audio-choice-record .status.scheduling').hide();
				audioChoice.find('.audio-choice-editor .audio-choice-record .status.in-call').show();
			}
			
			var recordingIsCancelled = false;
			var audioFileId = null;
			
			var cancelFunction = function() {
				if (!recordingIsCancelled) {
					recordingIsCancelled = true;
					
					showInputView();
					
					// We can only really _cancel_ the request if we have the audioFileId
					if (audioFileId != null) {
						$.ajax({
							url : OpenVBX.home + '/audiofiles/cancel_recording',
							type : 'POST',
							dataType : 'json',
							data : {
								id : audioFileId
							},
							success : function() {
							}
						});
					}
				}
			};
			
			// Whenever the close button is clicked, this cancel function will get called.
			audioChoice.data('cancel_recording_function', cancelFunction);

			showStatusForSchedulingCall();
			
			$.ajax({
				url : OpenVBX.home + '/audiofiles/add_from_twilio_recording',
				data : {
					to : numberToCall,
					callerid : callerId,
					tag : tag
				},
				success : function(data) {
					if (data.error) {
						showInputView();
						
						$('.audio-choice-record .error', audioChoice)
							.html(data.message)
							.removeClass('hide');

					} else {
						audioFileId = data.id;
						
						if (recordingIsCancelled && data.id) {
							// Someone immediately clicked cancel right after starting the call.  They're going to get dialed
							// and there's nothing we can do about that.  But, if we issue a cancel request right now, hopefully
							// it'll get marked as cancelled before they answer.

							$.ajax({
								url : OpenVBX.home + '/audiofiles/cancel_recording',
								type : 'POST',
								dataType : 'json',
								data : {
									id : audioFileId
								},
								success : function() {
								}
							});
							return;
						}

						showStatusForInCall();
						
						var recordingComplete = function(data) {
							
							audioChoice.data('cancel_recording_function', null);
							
							showInputView();
							
							// Save the new URL in our fake form.  When the user saves the flow, this will
							// get committed to the database.
							Pickers.audio.saveValue(audioChoice, 'play', null, data.url);

							Pickers.audio.insertRecordingIntoLibrary(audioChoice, data.url, data.label);

							Pickers.audio.closeEditorAndShowPlayValue(audioChoice, data.url, true);
						};
			
						var poormansLimit = 25;
						var poormansCounter = 0;
						var checkRecordingComplete = function(data) {
							
							if (recordingIsCancelled) {
								return;
							}
							
							if (data) {
								if (data.error) {
									$('.error-dialog').dialog('option', 'buttons', { 
										"Ok": function() {
											showInputView();
											$(this).dialog("close");
										} 
									});
									$('.error-dialog .error-code').text('');
									$('.error-dialog .error-message').text('There was a problem checking to see if the recording had finished: ' + data.message);
									$('.error-dialog').dialog('open');
									
									// Return so we don't schedule another timeout
									return;
								} else if (data.finished) {
									return recordingComplete(data);
								} else {
									// It's not finished yet!
								}
							}
							 
							if (poormansCounter <= poormansLimit) {
								poormansCounter ++;
								return setTimeout(function() {
									$.ajax({
										url : OpenVBX.home + '/audiofiles/check_if_recording_is_finished',
										type : 'POST',
										dataType : 'json',
										data : {
											id : audioFileId
										},
										success : checkRecordingComplete,
										error : function(request, status, error) {
											// We swallow the error.  This can/will happen during normal use
											// when someone cancels the recording but this request is already
											// in flight
										},
										global : false
										});
								}, 6000);
							}
						};
			
						checkRecordingComplete();
					}
				},
				type : 'POST',
				dataType : 'json'
			});
		},
		
		insertRecordingIntoLibrary : function(audioChoice, url, label) {
			var librarySelect = audioChoice.find('select[name="library"]');
			$("<option value='" + url + "' title='" + (new Date().getTime() / 1000) + "'>" + label + "</option>").insertAfter(librarySelect.find('option[value=""]'));
			
			// If library wasn't visible before, it should be visible now.
			audioChoice.find('.audio-choice-library .empty-container').hide();
			audioChoice.find('.audio-choice-library .chooser-container').show();
		},

		chooseRecordingFromLibrary : function(event) {
			event.preventDefault();
			
			var audioChoice = $(this).closest('.audio-choice');
			
			Pickers.audio.showPlayer(audioChoice, 'library', $(this).val(), true);
		},
		
		setRecordingFromLibarary : function(event) {
			event.preventDefault();

			var audioChoice = $(this).closest('.audio-choice');
			var url = audioChoice.find('select[name="library"]').val();

			if (url != '') {
				Pickers.audio.saveValue(audioChoice, 'play', null, url);

				Pickers.audio.closeEditorAndShowPlayValue(audioChoice, url, false);
			} else {
				// nothing is selected - don't do anything
			}
		},
		
		hidePlayer : function(audioChoice, playerPrefix) {
			var soundId = audioChoice.data('soundId');

			if (soundId) {
				soundManager.destroySound(soundId);
				audioChoice.data('soundId', null);
			}

			audioChoice.find('.' + playerPrefix + '-player').hide();
		},

		showPlayer : function(audioChoice, playerPrefix, url, autoPlay) {
			matches = url.match(/^vbx-audio-upload:\/\/(.*)$/);
			
			if (matches) {
				url = OpenVBX.assets + "/audio-uploads/" + matches[1];
			}

			var player = audioChoice.find('.' + playerPrefix + '-player')
			player.show();

			var playButton = player.find('.' + playerPrefix + '-play-button');
			var pauseButton = player.find('.' + playerPrefix + '-pause-button');
			var loadBar = player.find('.' + playerPrefix + '-load-bar');
			var playBar = player.find('.' + playerPrefix + '-play-bar');
			var audioPlayTime = player.find('.' + playerPrefix + '-play-time');

			// Make sure everything starts with the default state
			playButton.show();
			pauseButton.hide();
			loadBar.css('width', '0px');
			playBar.css('width', '0px');
			audioPlayTime.html("<img src=\"" + OpenVBX.assets + "/assets/i/ajax-loader.gif\" alt=\"...\" />");
			
			var updatePlayBarAndTimeWithPercent = function(soundObject, percentPlayed) {
				// If the file was already loaded, then 'whileloading' never gets called and our
				// load bar might still be stuck at zero.  Let's make sure it's always 100% once
				// we start playing...
				loadBar.css('width', '100%');
				playBar.css('width', percentPlayed + '%');
				
				audioPlayTime.text(convertMsecsToMinutesAndSeconds(soundObject.position));
			};
			
			
			var showPlayHidePause = function() {
				playButton.show();
				pauseButton.hide();
			};
			
			var hidePlayShowPause = function() {
				playButton.hide();
				pauseButton.show();
			};
			
			// In case we're repurposing the same player w/out hiding it first,
			// we need to unload whatever sound might already be associated with this
			// player id.
			var lastSoundId = audioChoice.data('soundId');
			if (lastSoundId != null) {
				if (soundManager.getSoundById(lastSoundId) != null) {
					soundManager.destroySound(lastSoundId);
				}
			}
			
			var soundId = 'sound-' + Math.round((Math.random() * 1000000)).toString(16);
			
			// Tuck this away for now.  When the player gets hidden, we'll pull this
			// out and use it to unload the current sound object.
			audioChoice.data('soundId', soundId);
			
			var soundObject = soundManager.createSound({
				id: soundId,
				url: url,
				onplay: hidePlayShowPause,
				onresume: hidePlayShowPause,
				onpause: showPlayHidePause,
				onfinish: showPlayHidePause,
				onload: function () {
					// Get rid of the spinner
					audioPlayTime.html('');
					
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
			
			playButton.unbind();
			playButton.click(function(event) {
				event.preventDefault();
				
				if (soundObject.paused) {
					soundObject.resume();
				} else {
					soundObject.play();
				}
			})

			pauseButton.unbind();
			pauseButton.click(function(event) {
				event.preventDefault();
				soundObject.pause();
			});
			
			// Let the user seek w/in the file
			loadBar.unbind();
			loadBar.click(function(e) {
				var offset = loadBar.offset();
				var xOffset = e.pageX - offset.left;
				var width = loadBar.width();
				var percent = (xOffset / width)
				
				var msecPosition = ((xOffset / width) * soundObject.durationEstimate);
				
				soundObject.setPosition(msecPosition);
				updatePlayBarAndTimeWithPercent(soundObject, Math.round((xOffset / width) * 100));
			});

			if (autoPlay) {
				soundObject.play();
			} else {
				// Even if we're not going to start playing the file now, we want to start
				// buffering it.
				soundObject.load();
			}
		},
		
		selectAction : function(event) {
			event.preventDefault();
			var selection = $(this).attr('href');

			// Find the root of our audio speech picker
			var audioChoice = $(this).closest('.audio-choice');
			audioChoice.trigger('editor-open', [audioChoice]);

			// Hide the audio choice selector now that we have an input method selected
			audioChoice.find('.audio-choice-selector').hide();

			// Show the parent editor div
			audioChoice.find('.audio-choice-editor').show();
			// And then the individual editor for the input method we've chosen.
			audioChoice.find('.audio-choice-editor .audio-choice-' + selection).show();

			if (selection == 'read-text') {
				// Start with whatever the current value is.
				var text = audioChoice.find('input[name$="_say"]').val();
				audioChoice.find('.audio-choice-editor .audio-choice-' + selection).find('textarea').val(text);
			} else if (selection == 'library') {

				// Normally we'd register to receive change() events via .live(...) but that apparenlty doesn't
				// work on IE.  The 'change' events in particular don't bubble up.
				var select = audioChoice.find('.audio-choice-library').find('select[name="library"]');
				select.unbind('change', Pickers.audio.chooseRecordingFromLibrary);
				select.change(Pickers.audio.chooseRecordingFromLibrary);
				
				// Append timestamps to the end of each option
				select.find('option').each(function (index, element) {
					element = $(element);
					
					var title = element.attr('title');
					
					if (title && (title != '')) {
						// Only do this once!
						element.attr('title', '');
						
						var timestamp = parseInt(title) * 1000;
						element.text(element.text() + " (" + convertTimeToString(timestamp) + ")");
					} else {
						// It's already been done
					}
				});
				
				
			} else if (selection == 'upload') {

				swfControl = audioChoice.find('.swfupload-control');

				var button = audioChoice.find('.button');

				var showErrorDialogWithMessage = function(message) {
					$('.error-dialog').dialog('option', 'buttons', { 
						"Ok": function() { 
							$(this).dialog("close"); 
						}
					});
					$('.error-dialog .error-code').text('');
					$('.error-dialog .error-message').text(message);
					$('.error-dialog').dialog('open');
				}

				swfControl.swfupload({
					upload_url: OpenVBX.home + '/audiofiles/add_file',
					file_size_limit : "10240",
					file_types : "*.mp3",
					file_types_description : "Audio Files",
					file_upload_limit : "0",
					flash_url : OpenVBX.assets + "/assets/j/swfupload/swfupload.swf",
					button_image_url : OpenVBX.assets + '/assets/j/swfupload/transparent_538x68.png',
					button_width : '100%',
					button_height : 68,
					button_placeholder : button[0],
					button_window_mode: 'transparent',
					debug: false,
					post_params : {
						'tag' : audioChoice.find('input[name$="_tag"]').val()
					}
				})
				.bind('fileQueued', function(event, file){
					// start the upload since it's queued
					$(this).swfupload('startUpload');

					// Show upload progress bar / hide the upload button
					audioChoice.find('.audio-choice-editor .audio-choice-upload .upload-bar-container').show();
					audioChoice.find('.audio-choice-editor .audio-choice-upload .swfupload-container').css('visibility', 'hidden');
					audioChoice.find('.audio-choice-editor .audio-choice-upload .swfupload-container').css('height', '0px');
				})
				.bind('uploadSuccess', function(event, file, serverData){
					var result = JSON.parse(serverData);

					if (result.error) {
						showErrorDialogWithMessage(result.message);
					} else {
						Pickers.audio.saveValue(audioChoice, 'play', null, result.url);

						Pickers.audio.insertRecordingIntoLibrary(audioChoice, result.url, result.label);
						Pickers.audio.closeEditorAndShowPlayValue(audioChoice, result.url, true);
					}

					// Hide our progress bar
					audioChoice.find('.audio-choice-editor .audio-choice-upload .upload-progress-bar').css('width', '0px');
					audioChoice.find('.audio-choice-editor .audio-choice-upload .upload-bar-container').hide();
					audioChoice.find('.audio-choice-editor .audio-choice-upload .swfupload-container').css('visibility', 'visible');
					audioChoice.find('.audio-choice-editor .audio-choice-upload .swfupload-container').css('height', '68px');
				})
				.bind('uploadProgress', function(event, file, bytesLoaded, bytesTotal) {
					audioChoice.find('.audio-choice-editor .audio-choice-upload .upload-progress-bar').css('width', ((bytesLoaded / bytesTotal) * 100) + '%');
				})
				.bind('uploadComplete', function(event, file){
					// upload has completed, lets try the next one in the queue
					// $(this).swfupload('startUpload');
				})
				.bind('uploadError', function(event, file, errorCode, message){
					showErrorDialogWithMessage("Upload failed: " + message);
				});				
			}
		},
		
		closeEditor : function(audioChoice) {
			
			audioChoice.trigger('editor-close', [audioChoice]);

			// If the audio uploader was visible, we need to unload the flash object
			if (audioChoice.find('.audio-choice-upload').is(':visible')) {
				audioChoice.find('.swfupload-control').swfuploadUnload();
			}
			
			if (audioChoice.find('.audio-choice-record').is(':visible')) {
				// cancel the recording!
				Pickers.audio.cancelDeviceRecordingIfActive(audioChoice);
			}
			
			// Reset the state of the library UI in case it was used
			audioChoice.find('select[name="library"]').attr('selectedIndex', 0);
			Pickers.audio.hidePlayer(audioChoice, 'library');
			
			// Hide every editor
			audioChoice.find('.audio-choice-editor-padding').children().each(function(index){
				$(this).hide();
			});
			
			// Hide the container of all the editors
			audioChoice.find('.audio-choice-editor').hide();
		},
		
		closeEditorsAndShowSelector : function(event) {
			event.preventDefault();

			var audioChoice = $(this).closest('.audio-choice');
			
			Pickers.audio.closeEditor(audioChoice);
			
			// Hide errors and clear them
			audioChoice.find('.error').text('').addClass('hide');

			// Show our main selector
			audioChoice.find('.audio-choice-selector').show();
		},
		
		closeEditorAndShowSayValue : function(audioChoice, sayValue) {
			Pickers.audio.closeEditor(audioChoice);
			
			audioChoice.find('.audio-choice-current-value').show();
			audioChoice.find('.audio-choice-current-value .audio-choice-play-audio').hide();
			audioChoice.find('.audio-choice-current-value .audio-choice-read-text').show();
			audioChoice.find('.audio-choice-current-value .audio-choice-read-text').find('.read-text').text(sayValue);
		},

		closeEditorAndShowPlayValue : function(audioChoice, playValue, autoPlay) {
			Pickers.audio.closeEditor(audioChoice);
			
			audioChoice.find('.audio-choice-current-value').show();
			audioChoice.find('.audio-choice-current-value .audio-choice-play-audio').show();
			audioChoice.find('.audio-choice-current-value .audio-choice-read-text').hide();
			audioChoice.find('.audio-choice-current-value .audio-choice-read-text').find('.read-text').text();
			
			Pickers.audio.showPlayer(audioChoice, 'current', playValue, autoPlay);
		},

		showInputSelector : function(event) {
			event.preventDefault();

			var audioChoice = $(this).closest('.audio-choice');
			var currentValue = audioChoice.find('.audio-choice-current-value');
			
			// Hide any of the current value renderings that are active
			currentValue.children().each(function (index) {
				$(this).hide();
			})
			
			currentValue.hide();
			audioChoice.find('.audio-choice-selector').show();
			
			// Since we have a current value already, provide a way to cancel to change.
			audioChoice.find('.audio-choice-close-button').show();
		},
		
		closeSelectorAndShowCurrentValue : function(event) {
			event.preventDefault();
			
			var audioChoice = $(this).closest('.audio-choice');

			audioChoice.find('.audio-choice-editor').hide();
			audioChoice.find('.audio-choice-selector').hide();
			audioChoice.find('.audio-choice-current-value').show();

			var mode = audioChoice.find('input[name$="_mode"]').val();
			if (mode == 'say') {
				audioChoice.find('.audio-choice-current-value .audio-choice-read-text').show();
			} else if (mode == 'play') {
				audioChoice.find('.audio-choice-current-value .audio-choice-play-audio').show();
			} else {
				alert("Unexpected mode: " + mode);
			}
		},
		
		saveReadText : function(event, audioChoice) {
			event.preventDefault();
			var parent = $(this).closest('.audio-choice-read-text');
			if($(audioChoice).length) {
				parent = $(audioChoice).closest('.audio-choice-read-text');
				audioChoice = $(audioChoice).closest('.audio-choice');
			} else {
				audioChoice = $(this).closest('.audio-choice');
			}

			var text = parent.find('.voicemail-text').val();
			
			// If no text value found, handle gracefully without saving.
			if(text) {
				// Set state that will be saved when the flow is saved.
				Pickers.audio.saveValue(audioChoice, 'say', text, null);
				Pickers.audio.closeEditorAndShowSayValue(audioChoice, text);
			}
			else {
				$.notify('Text to be read cannot be empty (Hint: insert a space to say nothing).');
			}
		}
	},
	
	usergroup : {
		picker : null,
		open : function(data) {
			
			// If we have an older version lying around, we want it to go away
			if ($('.usergroup-dialog').length > 0) {
				$('.usergroup-dialog').remove();
			}
			
			$('body').append($(data));
		
			$('.usergroup-dialog').dialog({ 
				autoOpen: false,
				bgiframe: true,
				resizable: false,
				height:480,
				width:500,
				modal: true,
				title: 'Choose a user or group',
				overlay: {
					backgroundColor: '#000',
					opacity: 0.5
				},
				buttons: {
					Cancel: function() {
						$(this).dialog('close');
					},
					"Add User" : Pickers.usergroup.addUser,
					"Add Group" : Pickers.usergroup.addGroup
				}
			});

			$('.usergroup-dialog').dialog('open');
		},
		dialog : function(event) {
			event.preventDefault();

			Pickers.usergroup.picker = event.target;

			$.ajax({
				url : OpenVBX.home + '/dialog/usergroup',
				cache : false,
				data : { 
					'barebones' : 1
				},
				success : function(data, textStatus) {
					Pickers.usergroup.open(data);
				},
				dataType : 'html'
			});
			
			return false;
		},
		hoverIn : function(event) {
			$(event.target).parents('tr').addClass('hover');
		},
		hoverOut : function(event) {
			$(event.target).parents('tr').removeClass('hover');
		},
		select : function(event) {
			var tr = $(event.target).parents('tr');
			
			var usergroup_id = tr.attr('rel').replace(/^(user|group)_/, '');
			var usergroup_type = tr.hasClass('user') ? 'user' : 'group';
			var usergroup_label = '';
			
			var cells = $(event.target).parents('tr').children('td')

			if (usergroup_type == 'user') {
				usergroup_label = $(cells[1]).text() + " (" + $(cells[2]).text() + ")"
			} else {
				usergroup_label = $(cells[1]).text();
			}
			
			Pickers.usergroup.setPickerValue(usergroup_id, usergroup_type, usergroup_label);
		},
		setPickerValue : function (usergroup_id, usergroup_type, usergroup_label) {
			var container = $(Pickers.usergroup.picker).parents('.usergroup-container');
			
			$(container).find('p').addClass('selected-usergroup').removeClass('placeholder');
			
			$('input.usergroup-id', container).val(usergroup_id);
			$('input.usergroup-type', container).val(usergroup_type);
			$('.selected-usergroup', container).text(usergroup_label);
			
			$('.usergroup-dialog').dialog('close');
			$(container).trigger('usergroup-selected', [usergroup_label, usergroup_type, usergroup_id]);
			
			
		},
		editUser : function(event) {
			event.preventDefault();
			event.stopPropagation();
			
			var userId = $(event.target).closest('tr').attr('rel').replace(/^user_/, '');
			$.postJSON('accounts/user/get', { id: userId }, showUserEdit);

			$(document).unbind("user-edited", Pickers.usergroup.userEdited);
			$(document).bind("user-edited", Pickers.usergroup.userEdited);

			return false;
		},
		editGroup : function(event) {
			event.preventDefault();
			event.stopPropagation();

			var groupId = $(event.target).closest('tr').attr('rel').replace(/^group_/, '');
			$.postJSON('accounts/group/get', { id: groupId }, showGroupEdit);
			
			$(document).unbind("group-edited", Pickers.usergroup.groupEdited);
			$(document).bind("group-edited", Pickers.usergroup.groupEdited);
			
			return false;
		},
		addUser : function() {
			showUserAdd(null);
			
			$(document).unbind("user-added", Pickers.usergroup.userAdded);
			$(document).bind("user-added", Pickers.usergroup.userAdded);

		},
		addGroup : function(event) {
			showGroupEdit(null);
			
			$(document).unbind("group-added", Pickers.usergroup.groupAdded);
			$(document).bind("group-added", Pickers.usergroup.groupAdded);
		},
		userAdded : function(event, data) {
			Pickers.usergroup.setPickerValue(data.id, 'user', data.first_name + " " + data.last_name + " (" + data.email + ")");
		},
		userEdited : function(event, data) {
			var tr = $('.usergroup-dialog .users-and-groups-table tr[rel="user_' + data.id + '"]');
			var cells = tr.find('td');
			
			$(cells[1]).text(data.first_name + ' ' + data.last_name);
			$(cells[2]).text(data.email);
		},
		groupAdded : function(event, data) {
			Pickers.usergroup.setPickerValue(data.id, 'group', data.name);
		},
		groupEdited : function(event, data) {
			var tr = $('.usergroup-dialog .users-and-groups-table tr[rel="group_' + data.id + '"]');
			var cells = tr.find('td');
			
			$(cells[1]).text(data.name);
		}
	}
};

$(document).ready(function() {

    if($.browser.msie) {
        // When someone clicks one of the [read text, upload, record, library] items from
        // the selector, we should open the editor for that input type.
        $('.audio-choice-selector a').livequery('click', Pickers.audio.selectAction);
        // When someone cancels the editor, we should return to the input type selector
        $('.audio-choice-editor .audio-choice-close-button').livequery('click', Pickers.audio.closeEditorsAndShowSelector);
        // When someone saves their new "read text" setting, we should save and then show the newly saved value
        $('.audio-choice-editor .audio-choice-read-text button[type="submit"]').livequery('click', Pickers.audio.saveReadText);
        // When someone wants clicks edit on the current value, we should show the input selector guy
        $('.audio-choice-current-value .change').livequery('click', Pickers.audio.showInputSelector);
        // Whene someone closes the selector, we should go back to the current value
        $('.audio-choice-selector .audio-choice-close-button').livequery('click', Pickers.audio.closeSelectorAndShowCurrentValue);
        // When someone clicks the call & record button, they should get a phone call
        $('.audio-choice-editor .audio-choice-record button[type="submit"]').livequery('click', Pickers.audio.startDeviceRecording);
        // When someone is on the library, and they've set one of the recordings to be the active recording, we should save it and go back to the input selector
        $('.audio-choice-editor .audio-choice-library button[type="submit"]').livequery('click', Pickers.audio.setRecordingFromLibarary);
        
        // When someone hovers over the flash upoader, we want to change the background color that's
        // _behind_ the flash widget 
        $('.audio-choice-editor .swfupload-control').livequery('mouseenter', function(e) {
            $(e.target).closest('.audio-choice').find('.swfupload-container').addClass('hover');
        });
        $('.audio-choice-editor .swfupload-control').livequery('mouseleave', function(e) {
            $(e.target).closest('.audio-choice').find('.swfupload-container').removeClass('hover');
        });

        $('.usergroup-picker').livequery('click', Pickers.usergroup.dialog);
        $('.usergroup-dialog td').livequery('mouseover', Pickers.usergroup.hoverIn)
            .livequery('mouseout', Pickers.usergroup.hoverOut);
        $('.usergroup-dialog td').livequery('click', Pickers.usergroup.select);
        
        $('.usergroup-dialog a.edit-user').livequery('click', Pickers.usergroup.editUser);
        $('.usergroup-dialog a.edit-group').livequery('click', Pickers.usergroup.editGroup);
    } else {
        // When someone clicks one of the [read text, upload, record, library] items from
        // the selector, we should open the editor for that input type.
        $('.audio-choice-selector a').live('click', Pickers.audio.selectAction);
        // When someone cancels the editor, we should return to the input type selector
        $('.audio-choice-editor .audio-choice-close-button').live('click', Pickers.audio.closeEditorsAndShowSelector);
        // When someone saves their new "read text" setting, we should save and then show the newly saved value
        $('.audio-choice-editor .audio-choice-read-text button[type="submit"]').live('click', Pickers.audio.saveReadText);
        // When someone wants clicks edit on the current value, we should show the input selector guy
        $('.audio-choice-current-value .change').live('click', Pickers.audio.showInputSelector);
        // Whene someone closes the selector, we should go back to the current value
        $('.audio-choice-selector .audio-choice-close-button').live('click', Pickers.audio.closeSelectorAndShowCurrentValue);
        // When someone clicks the call & record button, they should get a phone call
        $('.audio-choice-editor .audio-choice-record button[type="submit"]').live('click', Pickers.audio.startDeviceRecording);
        // When someone is on the library, and they've set one of the recordings to be the active recording, we should save it and go back to the input selector
        $('.audio-choice-editor .audio-choice-library button[type="submit"]').live('click', Pickers.audio.setRecordingFromLibarary);
        
        // When someone hovers over the flash upoader, we want to change the background color that's
        // _behind_ the flash widget 
        $('.audio-choice-editor .swfupload-control').live('mouseenter', function(e) {
            $(e.target).closest('.audio-choice').find('.swfupload-container').addClass('hover');
        });
        $('.audio-choice-editor .swfupload-control').live('mouseleave', function(e) {
            $(e.target).closest('.audio-choice').find('.swfupload-container').removeClass('hover');
        });

        $('.usergroup-picker').live('click', Pickers.usergroup.dialog);
        $('.usergroup-dialog td').live('mouseover', Pickers.usergroup.hoverIn)
            .live('mouseout', Pickers.usergroup.hoverOut);
        $('.usergroup-dialog td').live('click', Pickers.usergroup.select);
        
        $('.usergroup-dialog a.edit-user').live('click', Pickers.usergroup.editUser);
        $('.usergroup-dialog a.edit-group').live('click', Pickers.usergroup.editGroup);
    }
});
