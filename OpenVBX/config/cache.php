<?php

/**
 * -------------------------------------------------------------------
 * OpenVBX Cache
 * -------------------------------------------------------------------
 * Define the cache settings for the OpenVBX Caches
 * @see: https://github.com/twilio/OpenVBX/wiki/Object-Cache for more
 * information on the caching system details
 * @since: OpenVBX 1.2
 */

/**
 * Enable/disable the cache system
 */
$config['cache']['cache_enabled'] = true;

/**
 * Currently supported caches:
 * - memory: use memory for a per-page load cache
 * - apc: use APC for the memory cache
 * - memcached: use memcached for the memory cache
 * 
 * @todo auto-detect cache availability
 */
$config['cache']['cache_type'] = 'memory';

/**
 * Default expiration time for cached items
 * Does not effect the API Cache
 */
$config['cache']['default_expires'] = 3600;

/**
 * Default Memcache Settings
 * Only applicable if Memcache is in use, otherwise
 * this can sefely be ignored
 */
$config['cache']['memcache'] = array(
	'servers' => array(
		'127.0.0.1'
	),
	'port' => '11211'
);

/**
 *--------------------------------------------------------------------------
 * Local config overrides
 *--------------------------------------------------------------------------
 *
 * Sometimes your local environment just needs some things to be overridden
 *
 */
if (is_file(APPPATH.'config/cache-local.php')) {
	include_once(APPPATH.'config/cache-local.php');
}