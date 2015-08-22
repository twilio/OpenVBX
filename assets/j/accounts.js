/*
Security.js - Group/user editing script
*/

var valUser; // handle to user validator
var valGroup; // handle to group validator

$(document).ready(function() {
	dlgInviteUser = $('#dialog-invite-user').dialog({ 
		autoOpen: false,
		width: 640,
		buttons: {
			'OK': function() {
				$.postJSON('accounts/user/invite', 
						   $('input', dlgInviteUser),
						   function(data) {
							   if(!data.success) {
								   $('.error-message', dlgInviteUser).text(data.error);
								   return;
							   }

								buildUserBlock(data);
								$(this).dialog('close');
						   }
						  );
			},
			Cancel: function() {
				$(this).dialog('close');
			}
		}
	});
	
	var dialogUserEditOrAddPrototype = $('#dialog-user-edit-or-add-prototype');
	
	dialogUserEditOrAddPrototype.clone().attr('id', 'dialog-user-edit').appendTo(dialogUserEditOrAddPrototype.parent());	
	dialogUserEditOrAddPrototype.clone().attr('id', 'dialog-user-add').appendTo(dialogUserEditOrAddPrototype.parent());
	
	$('#dialog-user-add').attr('title', 'Add User');
	$('#dialog-user-add').dialog({ 
		autoOpen: false,
		width: 640,
		buttons: {
			'Add': function() { 
				var dialog = $(this);
				var params = $('#dialog-user-add').values();
				$('button', dialog).prop('disabled', true);
				$.postJSON('accounts/user/save', params, function(data) {
					// either update the old one or make a new one
					if (typeof data == 'object') {
						if (data.error == false) {
							dialog.dialog('close');
							buildUserBlock(data);
							$('#dialog-user-add input[type="text"]').val('');
							$('#dialog-user-add input[type="checkbox"]').prop('checked', false);
							$('.error-message', dialog).hide();
							$(document).trigger('user-added', [data]);
							if (window.parent.Client) {
								window.parent.Client.ui.refreshUsers();
							}
						} else {
							$('.error-message', dialog).html(data.message.replace(/\n/g, '<br />')).show();
						}
					}
					$('button', dialog).prop('disabled', false);
				});
			},
			Cancel: function() { 
				$(this).dialog('close');
			}
		}
	});
	
	$('#dialog-user-edit').attr('title', 'Edit User');
	$('#dialog-user-edit').dialog({ 
		autoOpen: false,
		width: 640,
		buttons: {
			'OK': function() { hideUserEdit(true); },
			Cancel: function() { hideUserEdit(false); }
		}
	});

	$('#dialog-group-edit').dialog({ 
		autoOpen: false,
		width: 350,
		buttons: {
			'OK': function() { hideGroupEdit(true); },
			Cancel: function() { hideGroupEdit(false); }
		}
	});

	$('#dialog-delete').dialog({ 
		autoOpen: false,
		width: 480,
		buttons: {
			'OK': function() {
				var entity = $('.pending-deletion');
				var method = entity.hasClass('group') 
					? '/accounts/group/delete' : '/accounts/user/delete';
				var params = { id: entity.attr('rel') };

				$.ajax({
					url : OpenVBX.home + method,
					data : params, 
					dataType : 'json',
					type : 'POST',
					success : function(data) {
						entity.fadeRemove();
						if (window.parent.Client) {
							window.parent.Client.ui.refreshUsers();
						}
					}
				});
				
				$('.pending-deletion').removeClass('pending-deletion');
				$(this).dialog('close');
			},
			Cancel: function() { $(this).dialog('close'); }
		}
	});

	var hideScreen = function() {
		$('.screen').animate(
			{
				background: 'none',
				opacity: 0
			}, 
			2000,
			function() {
				$('.screen').hide();
			});
	};

	var syncComplete = false;
	var syncedGroups = 0;
	var syncedUsers = 0;
	var doSyncComplete = function(user_length, group_length) {
		if(user_length == syncedUsers &&
		   group_length == syncedGroups) {
			syncComplete = true;
		}

		if(syncComplete)
		{
			hideScreen();
			syncComplete = false;
		} else {
			setTimeout(function() {
				doSyncComplete(user_length, group_length)
			}, 1000);
		}
	};

	var syncUsers = function(users) {
		for(var _id in users) 
		{
			var max = 1000000;
			var params = {
				email : users[_id],
				is_admin : 0,
				is_active : 1,
				auth_type : 'google',
				notification : 'email',
				first_name : '',
				last_name : '',
				extension : Math.floor(Math.random()*max+1),
				password : Math.floor(Math.random()*max+1)
			};

			$.ajax({
				url : 'accounts/user/save',
				data : params,
				success : function(data) {
					// either update the old one or make a new one
					if (typeof data == 'object') {
						if (data.success == true) {
							$('.screen .message').append('Added '+data.email+' Successfully<br />');
							buildUserBlock(data);
						} else {
							$('.screen .message').append(data.email+' already exists, not modified.<br />');
						}
						if (window.parent.Client) {
							window.parent.Client.ui.refreshUsers();
						}
					}
					syncedUsers += 1;
				},
				error : function(xhr, status, error) {
					$('.screen .message').append(error);
				},
				type : 'POST',
				dataType: 'json'
			});
			
		}
	};
	
	var syncGroups = function(groups) {
		for(var _id in groups) 
		{
			var max = 1000000;
			var params = {
				name : groups[_id]
			};

			$.ajax({
				url : 'accounts/group/save',
				data : params,
				success : function(data) {
					// either update the old one or make a new one
					if (typeof data == 'object') {
						if (data.success == true) {
							$.notify('Added group '+data.name+' successfully.');
							buildGroupBlock(data);
						} else {
							$.notify(data.name+' already exists, not modified.');
						}
					}
					syncedGroups += 1;
				},
				type : 'POST',
				dataType: 'json'
			});
		}
	};
	
	var runGoogleAppSync = function() {
		$.ajax({
			url : 'accounts/appsync',
			data : $('#dialog-google-app-sync input'),
			success : function(data) {
				if(data.error) {
					$('.screen').html('<div class="message">It didn\'t quite work out, very sorry about that: '+ data.message +'<br /><button class="hide-worker">Continue</Button></div>');
					$('.hide-worker').click(hideScreen);
				} else {
					$('.screen').html('<div class="message">Awesome, we got some users to process... <br />working on that now...<br /></div>');
					syncUsers(data.users);
					syncGroups(data.groups);
					doSyncComplete(data.users.length, data.groups.length);
				}
			},
			error : function(xhr, status, error) {
				$('.screen').html('<div class="message">Holy crap!!! SOMETHING WENT WRONG WITH THE INTARWEBZ<br /><button class="hide-worker">Continue</Button></div>');
				$('.hide-worker').click(hideScreen);
			},
			dataType: 'json',
			type: 'POST'
		});
	};

	var initGoogleAppSync = function() {
		$(this).dialog('close');
		var opacity = 0.8;
		$('.screen').show().animate(
			{
				backgroundColor: '#000000',
				display: "toggle",
				opacity: opacity
			}, 
			500,
			'linear', 
			function() {
				$('.screen').html('<div style="margin: 10% auto; width: 100%; position: absolute; z-index: 999999; color: white; opacity: 1;">Synchronizing your intarwebs, please hold...</div>');
				setTimeout(runGoogleAppSync, 2000);
			});
	};
	
	dlgGoogleAppSync = $('#dialog-google-app-sync').dialog({ 
		autoOpen: false,
		width: 480,
		buttons: {
			'OK' : initGoogleAppSync,
			Cancel : function() {
				$(this).dialog('close');
			}
		}
	});
	
	$('#button-add-user').click(function(e) {
		showUserAdd(null);
		return false;
	});

	$('#button-add-group').click(function(e) {
		showGroupEdit(null);
		return false;
	});

	$('#google-app-sync').click(function(e) {
		dlgGoogleAppSync.dialog('open');
	});

	// $('.user-edit, .group-edit').livequery('click', function(event) {
	$('.group-edit').livequery('click', function(event) {
		var container_el = $(this).closest('.group, .user');
		if(container_el.hasClass('group')) {
			$.postJSON('accounts/group/get', {	id: container_el.attr('rel') }, showGroupEdit);
		} else {
			$.postJSON('accounts/user/get', { id: container_el.attr('rel') }, showUserEdit);
		}
		return false;
	});

	$('.user-remove, .group-remove').livequery('click',  function(evt) {

		var entity = $(this)
			.closest('.group, .user')
			.addClass('pending-deletion');

		$('#dConfirmMsg')
			.text('Are you sure you want to delete this ' 
				  + (entity.hasClass('group') ? 'group' : 'user') 
				  + '?')
		$('#dialog-delete').dialog('open');
		return false;
	});

	addUserEvents('.user');
	addGroupUserEvents('.group li');
	addGroupEvents('.group');

	valUser = $("#dialog-user-edit form").validate({
		rules: {
			first_name: {
				required: true,
				minlength: 2
			},
			last_name: {
				required: true,
				minlength: 2
			},
			email: {
				required: true,
				email: true
			},
			extension: {
				digits: true
			}
		},
		messages: {
			first_name: {
				required: "First name required",
				minlength: "First name too short"
			},
			last_name: {
				required: "Last name required",
				minlength: "Last name too short"
			},
			email: {
				required: "Email required",
				email: "Invalid email"
			},
			extension: {
				digits: "Numbers only"
			}
		}
	});

	valGroup = $("#dialog-group-edit form").validate({
		rules: {
			group_name: {
				required: true,
				minlength: 2
			}
		},
		messages: {
			group_name: {
				required: "Group name required",
				minlength: "Group name too short"
			}
		}
	});
});

