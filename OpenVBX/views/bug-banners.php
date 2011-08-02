<?php if(OpenVBX::getTwilioAccountType() == 'Trial'): ?>
<div id="upgrade-account" class="shout-out">
	<p>You are using a Twilio Free Trial Account.  <a href="https://www.twilio.com/user/billing/add-funds">Upgrade your Twilio account</a> to buy your own phone numbers and make outbound calls.</p>
</div><!-- #upgrade-account .shout-out -->
<?php endif; ?>