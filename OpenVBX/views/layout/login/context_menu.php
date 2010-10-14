		<div id="login-context-menu" class="context-menu">

			<div class="notify <?php echo (isset($error) && !empty($error))? '' : 'hide' ?>">

				<p class="message">
					<?php if(isset($error) && $error): ?>
						<?php echo $error ?>
					<?php endif; ?>
					<a href="" class="close action"><span class="replace">Close</span></a>
				</p>

			</div><!-- .notify -->

		</div><!-- #login-context-menu .context-menu -->

