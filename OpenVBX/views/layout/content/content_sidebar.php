		<div class="yui-b">
				<div id="vbx-sidebar">
				<?php if(isset($counts)): ?>
					<div id="vbx-main-nav">

						<h3 class="vbx-nav-title">Messages</h3>
						<ul id="messages-nav" class="vbx-main-nav-items">
						<?php foreach($counts as $id => $item):
								$inbox = ($id == 0)? 'inbox' : '';
								$inbox_id = ($id == 0)? '' : $id;
								$class = (isset($group) && $id == $group) ? 'selected vbx-nav-item' : 'vbx-nav-item'; ?>
								<li class="<?php echo $class ?>">
										<a title="<?php echo $item->name ?>" href="<?php echo site_url('messages/inbox/' .$inbox_id) ?>">
										<span class="label"><?php echo $item->name ?></span>
										<?php if(isset($item->new) && $item->new > 0): ?>
												<span class="count" rel="<?php echo $id ?>"><?php echo $item->new ?></span>
										<?php endif; ?>
										</a>
								</li>
						<?php endforeach; ?>
						</ul>

						<?php if(!empty($setup_links)): ?>
						<h3 class="vbx-nav-title">Setup</h3>
						<ul id="setup-nav" class="vbx-main-nav-items">
						<?php foreach($setup_links as $link => $name):
								$class = (isset($section) && $section == $link)? 'selected vbx-nav-item' :'vbx-nav-item' ?>
								<li class="<?php echo $class ?>">
										<a title="<?php echo $name ?>" href="<?php echo site_url($link) ?>"><?php echo $name?></a>
								</li>
						<?php endforeach; ?>
						</ul>
						<?php endif; ?>

						<?php if(!empty($log_links)): ?>
						<h3 class="vbx-nav-title">Activity</h3>
						<ul id="activity-nav" class="vbx-main-nav-items">
						<?php foreach($log_links as $link => $name):
								$class = (isset($section) && $section == $link)? 'selected vbx-nav-item' :'vbx-nav-item' ?>
								<li class="<?php echo $class ?>">
										<a title="<?php echo $name ?>" href="<?php echo site_url($link) ?>"><?php echo $name?></a>
								</li>
						<?php endforeach; ?>
						</ul>
						<?php endif; ?>

						<?php if(!empty($admin_links)): ?>
						<h3 class="vbx-nav-title">Admin</h3>
						<ul id="admin-nav" class="vbx-main-nav-items">
						<?php foreach($admin_links as $link => $name):
								$class = (isset($section) && $section == $link)? 'selected vbx-nav-item' :'vbx-nav-item' ?>
								<li class="<?php echo $class ?>">
										<a title="<?php echo $name ?>" href="<?php echo site_url($link) ?>"><?php echo $name?></a>
								</li>
						<?php endforeach; ?>
						</ul>
						<?php endif; ?>

						<?php if(!empty($plugin_menus)): ?>
						<?php foreach($plugin_menus as $name => $links): ?>
						<h3 class="vbx-nav-title"><?php echo $name ?></h3>
						<ul class="vbx-main-nav-items">
						<?php foreach($links as $link => $name): 
								$class = (isset($section) && $section == '/'.$link)? 'selected vbx-nav-item' :'vbx-nav-item' ?>
								<?php if(is_array($name)): ?>
									<?php foreach($name as $sub_id => $sub_name): ?>
										<li class="<?php echo $class ?>">
							                <a title="<?php echo $sub_name ?>" href="<?php echo site_url($link) ?>"><?php echo $sub_id + 1 ?>. <?php echo $sub_name?></a>
										</li>
									<?php endforeach;?>
								<?php else: ?>
								<li class="<?php echo $class ?>">
									<a title="<?php echo $name ?>" href="<?php echo site_url($link) ?>"><?php echo $name?></a>
								</li>
								<?php endif; ?>
						<?php endforeach; ?>
						</ul>
						<?php endforeach; ?>
						<?php endif; ?>
					</div><!-- #vbx-main-nav -->
				<?php endif; ?>
				</div><!-- #vbx-sidebar -->
		</div><!-- .yui-b -->
