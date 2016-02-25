	<div class="vbx-content-main">

		<?php if(empty($items)): ?>

		<div class="vbx-content-container">
			<div class="vbx-content-section">
				<div class="messages-blank">
					<h2>No messages in "<?php echo $group_name; ?>"</h2>
					<p>When a voicemail or text message is received in "<?php echo $group_name ?>", they will show up here.  You can call them back, reply, listen to the message, read the transcription, archive it, and assign it to a user.</p>
				</div>
			</div><!-- .vbx-content-section -->
		</div><!-- .vbx-content-container -->

		<?php else: ?>
			
		<div class="vbx-content-menu vbx-content-menu-top">
			<ul class="inbox-menu vbx-menu-items-left">
				<li class="menu-item"><a href="" class="dropdown-select-button link-button"><span>Select</span></a>
					<ul class="hide">
						<li><a class="select select-all" href="">Select All</a></li>
						<li><a class="select select-none" href="">Select None</a></li>
						<li><a class="select select-read" href="">Select Read</a></li>
						<li><a class="select select-unread" href="">Select Unread</a></li>
					</ul>
				</li>
				<li class="menu-item"><a href="" class="delete-button link-button"><span>Delete</span></a></li>
			</ul><!-- .vbx-menu-items -->

			<?php echo $pagination; ?>
		</div><!-- .vbx-content-menu -->



		<table border="0" class="vbx-items-grid">
			<tbody>
				<?php foreach($items as $item): ?>
				<tr rel="<?php echo $item['id'] ?>" class="message-row <?php echo ($item['type']=='sms')? 'sms-type' : 'call-type'?> <?php echo ($item['unread'])? 'unread' : 'read'?>">
					<td class="message-select">
						<div style="padding: 6px">
							<input type="checkbox" name="message[id][]" value="<?php echo $item['id'] ?>" />
						</div>
					</td>
					<td class="message-caller message-details-link" style="width:220px">
						<span class="phone-number"><?php echo $item['caller'] ?></span>
						<a href="<?php echo site_url("messages/details/{$item['id']}")?>" class="quick-call-button"><span class="replace"><?php echo $item['caller'] ?></span></a>
						<?php if($item['type'] == 'sms'): ?>
						<a href="<?php echo site_url("messages/details/{$item['id']}")?>" class="quick-sms-button"><span class="replace"><?php echo $item['caller'] ?></span></a>
						<?php echo (substr($item['sid'],0,2) == 'MM'?'<span class="phone-number" title="Click to View Media">&nbsp;mms</span>':'') ?>
						<?php endif; ?>

						<div id="quick-call-popup-<?php echo $item['id'] ?>" class="quick-call-popup hide">
							<a href="" class="close action toggler"><span class="replace">close</span></a>
							<p class="call-to-phone"><?php echo $item['caller'] ?></p>
							<ul class="caller-id-phone">
								<li><a href="<?php echo site_url("messages/details/{$item['id']}/callback") ?>" class="call">Call<span class="to hide"><?php echo $item['caller'] ?></span> <span class="callerid hide"><?php echo $item['called'] ?></span><span class="from hide"><?php echo isset($user_numbers[0])? $user_numbers[0]->value : '' ?></span></a></li>
							</ul>
						</div>

						<?php if($item['type'] == 'sms'): ?>
						<div id="quick-sms-popup-<?php echo $item['id'] ?>" class="quick-sms-popup hide">
							<a href="" class="close action sms-toggler"><span class="replace">close</span></a>
							<input class="sms-message" type="text" name="content" />
							<span class="count">160</span>
							<button class="send-button" rel="<?php echo $item['id'] ?>"><span>Send</span></button>
							<img class="sending-sms-loader hide" src="<?php echo asset_url('assets/i/ajax-loader.gif')?>" alt="..." />
							<p class="sms-to-phone hide"><?php echo $item['caller'] ?></p>
							<p class="from-phone hide"><?php echo $item['called'] ?></p>
						</div>
						<?php endif; ?>
					</td>
					<td class="message-playback">
						<?php if($item['type'] != 'sms'): ?>
						<a id="play-<?php echo $item['id'] ?>" href="<?php echo site_url("messages/details/{$item['id']}") ?>" class="play playback-button quick-play">
							<span class="replace">Play</span>
							<span class="call-duration"><?php echo $item['recording_length'] ?></span>
						</a>
						<?php endif; ?>
					</td>
					<td class="message-content message-details-link">
							<span class="transcript"><?php echo $item['short_summary'] ?></span>
							<table id="player-<?php echo $item['id'] ?>" class="player" style="display: none;">
								<tr>
								<td width="100%">
									<div id="player-bar-<?php echo $item['id']?>" class="player-bar">
										<div id="load-bar-<?php echo $item['id']?>" class="load-bar">
											<div id="play-bar-<?php echo $item['id']?>" class="play-bar"></div>
										</div>
									</div>
								</td>
								<td>
									<div class="play-time"><img src="<?php echo asset_url('assets/i/ajax-loader.gif')?>" alt="..." /></div>
								</td>
							</tr></table>
					</td>
					<td class="message-owner">
						<?php if($item['type'] == 'voice'): ?>
						<?php if($item['owner_type'] == 'group'): ?>

						<a href="" class="unassigned assign-button"><span class="replace">Assign to</span></a>
						<span class="owner-name" title="<?php echo format_name($item['assigned_user']) ?>">
							<?php 
								if (!empty($item['assigned_user'])) {
									echo format_name_as_initials($item['assigned_user']);
								} 
							?>
						</span>

						<div class="assign-to-popup hide">
							<a href="" class="close action toggler"><span class="replace">close</span></a>
							<p class="popup-label">Assign to</p>
							<ul class="assign-user-list">
								<?php foreach($active_users as $u): ?>
								<li class="<?php echo ($u->id == $item['assigned'])? 'assigned ' : ''?>user"><a rel="<?php echo $u->id?>" href=""><?php echo format_name($u) . ' (' . format_name_as_initials($u) . ')' ?></a></li>
								<?php endforeach; ?>
							</ul>
						</div>

						<?php endif; ?>
						<?php endif; ?>

					</td>
					<td class="message-status message-details-link">
						<?php if($item['type'] == 'voice'): ?>
						<span class="<?php echo $item['ticket_status'] ?>-status message-status-label"><span class="replace">Open</span></span>
						<?php endif;?>
					</td>
					<td class="message-timestamp message-details-link">
						<div class="unformatted-absolute-timestamp hide">
							<?php echo strtotime($item['received_time']) ?>
						</div>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table><!-- .vbx-items-grid -->

		<div class="vbx-content-menu vbx-content-menu-bottom">
			<ul class="inbox-menu vbx-menu-items">
				<li class="menu-item"><a href="" class="dropdown-select-button link-button"><span>Select</span></a>
					<ul class="hide">
						<li><a class="select select-all" href="">Select All</a></li>
						<li><a class="select select-none" href="">Select None</a></li>
						<li><a class="select select-read" href="">Select Read</a></li>
						<li><a class="select select-unread" href="">Select Unread</a></li>
					</ul>
				</li>
				<li class="menu-item"><a href="" class="delete-button link-button"><span>Delete</span></a></li>
			</ul><!-- .vbx-menu-items -->

			<?php echo $pagination; ?>
		</div><!-- .vbx-content-menu -->

		<?php endif; ?>

	</div><!-- .vbx-content-main -->
