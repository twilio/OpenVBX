<h3>About</h3>

<ul>
	<li><b>Current Version:</b> <?php echo $openvbx_version; ?></li>
<?php if ($this->tenant->id == VBX_PARENT_TENANT): /* if parent tenant */ ?>
	<li><b>Schema Version:</b> <?php echo OpenVBX::schemaVersion() ?></li>
	<li><b>Latest Schema Available:</b> <?php echo OpenVBX::getLatestSchemaVersion(); ?></li>
	<li><b>Site Revision:</b> <?php echo $site_revision; ?></li>
	<li><b>Rewrite enabled:</b> <?php echo $rewrite_enabled['value']? 'Yes' : 'No' ?></li>
<?php endif; /* if parent tenant */ ?>
	<li><b>Client Application:</b><br />&nbsp; - <?php 
		if (empty($client_application_error))
		{
			echo $client_application->voice_url.'<br>&nbsp; - '.$client_application->voice_fallback_url;
		}
		else 
		{
			echo '<b>An error occurred when requesting the Client Application data:</b> '.$client_application_error_message;
		}
	?>		
	</li>
</ul>

<h3>Caching</h3>
<ul>
	<li><b>Caching Enabled:</b> <?php 
		echo ($cache_enabled ? 'true' : 'false'); 
		if ($cache_enabled) 
		{
			$cache_type = $this->cache->friendly_name();
			$more_info = $this->cache->more_info();
			if (!empty($more_info))
			{
				$cache_type = '<a href="'.$more_info.'">'.$cache_type.'</a>';
			}
			echo ', '.$cache_type;
		}
	?></li>
	<li><b>API Caching Enabled:</b> <?php 
		echo ($api_cache_enabled ? 'true' : 'false'); 
		if ($api_cache_enabled) 
		{
			$api_cache_type = $this->api_cache->friendly_name();
			$api_more_info = $this->api_cache->more_info();
			if (!empty($api_more_info))
			{
				$api_cache_type = '<a href="'.$api_more_info.'" onclick="window.open(this.href); return false;">'.$api_cache_type.'</a>';
			}
			echo ', '.$api_cache_type;
		}
	?></li>
</ul>

<p><a href="<?php echo current_url(); ?>/caches/flush">&raquo; Flush All Caches</a></p>

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
	</ul>
<?php endif; /* if parent tenant 2 */ ?>

<h3>Props!</h3>
<p>Thanks to everyone involved, you made it better than envisioned!</p>

<?php $this->load->view('settings/license.php'); ?>