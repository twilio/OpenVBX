<div class="vbx-content-main">
<?php
	$this->load->view('numbers/menu-top');
?>
	<div class="vbx-content-container">
		<div class="vbx-table-section">
		<?php
			if ($count_real_numbers < 1)
			{
				$this->load->view('numbers/numbers-start');
			}
			
			// Incoming Numbers
			$this->load->view('numbers/incoming');
		
			// Available Numbers
			if (count($available_numbers))
			{
				$this->load->view('numbers/available');
			}
		
			// Other Numbers
			if (count($other_numbers))
			{
				$this->load->view('numbers/other');
			}
		?>
		</div><!-- .vbx-table-section -->
	</div><!-- .vbx-content-container -->
</div><!-- .vbx-content-main -->

<div class="vbx-content-hidden" style="display: none">
<?php
	$this->load->view('numbers/dialog-change');
	$this->load->view('numbers/dialog-delete');
	$this->load->view('numbers/dialog-add');
	$this->load->view('numbers/dialog-other-details');
?>
</div>