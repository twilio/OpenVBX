<div class="vbx-content-main">

	<div class="vbx-content-menu vbx-content-menu-top">
		<h2 class="vbx-content-heading">Connect</h2>
		<ul class="user-groups-menu vbx-menu-items-right">
			<li class="menu-item"><button id="button-add-user" class="inline-button normal-button"><span>Add Number</span></button></li>
			<li class="menu-item"><button id="button-add-group" class="inline-button normal-button"><span>Add Flow</span></button></li>
		</ul>
	</div><!-- .vbx-content-menu -->
	
	<div class="yui-gc connect-section">
		<div class="yui-u first">	

			<div id="numbers-container">
				<h3>Numbers</h3>
				<p>Create, modify, and connect numbers to your flows.</p>
				<ul class="numbers-list"> 
				    <?php if(isset($numbers)) foreach($numbers as $number): ?>
				    <li class="number" rel="<?php echo $number['id'] ?>">
					    <div class="number-utilities">
						    <a class="number-edit edit-mini action-mini" href="#edit"><span class="replace">Edit</span></a>
						    <a class="number-delete trash-mini action-mini" href="#delete"><span class="replace">Delete</span></a>
					    </div>
					    <div class="number-info">
						    <p class="number-name"><?php echo $number['name'] ?></p>
						    <p class="number-nickname">Nickname</p>
					    </div>
						<div class="number-flow">
							<p class="drop-flow">Drop Flow</p>
						</div>
				    </li>
				    <?php endforeach; ?>
				    <li class="number" rel="prototype" style="display:none;">
					    <div class="number-utilities">
						    <a class="number-edit" href="#edit"><span class="replace">Edit</span></a>
						    <a class="number-delete" href="#delete"><span class="replace">Delete</span></a>
					    </div>
					    <div class="number-info">
						    <p class="number-name">(prototype)</p>
						    <p class="number-nickname"></p>
					    </div>
				    </li>
				</ul><!-- .numbers-list -->
			</div><!-- #numbers-container -->

		</div><!-- .yui-u .first -->
		
		<div class="yui-u">

			<div id="flows-container">
				<h3>Flows</h3>
				<p>Drag a flow to a number, drop to connect.</p>

				<ul class="flows-list">
				    <?php if(isset($flows)) foreach($flows as $flow_id => $flow): ?>
					<li class="flow" rel="<?php echo $flow_id ?>">
					    <div class="flow-utilities">
						    <a class="flow-edit" href="#edit"><span class="replace">Edit</span></a>
						    <a class="flow-remove" href="#remove"><span class="replace">Remove</span></a>
					    </div>
					    <div class="flow-info">
						    <p class="flow-name"><?php echo $flow->name ?></p>
					    </div>
					</li>
					<?php endforeach; ?>

					<li class="flow" rel="prototype" style="display:none;">
						<div class="flow-utilities">
							<a class="flow-edit" href="#">Edit Flow</a>
							<a class="flow-remove" href="#">Remove Flow</a>
						</div>

						<div class="flow-info">
							<p class="flow-name">(prototype)</p>
						</div>
					</li>
				</ul>
			</div><!-- #flows-container -->

		</div><!-- .yui-u -->

	</div><!-- .yui-ge 2/3, 1/3 -->

</div><!-- .vbx-content-main -->