function addGroupEvents(el) {
	$(el).click(function() {
		$(this).toggleClass('expanded', !$(this).hasClass('expanded'));
	});

	$(el).droppable({
		accept: '.user',
		hoverClass: 'ui-state-hover',
		drop: function(event, ui) {
			var group_el = $(this);
			var user_id = ui.draggable.closest('.user').attr('rel');

			if(!group_el.is(':has(li[rel="' + user_id + '"])')) {
				$('.ui-draggable-dragging').hide();

				var username = ui.draggable.find('.user-name').text();
				var groupname = group_el.find('.group-name').text();
			
				params = { 
					group_id: group_el.attr('rel'), 
					user_id: user_id
				};

				$('.group-counter', group_el).hide();
				$('.group-counter-loader', group_el).show();

				$.ajax({
					url : OpenVBX.home + '/accounts/group_user/add',
					data : params, 
					success : function(data) {
						if (!data.error) {
							$('ul', group_el).append('<li rel="' + user_id + '"><span>' + username + '</span> <a class="remove">Remove</a></li>');
							addGroupUserEvents($('li:last-child', group_el));
							$('.group-counter-loader', group_el).hide();
							$('.group-counter', group_el)
								.show().text($('.members li', group_el).length);
							$.notify(username + ' has been added to ' + groupname);
						}
					},
					dataType : 'json',
					type : 'POST'
				});
			}
		}
	})
	.find('.members').sortable({
		axis: 'y',
		containment: 'parent',
		items: 'li',
		opacity: 0.5,
		revert: true,
		tolerance: 'pointer',
		placeholder: 'members-ui-draggable-placeholder',
		update: function(event, ui) {
			var group_el = $(ui.item[0]).closest('.group'),
				groupname = group_el.find('.group-name').text(),
				group_id = group_el.attr('rel'),
				members = $(ui.item[0]).closest('.members');
			
			// we're not quite working how sortable tends to work
			// so we need to gather the user_ids ourselves
			var order = [];
			members.find('li').each(function(i) {
						order.push($(this).attr('rel'));
					});
			
			if (order.length) {
				members.sortable('disable');
				$('.group-counter', group_el).hide();
				$('.group-counter-loader', group_el).show();
				
				$.post(
					OpenVBX.home + '/accounts/group/order',
					{
						group_id: group_el.attr('rel'),
						group_order: order
					},
					function(data) {
						if (data.success) {
							$.notify(groupname + ' group order updated');
						}
						else {
							$.notify(groupname + ' group could not be updated: ' + data.message);
						}
						members.sortable('enable');
						$('.group-counter', group_el).show();
						$('.group-counter-loader', group_el).hide();
					},
					'json'
				);
			}
		}
	}).disableSelection();
}

