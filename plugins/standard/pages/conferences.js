function joinConference(params) {
    params = $.extend(params, { 'Digits': 1 });
    window.parent.Client.call(params)
}

$(document).ready(function(){
	jQuery(function($) {
		$(function(){
					
			var
			calls = {},
			select = $('#calls'),
			updateCalls = function() {
				$.getJSON(window.location + '/?json=1', function(data) {
					$.each(data, function(conference_name, call) {
						if(!calls[conference_name]) {
							calls[conference_name] = call;
							
							row = '<tr class="items-row" id="' + call.friendly_name + '">';
							row += '<td>' + call.date_created + '</td>';
							row += '<td>' + call.friendly_name + '</td>';
							row += '<td>' + call.status + '</td>';
							row += '<td><a href="#" class="join">Join</a></td>';
							row += '<td><a href="#" class="listen">Listen</a></td></tr>';
							
							select.append(row);
						}
					});
			
					$.each(calls, function(conference_name, call) {
					  if(!data[conference_name]) {
						delete calls[conference_name];
						$('#' + conference_name).fadeOut(250, function() {
						  $(this).remove();
						});
					  }
					});
				});
			};

			updateCalls();
			setInterval(updateCalls, 5000);
		});
	
		$('#calls').delegate('.join', 'click', function(e) {
			e.preventDefault();

			joinConference({
				'conference_name': $(this).closest('tr').attr('id'),
				'muted': 'false',
				'beep': 'true'
			});
		});
		
		$('#calls').delegate('.listen', 'click', function(e) {
			e.preventDefault();

			joinConference({
				'conference_name': $(this).closest('tr').attr('id'),
				'muted': 'true',
				'beep': 'false'
			});
		});
	});
});