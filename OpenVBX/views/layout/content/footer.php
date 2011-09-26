		<?php 
		/** Updated, Disruptive Technologies, for Tropo VBX conversion **/
		// Check for tropo/twilio
		$ci = &get_instance();
		if ($ci->twilio_sid) {
			$twilio = true;
		} else {
			$twilio = false;
		}
		if ($ci->tropo_username) {
			$tropo = true;
		} else {
			$tropo = false;
		}
		/** End Disruptive Technologies code **/
		?>
		<div id="ft">
			<p class="copyright">OpenVBX &bull; <em>v</em><?php echo OpenVBX::version() ?> r<?php echo OpenVBX::schemaVersion() ?> &mdash; Powered by 
			<?php if ($twilio): ?>
			<a href="http://twilio.com/">Twilio Inc.</a> &bull; <a href="http://www.twilio.com/legal/tos">Terms</a> &bull; <a href="http://www.twilio.com/legal/privacy">Privacy</a>
			<?php endif;
			if ($twilio && $tropo) echo " and ";
			if ($tropo): ?>
			<a href="http://tropo.com/">Tropo</a> a Voxeo Corporation brand &bull; <a href="https://www.tropo.com/policies/home.jsp">Terms</a> &bull; <a href="https://www.tropo.com/policies/home.jsp?doc=PrivacyPolicy">Privacy</a>
			<?php endif; ?>
			</p>
		</div><!-- #ft -->