function addUserEvents(el) {
	return $(el)
	.click(function() {
		if ($(this).hasClass('selected')) {
			$(this).removeClass('selected');
		} else {
			$('.user').removeClass('selected');
			$(this).addClass('selected');
		}
	})
	.draggable({
		revert: true,
		helper: 'clone',
		zIndex: '2',
		opacity: 0.7
	});
}

function addGroupUserEvents(el) {
	$('a.remove', el).click(function(evt) {
		evt.stopPropagation();

		var userLi = $(this).closest('li');
		var group_el = $(this).closest('.group');

		params = {
			group_id: group_el.attr('rel'),
			user_id: userLi.attr('rel')
		};
		$.ajax({
			url : 'accounts/group_user/delete',
			data : params,
			success : function(data) {
				if (!data.error) {
					var username = $('span', userLi).text();
					var groupname = $('.group-name', group_el).text();
					$.notify( username + ' has been removed from ' + groupname);
					userLi.fadeOut(function() {
						userLi.remove();
						userCount = $('.members li', group_el).length;
						$('.group-counter', group_el).text(userCount);
					});
				}
			},
			error : function() {
			},
			type : 'POST',
			dataType : 'json'
		});
	});
}


function showUserAdd(data) {
	var dialog = $('#dialog-user-add');
	
	$('input[type="text"]', dialog)
		.val('')
		.first().focus();
		
	$('.single-existing-number', dialog).show();
	$('.multiple-existing-numbers', dialog).hide();		
	
	/* HACK: Not sure why but charcode warnings occuring */
	setTimeout(function() {
		$('#dialog-user-add input:first').focus();
	}, 100);

	dialog.dialog('open');
}

