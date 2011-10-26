<h3>About</h3>

<ul>
	<li>Current Version: <?php echo OpenVBX::version() ?></li>
<?php if ($this->tenant->id == VBX_PARENT_TENANT): /* if parent tenant */ ?>
	<li>Schema Version: <?php echo OpenVBX::schemaVersion() ?></li>
	<li>Latest Schema Available: <?php echo OpenVBX::getLatestSchemaVersion(); ?></li>
	<li>Database configuration: <?php echo "{$server_info['mysql_driver']}://{$this->db->username}@{$this->db->hostname}/{$this->db->database}" ?></li>
	<li>Rewrite enabled: <?php echo $rewrite_enabled['value']? 'Yes' : 'No' ?></li>
<?php endif; /* if parent tenant */ ?>
</ul>

<?php if ($this->tenant->id == VBX_PARENT_TENANT): /* if parent tenant 2 */ ?>
	<h3>Server Info</h3>
	<ul>
		<li>Apache Version: <?php echo $server_info['apache_version']; ?></li>
		<li>PHP Version: <?php echo $server_info['php_version']; ?></li>
		<li>MySQL Version: <?php echo $server_info['mysql_version']; ?> using <?php echo $server_info['mysql_driver']; ?> driver</li>
	</ul>
<?php endif; /* if parent tenant 2 */ ?>

<br />

<p>Thanks to everyone involved, you made it better than envisioned!</p>

<?php $this->load->view('settings/license.php'); ?>