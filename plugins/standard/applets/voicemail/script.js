
$(document).ready(function(){
    // detect when voicemail applet user or group is chosen
    $(".voicemail-applet .usergroup-container").live('usergroup-selected', function(e, usergroup_label, type) {

    	// If a group was set, then we need the user to manually configure the prompt
    	$('.prompt-for-group', $(e.target).parent())[ type == 'group' ? 'show' : 'hide' ]();

		// If an invidual was set, then we just use whatever VM prompt has been configured for that person
		$('.prompt-for-individual', $(e.target).parent())[ type == 'user' ? 'show' : 'hide' ]();
    });
    
});
