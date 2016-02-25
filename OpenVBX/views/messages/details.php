<div class="vbx-content-main">

	<form id="message-details" class="vbx-form" action="<?php echo site_url("messages/details/$id") ?>" method="post">

	<div class="vbx-content-menu vbx-content-menu-top">
		<a href="<?php echo site_url("messages/inbox/$folder_id") ?>" class="back-link">&laquo; Back to <?php echo empty($folder)? 'Inbox' : $folder ?></a>
		<ul class="details-menu vbx-menu-items-right">
			<li class="menu-item"><a id="save-details" class="link-button"><span>Update voicemail</span></a></li>
			<li class="menu-item"><a id="delete-<?php echo $id?>" href="" class="delete-button link-button"><span>Delete</span></a></li>
		</ul>
	</div><!-- .vbx-content-menu -->


	<div class="vbx-content-container">
		<div class="message-details-header">
			<p class="date-created unformatted-relative-timestamp hide">
				<?php echo strtotime($received_time) ?>
			</p>
			
			<table class="details-info">
				<tbody>
					<tr>
						<td>
							<span class="call-from-label">From:</span>
						</td>
						<td>
							<span class="call-from-number"><?php echo $caller ?></span>
							<a href="" class="quick-call-button"><span class="replace">Call</span></a>
							<div id="quick-call-popup-<?php echo $id ?>" class="quick-call-popup hide">
								<a href="" class="close action toggler"><span class="replace">close</span></a>	
								<p class="call-from-number"><?php echo $caller ?></p>
								<ul class="caller-id-phone">
									<li><a href="<?php echo site_url("messages/details/{$id}/callback") ?>" class="call">Call<span class="to hide"><?php echo $caller ?></span> <span class="callerid hide"><?php echo $called ?></span><span class="from hide"><?php echo isset($user_numbers[0])? $user_numbers[0]->value : '' ?></span></a></li>
								</ul>
							</div>
						</td>
					</tr>
					<tr>
						<td>
							<span class="call-to-label">To:</span>
						</td>
						<td>
							<span class="call-to-number"><?php echo $called ?></span>
						</td>
					</tr>
				</tbody>
			</table>

			<?php if($type == 'voice'): ?>
			<table class="details-info">
				<tbody>
					<tr>
						<td><span class="status-label">Status:</span></td>
						<td>
							<fieldset class="vbx-input-container">
							<select id="ticket-status" class="small" name="ticket_status">
								<?php foreach(array('pending', 'open', 'closed') as $status): $selected = ($status == $ticket_status)? 'selected="selected"' : '' ?>
								<option value="<?php echo $status ?>" <?php echo $selected ?>><?php echo $status ?></option>
								<?php endforeach; ?>
							</select>
							</fieldset>
						</td>
					</tr>
					<?php if($owner_type == 'group'): ?>
					<tr>
						<td><span class="assign-label">Assign to:</span></td>
						<td>
							<fieldset class="vbx-input-container">
							<select id="assign-to" class="medium" name="assigned">
								<option>Select a user</option>
								<?php foreach($active_users as $user): ?>
								<option value="<?php echo $user['id'] ?>" <?php echo $user['id'] == $assigned? 'selected="selected"' : '' ?>>
									<?php echo format_name($user) ?>
								</option>
								<?php endforeach; ?>
							</select>
							</fieldset>
						</td>
					</tr>
					<?php endif; ?>
				</tbody>
			</table>
			<?php endif; ?>
		</div><!-- .message-details-header -->

		<?php if($type == 'voice'): ?>
		<div class="message-details-playback message-row">
			<table>
				<tbody>
					<tr>
						<td>
							<a id="play-<?php echo $id ?>" href="<?php echo site_url("messages/details/$id") ?>" class="playback-button play quick-play">
								<span class="replace">Play</span>
								<span class="call-duration"><?php echo $recording_length ?></span>
							</a>
						</td>
						<td id="player-<?php echo $id ?>" class="player" style="display: none; width: 100%">
							<table style="width: 100%">
								<tr>
									<td style="width: 100%">
										<div id="player-bar-<?php echo $id?>" class="player-bar">
											<div id="load-bar-<?php echo $id?>" class="load-bar">
												<div id="play-bar-<?php echo $id?>" class="play-bar"></div>
											</div>
										</div>
									</td>
									<td>
										<div class="play-time"><img src="<?php echo asset_url('assets/i/ajax-loader.gif')?>" alt="..." /></div>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</tbody>
			</table>
			
		</div><!-- .message-details-playback -->
		<?php endif; ?>

		<div class="message-details-transcript">
			<?php if($type == 'sms'): ?>
			<h3>Text Message</h3>
			<?php else: ?>
			<h3>Transcription</h3>
			<?php endif; ?>
			<div class="message-transcript"><?php echo (is_null($message->content_text) ? "(no transcription)" : $message->content_text) ?></div>
			<?php if(substr($message->call_sid,0,2) == 'MM'):
				echo "<br><br><h3>Media Attachements</h3>";
				
				$ci = & get_instance();
				
				require_once(APPPATH . 'libraries/twilio.php');
				
				$ci->twilio = new TwilioRestClient($ci->twilio_sid,$ci->twilio_token,$ci->twilio_endpoint);
				
				$media_url = "Accounts/{$this->twilio_sid}/Messages/{$message->call_sid}/Media.xml";
				
				$mediaObj = $ci->twilio->request($media_url, "GET");
				
				$media_xml = $mediaObj->ResponseXml;
				
				foreach($media_xml->MediaList->Media as $media):
				
					$media_uri = 'https://api.twilio.com/2010-04-01/Accounts/'.$this->twilio_sid.'/Messages/'.$message->call_sid.'/Media/'.$media->Sid;
					
					if(strpos($media->ContentType,'image')!==false):
						echo '<img src="'.$media_uri.'" type="'.$media->ContentType.'">';
					elseif(strpos($media->ContentType,'audio')!==false):
						echo '<audio controls>';
						echo '<source src="'.$media_uri.'" type="'.$media->ContentType.'">';
						echo 'Your browser does not support the audio element.</audio>';
					elseif(strpos($media->ContentType,'video')!==false):
						echo '<video controls>';
						echo '<source src="'.$media_uri.'" type="'.$media->ContentType.'">';
						echo 'Your browser does not support the video element.</video>';
					endif;
					
					echo '<br>'.$media->ContentType.': <a href="'.$media_uri.'">Click To Download</a>';
					echo '<br><br>';
					
				endforeach;
	
			endif; ?>
		</div><!-- .message-details-transcript -->

	</div><!-- .vbx-content-container -->
	</form>

	<?php if($type == 'sms'): ?>
	<div class="vbx-content-container">
		<div class="message-details-notes">
			<form id="reply-sms" name="reply-sms" action="<?php echo site_url("messages/sms/$id") ?>" method="post">
				<h3>Reply</h3>
				<input type="hidden" name="from" value="<?php echo $called ?>" />
				<input type="hidden" name="to" value="<?php echo $caller ?>" />
				<textarea id="content" name="content"></textarea>
				<p class="count-desc"><span class="count">160</span> characters left</p>
				<button class="submit-button"><span>Send SMS</span></button>
				<img class="loader hide" src="<?php echo asset_url('assets/i/ajax-loader.gif')?>" alt="..." />

			</form>
			<ul id="message-details-notes-list">
				<?php foreach($annotations['items'] as $annotation): ?>
				<?php if($annotation->annotation_type == 'sms'): ?>
				<li class="note">
					<p class="note-created unformatted-relative-timestamp hide"><?php echo strtotime($annotation->created) ?></p>
					<div class="note-header">
						<?php
							if ($gravatars)
							{
								echo '<img class="gravatar" src="'.
									gravatar_url($annotation->email, 20, $default_gravatar).
									'" width="20" height="20">';
							}
						?>
						<p class="note-user-fullname"><?php echo $annotation->first_name ?> <?php echo $annotation->last_name ?></p>
						<p class="note-user-email"><?php echo $annotation->email ?></p>
					</div>
					<div class="note-content">
						<?php echo $annotation->description ?>
					</div>
				</li>
				<?php endif; ?>
				<?php endforeach; ?>
			</ul>
		</div><!-- .message-details-notes -->
	</div><!-- .vbx-content-container -->
	<?php endif; ?>
				
	<?php if($type == 'voice'): ?>
	<div class="vbx-content-container">
		<div class="message-details-notes">
			<form id="add-annotation" name="add-annotation" action="<?php echo site_url("messages/details/$id/annotations") ?>" method="post">
				<h3>Private Notes</h3>
				<table>
					<tbody>
						<tr>
							<td>
								<textarea id="description" name="description"></textarea>
							</td>
						</tr>
						<tr>
							<td style="text-align: right;">
								<input type="hidden" name="annotation_type" value="noted" />
								<button type="submit" class="submit-button"><span>Add Note</span></button>
							</td>
						</tr>
					</tbody>
				</table>
			</form>
			
		<?php if ($annotations['max'] > 0): ?>
			<p class="note-count">Showing <?php echo $annotations['max']; ?> of <?php echo $annotations['total']; ?></p>
		<?php else: ?>
			<p class="note-count">No Annotations</p>
		<?php endif; ?>
			<ul id="message-details-notes-list">
				<?php foreach($annotations['items'] as $annotation): ?>
				<li class="note">
					<p class="note-created unformatted-relative-timestamp hide"><?php echo strtotime($annotation->created) ?></p>
					<div class="note-header">
						<?php
							if ($gravatars)
							{
								echo '<img class="gravatar" src="'.
									gravatar_url($annotation->email, 20, $default_gravatar).
									'" width="20" height="20">';
							}
						?>
						<p class="note-user-fullname"><?php echo $annotation->first_name ?> <?php echo $annotation->last_name ?></p>
						<p class="note-user-email"><?php echo $annotation->email ?></p>
					</div>
					<div class="note-content">
						<?php echo $annotation->description ?>
					</div>
				</li>
				<?php endforeach; ?>
			</ul>
		<?php if ($annotations['max'] > 0): ?>
			<p class="note-count">Showing <?php echo $annotations['max']; ?> of <?php echo $annotations['total']; ?></p>
		<?php endif; ?>
		
		</div><!-- .message-details-notes -->
	</div><!-- .vbx-content-container -->
	<?php endif; ?>

	<div class="vbx-content-menu vbx-content-menu-bottom">
		<a href="<?php echo site_url("messages/inbox/$folder_id") ?>" class="back-link">&laquo; Back to <?php echo empty($folder)? 'Inbox' : $folder ?></a>
		<ul class="details-menu vbx-menu-items-right">
			<li class="menu-item"><a id="delete-<?php echo $id?>" href="" class="delete-button link-button"><span>Delete</span></a></li>
		</ul>
	</div><!-- .vbx-content-menu -->

</div><!-- .vbx-content-main -->
