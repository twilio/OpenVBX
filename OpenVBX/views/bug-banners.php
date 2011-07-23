<?php if(OpenVBX::getTwilioAccountType() == 'Trial'): ?>
<div id="upgrade-account" class="shout-out">
	<p>You are using a Twilio Free Trial Account.  <a href="https://www.twilio.com/user/billing/add-funds">Upgrade your Twilio account</a> to buy your own phone numbers and make outbound calls.</p>
</div><!-- #upgrade-account .shout-out -->
<?php else: ?>
<div id="mobile-app" class="shout-out hide">
	<a href="#" class="close-shout-out close action"><span class="replace">Close</span></a>
	<p>Get the OpenVBX iPhone App and be the coolest kid in your class. <a href="<?php echo site_url('devices#mobile-apps') ?>">Learn more</a></p>
</div><!-- #mobile-app .shout-out -->
<?php endif; ?>