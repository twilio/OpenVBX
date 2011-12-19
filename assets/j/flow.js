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

var Flows = {};

/* Static property describes when flows has been modified */
Flows.modified = false;

/* Setup validation stub to error a successful validation response */
Flows.validation = [function() { return { error : false } }];

/* User interface helpers */
Flows.toggle = {
	speed : 1000,
	item : function(selector) {
		if($(selector)
		   .parent().hasClass('current-item')) {
			return;
		}
		
		$('.current-item a')
			.toggleAnimateToClass('#flowline-items .current-item a', 
								  '#flowline-items .flowline-item a', 
								  this.speed)
			.parent().removeClass('current-item');
		$(selector).toggleAnimateToClass('#flowline-items .current-item a', 
										 '#flowline-items .flowline-item a', 
										 this.speed)
		.parent().addClass('current-item');
	}
};

Flows.list = function(data) {	
	return $(document).data('flow-list') || $(document).data('flow-list', data);
};

Flows.uniqid = function() {
	/* Ensure an always unique id for flows */
	var make_uniqid = function() {
		var id = Math.floor(4096 + (Math.random() * 16773119)).toString(16);
		var list = Flows.list();
		var unique = $.each(list, function() {
			if(this.id == id) {
				return false;
			}
			
			return true;
		});

		if(typeof(list) != "undefined" && 
		   typeof(list.length) > 0 &&
		   unique) {
			return make_uniqid();
		}

		return id;
	};
	
	return make_uniqid();
}	

/* For managing the instance */
Flows.link = { 
	tail : function() {
		var list = Flows.list();
		return list[list.length - 1];
	},
	head : function() {
		var list = Flows.list();
		return list[0];
	},
	current : function() {
		var list = Flows.list();
		var index = 0;
		$.each($('.flowline-item'), function() {
			if($(this).hasClass('current-item')) {
				return false;
			}
			index ++;
			return true;
		});

		return list[index];
	},
	create : function(type, id) {
		var list = Flows.list();
		id = id || Flows.uniqid();
		link = {
			id : id, 
			type : type,
			href : document.location.hash.replace('#flowline','') + '/' + id
		};
		list.push(link);
		$(document).trigger('flow-created', link);
		return link;
	},
	pop : function() {
		var list = Flows.list();
		try {
			var popped = list[list.length -1];
			list.pop();
			$(document).trigger('flow-popped', popped);
		} catch(e) {
			return false;
		}
		return true;
	},
	values : function(parent) {
		var data = {};
		var els = $(parent).find(':input').get();
		
		$.each(els, function() {
			if (this.name && !this.disabled 
				&& (this.checked 
					|| /select|textarea/i.test(this.nodeName) 
					|| /text|hidden|password/i.test(this.type))) {
				var val = $(this).val();
				if(val != null) {
					var type = $.type(data[this.name]);
					switch(type) {
						case 'string':
							data[this.name] = new Array(data[this.name]);
						case 'object':
						case 'array':
							data[this.name].push(val);
							break;
						default:
							var matches = this.name.match(/^(.*)\[(.+)\]$/);
							if( matches && matches.length ) {
								var key = matches[1] + '[]';
								if(!$.isArray(data[key])) {
									data[key] = new Array();
								}
								data[key].push(val);
							} else {
								data[this.name] = val;
							}
							break;
						}
				}
			}
		});
		
		return {
			name : $('.applet-name:first', parent).text(),
			data : data,
			id : $(parent).attr('id'),
			type : $(parent).attr('rel')
		};
	}
};