function showUserEdit(data) {
	var dialog = $('#dialog-user-edit');

	$('input[type="hidden"]', dialog).val('');
	$('form', dialog)[0].reset();
	$('.error-message', dialog).hide();

	if (data.devices.length > 1) {
		$('.single-existing-number', dialog).hide();
		$('.multiple-existing-numbers', dialog).show();
	} else {
		$('.single-existing-number', dialog).show();
		$('.multiple-existing-numbers', dialog).hide();
		
		if (data.devices.length == 1) {
			$('.single-existing-number input[name="device_number"]', dialog).val(data.devices[0].value);
			$('.single-existing-number input[name="device_id"]', dialog).val(data.devices[0].id);
		}
	}
	
    dialog.values(data);
	valUser.resetForm();
	dialog.dialog('open');
}

function buildUserBlock(data) {
	var user_el = $('.user[rel="' + data.id + '"]');
	if (user_el.length < 1) {
		user_el = $('.user[rel="prototype"]').clone().attr('rel', data.id);
		user_el.appendTo('#user-container ul.user-list').fadeIn();
		addUserEvents(user_el);
	}
	var fullName = data.first_name + ' ' + data.last_name;

	$('.user-name', user_el).text(fullName);
	$('.user-email', user_el).text(data.email);
	$('.user-edit', user_el).attr('href', '/account/user/' + data.id);
	$('.members li[rel="' + data.id + '"] span').text(fullName);
}

function buildGroupBlock(data) {
	var group_el = $('.group[rel="' + data.id + '"]');
	if (group_el.length < 1) {
		group_el = $('.group[rel="prototype"]').clone();
		group_el.attr('rel', data.id);
		group_el.appendTo('#group-container ul.group-list');
		group_el.fadeIn();
		addGroupEvents(group_el);
	}
	$('.group-name', group_el).text(data.name);
}

function hideUserEdit(save) {
	var dlgUser = $('#dialog-user-edit');
	if (save) {
		if (valUser.form() == false) return;
		var params = $('#dialog-user-edit').values();
		$.postJSON('accounts/user/save', params, function(data) {
			// either update the old one or make a new one
			if (typeof data == 'object') {
				if (data.error == false) {
					dlgUser.dialog('close');
					buildUserBlock(data);
					
					$(document).trigger('user-edited', [data])
				} else {
					$('.error-message', dlgUser).html(data.message.replace(/\n/g, '<br />')).show();
				}
			}
			if (window.parent.Client) {
				window.parent.Client.ui.refreshUsers();
			}
		});
	} else {
		dlgUser.dialog('close');
	}
}

function showGroupEdit(data) {
	var isEdit = (typeof data == 'object' && data != false && data != null);
	$('#dialog-group-edit').dialog({'title': isEdit ? 'Edit Group' : 'Add New Group'});
	// set the class of the dialog based on edit
	$($('#dialog-group-edit')).closest('.ui-dialog').addClass(isEdit ? 'manage' : 'add').removeClass(isEdit ? 'add' : 'manage');

	$('input[type="hidden"]', $('#dialog-group-edit')).val('');
	$('form', $('#dialog-group-edit')).get(0).reset();


	if (isEdit) $('#dialog-group-edit').values(data);

	valGroup.resetForm();
	$('#dialog-group-edit').dialog('open');
}

function hideGroupEdit(save) {
	if (save) {
		if (valGroup.form() == false) return;
		var params = $('#dialog-group-edit').values();
		var new_group = true;

		if(params.id) {
			new_group = false;
		}

		$.postJSON('accounts/group/save', params, function(data) {
			// either update the old one or make a new one
			if (typeof data == 'object') {
				if (!data.error) {
					if(new_group) {
						$.notify('Added Group "' + data.name + '" successfully.');
					} else {
						$.notify('Updated Group "' + data.name + '" successfully.');
					}
					$('#dialog-group-edit').dialog('close');
					$('#dialog-group-edit .error-message').hide();

					buildGroupBlock(data);
					if (new_group) {
						$(document).trigger('group-added', [data]);
					} else {
						$(document).trigger('group-edited', [data]);
					}
				} else {
					$('#dialog-group-edit .error-message').text(data.message).show();
				}
			}
		});
	} else {
		$('#dialog-group-edit').dialog('close');
	}
}
