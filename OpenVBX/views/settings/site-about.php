<h3>About</h3>

<ul>
	<li><b>Current Version:</b> <?php echo $openvbx_version; ?></li>
<?php if ($this->tenant->id == VBX_PARENT_TENANT): /* if parent tenant */ ?>
	<li><b>Schema Version:</b> <?php echo OpenVBX::schemaVersion() ?></li>
	<li><b>Latest Schema Available:</b> <?php echo OpenVBX::getLatestSchemaVersion(); ?></li>
	
	<li><b>Rewrite enabled:</b> <?php echo $rewrite_enabled['value']? 'Yes' : 'No' ?></li>
<?php endif; /* if parent tenant */ ?>
</ul>

<?php if ($this->tenant->id == VBX_PARENT_TENANT): /* if parent tenant 2 */ ?>
	<h3>Server Info</h3>
	<ul>
		<li><b>Apache Version:</b> <?php echo $server_info['apache_version']; ?></li>
		<li><b>PHP Version:</b> <?php echo $server_info['php_version']; ?></li> 
		<li><b>PHP Interface:</b> <?php echo $server_info['php_sapi']; 
			if (!empty($_SERVER['GATEWAY_INTERFACE']))
			{
				echo ' ('.$_SERVER['GATEWAY_INTERFACE'].')';
			}
		?></li>
		<li><b>MySQL Version:</b> <?php echo $server_info['mysql_version']; ?></li>
		<li><b>Database configuration:</b> <?php echo "{$server_info['mysql_driver']}://{$this->db->username}@{$this->db->hostname}/{$this->db->database}" ?></li>
		<li><b>System OS:</b> <?php echo $server_info['system_version']; ?></li>
		<li><b>Current Url:</b> <?php echo $server_info['current_url']; ?></li>
		<li><b>Client Application:</b><br />&nbsp; - <?php echo $client_application->voice_url; ?><br />&nbsp; - <?php echo $client_application->voice_fallback_url; ?></li>
	</ul>
<?php endif; /* if parent tenant 2 */ ?>

<br />

<p>Thanks to everyone involved, you made it better than envisioned!</p>

<?php $this->load->view('settings/license.php'); ?>