<div id="dialer" class="closed">

	<div class="client-ui-tab open">
		
		<div class="client-ui-bg-overlay"><!-- leave me alone! --></div>
		<div class="client-ui-inset">
			<div id="client-ui-tab-status">
				<div class="client-ui-tab-wedge"><a href="#dialer"><span class="symbol">&raquo;</span> Hide</a></div>
				<div class="client-ui-tab-status-inner">
					<div class="mic"></div>
					<h3 class="client-ui-timer">0:00</h3>
				</div><!-- .client-ui-tab-status-inner -->
			</div><!-- #client-ui-tab-status -->
		</div><!-- #client-ui-tab-inset -->
		
	</div><!-- .client-ui-tab .open -->

	<div class="client-ui-content">
		<div class="client-ui-bg-overlay"><!-- leave me alone! --></div>
		<div class="client-ui-inset">

			<div id="client-make-call">
				<?php $this->load->view('dialer/make-call-form'); ?>					
			</div><!-- #client-make-call -->

			<div id="client-on-call" style="display: none;">
				<?php $this->load->view('dialer/on-call-pad')?>
			</div><!-- #client-on-call -->
		</div>
	</div>
	
</div><!-- /dialer -->