/* Event handlers */
Flows.events = {
	flow : {
		setName : function(event, name) {
			if($(event.target).attr('id').match(/^prototype-/)) {
				return true;
			}

			$('h2.applet-name', event.target).text(name.substr(0, 42));
			$('#instance-row a[href$="'+ $(event.target).attr('id') +'"] .applet-item-name').text(name.substr(0, 42));
		},
		beforeSave : function(event, success) {
			event.preventDefault();
			var flow_instances = $('#instances .flow-instance');
			var textareas = $('.audio-choice .audio-choice-read-text textarea:visible', flow_instances);
				
			if ($('.flow-name-edit').is(':visible')) {
				new_name = $('.flow-name-edit input[name="name"]').val();
				old_name = $('.vbx-form input.flow-name');
				if (new_name != old_name) {
					$('.vbx-form input.flow-name').val(new_name);
				}
			}
				
			if(textareas.length) {
				textareas.each(function() {
					Pickers.audio.saveReadText(event, $(this));
				}).last().queue(function() {
					$(document).trigger('flow-save', [success]);
				});
			} else {
				$(document).trigger('flow-save', [success]);
			}
		},
		save : function(event, success) {
			event.preventDefault();
			var response = null;
			var validated = $.each(Flows.validation, function() {
				response = this();
				if(response.error)
					return false;

				return true;
			});

			if(response && response.error) {
				$.notify(response.message);
				return false;
			}
			
			var flow_data = {};
			$('#instances .flow-instance').each( function(index) {
				flow_data[$(this).attr('id')] = Flows.link.values(this);
			}).last().queue(
				function() {
					var vbx_form = $('.vbx-form');
					params = {
						id : $('input.flow-id', vbx_form).val(),
						name : $('input.flow-name', vbx_form).val()
					};
					if($(vbx_form).hasClass('sms')) 
					{
						params.sms_data = JSON.stringify(flow_data);
					} 

					if($(vbx_form).hasClass('voice')) 
					{
						params.data = JSON.stringify(flow_data);
					} 

					$.ajax({
						url: OpenVBX.home + '/flows/edit/' + Flows.id,
						data : params,
						dataType : 'json',
						success : function(data) {
							if(!data.error) {
								Flows.modified = false;
								if(success) {
									success();
								}
								$(document).trigger('flow-after-save');
								return $.notify('Flow has been saved.').flicker();
							}
							
							$.notify(data.message);
						},
						type : 'POST'
					});

					$(this).dequeue();
				}
			);
		},
		copy : function(event) {
			$.ajax({
				url: OpenVBX.home + '/flows/copy/' + Flows.id,
				data : {
					name : $('#dialog-save-as input[name="name"]').val(),
					data : JSON.stringify(flow_data)
				},
				dataType : 'json',
				success : function(data) {
					if(!data.error) {
						return $.notify('Flow has been copied to '+data.name).flicker();
					}
				},
				type : 'POST'
			});
		},
		close : function(event) {

			var instance = $(event.target).parents('.flow-instance');
			var re = new RegExp( '/' + $(instance).attr('id') + '.*' );
			var matches = document.location.hash.match(re);
			if(matches && matches.length > 0) {
				matches = matches[0].split('/');
			}
			document.location.hash = document.location.hash.replace(re, '');
			$(document).trigger('hashchange');
			setTimeout(	function() {
				for(var id in matches) {
					if(matches[id].length) {
						$('#' + matches[id]).fadeOut();
					}
				}
			}, 600);
			
			return false;
		},
		change : function(event) {
			try {
				var instances = document.location.hash;
				instances = instances.split('/');
				var current_instance = instances[instances.length -1];
				var clean_instances = {};
				var depth = instances.length;

				for(var instance_id in instances) {
					try {
						if(instances[instance_id] == '#flowline') {
							continue;
						}

						var instance = $('#'+instances[instance_id]);
						$(instance).data('depth', instance_id);
						clean_instances[instances[instance_id]] = instance;
					}
					catch(e) {
						continue;
					}
				}

				$('.flow-instance').each(function() {
					var instance = $(this);
					try {
						if(!clean_instances[instance.attr('id')]) {
							var instance_depth = $(instance).data('depth');
							if(instance_depth < depth || instance_depth > instances.length) {
								instance.hide();
							}
							return true;
						}

						if(!instance.is(':visible')) {
							instance.trigger('show');
						}
					} catch (e) {
						instance.hide();
					}

					return true;
				});

				var cell = $('#'+current_instance)
					.parents('.instance-cell')
					.get(0);

				$('#flowline').animate({
                    scrollLeft: cell.offsetLeft - 30,
					queue : true
				});
			}
			catch(e)
			{
				$('.flow-instance').hide();
				$('#start').show();
			}
		},
		created : function(event, link) {
			Flows.modified = true;
			var depth = $(document).data('depth') || 0;
			$(document).data('depth', ++depth);

			$('#flowline-items').append('<li class="flowline-item"><a href="#flowline'
										+ link.href
										+'">'
										+ link.type
										+'</a></li>');
		},
		popped : function(event, link) {
			var depth = $(document).data('depth') || 0;
			$(document).data('depth', --depth);
		},
		shown : function(event, flow) {
			$(event.target).show().removeClass('hide');
		},
		select : function(event, flow) {
			/* This selection logic is made to detect 
			 * which instance you clicked on and decide how to build out the hash tag */
			var instance = $(event.target).hasClass('flow-instance')? $(event.target) : $(event.target).parents('.flow-instance');
			var re = new RegExp( '^.*' + $(instance).attr('id') );
			try {
				/* If the unique id exists in current path - shave off the end */
				document.location.hash = document.location.hash.match( re )[0];
			} catch(e) {
				/* We must scan the visible instances of the path and 
				 * build out the missing instances till 
				 * we reach the instance we've selected */
				var hash = document.location.hash;
				var instances = hash.split('/');
				var last_instance = instances[instances.length - 1];
				var append = false;
				$('.flow-instance:visible').each(function() {
					$(document).trigger('applet-visible', [$(this)]);
					if($(this).attr('id') == $(instance).attr('id')) {
						/* End of path */
						return false;
					}

					if(append) {
						hash = hash + '/' + $(this).attr('id');
					}					

					if($(this).attr('id') == last_instance) {
						/* Begin appending at the next instance */
						append = true;
					}


					return true;
				});
				document.location.hash = hash + '/' + $(instance).attr('id');
			}
			$(document).trigger('hashchange');
		},
		hidden : function(event, flow) {
		}
	},
	drag : {
	},
	drop : {
		add: function(event, ui) {
			var afterDrop = function() {
				Flows.modified = true;
				$(event.target).trigger('click', event);
				$('.flow-instance:visible').each(function() {
					var flow = this;
					var found = false;
					var instances = document.location.hash.split('/');
					for(var instance_id in instances) {
						if(instances[instance_id] == $(flow).attr('id')) {
							found = true;
						}
					}
					if(!found) {
						$(flow).fadeOut('fast');
					}
				});

				var link = Flows.link.create(
					$(ui.draggable).attr('rel')
				);

				$('input', event.target).attr('value', link.href.replace(/^\//, ''));
				var plugin_path = '/plugins/' + link.type.replace(/---/,'/applets/');
				$('.item-body', event.target).html('<a class="item-box" href="#flowline'
												   + link.href
												   + '"><div class="'
												   + link.type
												   + '-icon applet-icon" style="background: url(\''
                                                   + OpenVBX.assets
												   + plugin_path
												   + '\/icon.png\') no-repeat center center;"><span class="replace">'
												   + link.type
												   + '</span></div><span class="applet-item-name">'
												   + $('.applet-name', ui.draggable).text()
												   + '</span></a>'
												   + '<div class="flowline-item-remove action-mini remove-mini"><span class="replace">remove</span></div>'
												  );

				$(event.target).addClass('filled-item').removeClass('empty-item');
				var template = $('#prototypes #prototype-'+ link.type).html();
				template = template
					.replace(/prototype/g, link.id);
				$('#instance-row').append('<td class="instance-cell"><form><div id="'
									   + link.id
									   + '" class="flow-instance '
									   + link.type
									   + '" rel="'
									   + link.type
									   + '" style="display: none">'
									   + template
									   + '</div></form></td>');

				$('#'+link.id + ' textarea').text('');
				$('#'+link.id + ' input[type="text"]').val('');
				$('#'+link.id + ' .flowline-item').droppable(Flows.events.drop.options);
				window.location.hash = '#flowline' + link.href;
			};

			$('#dialog-replace-applet').dialog('option', 'buttons', {
				'OK': function() {
					afterDrop();
					$(this).dialog('close');
				},
				Cancel: function() {
					$(this).dialog('close');
				}
			});
			
			if($('input', event.target).attr('value').length > 0) {
				return $('#dialog-replace-applet').dialog('open');
			}
			
			return afterDrop();
		},
		out : function(event, ui) {
			event.preventDefault();
		},
		remove : function(event, ui) {
			event.preventDefault();
			var target = event.target;
			var afterConfirmed = function() {
				Flows.modified = true;
				var item = $(target).parents('.flowline-item');
				item.addClass('empty-item').removeClass('filled-item');
				var href = $('.item-box', item).attr('href');
				if(href && href.length) {
					href = href.split('/');
					if(href) {
						href = href[href.length - 1];
						$('#'+href).remove();
						delete flow_data[href];
					}
				}
				$('.item-body', item).text('Drop applet here');
				$('input', item).val('');
			};

			$('#dialog-remove-applet').dialog('option', 'buttons', {
				'OK': function() {
					afterConfirmed();
					$(this).dialog('close');
				},
				Cancel: function() {
					$(this).dialog('close');
				}
			});

			$('#dialog-remove-applet').dialog('open');

			return false;
		},
		hover : function(event, ui) {
		}
	}
};

/* Event options */
Flows.events.drop.options = {
	accept : '.applet-item',
	hoverClass: 'ui-state-active',
	over : Flows.events.drop.over,
	out : Flows.events.drop.out,
	drop : Flows.events.drop.add,
	helper : 'clone'
};

Flows.events.drag.options = {
	revert : 'invalid',
	snapTolerance: 40,
	hoverClass: 'ui-state-active',
	cursor : 'pointer',
	zIndex : 9999,
	helper : 'clone'
};

Flows.initialize = function() {
	$.easing.def = "easeInOutExpo"; // default animation easing here
	$('.current-item a').data('animated', true);
	$('.flowline-item a').click(function() {
		Flows.toggle.item(this);
	});

	$('#prototypes textarea').text('');
	$('#prototypes input[type="text"]').val('');

	$('#dialog-replace-applet').dialog({ 
		autoOpen: false,
		width: 480,
		buttons: {
			'OK': function() {
				$(this).dialog('close');
			},
			Cancel: function() {
				$(this).dialog('close');
			}
		}
	});

	$('#dialog-remove-applet').dialog({ 
		autoOpen: false,
		width: 480,
		buttons: {
			'OK': function() {
				$(this).dialog('close');
			},
			Cancel: function() {
				$(this).dialog('close');
			}
		}
	});

	$('#dialog-save-as').dialog({ 
		autoOpen: false,
		width: 480,
		buttons: {
			'OK': function() {
				$(document).trigger('flow-copy');
				$(this).dialog('close');
			},
			Cancel: function() {
				$(this).dialog('close');
			}
		}
	});
	
	$('#dialog-close').dialog({ 
		autoOpen: false,
		width: 480,
		buttons : {
			'Yes': function() {
				$(this).dialog('close');
			},
			'No': function() {
				$(this).dialog('close');
			},
			Cancel: function() {
				$(this).dialog('close');
			}
		}
	});

	$('.navigate-away, .util-menu a, .close-button').live('click', function(e) {
		if($(this).attr('href') != '') {
			var href = $(this).attr('href');
			if(Flows.modified) {
				$('#dialog-close').dialog('option', 'buttons', { 
					'Yes': function() {
						$(document).trigger('flow-before-save', [function() { 
							document.location = href;
							$(this).dialog("close"); 
						}]);
					},
					'No': function() { 
						document.location = href;
						$(this).dialog("close"); 
					},
					'Cancel': function() {
						$(this).dialog("close");
					}
				});

				$('#dialog-close').dialog('open');
				return false;
			}
		}
	});

	$('.timing-timerange-wrap input').timePicker({ show24Hours: false });
	$('.timing-timerange-wrap .timepicker-widget').each(function() {
		$this = $(this);
		if ($this.val() == '') Pickers.timing.setDisabled(
			$this,
			$this.find('input').first().val() == ''
		);
	});
	$('.timing-timerange-wrap a').live('click', function(e) {
		e.preventDefault();
		e.stopPropagation();
		$widget = $(this).siblings('.timepicker-widget');
		Pickers.timing.setDisabled($widget, $(this).hasClass("timing-remove"));
	});

	$('#flow-rename, .flow-name, #flow-rename-cancel').live('click', function(e) {
		e.stopPropagation();
		e.preventDefault();
		var $this = $(this);
		$this.closest('span').hide().siblings('span').show();
		if ($this.attr('id') == 'flow-rename-cancel') {
			var $input = $('input[name="name"]');
			$input.val($input.attr('data-orig-value'));
		}
	});
	$('.flow-name').hover(function() {
		$('#flow-rename').show();
	}, function() {
		$('#flow-rename').hide();
	});
	$(document).bind('flow-after-save', function() {
		if ($('.flow-name-title .flow-name-edit').is(':visible')) {
			$('.flow-name-title .flow-name').text($('.flow-name-title .flow-name-edit input[name="name"]').val()).show()
				.siblings('span').hide();
		}
	});

	$('.applet-item').draggable(Flows.events.drag.options);
	$('.flowline-item').droppable(Flows.events.drop.options);
	$('.flow-instance').live('show', Flows.events.flow.shown);
	$('.flow-instance').live('hide', Flows.events.flow.hidden);
	$('.flow-instance').live('click', Flows.events.flow.select);
	$('.flow-instance').live('set-name', Flows.events.flow.setName);
	$('.flow-instance .close-flow-instance').live('click', Flows.events.flow.close);
	$('.flow-instance .flowline-item-remove').live('click', Flows.events.drop.remove);
	$(document).bind('flow-created', Flows.events.flow.created);
	$(document).bind('flow-popped', Flows.events.flow.popped);
	$(document).bind('flow-before-save', Flows.events.flow.beforeSave);
	$(document).bind('flow-save', Flows.events.flow.save);
	$(document).bind('flow-copy', Flows.events.flow.copy);
	$('.flow-instance').trigger('hide');
	$('.save-button').click(function(event) {
		event.stopPropagation();
		event.preventDefault();
		$(document).trigger('flow-before-save');
	});

	$(window).bind('hashchange', Flows.events.flow.change);
	Flows.id = $('#flow-meta .flow-id', document).attr('id').replace('flow-','');
	Flows.list([]);
	if(document.location.hash.length < 1) {
		document.location.hash = '#flowline/start';
	} else {
		$(window).trigger('hashchange');
	}

	$('.modal-tabs').modalTabs({ attr : 'rel', history : false });
	$('.vbx-form').live('submit', preventDefault);
	$('.vbx-form :input, .flow-name-edit :input').change(function() {
		Flows.modified = true;
	});
	
	$('.view-source').live('click', function(e) {
		e.stopPropagation();
		e.preventDefault();
		window.open(this.href);
	});
};
	
$(document).ready(function() {
	Flows.initialize();
});

